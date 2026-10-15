<?php 

function conectar() {
    $host = "localhost";
    $usuario = "root";
    $password = "12345";
    $baseDatos = "cursos";

    $conexion = new mysqli(
        $host,
        $usuario,
        $password, 
        $baseDatos
    );

    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    return $conexion;
}

// Inicialización global de la variable $conexion
$conexion = conectar();

?>