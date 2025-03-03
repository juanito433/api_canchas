<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>

    <h2>Información de la reservacion</h2>
    <form action="">

        <h4>Folio: {{ $reservation->id }}</h4>

        <h4>Miembro: {{ $member->name }} {{ $member->lastname }}</h4>

        <p>Deporte: {{ $sport->name }}</p>
        <p>Cancha reservada; Número - {{ $sportcourt->num_sportcourt }}</p>
        <p>Modalidad: {{ $mode->name }}</p>
        <p>Dia reservado; {{ $schedule->days }}</p>
        <p>Fecha reservada; {{ $reservation->date }}</p>
        <p>Compañeros; </p>
        <div
            style="
        display: flex;
        align-items: center;
        justify-content: space-evenly;
        flex-wrap: wrap;
        width: 120px;
        ">
            @foreach ($teammates as $teammate)
                <h4>{{ $teammate->name }}, </h4>
            @endforeach
        </div>
        <h>Horario</h>
        <p>{{ $schedule->start_time }} a {{ $schedule->end_time }}</p>
        <p> Confimación: {{ $reservation->confirmation }}</p>
        <p>Fecha de resersacion; {{ $reservation->created_at }}</p>
    </form>

</body>

</html>
