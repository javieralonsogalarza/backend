<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use League\Glide\Responses\LaravelResponseFactory;
use League\Glide\ServerFactory;
use Illuminate\Support\Facades\Storage;

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

    // Guardar la imagen en la carpeta 'public/uploads/img' usando Storage
    Storage::disk('public')->put($folderPath . '/' . $filename, $imageData);

    // Verificar si el archivo se ha guardado correctamente
    if (Storage::disk('public')->exists($folderPath . '/' . $filename)) {
        return response()->json([
            'status' => 'success',
            'filepath' => Storage::url($folderPath . '/' . $filename)
        ]);
    } else {
        return response()->json([
            'status' => 'error',
            'message' => 'No se pudo guardar la imagen.'
        ], 500);
    }
}


}
