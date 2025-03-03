<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <center>
        <h2>Información de la reservacion</h2>
        <form action="">

            <h4>folio: {{ $reservation->id }}</h4>

            <h4>{{ $member->name }} {{$member->lastname}}</h4>

            <h4>{{ $schedule->days }}</h4>

            <h4>Compañeros; </h4>
            @foreach ($teammates as $teammate)
                <div class="teammate-container">
                    <h5>{{ $teammate->name }}</h5>
                </div>
            @endforeach
            <h4>{{ $reservation->date }}</h4>

            <h4>Horario</h4>
            <h5>{{$schedule->start_time}}</h5>
            <h5>{{$schedule->end_time}}</h5>


            <h4>{{ $reservation->confirmation }}</h4>


        </form>
    </center>
</body>

</html>
