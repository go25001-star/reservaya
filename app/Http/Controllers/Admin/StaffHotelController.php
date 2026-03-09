<?php

namespace App\Http\Controllers\Admin;

use App\Models\Hotel;
use App\Models\StaffHotel;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class StaffHotelController extends Controller
{
    
    public function index()//aqui va a ir el id de el hotel
    {
        try{
            $staff = Hotel::findOrFail(1)//aqui va a ir el id de el hotel
            ->staff_hotel()
            ->get();
             return response()->json([
                'status'=> 'ok',//esta condicion te enviara qeu todo esta bien 
                'data'=> $staff//devolvera toda la data del staff

            ],200);//signifa ok o todo bien 

        }catch(ModelNotFoundException $m){
            return response()->json([
                'status'=> 'error',
                'message'=> 'No se encontro el hotel'
            ],404);//el 404 es para decir un error que no se sencontro registro
        }
        catch(\Exception $e){//es para designar un error global
            return response()->json([
                'status'=> 'error',
                'message'=> 'Error interno de el servidor'
            ],500);//es solamente para un error de servidor que se utiliza el 500
        }
    }

    /**
     * Store a newly created resource in storage.
     */
  public function store(Request $request)
   {$request->validate([
        'hotel_id' => 'required|exists:hoteles,id',

        'name' => 'required|string|max:191',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8',

        'rol' => 'required|in:P,G,R,UA',
        'fecha_asignacion' => 'required|date',
    ]);

    DB::beginTransaction();

    try {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $staff = StaffHotel::create([
           'rol' => $request->rol,
           'fecha_asignacion'=> $request->fecha_asignacion,
           'estado' => true,
           'hotel_id' => $request->hotel_id,
           'user_id' => $user->id,
        ]);
        DB::commit();
        $staff->load([//nota personal el load es para cargar una parte especifica de la tabla 
            'hotel:id,nombre',
            'user:id,name'
        ]); 
        return response()->json([
            'status' => 'ok',
            'message' => 'Staff registrado correctamente',
            'data' => $staff
        ], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => 'error',
            'message' => 'Error interno del servidor'
        ], 500);
    }

}

  //pendiente de probar
    public function show(string $id)
    {
       try {
        
        $staff = StaffHotel::with(['user'])->findOrFail($id);

        $staff->load([
            'hotel:id,nombre',
        ]); 

        return response()->json([// en la parte de hotel quiero que me devuleva sdolo id y nombre 
            'status' => 'ok',
            'data' => $staff 
        ], 200);

       } catch (ModelNotFoundException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'No se encontró el registro de staff'
        ], 404);
        } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error interno del servidor: ' . $e->getMessage()
        ], 500);
    }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
      //basio por que no se puede hascer un degrado de un nivel asi de brusco
    }

    /**
     * Remove the specified resource from storage.
     */
   public function destroy(string $id)
   {
     try {

        $staff = StaffHotel::findOrFail($id);
        $staff->estado = false;//con esto solo desactivamos el estado de el staff registrado
        $staff->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'estado desactivado correctamente',
            'data' => $staff
        ], 200);

    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'No se encontró el registro de el usuario'
        ], 404);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error interno del servidor'
        ], 500);
    }
   }
   
 
}
