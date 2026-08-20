<?php
include("includes/conexion.php");
include("includes/productos_data.php");

$sql = "CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    oferta DECIMAL(10,2) NULL,
    imagen VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    especificaciones JSON NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabla 'productos' creada exitosamente o ya existía.\n";
} else {
    echo "Error al crear la tabla: " . $conn->error . "\n";
}

// Vaciar tabla antes de insertar (opcional, para evitar duplicados si se corre varias veces)
$conn->query("TRUNCATE TABLE productos");

$stmt = $conn->prepare("INSERT INTO productos (id, nombre, precio, oferta, imagen, descripcion, especificaciones) VALUES (?, ?, ?, ?, ?, ?, ?)");

foreach ($productos_data as $prod) {
    $id = $prod['id'];
    $nombre = $prod['nombre'];
    $precio = $prod['precio'];
    $oferta = isset($prod['oferta']) ? $prod['oferta'] : null;
    $imagen = $prod['imagen'];
    $descripcion = $prod['descripcion'];
    $especificaciones = isset($prod['especificaciones']) ? json_encode($prod['especificaciones']) : null;

    $stmt->bind_param("isddsss", $id, $nombre, $precio, $oferta, $imagen, $descripcion, $especificaciones);
    if ($stmt->execute()) {
        echo "Producto {$id} insertado.\n";
    } else {
        echo "Error insertando producto {$id}: " . $stmt->error . "\n";
    }
}

$stmt->close();
$conn->close();
?>
