<!doctype html>
<html lang="en">

<head>
  <title>Agenda</title>
  <!-- Required meta tags -->
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <!-- Incluye primero la librería de jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap CSS v5.2.1 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
  <!-- Incluimos las carpetas del plugin del reloj -->
  <script src="js/bootstrap-clockpicker.js"></script>
  <link rel="stylesheet" href="css/bootstrap-clockpicker.css">

</head>

<body>

  <div class="container">
    <div class="col-md-8 offset-md-2">
      <div id='calendar'></div>
    </div>
  </div>

  <!-- Modal (Agregar, Modificar, Eliminar) -->
  <div class="modal fade" id="ModalEventos" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="tituloEvento"></h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <input type="text" id="txtID" hidden name="txtID">
            <input type="text" id="textFecha" hidden name="textFecha">

            <div class="form-group col-md-8">
              <label>Titulo:</label>
              <input type="text" id="txtTitulo" class="form-control" placeholder="Titulo del evento...">
            </div>

            <div class="form-group col-md-4">
              <label>Hora:</label>
              <div class="input-group clockpicker">
                <input type="text" class="form-control" value="09:30">
                <span class="input-group-addon">
                  <span class="glyphicon glyphicon-time"></span>
                </span>
              </div>
            </div>

            <div class="form-group">
              <label>Descripción:</label>
              <textarea id="textDescription" rows="3" class="form-control"></textarea>
            </div>

            <div class="form-group">
              <label>Color:</label>
              <input type="color" value="#ff0000" id="txtColor" class="form-control" style="height: 36px;">
            </div>
          </div>

          <div id="descripcionEvento"></div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" id="botonGuardar">Guardar</button>
          <button type="button" class="btn btn-success" id="botonModificar">Editar</button>
          <button type="button" class="btn btn-danger" id="botonEliminar">Borrar</button>
          <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </div>
  </div>


  <!-- Calendar functionalities -->

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var calendarEl = document.getElementById('calendar');
      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: "es",
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },

        // Evento para manejar el clic sobre un día del calendario
        dateClick: function(info) {
          limpiarFormulario();
          $('#textFecha').val(info.dateStr); // Asignar la fecha del clic al campo correspondiente
          $('#ModalEventos').modal('show'); // Mostrar el modal para agregar un nuevo evento
        },

        // Cargar eventos desde la fuente de datos (BD)
        events: 'http://localhost/Agenda/eventos.php',

        // Al hacer clic en un evento existente para ver detalles
        eventClick: function(calEvent, jsEvent, view) {
          console.log(calEvent); // Verificar el objeto del evento en la consola

          // Mostrar los valores en los campos del formulario
          $('#tituloEvento').html(calEvent.event.title);
          $('#textDescription').val(calEvent.event.extendedProps.descripcion || '');
          $('#txtID').val(calEvent.event.id || '');
          $('#txtTitulo').val(calEvent.event.title);
          $('#txtColor').val(calEvent.event.backgroundColor || '#000000');

          // Obtener la fecha y hora de inicio correctamente
          var fechaHora = calEvent.event.start.toISOString().split("T");
          $('#textFecha').val(fechaHora[0]); // Asignar la fecha en el formato `YYYY-MM-DD`
          $('#txtHora').val(fechaHora[1].substring(0, 5)); // Hora en formato `HH:MM`

          // Mostrar el modal para editar el evento
          $("#ModalEventos").modal('show');
        },

        //Para poder mover un evento de una fecha a otra desde la interfaz del calendario
        editable: true,
        eventDrop: function(calEvent) {
          $('#txtID').val(calEvent.event.id || '');
          $('#txtTitulo').val(calEvent.event.title);
          $('#txtColor').val(calEvent.event.backgroundColor || '#000000');
          $('#textDescription').val(calEvent.event.extendedProps.descripcion || '');
          var fechaHora = calEvent.start.format().split("T");
          $('#textFecha').val(fechaHora[0]); // Asignar la fecha en el formato `YYYY-MM-DD`
          $('#txtHora').val(fechaHora[1].substring(0, 5)); // Hora en formato `HH:MM`

          recolectarDatosGUI();
          EnviarInformacion('modificar', nuevoEvento, true);
        }
      });

      calendar.render(); // Renderizar el calendario en el contenedor

      var nuevoEvento;

      $('#botonGuardar').click(function() { // Manejador del botón para guardar el evento en el calendario
        recolectarDatosGUI(); // Recolectar los datos del formulario
        EnviarInformacion('agregar', nuevoEvento); // Enviar la información para agregar un nuevo evento
      });

      $('#botonEliminar').click(function() { // Manejador del botón para guardar el evento en el calendario
        recolectarDatosGUI(); // Recolectar los datos del formulario
        EnviarInformacion('eliminar', nuevoEvento); // Enviar la información para agregar un nuevo evento
      });

      $('#botonModificar').click(function() { // Manejador del botón para guardar el evento en el calendario
        recolectarDatosGUI(); // Recolectar los datos del formulario
        EnviarInformacion('modificar', nuevoEvento); // Enviar la información para agregar un nuevo evento
      });

      // Función para recolectar los datos del formulario
      function recolectarDatosGUI() {
        nuevoEvento = {
          id: $('#txtID').val(),
          title: $('#txtTitulo').val(),
          start: $('#textFecha').val() + "T" + $('#txtHora').val(), // Formato ISO para `start`
          end: $('#textFecha').val() + "T" + $('#txtHora').val(), // Formato ISO para `end`
          color: $('#txtColor').val(),
          descripcion: $('#textDescription').val(),
          textColor: "#ffffff"
        }
      }

      // Función para enviar la información al backend (PHP)
      function EnviarInformacion(accion, objEvento, modal) {
        $.ajax({
          type: 'POST',
          url: 'eventos.php?accion=' + accion,
          data: objEvento,
          success: function(msg) {
            console.log("Respuesta del servidor: ", msg); // Mostrar respuesta del servidor para depuración
            if (msg) {
              // Refrescar los eventos en el calendario
              calendar.refetchEvents();
              if (!modal) {
                $('#ModalEventos').modal('toggle'); // Cerrar el modal
              }
            }
          },
          error: function() {
            alert("Hubo un error al guardar el evento en el servidor.");
          }
        });
      }

      function limpiarFormulario() {
        // Mostrar los valores en los campos del formulario
        $('#tituloEvento').html('');
        $('#textDescription').val('');
        $('#txtID').val('');
        $('#txtTitulo').val('');
        $('#txtColor').val('');
      }
    });
  </script>

  <script type="text/javascript">
    $('.clockpicker').clockpicker();
  </script>

  <!--
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var calendarEl = document.getElementById('calendar');
      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: "es",
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        //Provides us with information about the date of the click.
        dateClick: function(info) {
          $("#exampleModal").modal('show');
        },
        //Al clicar sobre un día (para asignar un evento)
        dateClick: function(info) {
          $('#textFecha').val(info.dateStr);
          $('#ModalEventos').modal('show');
        },
        //Events
        events: 'http://localhost/Agenda/eventos.php',

        //To show array information in the bootstrap modal (Eventos registrados en BD)
        eventClick: function(calEvent, jsEvent, view) {

          // Asignar los valores a los campos del formulario
          $('#tituloEvento').html(calEvent.event.title);
          $('#textDescription').val(calEvent.event.extendedProps.descripcion || '');
          $('#txtID').val(calEvent.event.id || '');
          $('#txtTitulo').val(calEvent.event.title);
          $('#txtColor').val(calEvent.event.backgroundColor || '#000000'); // Usar `backgroundColor` en lugar de `color`

          // Obtener la fecha y hora de inicio correctamente
          var fechaHora = calEvent.event.start.toISOString().split("T");
          $('#textFecha').val(fechaHora[0]); // Fecha en formato `YYYY-MM-DD`
          $('#txtHora').val(fechaHora[1].substring(0, 5)); // Hora en formato `HH:MM`

          // Mostrar el modal
          $("#ModalEventos").modal('show');
        }
      });

      calendar.render();

      var nuevoEvento;
      // Manejador del botón para guardar el evento en el calendario
      $('#botonGuardar').click(function() {
        recolectarDatosGUI();
        EnviarInformacion('agregar', nuevoEvento);
      });

      function recolectarDatosGUI() {
        nuevoEvento = {
          id: $('#txtID').val(),
          title: $('#txtTitulo').val(),
          start: $('#textFecha').val() + " " + $('#txtHora').val(),
          color: $('#txtColor').val(),
          descripcion: $('#textDescription').val(),
          textColor: "#ffffff",
          end: $('#textFecha').val() + " " + $('#txtHora').val()
        }
      }

      function EnviarInformacion(accion, objEvento) {
        $.ajax({
          type: 'POST',
          url: 'eventos.php?accion=' + accion,
          data: objEvento,
          success: function(msg) {
            if (msg) {
              // Agregar el evento al calendario
              calendar.refetchEvents();
              $('#ModalEventos').modal('toggle');
            }
          },
          error: function() {
            alert("Hay un error...")
          }
        })
      }
    });
  </script>
  -->
  <!-- Bootstrap JavaScript Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

</body>

</html>