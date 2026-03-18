<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RolEnum;
use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\StaffHotel;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffHotelController extends Controller
{
    public function index(Request $request)
    {
        try {

            $hotelid = $request->query('hotel');

            $userAuthId = auth('api')->user()->id;

            $staffAuth = StaffHotel::where('user_id', $userAuthId)
                ->where('hotel_id', $hotelid)
                ->select('rol')
                ->firstOrFail();

            $query = Hotel::findOrFail($hotelid)->staffHotels()->with('user:id,name,email')->where('estado', true); //en el frontend no me mostrava el email

            if ($staffAuth->rol === RolEnum::GERENTE->value) {

                $query->where('rol', RolEnum::RECEPCIONISTA->value);
            } elseif ($staffAuth->rol === RolEnum::PROPIETARIO->value) {

                $query->whereIn('rol', [
                    RolEnum::GERENTE->value,
                    RolEnum::RECEPCIONISTA->value,
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permiso para ver a los trabajadores de este hotel',
                ], 403);
            }

            $staff = $query->get();

            return response()->json([
                'status' => 'success',
                'data' => $staff,
            ], 200);
        } catch (ModelNotFoundException $m) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontro el hotel',
            ], 404); // el 404 es para decir un error que no se se encontro el registro
        } catch (\Exception $e) { // es para designar un error global
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno de el servidor',
            ], 500); // es solamente para un error de servidor que se utiliza el 500
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'hotel_id' => 'required|exists:hoteles,id',
                'name' => 'required|string|max:191',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
                'rol' => 'required|in:GERENTE,RECEPCIONISTA',
                'fecha_asignacion' => 'required|date',
            ]);

            $userAuth = auth('api')->user();

            // Verificar que pertenece al hotel
            $staffAuth = StaffHotel::where('user_id', $userAuth->id)
                ->where('hotel_id', $request->hotel_id)
                ->exists();

            if (! $staffAuth) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permiso para registrar usuarios en este hotel',
                ], 403);
            }

            if ($userAuth->hasRole(RolEnum::GERENTE->value) && $request->rol !== RolEnum::RECEPCIONISTA->value) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Como Gerente solo puedes registrar Recepcionistas',
                ], 403);
            }

            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole(RolEnum::USUARIOADMIN->value);
            $user->assignRole(RolEnum::from($request->rol)->value);

            $staff = StaffHotel::create([
                'rol' => $request->rol,
                'fecha_asignacion' => $request->fecha_asignacion,
                'estado' => true,
                'hotel_id' => $request->hotel_id,
                'user_id' => $user->id,
            ]);

            DB::commit();

            $staff->load([
                'hotel:id,nombre',
                'user:id,name',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Usuario del hotel registrado correctamente',
                'data' => $staff,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Faltan campos requeridos',
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {

            $userAuthId = auth('api')->id();

            $userAuth = StaffHotel::where('user_id', $userAuthId)->firstOrFail();

            $staff = StaffHotel::where('id', $id)
                ->where('hotel_id', $userAuth->hotel_id)
                ->firstOrFail();

            $staff->load([
                'user:id,name',
                'hotel:id,nombre',
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $staff->makeHidden(['hotel_id', 'user_id', 'created_at', 'updated_at']),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontró el registro de staff',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $hotelusuario)
    {
        try {
            $request->validate([
                'name'     => 'sometimes|string|max:191',
                'password' => 'sometimes|min:8',
                'rol'      => 'sometimes|in:GERENTE,RECEPCIONISTA',
            ]);

            $userAuthId = auth('api')->id();

            $staff = StaffHotel::findOrFail($hotelusuario);

            $staffAuth = StaffHotel::where('user_id', $userAuthId)
                ->where('hotel_id', $staff->hotel_id)
                ->firstOrFail();

            if ($request->has('name')) {
                $staff->user->name = $request->name;
            }

            if ($request->has('password')) {
                $staff->user->password = Hash::make($request->password);
            }

            $staff->user->save();

            if ($request->has('rol')) {
                $staff->user->syncRoles([
                    RolEnum::USUARIOADMIN->value,
                    RolEnum::from($request->rol)->value,
                ]);
                $staff->rol = $request->rol;
                $staff->save();
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Usuario actualizado correctamente',
                'data'    => $staff->load('user:id,name,email'),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No se encontró el usuario',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $userAuth = auth('api')->user();
            $userAuthId = $userAuth->id;

            // 1. Primero buscar el target
            $staff = StaffHotel::findOrFail($id);

            // 2. Ahora buscar el auth en el mismo hotel del target
            $staffAuth = StaffHotel::where('user_id', $userAuthId)
                ->where('hotel_id', $staff->hotel_id)
                ->first();

            if (! $staffAuth) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes un perfil de staff asignado en este hotel',
                ], 403);
            }

            // 3. No puede desactivarse a sí mismo
            if ((int) $staff->user_id === (int) $userAuthId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No puedes desactivarte a ti mismo',
                ], 403);
            }

            // 4. Gerente solo puede desactivar Recepcionistas
            if ($userAuth->hasRole(RolEnum::GERENTE->value) && ! $staff->user->hasRole(RolEnum::RECEPCIONISTA->value)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Como Gerente solo puedes desactivar Recepcionistas',
                ], 403);
            }

            $staff->estado = false;
            $staff->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Usuario desactivado correctamente',
                'data' => $staff->makeHidden(['hotel_id', 'user_id', 'created_at', 'updated_at']),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontró el registro del usuario',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }
}
