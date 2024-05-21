<?php
$ipservidor = "localhost";
$usuario = "mohid";
$contra = "0000";
$bbdd = "gestor_tareas";

$conexion_db = new mysqli($ipservidor, $usuario, $contra);

$crear_db = "CREATE DATABASE IF NOT EXISTS gestor_tareas";

mysqli_query($conexion_db, $crear_db);

mysqli_select_db($conexion_db, $bbdd);

return $conexion_db;
?>