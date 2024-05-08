<?php
$sapi_type = php_sapi_name();
if ($sapi_type != "cli") {
    echo "este script solo esta disponible por ahora en la linea de comandos\n";
    die ();
}

$conexion_db = include "../../conn/conn_db.php";

function agregarTarea($conexion_db, $nombre, $descripcion) {
    if (empty(trim($nombre)) || empty(trim($descripcion))) {
        die ("el nombre o la descripcion de la tarea no pueden estar vacios\n");
    }

    $insertar = "INSERT INTO tareas (nombre, descripcion, estado) VALUES ('$nombre', '$descripcion', 'pendiente')";

    if (mysqli_query($conexion_db, $insertar)) {
        echo "tarea añadida correctamente\n";
    }
}

function listarTareas($conexion_db) {
    $consulta = mysqli_query($conexion_db, "SELECT * FROM tareas WHERE estado != 'completada'");

    if (mysqli_num_rows($consulta) > 0) {
        while($fila = mysqli_fetch_assoc($consulta)) {
            echo "[{$fila['id']}] {$fila['nombre']} - {$fila['descripcion']} - {$fila['estado']}\n";
        }
    } else {
        die ("no hay tareas para mostrar\n");
    }
}

function listarTareasCompletadas($conexion_db) {
    $consulta = mysqli_query($conexion_db, "SELECT * FROM tareas WHERE estado = 'completada'");

    if (mysqli_num_rows($consulta) > 0) {
        while($fila = mysqli_fetch_assoc($consulta)) {
            echo "[{$fila['id']}] {$fila['nombre']} - {$fila['descripcion']} - {$fila['estado']}\n";
        }
    } else {
        die ("no hay tareas completadas\n");
    }
}

function cambiarEstado($conexion_db, $idTarea, $nuevoEstado) {
    $cambioEstado = "UPDATE tareas SET estado = '$nuevoEstado' WHERE id = $idTarea";
    if (mysqli_query($conexion_db, $cambioEstado)) {
        echo "tarea modificada con exito\n";
    }
}

function eliminarTarea($conexion_db, $idTarea) {
    $eliminar = "DELETE FROM tareas WHERE id = $idTarea";
    if (mysqli_query($conexion_db, $eliminar)) {
        if (mysqli_affected_rows($conexion_db) > 0) {
            echo "tarea eliminada con exito\n";
        } else {
            echo "la tarea que deseas eliminar no existe\n";
        }
    }
}

if ($argc < 2) {
    echo "-----------------------------------------------------------------\n";
    echo "  uso: php task_manager.php <comando> [argumentos]\n";
    echo "-----------------------------------------------------------------\n";
    echo "  uso de las posibles opciones en el programa:\n";
    echo "-----------------------------------------------------------------\n";
    echo "  agregar <nombre> <descripción>: agrega una nueva tarea\n";
    echo "  listar: lista todas las tareas\n";
    echo "  completadas: lista las tareas completadas\n";
    echo "  estado <id_tarea> <nuevo_estado>: cambia el estado de la tarea\n";
    echo "  eliminar <id_tarea>: elimina una tarea\n";
    echo "-----------------------------------------------------------------\n";
    die();
}

$opcion = $argv[1];
switch ($opcion) {
    case 'agregar':
        if ($argc < 4) {
            die("uso: php task_manager.php agregar <nombre> <descripcion>\n");
        }
        agregarTarea($conexion_db, $argv[2], $argv[3]);
        break;
    case 'listar':
        listarTareas($conexion_db);
        break;
    case 'completadas':
        listarTareasCompletadas($conexion_db);
        break;
    case 'estado':
        if ($argc < 4) {
            die("uso: php task_manager.php estado <id_tarea> <nuevo_estado>\n");
        }
        cambiarEstado($conexion_db, $argv[2], $argv[3]);
        break;
    case 'eliminar':
        if ($argc < 3) {
            die("uso: php task_manager.php eliminar <id_tarea>\n");
        }
        eliminarTarea($conexion_db, $argv[2]);
        break;
    default:
        die("comando no valido\n");
}

mysqli_close($conexion_db);
?>