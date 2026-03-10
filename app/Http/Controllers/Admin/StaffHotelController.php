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
    public function index(string $id)// aqui va a ir el id de el hotel
    {
        try {

            $userAuthId = auth('api')->user()->id;

            $staffAuth = StaffHotel::where('user_id', $userAuthId)
                ->where('hotel_id', $id)
                ->select('rol')
                ->firstOrFail();

            $query = Hotel::findOrFail($id)->staffHotels()->with('user:id,name');

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
                'status' => 'ok',
                'data' => $staff,
            ], 200);

        } catch (ModelNotFoundException $m) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontro el hotel',
            ], 404); // el 404 es para decir un error que no se se encontro el registro
        } catch (\Exception $e) {// es para designar un error global
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

                'rol' => 'required|in:PROPIETARIO,GERENTE,RECEPCIONISTA',
                'fecha_asignacion' => 'required|date',
            ]);

            $userAuthId = auth('api')->id();

            $staffAuth = StaffHotel::where('user_id', $userAuthId)
                ->where('hotel_id', $request->hotel_id)
                ->exists();

            if (! $staffAuth) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permiso para registrar usuarios en este hotel',
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
            $staff->load([// nota personal el load es para cargar una parte especifica de la tabla
                'hotel:id,nombre',
                'user:id,name',
            ]);

            return response()->json([
                'status' => 'ok',
                'message' => 'Usuario del hotel registrado correctamente',
                'data' => $staff,
            ], 201);
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

            return response()->json([// en la parte de hotel quiero que me devuleva sdolo id y nombre
                'status' => 'ok',
                'data' => $staff,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontró el registro de staff',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // basio por que no se puede hascer un degrado de un nivel asi de brusco
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            $userAuthId = auth('api')->id(); // Trae el id del usuario que esta logueado,

            $staffAuth = StaffHotel::where('user_id', $userAuthId)->firstOrFail(); // Verificamos los datos del usuario logueado sean los correctos con los guardaos en la BD

            $staff = StaffHotel::findOrFail($id); // El usuario del hotel que vamos a desactivar;

            if ((int) $staff->user_id === (int) $userAuthId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No puedes desactivarte a ti mismo',
                ], 403);
            }

            if ($staffAuth->hotel_id !== $staff->hotel_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permiso para desactivar a este usuario',
                ], 403);
            }

            $staff->estado = false;
            $staff->save();

            return response()->json([
                'status' => 'ok',
                'message' => 'Usuario desactivado correctamente',
                'data' => $staff,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontró el registro de el usuario',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }
}
