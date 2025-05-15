<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use League\Glide\Responses\LaravelResponseFactory;
use League\Glide\ServerFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Partido;


class ImagenController extends Controller
{
    public function show(Filesystem $filesystem, Request $request, $path)
    {
        $server = ServerFactory::create([
            'response' => new LaravelResponseFactory($request),
            'source' => $filesystem->getDriver(),
            'cache' => $filesystem->getDriver(),
            'cache_path_prefix' => '.glide-cache',
        ]);

        return $server->getImageResponse($path, $request->all());
    }
    
public function uploadImage(Request $request)
{
        try {
    // Validar que la solicitud tenga la imagen y el ID del jugador
    $request->validate([
        'image' => 'required|string', // Imagen en base64
        'jugador_id' => 'required|integer'
    ]);

    // Obtener el ID del jugador y la imagen en base64
    $jugadorId = $request->input('jugador_id');
    $imageData = $request->input('image');

    // Decodificar la imagen base64
    list($type, $imageData) = explode(';', $imageData);
    list(, $imageData) = explode(',', $imageData);
    $imageData = base64_decode($imageData);

    // Crear el nombre del archivo usando el ID del jugador
    $filename = 'jugador_' . $jugadorId . '.png';
    $folderPath = 'uploads/img';

            // Asegurarse de que la carpeta exista
            if (!Storage::disk('public')->exists($folderPath)) {
                Storage::disk('public')->makeDirectory($folderPath, 0775, true);
            }

            // Guardar la imagen
    Storage::disk('public')->put($folderPath . '/' . $filename, $imageData);

    // Verificar si el archivo se ha guardado correctamente
    if (Storage::disk('public')->exists($folderPath . '/' . $filename)) {
        return response()->json([
            'status' => 'success',
            'filepath' => Storage::url($folderPath . '/' . $filename)
        ]);
            }

            throw new \Exception('No se pudo guardar la imagen.');

        } catch (\Exception $e) {
            Log::error('Error en uploadImage: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

public function uploadImageSegunda(Request $request)
{
    try {
        $validator = \Validator::make($request->all(), [
            'image' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!str_starts_with($value, 'data:image')) {
                        $fail('El formato de la imagen no es válido');
                    }
                },
            ],
            'torneocategoria_id' => 'required|integer',
            'torneo' => 'required|string',
            'categoria' => 'required|string',
            'partido' => 'required|integer',
            'partido_id' => 'required|integer',
            'categoria_id' => 'required|integer',
            'ronda' => 'nullable|integer',
            'grupo' => 'nullable|string'
        ], [
            'image.required' => 'La imagen es requerida',
            'image.string' => 'La imagen debe ser una cadena base64',
            'torneocategoria_id.required' => 'El ID de torneo-categoría es requerido',
            'torneocategoria_id.integer' => 'El ID de torneo-categoría debe ser un número',
            'torneo.required' => 'El nombre del torneo es requerido',
            'categoria.required' => 'La categoría es requerida',
            'partido.required' => 'El partido es requerido',
            'partido_id.required' => 'El ID del partido es requerido',
            'partido_id.integer' => 'El ID del partido debe ser un número',
            'categoria_id.required' => 'El ID de la categoría es requerido',
            'categoria_id.integer' => 'El ID de la categoría debe ser un número',
            'ronda.required' => 'La ronda es requerida',
            'ronda.integer' => 'La ronda debe ser un número'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $imageData = $request->input('image');

        try {
            list($type, $imageData) = explode(';', $imageData);
            list(, $imageData) = explode(',', $imageData);
            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                throw new \Exception('Error decodificando la imagen base64');
            }
        } catch (\Exception $e) {
            Log::error('Error procesando imagen: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error procesando la imagen'
            ], 400);
        }

        $torneoFolder = $this->sanitizeFilename($request->input('torneo')) . '-' . $request->input('torneocategoria_id');
        $categoriaFolder = $this->sanitizeFilename($request->input('categoria')) . '-' . $request->input('categoria_id');
        
        // Aquí implementamos la lógica para determinar el nombre de la ronda
        $rondaNumero = $request->input('ronda') ? $request->input('ronda') : 0 ;
        $nombreRonda = '';
        
        switch ($rondaNumero) {
            case 32:
                $nombreRonda = 'Ronda-de-32';
                break;
            case 16:
                $nombreRonda = 'Ronda-de-16';
                break;
            case 8:
                $nombreRonda = 'Octavos-de-final';
                break;
            case 4:
                $nombreRonda = 'Cuartos-de-final';
                break;
            case 2:
                $nombreRonda = 'Semifinal';
                break;
            case 1:
                $nombreRonda = 'Final';
                break;
            default:
                $nombreRonda = 'Fase-de-Grupos';
        }
        
        // Ahora usamos el nombre descriptivo, pero mantenemos el número para referencia
        $rondaFolder = $nombreRonda . '-' . $rondaNumero;
        
        $grupoFolder = $request->input('grupo') ? '' . $this->sanitizeFilename($request->input('grupo')) : null;
        $partidoFolder = 'partido-' . $request->input('partido_id');

        $folderPath = ['segundo_modulo', 'uploads', $torneoFolder, $categoriaFolder, $rondaFolder];
        
        if ($grupoFolder) {
            $folderPath[] = $grupoFolder;
        }
        
        $folderPath[] = $partidoFolder;
        $folderPath = implode('/', $folderPath);

        $filename = 'imagen.png';
        $fullPath = $folderPath . '/' . $filename;

        Log::info('Intentando guardar imagen', [
            'folderPath' => $folderPath,
            'filename' => $filename
        ]);

        if (!Storage::disk('public')->exists($folderPath)) {
            Storage::disk('public')->makeDirectory($folderPath, 0775, true);
        }

        if (Storage::disk('public')->exists($fullPath)) {
            Storage::disk('public')->delete($fullPath);
        }

        if (Storage::disk('public')->put($fullPath, $imageData)) {
            return response()->json([
                'status' => 'success',
                'filepath' => Storage::url($fullPath),
                'details' => [
                    'torneocategoria_id' => $request->input('torneocategoria_id'),
                    'torneo' => $request->input('torneo'),
                    'categoria' => $request->input('categoria'),
                    'categoria_id' => $request->input('categoria_id'),
                    'partido' => $request->input('partido'),
                    'partido_id' => $request->input('partido_id'),
                    'ronda' => $request->input('ronda'),
                    'nombreRonda' => $nombreRonda,
                    'grupo' => $request->input('grupo')
                ]
            ]);
        }

        throw new \Exception('No se pudo guardar la imagen');

    } catch (\Exception $e) {
        Log::error('Error en uploadImageSegunda: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}

    protected function sanitizeFilename($string)
    {
        // Convertir a minúsculas
        $string = strtolower($string);
        
        // Eliminar caracteres especiales y espacios
        $string = preg_replace('/[^a-z0-9\-_]/', '_', $string);
        
        // Eliminar múltiples guiones bajos consecutivos
        $string = preg_replace('/_+/', '_', $string);
        
        // Eliminar guiones bajos al principio y al final
        $string = trim($string, '_');

        // Acortar si es demasiado largo
        return substr($string, 0, 30);
    }
    
    
        
    public function actualizarReporteJsonGenerado(Request $request)
{
    $Result = (object)['Success' => false, 'Message' => null];

    try {
        // Validar que existe el partido_id en el request
        $validator = Validator::make($request->all(), [
            'partido_id' => 'required|exists:partidos,id',
        ]);

        if ($validator->fails()) {
            $Result->Message = 'ID de partido inválido o no existe';
            return response()->json($Result);
        }

        // Obtener el partido y actualizar el campo
        $partido = Partido::findOrFail($request->partido_id);
        
        // Verificar que el usuario tiene permiso (opcional, si quieres restringir el acceso)
        // if ($partido->comunidad_id != Auth::guard('web')->user()->comunidad_id) {
        //     $Result->Message = 'No tiene permisos para modificar este partido';
        //     return response()->json($Result);
        // }
        
        $partido->reporte_json_generado = now();
        
        if ($partido->save()) {
            $Result->Success = true;
            $Result->Message = 'Reporte JSON actualizado correctamente';
            $Result->fecha = $partido->reporte_json_generado;
        } else {
            $Result->Message = 'No se pudo actualizar el registro';
        }
    } catch (\Exception $e) {
        $Result->Message = 'Error: ' . $e->getMessage();
    }

    return response()->json($Result);
}



/**
 * Verifica si el campo reporte_json_generado está marcado
 *
 * @param int $partido_id
 * @return \Illuminate\Http\JsonResponse
 */
public function verificarReporteJsonGenerado(Request $request)
{
    $Result = (object)['Success' => false, 'Message' => null, 'reporteGenerado' => false, 'fecha' => null];

    try {
        // Validar que existe el partido_id en el request
        $validator = Validator::make($request->all(), [
            'partido_id' => 'required|exists:partidos,id',
        ]);

        if ($validator->fails()) {
            $Result->Message = 'ID de partido inválido o no existe';
            return response()->json($Result);
        }

        $partido = Partido::findOrFail($request->partido_id);
        $Result->Success = true;
        $Result->reporteGenerado = !is_null($partido->reporte_json_generado);
        $Result->fecha = $partido->reporte_json_generado;
    } catch (\Exception $e) {
        $Result->Message = 'Error: ' . $e->getMessage();
    }

    return response()->json($Result);
}
}