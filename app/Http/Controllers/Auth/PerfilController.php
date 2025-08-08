<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Comunidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PerfilController extends Controller
{
    protected $viewName = 'perfil';

    public function index()
    {
        return view('auth'.'.'.$this->viewName.'.index', ['ViewName' => ucfirst($this->viewName)]);
    }

    public function partialView()
    {
        $entity = Comunidad::find(Auth::guard('web')->user()->comunidad->id);
        return view('auth.'.$this->viewName.'.ajax.partialView', ['Model' => $entity, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function store(Request $request)
    {
        $entity = null; $imagen_path = null; $imagen_reportes_path = null;

        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $Validator = Validator::make($request->all(), [
                'nombre' => 'required|max:150|unique:comunidads,nombre,'.(Auth::guard('web')->user()->comunidad->id).',id,deleted_at,NULL',
                'color_primario' => 'required|max:15',
                'color_secundario' => 'required|max:15',
                'color_alternativo' => 'required|max:15',
                'titulo_fuente' => 'required|max:50',
                'parrafo_fuente' => 'required|max:50',
                'telefono' => 'nullable|max:15',
                'email' => 'required|email|max:150',
                'facebook' => 'nullable|max:150',
                'twitter' => 'nullable|max:150',
                'instagram' => 'nullable|max:150'
            ]);

            if (!$Validator->fails()){

                if($request->id != 0) $entity = Comunidad::find(Auth::guard('web')->user()->comunidad->id);

                if($entity != null)
                {
                    if($request->file('imagen')) $imagen_path = $request->imagen->store('public/uploads/comunidad');
                    if($request->file('imagen_reportes')) $imagen_reportes_path = $request->imagen_reportes->store('public/uploads/comunidad');

                    $request->merge([
                        'slug' => Str::slug($request->nombre),
                        'imagen_path' => $entity != null ? ($imagen_path ?? $entity->imagen_path) : ($imagen_path ?? null),
                        'imagen_reportes_path' => $entity != null ? ($imagen_reportes_path ?? $entity->imagen_reportes_path) : ($imagen_reportes_path ?? null)
                    ]);

                    $request->merge(['user_update_id' =>  Auth::guard('web')->user()->id]);
                    $entity->update($request->all());

                    DB::commit();

                    $Result->Success = true;

                }else{
                    $Result->Message = "El modelo que intenta actualizar no existe.";
                }
            }

            $Result->Errors = $Validator->errors();

        }catch (\Exception $e)
        {
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }
    
    
    

    public function getImagenComunidad($comunidad_id, $tipo = 'imagen')
    {
        try {
            $comunidad = Comunidad::find($comunidad_id);
            
            if (!$comunidad) {
                return response()->json(['error' => 'Comunidad no encontrada'], 404);
            }

            $imagen_path = null;
            
            if ($tipo === 'reportes') {
                $imagen_path = $comunidad->imagen_reportes_path;
            } else {
                $imagen_path = $comunidad->imagen_reportes_path;
            }

            if (!$imagen_path) {
                // Retornar imagen por defecto
                $default_path = public_path('upload/image/default.png');
                if (file_exists($default_path)) {
                    return response()->file($default_path);
                }
                return response()->json(['error' => 'Imagen no encontrada'], 404);
            }

            // Construir la ruta completa de la imagen
            $full_path = storage_path('app/' . $imagen_path);
            
            if (!file_exists($full_path)) {
                // Si no existe en storage, buscar en public
                $public_path = public_path('img/' . basename($imagen_path));
                if (file_exists($public_path)) {
                    return response()->file($public_path);
                }
                
                // Retornar imagen por defecto
                $default_path = public_path('upload/image/default.png');
                if (file_exists($default_path)) {
                    return response()->file($default_path);
                }
                
                return response()->json(['error' => 'Imagen no encontrada'], 404);
            }

            return response()->file($full_path);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener la imagen: ' . $e->getMessage()], 500);
        }
    }

    public function getImagenUrl($comunidad_id, $tipo = 'imagen')
    {
        try {
            $comunidad = Comunidad::find($comunidad_id);
            
            if (!$comunidad) {
                return response()->json(['error' => 'Comunidad no encontrada'], 404);
            }

            $imagen_path = null;
            
            if ($tipo === 'reportes') {
                $imagen_path = $comunidad->imagen_reportes_path;
            } else {
                $imagen_path = $comunidad->imagen_reportes_path;
            }

            $url = null;
            
            if ($imagen_path) {
                // Generar URL pública para la imagen
                $url = url('/public-imagen/' . $comunidad_id . '/' . $tipo);
            } else {
                // URL de imagen por defecto
                $url = url('/upload/image/default.png');
            }

            return response()->json([
                'success' => true,
                'url' => $url,
                'imagen_path' => $imagen_path
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener la URL de la imagen: ' . $e->getMessage()], 500);
        }
    }

}
