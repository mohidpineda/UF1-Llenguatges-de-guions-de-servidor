<?php
$mensajes = json_decode(file_get_contents('../../mensajes/msgs.json'), true);

if (php_sapi_name() != "cli") {
    die($mensajes["msg01"] . "\n");
}

$config_file = $_SERVER['HOME'] . '/.config/task_manager_config.txt';
$primera_ejecucion = !file_exists($config_file);

if ($primera_ejecucion) {
    echo $mensajes["msg02"] . "\n";
    $opcion = readline($mensajes["msg18"]);

    switch ($opcion) {
        case '1':
            $configuracion = "mariadb";
            break;
        case '2':
            $configuracion = "sqlite";
            break;
        default:
            die($mensajes["msg03"] . "\n");
    }

    file_put_contents($config_file, $configuracion);
} else {
    $configuracion = file_get_contents($config_file);
}

switch ($configuracion) {
    case 'mariadb':
        $conexion_db = include "../../conn/conn_mariadb.php";

        $crear_tabla = "CREATE TABLE IF NOT EXISTS tareas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255) NOT NULL,
            descripcion TEXT,
            estado ENUM('pendiente', 'en progreso', 'por mejorar', 'completada') DEFAULT 'pendiente'
        )";

        mysqli_query($conexion_db, $crear_tabla);

        break;
    case 'sqlite':
        $conexion_db = include "../../conn/conn_sqlite.php";

        $crear_tabla = "CREATE TABLE IF NOT EXISTS tareas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            descripcion TEXT,
            estado TEXT DEFAULT 'pendiente'
        )";

        $stmt = $conexion_db->prepare($crear_tabla);
        $stmt->execute();
        break;
    default:
        die($mensajes["msg17"] . "\n");
}

function agregarTarea($conexion_db, $nombre, $descripcion, $mensajes) {
    if (empty(trim($nombre)) || empty(trim($descripcion))) {
        die($mensajes["msg16"] . "\n");
    }

    global $configuracion;

    switch ($configuracion) {
        case 'mariadb':
            $insertar = "INSERT INTO tareas (nombre, descripcion, estado) VALUES ('$nombre', '$descripcion', 'pendiente')";
            if (mysqli_query($conexion_db, $insertar)) {
                echo $mensajes["msg05"] . "\n";
            }
            break;
        case 'sqlite':
            $stmt = $conexion_db->prepare("INSERT INTO tareas (nombre, descripcion, estado) VALUES (?, ?, 'pendiente')");
            $stmt->execute([$nombre, $descripcion]);
            echo $mensajes["msg05"] . "\n";
            break;
    }
}

function listarTareas($conexion_db, $mensajes) {
    global $configuracion;

    switch ($configuracion) {
        case 'mariadb':
            $consulta = mysqli_query($conexion_db, "SELECT * FROM tareas WHERE estado != 'completada'");
            if (mysqli_num_rows($consulta) > 0) {
                while($fila = mysqli_fetch_assoc($consulta)) {
                    echo "[{$fila['id']}] {$fila['nombre']} - {$fila['descripcion']} - {$fila['estado']}\n";
                }
            } else {
                echo $mensajes["msg06"] . "\n";
            }
            break;
        case 'sqlite':
            $stmt = $conexion_db->query("SELECT * FROM tareas WHERE estado != 'completada'");
            $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($tareas) > 0) {
                foreach ($tareas as $fila) {
                    echo "[{$fila['id']}] {$fila['nombre']} - {$fila['descripcion']} - {$fila['estado']}\n";
                }
            } else {
                echo $mensajes["msg06"] . "\n";
            }
            break;
    }
}

function listarTareasCompletadas($conexion_db, $mensajes) {
    global $configuracion;

    switch ($configuracion) {
        case 'mariadb':
            $consulta = mysqli_query($conexion_db, "SELECT * FROM tareas WHERE estado = 'completada'");
            if (mysqli_num_rows($consulta) > 0) {
                while($fila = mysqli_fetch_assoc($consulta)) {
                    echo "[{$fila['id']}] {$fila['nombre']} - {$fila['descripcion']} - {$fila['estado']}\n";
                }
            } else {
                echo $mensajes["msg07"] . "\n";
            }
            break;
        case 'sqlite':
            $stmt = $conexion_db->query("SELECT * FROM tareas WHERE estado = 'completada'");
            $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($tareas) > 0) {
                foreach ($tareas as $fila) {
                    echo "[{$fila['id']}] {$fila['nombre']} - {$fila['descripcion']} - {$fila['estado']}\n";
                }
            } else {
                echo $mensajes["msg07"] . "\n";
            }
            break;
    }
}

function cambiarEstado($conexion_db, $idTarea, $nuevoEstado, $mensajes) {
    global $configuracion;

    $cambioEstado = "UPDATE tareas SET estado = '$nuevoEstado' WHERE id = $idTarea";
    switch ($configuracion) {
        case 'mariadb':
            if (mysqli_query($conexion_db, $cambioEstado)) {
                echo $mensajes["msg08"] . "\n";
            }
            break;
        case 'sqlite':
            $stmt = $conexion_db->prepare($cambioEstado);
            $stmt->execute();
            echo $mensajes["msg08"] . "\n";
            break;
    }
}

function eliminarTarea($conexion_db, $idTarea, $mensajes) {
    global $configuracion;

    $eliminar = "DELETE FROM tareas WHERE id = $idTarea";
    switch ($configuracion) {
        case 'mariadb':
            if (mysqli_query($conexion_db, $eliminar)) {
                if (mysqli_affected_rows($conexion_db) > 0) {
                    echo $mensajes["msg09"] . "\n";
                } else {
                    echo $mensajes["msg10"] . "\n";
                }
            }
            break;
        case 'sqlite':
            $stmt = $conexion_db->prepare($eliminar);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                echo $mensajes["msg09"] . "\n";
            } else {
                echo $mensajes["msg10"] . "\n";
            }
            break;
    }
}

$options = getopt("a:t:d:i:n:", ["accion:", "titulo:", "descripcion:", "id_tarea:", "nuevo_estado:"]);
$accion = $options["a"] ?? $options["accion"] ?? "";

switch ($accion) {
    case 'agregar':
        $titulo = $options["t"] ?? $options["titulo"] ?? "";
        $descripcion = $options["d"] ?? $options["descripcion"] ?? "";
        if (!$titulo || !$descripcion) {
            die($mensajes["msg14"] . "\n");
        }
        agregarTarea($conexion_db, $titulo, $descripcion, $mensajes);
        break;
    case 'listar':
        listarTareas($conexion_db, $mensajes);
        break;
    case 'completadas':
        listarTareasCompletadas($conexion_db, $mensajes);
        break;
    case 'estado':
        $id_tarea = $options["i"] ?? $options["id_tarea"] ?? "";
        $nuevo_estado = $options["n"] ?? $options["nuevo_estado"] ?? "";
        if (!$id_tarea || !$nuevo_estado) {
            if ($configuracion === 'sqlite') {
                die($mensajes["msg12"] . "\n");
            } else {
                die($mensajes["msg15"] . "\n");
            }
        }
        cambiarEstado($conexion_db, $id_tarea, $nuevo_estado, $mensajes);
        break;
    case 'eliminar':
        $id_tarea = $options["i"] ?? $options["id_tarea"] ?? "";
        if (!$id_tarea) {
            die($mensajes["msg13"] . "\n");
        }
        eliminarTarea($conexion_db, $id_tarea, $mensajes);
        break;
    default:
        die($mensajes["msg11"] . "\n");
}

switch ($configuracion) {
    case 'mariadb':
        mysqli_close($conexion_db);
        break;
    case 'sqlite':
        $conexion_db = null;
        break;
}
?>