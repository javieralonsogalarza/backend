<?php

namespace App\Imports;

use App\Models\Categoria;
use App\Models\Jugador;
use App\Models\TipoDocumento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;

class JugadorsImport implements ToCollection, WithHeadingRow, WithProgressBar
{
    use Importable;

    private $Result;
    public function __construct()
    {
        $this->Result =  (object)['Success' => false, 'Message' => null, 'Errors' => [], 'Registers' => 0];
    }

    //RETORNA UN LISTADO DE JUGADORES
    /*public function model(array $row)
    {
        return new Jugador([
            'name'  => $row['name'],
            'email' => $row['email'],
            'at'    => $row['at_field'],
        ]);
    }*/

    public function collection(Collection $rows)
    {
        try {

            $CategoriaBase = false;  $Celular = false; $Edad = false; $Altura = false; $Peso = false;

           if(count($rows) > 0)
           {
               $Headers = array_keys($rows->first()->toArray());

               if(!in_array("nombres", $Headers)){
                   $this->Result->Message = "La cabecera Nombres no se encontró en el archivo";
               }else if(!in_array("apellidos", $Headers)){
                   $this->Result->Message = "La cabecera Apellidos no se encontró en el archivo";
               }else if(!in_array("tipo_documento", $Headers)){
                   $this->Result->Message = "La cabecera Tipo Documento no se encontró en el archivo";
               }else if(!in_array("nro_documento", $Headers)){
                   $this->Result->Message = "La cabecera Nro Documento no se encontró en el archivo";
               }else if(!in_array("sexo", $Headers)){
                   $this->Result->Message = "La cabecera Sexo no se encontró en el archivo";
               }

               if($this->Result->Message == null)
               {
                   if(in_array("categoria_base", $Headers)) $CategoriaBase = true;
                   if(in_array("celular", $Headers)) $Celular = true;
                   if(in_array("edad", $Headers)) $Edad = true;
                   if(in_array("altura_m", $Headers)) $Altura = true;
                   if(in_array("peso_kg", $Headers)) $Peso = true;

                   $TipoDocumentos = TipoDocumento::all();

                   foreach ($rows as $key => $row)
                   {
                       if( ($row['nombres'] != null && trim($row['nombres']) != "") && ($row['apellidos'] != null && trim($row['apellidos']) != "") &&
                           ($row['tipo_documento'] != null && trim($row['tipo_documento'])) && ($row['nro_documento'] != null && trim($row['nro_documento']))
                           && ($row['sexo'] != null && trim($row['sexo']) != "")
                       ) {
                               $TipoDocumento = trim(Str::upper($row['tipo_documento']));

                               if(in_array($TipoDocumento, $TipoDocumentos->pluck('nombre')->toArray())){
                                   $Validator = Validator::make($row->toArray(), [
                                       'nombres' => 'required|max:250',
                                       'apellidos' => 'required|max:250',
                                       'tipo_documento' => 'required',
                                       'nro_documento' => 'required|'.($TipoDocumento == "DNI" ? 'digits:8|numeric' : ($TipoDocumento == "PASAPORTE" ? 'digits:9|numeric' : 'digits:12') ).'|unique:jugadors,nro_documento,NULL,id,comunidad_id,'.Auth::guard('web')->user()->comunidad_id.',deleted_at,NULL',
                                       'edad' => 'nullable|numeric|digits_between:1,2',
                                       'sexo' => 'required|string|in:M,F',
                                       'celular' => 'nullable|max:15',
                                       'altura_m' => 'nullable|numeric|min:0|max:9.99|regex:/^\d+(\.\d{1,2})?$/',
                                       'peso_kg' => 'nullable|numeric|min:0|max:999.99|regex:/^\d+(\.\d{1,2})?$/'
                                   ], [
                                       'sexo.in' => 'El campo sexo acepta solo valores M o F',
                                       //'tipo_documento_id.required' => 'El campo tipo documento es obligatorio.',
                                       'nro_documento.required' => 'El campo número de documento para '.($TipoDocumento == "DNI" ? "DNI" : ($TipoDocumento == "PASAPORTE" ? 'PASAPORTE' : 'CARNET DE EXTRANJERÍA')).' es obligatorio.',
                                       'nro_documento.digits' => 'El campo número de documento para '.($TipoDocumento == "DNI" ? "DNI debe ser númerico y" : ($TipoDocumento == "PASAPORTE" ? 'PASAPORTE debe ser númerico y' : 'CARNET DE EXTRANJERÍA')).' debe terner :digits digitos.',
                                       'nro_documento.regex' => 'El formato para '.($TipoDocumento == "DNI" ? 'DNI' : ($TipoDocumento == "PASAPORTE" ? 'PASAPORTE' : 'CARNET DE EXTRANJERÍA').' no es válido.')
                                   ]);

                                   if(!$Validator->fails())
                                   {
                                       $Categoria = null;

                                       if($CategoriaBase)
                                       {
                                           if($row['categoria_base'] != null && $row['categoria_base'] != "") {
                                               $Categoria = Categoria::where('nombre', trim($row['categoria_base']))->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->first();
                                               if($Categoria == null){
                                                   $this->Result->Errors[] = ['key' => ($key + 1), 'Message' => 'No se le puedo asignar al jugador ' .($row['nombres'].' '.$row['apellidos']). ' la categoria '.$row['categoria_base'].' porque no existe.', 'error' => null];
                                               }
                                           }
                                       }

                                       $TipoDocumento = TipoDocumento::where('nombre', trim($row['tipo_documento']))->first();

                                       if($TipoDocumento != null)
                                       {
                                           Jugador::create([
                                               'comunidad_id' => Auth::guard('web')->user()->comunidad_id,
                                               'nombres' => $row['nombres'],
                                               'apellidos' => $row['apellidos'],
                                               'tipo_documento_id' => $TipoDocumento->id,
                                               'nro_documento' => $row['nro_documento'],
                                               'categoria_id' => $Categoria != null ? $Categoria->id : null,
                                               'edad' => $Edad ? $row['edad'] : null,
                                               'sexo' => $row['sexo'] == "Masculino" ? "M" : ($row['sexo'] == "Femenino" ? "F" : ($row['sexo'] == "M" ? "M" : ($row['sexo'] == "F" ? "F" : null))),
                                               'celular' => $Celular ? $row['celular'] : null,
                                               'altura' => $Altura ? $row['altura_m'] : null,
                                               'peso' => $Peso ? $row['peso_kg'] : null,
                                               'user_create_id' => Auth::guard('web')->user()->id
                                           ]);

                                           $this->Result->Registers++;

                                       }else{
                                           $this->Result->Errors[] = ['key' => ($key + 1), 'Message' => 'No se pudo registrar al jugador ' .($row['nombres'].' '.$row['apellidos']). ' porque el tipo de documento '.$row['tipo_documento'].' no es válido.', 'error' => null];
                                       }

                                   }else{
                                       $errors = [];
                                       foreach ($Validator->errors()->messages() as $messages) {foreach ($messages as $error){ $errors[] = ['error' => $error];}}
                                       $this->Result->Errors[] = ['key' => ($key + 1), 'Message' => 'No se pudo registrar al jugador ' .($row['nombres'].' '.$row['apellidos']). ' porque : ', 'error' => $errors];
                                   }
                               }else{
                                   $this->Result->Errors[] = ['key' => ($key + 1), 'Message' => 'No se pudo registrar al jugador ' .($row['nombres'].' '.$row['apellidos']). ' porque el tipo documento '.$row['tipo_documento'].' no es válido.', 'error' => null];
                               }
                           }
                       }

                   if($this->Result->Registers > 0 && count($this->Result->Errors) == 0 && $this->Result->Message == null){ $this->Result->Success = true; }
                   else if($this->Result->Registers == 0 && count($this->Result->Errors) == 0 && $this->Result->Message == null){ $this->Result->Message = "No se realizo ninguna insercción, debido a que algunos campos obligatorios estan vacios o no son válidos."; }
               }
           }
        }catch (\Exception $e)
        {
            $this->Result->Message = $e->getMessage();
        }
    }

    public function result()
    {
        return $this->Result;
    }

    //ESPECIFICAR DESDE QUE FILA INICIA EL HEADER
    public function headingRow(): int
    {
        return 1;
    }

    //ESPECIFICAR QUE HACE LA SEPARACIÓN DE COLUMNAS
    /*public function getCsvSettings(): array
    {
        return [
            'input_encoding' => 'UTF-8', //ENCODING UTF-8
            //'delimiter' => ""
        ];
    }*/

}
