<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Habitacion;
use App\Models\HabitacionImagen;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImagenHabitacionController extends Controller
{
    public function show(string $id)
    {
        try {
            $habitacion = Habitacion::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $habitacion->load('imagenes'),
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Habitación no encontrada con el ID = '.$id,
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'habitacion_id' => 'required|integer|exists:habitaciones,id',
                'imagenes' => 'required|array',
                'imagenes.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $habitacion = Habitacion::findOrFail($request->habitacion_id);

            $imagenesCreadas = [];
            foreach ($request->file('imagenes') as $file) {
                $url = asset('storage/'.$file->store('habitaciones', 'public'));
                $imagenesCreadas[] = HabitacionImagen::create([
                    'url' => $url,
                    'habitacion_id' => $habitacion->id,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Imágenes agregadas correctamente',
                'data' => $imagenesCreadas,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $imagen = HabitacionImagen::findOrFail($id);

            if ($request->hasFile('imagen')) {
                Storage::disk('public')->delete($imagen->url);

                $path = $request->file('imagen')->store('habitaciones', 'public');
                $imagen->update([
                    'url' => asset('storage/'.$path),
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Imagen actualizada correctamente',
                'data' => $imagen,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Imagen no encontrada',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $imagen = HabitacionImagen::findOrFail($id);

            Storage::disk('public')->delete($imagen->url);
            $imagen->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Imagen eliminada correctamente',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Imagen no encontrada',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }
}
