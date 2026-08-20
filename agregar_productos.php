<?php
include('includes/conexion.php');

// Productos a agregar
$nuevos_productos = [
    [
        "id" => 18,
        "nombre" => "Gabinete rgb",
        "precio" => 2500,
        "imagen" => "img/gabinetergb.png",
        "descripcion" => "Comodidad superior para largas horas de uso. Incluye pesas ajustables para personalizar su centro de gravedad."
    ],
    [
        "id" => 19,
        "nombre" => "Gabinete Blanco",
        "precio" => 3000,
        "imagen" => "img/gabineteBlanco.png",
        "descripcion" => "Un gabinete Gamer diseñados para quienes quieren llevar su experiencia al maximo (Gabinet color blanco con rgb especialmente pensado en gamers) ."
    ]
];

echo "Agregando productos a la base de datos...\n\n";

foreach ($nuevos_productos as $producto) {
    // Verificar si el producto ya existe
    $check_sql = "SELECT id FROM productos WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $producto['id']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "⚠️  Producto ID {$producto['id']} ya existe, saltando...\n";
        $check_stmt->close();
        continue;
    }
    $check_stmt->close();

    // Insertar el producto
    $sql = "INSERT INTO productos (id, nombre, precio, imagen, descripcion) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isdss",
        $producto['id'],
        $producto['nombre'],
        $producto['precio'],
        $producto['imagen'],
        $producto['descripcion']
    );

    if ($stmt->execute()) {
        echo "✅ Producto '{$producto['nombre']}' agregado correctamente (ID: {$producto['id']})\n";
    } else {
        echo "❌ Error al agregar producto '{$producto['nombre']}': " . $stmt->error . "\n";
    }

    $stmt->close();
}

echo "\nVerificando productos agregados...\n";
$result = $conn->query("SELECT id, nombre FROM productos WHERE id >= 18 ORDER BY id");
while ($row = $result->fetch_assoc()) {
    echo "- ID: {$row['id']}, Nombre: {$row['nombre']}\n";
}

$conn->close();
echo "\n¡Proceso completado!\n";
?>