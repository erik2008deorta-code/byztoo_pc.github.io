<?php
/**
 * ============================================================
 * 1. ARCHIVO DE VERIFICACIÓN DE LA BASE DE DATOS
 * ============================================================
 *
 * ¿Qué hace?
 * Este archivo revisa si la base de datos está accesible y muestra
 * las tablas que existen. También verifica si la tabla "productos"
 * existe y muestra sus registros.
 *
 * ¿Por qué está aquí?
 * Es una herramienta sencilla para comprobar que la conexión y las
 * tablas básicas de la base de datos están funcionando.
 *
 * ¿Qué pasaría si no existiera?
 * Sería más difícil saber si la base de datos está bien configurada.
 */

// include() trae la conexión a la base de datos.
// Sin esto, no podríamos usar $conn para hacer consultas.
include('includes/conexion.php');

// SHOW TABLES es una instrucción SQL que pide la lista de todas las tablas.
$result = $conn->query('SHOW TABLES');

echo "Tablas en la base de datos:\n";

// while() repite el bloque de código mientras haya filas en el resultado.
while ($row = $result->fetch_array()) {
    // fetch_array() toma una fila y la deja en formato de lista.
    // $row[0] es el nombre de la tabla.
    echo "- " . $row[0] . "\n";
}

/**
 * ============================================================
 * 2. VERIFICAR LA EXISTENCIA DE LA TABLA PRODUCTOS
 * ============================================================
 *
 * ¿Qué hace?
 * Comprueba si en la base de datos existe una tabla llamada "productos".
 */

$result2 = $conn->query("SHOW TABLES LIKE 'productos'");

// num_rows es la cantidad de filas que devolvió la consulta.
if ($result2->num_rows > 0) {
    echo "\nTabla 'productos' existe. Contenido:\n";

    // Si la tabla existe, la consultamos para mostrar sus datos.
    $productos = $conn->query('SELECT * FROM productos');
    while ($prod = $productos->fetch_assoc()) {
        // fetch_assoc() trae cada fila como un array asociativo.
        // $prod['id'] y $prod['nombre'] son columnas de la tabla.
        echo "- ID: " . $prod['id'] . ", Nombre: " . $prod['nombre'] . "\n";
    }
} else {
    echo "\nTabla 'productos' NO existe.\n";
}
?>