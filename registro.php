<?php include("includes/header.php"); ?>
<?php include("includes/conexion.php"); ?>

<?php
$mensaje = "";
$es_error = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Sería ideal hashear la contraseña en el futuro
    $sql = "INSERT INTO usuarios (nombre, email, password, fecha_registro) 
            VALUES ('$nombre', '$email', '$password', NOW())";

    if ($conn->query($sql) === TRUE) {
        $mensaje = "Usuario registrado correctamente. ¡Ya podés iniciar sesión!";
        $es_error = false;
    } else {
        $mensaje = "Error al registrar: " . $conn->error;
        $es_error = true;
    }
}
?>

<!-- Aseguramos que cargue el diseño específico -->
<link rel="stylesheet" href="css/auth.css">

<div class="login-container">
    <!-- Elementos Decorativos -->
    <div class="blob-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="login-grid" style="display: flex; justify-content: center;">
        <!-- Sección (Formulario) -->
        <div class="form-section">
            <div class="form-wrapper">
                <div class="form-card">
                    <div class="form-card-glow"></div>
                    <div class="form-card-content">
                        <div class="form-header">
                            <div class="form-header-top" style="justify-content: center;">
                                <h2 class="form-title">Registrarse</h2>
                            </div>
                            <p class="form-subtitle" style="text-align: center;">Completá tus datos para crear una cuenta.</p>
                        </div>

                        <?php
                        if ($mensaje != "") {
                            $color = $es_error ? "#ff6b6b" : "#4caf50";
                            echo "<p style='color: $color; font-size: 0.875rem; text-align: center; margin-bottom: 1rem;'>$mensaje</p>";
                        }
                        ?>

                        <form method="POST">
                            <div class="input-group">
                                <label class="input-label">Nombre Completo</label>
                                <input type="text" name="nombre" class="input-field" placeholder="Juan Pérez" required>
                            </div>

                            <div class="input-group">
                                <label class="input-label">Email</label>
                                <input type="email" name="email" class="input-field" placeholder="ejemplo@correo.com" required>
                            </div>

                            <div class="input-group" style="position: relative;">
                                <label class="input-label">Contraseña</label>
                                <input type="password" name="password" class="input-field" placeholder="••••••••" required>
                            </div>

                            <button type="submit" class="submit-button">
                                <div class="submit-button-glow"></div>
                                <div class="submit-button-inner">Crear cuenta</div>
                            </button>

                            <div class="register-link">
                                ¿Ya tenés cuenta? <a href="login.php">Iniciá sesión acá</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>