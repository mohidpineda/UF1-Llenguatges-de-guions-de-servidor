<?php
$ipservidor = "localhost";
$usuario = "mohid";
$contra = "0000";
$bbdd = "gestor_tareas";

$conexion_db = new mysqli($ipservidor, $usuario, $contra, $bbdd);

return $conexion_db;
?>