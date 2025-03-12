<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Info reservation</title>
</head>

<body>

    <h2>Información de la reservacion</h2>

    <h4>Folio: {{ $reservation->id }}</h4>
    <div style="display: flex; justify-content: flex-start; align-items: center; height: 9px;">
        <p> Miembro: </p>
        <h4>{{ $member->name }} {{ $member->lastname }}</h4>
    </div>
    <br>
    <div style="display: flex; justify-content: flex-start; align-items: center; height: 10px;">
        <p> Folio del Miembro: #</p>
        <h4>{{ $member->id }}</h4>
    </div>
    <p>Deporte: {{ $sport->name }}</p>
    <p>Cancha reservada; Número #{{ $sportcourt->num_sportcourt }}</p>
    <p>Modalidad: {{ $mode->name }}</p>
    <p>Dia reservado; {{ $schedule->days }}</p>
    <p>Fecha reservada; {{ $reservation->date }}</p>
    <p>Compañeros; </p>
    <div>
        <ul>
            @foreach ($teammates as $teammate)
                <li>{{ $teammate->name }}</li>
            @endforeach
        </ul>
    </div>
    <h>Horario</h>
    <p>{{ $schedule->start_time }} a {{ $schedule->end_time }}</p>
    <p> Confimación: {{ $reservation->confirmation }}</p>
    <p>Fecha de reservacion; {{ $reservation->created_at }}</p>
    <div style="display: flex; justify-content: flex-start; align-items: center; height: 16px;">
        <p>Estatus: </p>
        <h4>{{ $reservation->status }}</h4>
    </div>
</body>

</html>
