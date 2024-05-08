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
        echo "no hay tareas para mostrar\n";
    }
}

function listarTareasCompletadas($conexion_db) {
    $consulta = mysqli_query($conexion_db, "SELECT * FROM tareas WHERE estado = 'completada'");

    if (mysqli_num_rows($consulta) > 0) {
        while($fila = mysqli_fetch_assoc($consulta)) {
            echo "[{$fila['id']}] {$fila['nombre']} - {$fila['descripcion']} - {$fila['estado']}\n";
        }
    } else {
        echo "no hay tareas completadas\n";
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

$options = getopt("a:t:d:", ["accion:", "titulo:", "descripcion:", "id_tarea:", "nuevo_estado:"]);

$accion = $options["a"] ?? $options["accion"] ?? "";
$titulo = $options["t"] ?? $options["titulo"] ?? "";
$descripcion = $options["d"] ?? $options["descripcion"] ?? "";

switch ($accion) {
    case 'agregar':
        if (!$titulo || !$descripcion) {
            die("uso: php task_manager.php -a|--accion agregar -t|--titulo=<nombre> -d|--descripcion=<descripcion>\n");
        }
        agregarTarea($conexion_db, $titulo, $descripcion);
        break;
    case 'listar':
        listarTareas($conexion_db);
        break;
    case 'completadas':
        listarTareasCompletadas($conexion_db);
        break;
    case 'estado':
        $id_tarea = $options["id_tarea"] ?? "";
        $nuevo_estado = $options["nuevo_estado"] ?? "";
        if (!$id_tarea || !$nuevo_estado) {
            die("uso: php task_manager.php -a|--accion estado --id_tarea=<id_tarea> --nuevo_estado=<nuevo_estado>\n");
        }
        cambiarEstado($conexion_db, $id_tarea, $nuevo_estado);
        break;
    case 'eliminar':
        $id_tarea = $options["id_tarea"] ?? "";
        if (!$id_tarea) {
            die("uso: php task_manager.php -a|--accion eliminar --id_tarea=<id_tarea>\n");
        }
        eliminarTarea($conexion_db, $id_tarea);
        break;
    default:
        echo "----------------------------------------------------------------------\n";
        echo "  uso: php task_manager.php <comando> [argumentos]\n";
        echo "----------------------------------------------------------------------\n";
        echo "  uso de las posibles opciones en el programa:\n";
        echo "----------------------------------------------------------------------\n";
        echo "  -a|--accion: agrega, lista, completa, cambia estado o elimina tareas\n";
        echo "  -t|--titulo: especifica el titulo de la tarea\n";
        echo "  -d|--descripcion: especifica la descripcion de la tarea\n";
        echo "----------------------------------------------------------------------\n";
        die();
}

mysqli_close($conexion_db);
?>