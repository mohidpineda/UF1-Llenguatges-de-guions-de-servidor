<?php
$ruta_db = '/home/mohid/gestor_tareas.db';
$conexion_db = new PDO('sqlite:' . $ruta_db);

return $conexion_db;
?>