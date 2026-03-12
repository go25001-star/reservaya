<?php

namespace App\Http\Controllers\Client;

use App\Models\Hotel;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class HabitacionController extends Controller
{
   

    public function index($hotel_id)
    {
        try {
         
        $habitaciones = Hotel::findOrFail($hotel_id)
        ->habitaciones()
        ->with(['tipoHabitacion', 'imagenes'])
        ->get();


          return response()->json([
            'status' => 'ok',
            'data' => $habitaciones
          ], 200);

        
        } catch (ModelNotFoundException $e) {
          return response()->json([
            'status' => 'error',
            'message' => 'El hotel buscado con el ID: ' . $hotel_id . ' No fue encontrado en el sistema'
          ], 404);

        } catch (\Exception $e) {
          return response()->json([
            'status' => 'error',
            'message' => 'Error interno en el servidor',
            'messageError' =>  $e->getMessage()
          ], 500);
        } 
    }
  
}
