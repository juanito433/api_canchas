<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>All Sport Courts</title>
    <style>
        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 10px;
        }
    </style>
</head>

<body>
    <h1>Canchas</h1>
    <table>
        <thead>
            <tr>
                <th>Deporte</th>
                <th>Número de cancha</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($courts as $court)
                <tr>
                    <td>
                        {{ $sports->firstWhere('id', $court->sport_id)->name ?? 'Desconocido' }}
                    </td>
                    <td>{{ $court->num_sportcourt }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
