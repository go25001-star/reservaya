<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RolEnum;
use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\StaffHotel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminHotelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $userAuthId = auth('api')->id();

            $staffHotel = StaffHotel::where('user_id', $userAuthId)
                ->where('estado', true)
                ->with('hotel:id,nombre,imagen')
                ->get()
                ->pluck('hotel');

            if ($staffHotel->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se encontraron hoteles registrados ',
                ], 404);
            }

            return response()->json([
                'status' => 'ok',
                'data' => $staffHotel,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error Interno del Servidor',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            if (! $request->has('hotel')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El objeto hotel es requerido',
                ], 422);
            }

            $hotelData = json_decode($request->hotel, true);

            if (! $hotelData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El formato del JSON es inválido',
                ], 422);
            }

            $data = [
                'nombre' => $hotelData['nombre'] ?? null,
                'descripcion' => $hotelData['descripcion'] ?? null,
                'direccion' => $hotelData['direccion'] ?? null,
                'email' => $hotelData['email'] ?? null,
                'telefono' => $hotelData['telefono'] ?? null,
                'telefono2' => $hotelData['telefono2'] ?? null,
                'telefono3' => $hotelData['telefono3'] ?? null,
                'fecha_asignacion' => $hotelData['fecha_asignacion'] ?? null,
            ];

            $validator = Validator::make($data, [
                'nombre' => 'required|string',
                'descripcion' => 'required|string',
                'direccion' => 'required|string',
                'email' => 'required|email',
                'telefono' => 'required|string',
                'telefono2' => 'nullable|string',
                'telefono3' => 'nullable|string',
                'fecha_asignacion' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Faltan campos requeridos',
                ], 422);
            }

            DB::beginTransaction();

            $user = auth('api')->user();

            if (! $user->hasRole(RolEnum::PROPIETARIO->value)) {
                $user->assignRole(RolEnum::PROPIETARIO->value);
            }

            $imagenPath = null;
            if ($request->hasFile('imagen')) {
                $imagenPath = $request->file('imagen')->store('hoteles', 'public');
            }

            $hotel = Hotel::create([
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'],
                'direccion' => $data['direccion'],
                'imagen' => $imagenPath,
                'email' => $data['email'],
                'telefono' => $data['telefono'],
                'telefono2' => $data['telefono2'],
                'telefono3' => $data['telefono3'],
            ]);

            StaffHotel::create([
                'hotel_id' => $hotel->id,
                'user_id' => $user->id,
                'rol' => RolEnum::PROPIETARIO->value,
                'fecha_asignacion' => $data['fecha_asignacion'],
                'estado' => true,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Hotel creado exitosamente.',
                'data' => $hotel->load('staffhotels:id,hotel_id,user_id,rol'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            if ($imagenPath) {
                Storage::disk('public')->delete($imagenPath);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $AuthUserId = auth('api')->id();

            $verificarUsuario = StaffHotel::where('user_id', $AuthUserId)
                ->where('hotel_id', $id)
                ->exists();

            if (! $verificarUsuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permisos para ver este hotel',
                ], 403);
            }

            $hotel = Hotel::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $hotel,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hotel no encontrado',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request->validate(
            [
                'nombre' => ['required', 'string'],
                'descripcion' => ['required', 'string'],
                'direccion' => ['required', 'string'],
                'email' => ['required', 'email'],
                'telefono' => ['required', 'string'],
                'telefono2' => ['nullable', 'string'],
                'telefono3' => ['nullable', 'string'],
            ]);

        try {
            $AuthUserId = auth('api')->id();

            $verificarUsuario = StaffHotel::where('user_id', $AuthUserId)
                ->where('hotel_id', $id)
                ->where('rol', RolEnum::PROPIETARIO->value)
                ->exists();

            if (! $verificarUsuario) {
                return response()->json(['status' => 'error',
                    'message' => 'No tienes permisos para actualizar los datos del hotel'], 403);
            }

            $hotel = Hotel::findOrFail($id);

            $hotel->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'direccion' => $request->direccion,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'telefono2' => $request->telefono2,
                'telefono3' => $request->telefono3,
            ]);

            return response()->json([
                'status' => 'ok',
                'message' => 'Hotel actualizado correctamente',
                'data' => $hotel,
            ], 200);

        } catch (ModelNotFoundException $m) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hotel no encontrado con el ID = '.$id,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }

    }

    public function destroy(string $hotel_id)
    {
        try {

            $AuthUserId = auth('api')->id();

            $verificarUsuario = StaffHotel::where('user_id', $AuthUserId)->
            where('hotel_id', $hotel_id)
                ->where('rol', RolEnum::PROPIETARIO->value)
                ->exists();

            if (! $verificarUsuario) {
                return response()->json(['status' => 'error',
                    'message' => 'No tienes permisos para desactivar el hotel',
                ], 403);
            }

            $hotel = Hotel::with('staffHotels')->findOrFail($hotel_id);

            $hotel->update(['estado' => false]);

            $hotel->staffHotels()->update(['estado' => false]);

            return response()->json([
                'status' => 'success',
                'message' => 'Hotel y staff desactivados correctamente.',
                'hotel' => $hotel->load('staffHotels'),
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hotel no encontrado con el ID = '.$hotel_id,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }
}
