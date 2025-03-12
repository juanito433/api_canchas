@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Reservación de Cancha</h2>
    <form action="{{ route('reservation.register') }}" method="POST" id="reservationForm">
        @csrf
        <div class="mb-3">
            <label for="schedule" class="form-label">Selecciona el horario</label>
            <input type="text" id="schedule" class="form-control" readonly placeholder="Selecciona una fecha y horario" data-bs-toggle="modal" data-bs-target="#scheduleModal">
        </div>

        <div class="mb-3">
            <label for="teammates" class="form-label">Selecciona compañeros (máximo 4)</label>
            <input type="text" id="searchTeammates" class="form-control" placeholder="Buscar compañeros">
            <ul id="teammatesList" class="list-group mt-2"></ul>
        </div>

        <div class="mb-3">
            <label for="confirmation" class="form-label">¿Confirmar reservación?</label>
            <select name="confirmation" id="confirmation" class="form-control">
                <option value="1">Sí</option>
                <option value="0">No</option>
            </select>
        </div>

        <input type="hidden" name="member_id" value="1"> <!-- Miembro fijo por el momento -->
        <input type="hidden" name="schedule_id" id="schedule_id">
        <input type="hidden" name="date" value="{{ now()->format('Y-m-d') }}">

        <button type="submit" class="btn btn-primary">Realizar Reservación</button>
    </form>
</div>

<!-- Modal de selección de horario -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleModalLabel">Seleccionar Horario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Calendario y horarios -->
                <div id="calendar"></div>
                <div id="scheduleDetails" class="mt-3">
                    <p><strong>Modalidad:</strong> <span id="modalidad"></span></p>
                    <p><strong>Horario:</strong> <span id="horario"></span></p>
                    <p><strong>Deporte:</strong> <span id="deporte"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="selectScheduleBtn">Seleccionar Horario</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Función para buscar compañeros
    const teammates = [
        { id: 1, name: 'Juan Carlos' },
        { id: 2, name: 'Carlos Gómez' },
        { id: 3, name: 'Luis Pérez' },
        { id: 4, name: 'Ana Rodríguez' },
        { id: 5, name: 'María López' },
    ];

    const searchTeammates = document.getElementById('searchTeammates');
    const teammatesList = document.getElementById('teammatesList');

    searchTeammates.addEventListener('input', function() {
        teammatesList.innerHTML = '';
        const searchTerm = searchTeammates.value.toLowerCase();
        const filteredTeammates = teammates.filter(teammate => teammate.name.toLowerCase().includes(searchTerm));
        
        filteredTeammates.forEach(teammate => {
            const li = document.createElement('li');
            li.classList.add('list-group-item');
            li.textContent = teammate.name;
            li.onclick = () => selectTeammate(teammate);
            teammatesList.appendChild(li);
        });
    });

    const selectedTeammates = [];
    function selectTeammate(teammate) {
        if (selectedTeammates.length < 4 && !selectedTeammates.includes(teammate)) {
            selectedTeammates.push(teammate);
            const teammateItem = document.createElement('span');
            teammateItem.textContent = teammate.name + ' ';
            document.querySelector('#teammates').appendChild(teammateItem);
        }
    }

    // Función para abrir el calendario
    const selectScheduleBtn = document.getElementById('selectScheduleBtn');
    const scheduleInput = document.getElementById('schedule');
    const scheduleModal = new bootstrap.Modal(document.getElementById('scheduleModal'));

    selectScheduleBtn.addEventListener('click', () => {
        const selectedDate = document.getElementById('calendar').value;
        const selectedSchedule = document.getElementById('schedule_id').value;

        scheduleInput.value = selectedDate;
        document.getElementById('schedule_id').value = selectedSchedule;
        scheduleModal.hide();
    });

    // Cargar calendario y horarios disponibles
    // Aquí puedes agregar código para cargar el calendario desde un servicio.
    // Esta es una simulación:
    const scheduleData = [
        { date: '2025-03-07', modalidad: 'Fútbol', horario: '10:00 - 12:00', deporte: 'Fútbol', id: 1 },
        { date: '2025-03-08', modalidad: 'Tenis', horario: '14:00 - 16:00', deporte: 'Tenis', id: 2 }
    ];

    const calendarDiv = document.getElementById('calendar');
    calendarDiv.innerHTML = scheduleData.map(schedule => `
        <div class="schedule-item" data-id="${schedule.id}" onclick="setSchedule('${schedule.date}', '${schedule.modalidad}', '${schedule.horario}', '${schedule.deporte}', '${schedule.id}')">
            ${schedule.date} - ${schedule.modalidad} (${schedule.horario})
        </div>
    `).join('');

    function setSchedule(date, modalidad, horario, deporte, id) {
        document.getElementById('modalidad').textContent = modalidad;
        document.getElementById('horario').textContent = horario;
        document.getElementById('deporte').textContent = deporte;
        document.getElementById('schedule_id').value = id;
    }
</script>
@endsection
