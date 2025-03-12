<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Profile of members</title>
</head>
<body>
    <div class="">
        <h1>Perfil</h1>
        <p>Nombre: {{$member->name}} {{$member->lastname}}</p>
        <p>Folio de miembro: {{$member->id}}</p>
        <p>Correo: {{$member->email}}</p>
        <p>N. Celular: {{$member->phone}}</p>
        <p>Fecha de registro: {{$member->created_at}}</p>

    </div>
</body>
</html>