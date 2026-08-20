<?php
// Iniciar la sesión para tener acceso al carrito
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurar el encabezado para que el navegador sepa que respondemos con JSON
header('Content-Type: application/json');

// Si no existe el carrito, lo inicializamos
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Verificamos si la petición es un POST y la acción es "agregar"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'agregar') {
    
    $id_producto = $_POST['id_producto'];
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $imagen = isset($_POST['imagen']) ? $_POST['imagen'] : '';
    
    $producto_encontrado = false;
    foreach ($_SESSION['carrito'] as $indice => $producto) {
        if ($producto['id'] == $id_producto) {
            $_SESSION['carrito'][$indice]['cantidad']++;
            $producto_encontrado = true;
            break;
        }
    }
    
    if (!$producto_encontrado) {
        $_SESSION['carrito'][] = [
            'id' => $id_producto,
            'nombre' => $nombre,
            'precio' => $precio,
            'imagen' => $imagen,
            'cantidad' => 1
        ];
    }
    
    // Calcular el total de productos en el carrito para actualizar la burbuja
    $cantidad_total = 0;
    foreach ($_SESSION['carrito'] as $prod) {
        $cantidad_total += $prod['cantidad'];
    }
    
    // Respondemos con JSON indicando éxito y la nueva cantidad
    echo json_encode([
        'status' => 'success',
        'mensaje' => 'Agregado',
        'cantidad_total' => $cantidad_total
    ]);
    exit;
}

// Si llega hasta acá sin cumplir las condiciones, es un error
echo json_encode(['status' => 'error', 'mensaje' => 'Petición inválida']);
