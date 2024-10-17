<?php
// Encabezado para que el contenido se devuelva como JSON
header('Content-Type: application/json');

$pdo = new PDO("mysql:dbname=agenda;host=localhost", "root", "");


$accion = (isset($_GET['accion'])) ? $_GET['accion'] : 'leer';

switch ($accion) {
    case 'agregar':

        $sentenciaSQL = $pdo->prepare("INSERT INTO
        evento (title, descripcion, color, textColor, inicio, fin) 
        VALUES (:title,:descripcion,:color,:textColor,:inicio,:fin)");

        $respuesta = $sentenciaSQL->execute(array(
            "title" => $_POST['title'],
            "descripcion" => $_POST['descripcion'],
            "color" => $_POST['color'],
            "textColor" => $_POST['textColor'],
            "inicio" => $_POST['start'],
            "fin" => $_POST['end']
        ));

        echo json_encode($respuesta);
        break;
    case 'eliminar':
        echo "Instrucción eliminar";
        $respuesta = false;
        if (isset($_POST['id'])) {
            $sentenciaSQL = $pdo->prepare("DELETE FROM evento WHERE ID=:ID");
            $respuesta = $sentenciaSQL->execute(array("ID" => $_POST['id']));
        }
        echo json_encode($respuesta);
        break;
    case 'modificar':
        $sentenciaSQL = $pdo->prepare("UPDATE evento SET
                title = :title,
                descripcion = :descripcion,
                color = :color,
                textColor = :textColor,
                inicio = :start,
                fin = :end
                WHERE id = :ID");

        $respuesta = $sentenciaSQL->execute(array(
            "ID" => $_POST['id'],
            "title" => $_POST['title'],
            "descripcion" => $_POST['descripcion'],
            "color" => $_POST['color'],
            "textColor" => $_POST['textColor'],
            "start" => $_POST['start'],
            "end" => $_POST['end']
        ));

        echo json_encode($respuesta);
        break;

    default:
        //Ajuste: Puedes mapear las columnas de la base de datos a los nombres esperados por FullCalendar:
        $sentenciaSQL = $pdo->prepare("SELECT id, title, descripcion, color, textColor, inicio AS start, fin AS end FROM evento");
        $sentenciaSQL->execute();
        $resultado = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($resultado);
        break;
}
