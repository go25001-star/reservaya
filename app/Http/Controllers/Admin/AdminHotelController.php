<?php

namespace App\Http\Controllers\Admin;

use App\Models\Hotel;
use App\Models\StaffHotel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AdminHotelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hoteles = Hotel::where('estado', true)->get();

        return response()->json([
            'message' => 'hoteles no encontrados',
            'data' => $hoteles], 200);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $data = $request->validate([
                'nombre' => 'required|string',
                'descripcion' => 'required|string',
                'direccion' => 'required|string',
                'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'email' => 'required|email',
                'telefono' => 'required|string',
                'telefono2' => 'nullable|string',
                'telefono3' => 'nullable|string',

                // staff
                'user_id' => 'required|integer|exists:users,id',
                'rol' => 'required|string',
                'fecha_asignacion' => 'required|date',
                'estado' => 'required|boolean',

            ]);

            DB::beginTransaction();

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
                'user_id' => $data['user_id'],
                'rol' => $data['rol'],
                'fecha_asignacion' => $data['fecha_asignacion'],
                'estado' => $data['estado'],
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Hotel creado exitosamente.',
                'hotel' => $hotel->load('staff_hotel'),
                'imagen' => $imagenPath,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al crear el hotel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        $hotel = Hotel::with('staff_hotel.user')->findOrFail($id);

        return response()->json($hotel, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        try {

            $hotel = Hotel::findOrFail($id);

            $request->validate(
                [
                    'nombre' => ['required', 'string'],
                    'descripcion' => ['required', 'string'],
                    'direccion' => ['required', 'string'],
                    'email' => ['required', 'email'],
                    'telefono' => ['required', 'string'],
                    'telefono2' => ['required', 'string'],
                    'telefono3' => ['required', 'string'],
                ]);
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
                'message' => 'Hotel actualizado correctamente',
                'hotel' => $hotel,
            ], 202);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Hotel no encontrado o error al actualizar',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    /*
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $hotel = Hotel::with('staff_hotel')->findOrFail($id);

            $hotel->update(['estado' => false]);

            $hotel->staff_hotel()->update(['estado' => false]);

            return response()->json([
                'message' => 'Hotel y staff desactivados correctamente.',
                'hotel' => $hotel->load('staff_hotel'),
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Hotel no encontrado con el ID = '.$id,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al desactivar el hotel',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
