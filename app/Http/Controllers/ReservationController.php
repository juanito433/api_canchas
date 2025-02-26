<?php

namespace App\Http\Controllers;

use App\Models\reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    //Función para visualizar todas las reservaciones
    public function all(){
        $reservations = reservation::all();
        return response()->json($reservations);
    }

    //Función para visualizar una sola reservación
    public function show($id){
        $reservation = reservation::find($id);
        return response()->json($reservation);
    }

    //Función para visualizar las reservaciones de un miembro
    public function memberReservations($id){
        $reservations = reservation::where('member_id', $id)->get();
        return response()->json($reservations);
    }

    //Realizar una reservacion de una cancha(con su modalidad, el miembro que hace la reservación y los teammates)
    
}
