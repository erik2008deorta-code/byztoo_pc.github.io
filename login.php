<?php
session_start();
include("includes/conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Consulta preparada (evita SQL Injection)
    $sql = "SELECT * FROM usuarios WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {

        $usuario = $resultado->fetch_assoc();

        $_SESSION["id_usuario"] = $usuario["id_usuario"];
        $_SESSION["nombre"]     = $usuario["nombre"];
        $_SESSION["email"]      = $usuario["email"];

        header("Location: index.php");
        exit();
    } else {
        $error = "Email o contraseña incorrectos";
    }
}
?>

<?php include("includes/header.php"); ?>

<link rel="stylesheet" href="css/auth.css">

<div class="login-container">
    <div class="blob-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="login-grid" style="display: flex; justify-content: center;">
        <div class="form-section">
            <div class="form-wrapper">
                <div class="form-card">
                    <div class="form-card-glow"></div>
                    <div class="form-card-content">
                        <div class="form-header">
                            <div class="form-header-top" style="justify-content: center;">
                                <h2 class="form-title">Iniciar sesión</h2>
                            </div>
                            <p class="form-subtitle" style="text-align: center;">Ingresá tus credenciales para continuar.</p>
                        </div>

                        <?php if (isset($error)): ?>
                            <p style="color: #ff6b6b; font-size: 0.875rem; text-align: center; margin-bottom: 1rem;">
                                <?= htmlspecialchars($error) ?>
                            </p>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="input-group">
                                <label class="input-label">Email</label>
                                <input type="email" name="email" class="input-field" placeholder="ejemplo@correo.com" required>
                            </div>

                            <div class="input-group" style="position: relative;">
                                <label class="input-label">Contraseña</label>
                                <input type="password" name="password" class="input-field" placeholder="••••••••" required>
                            </div>

                            <div class="form-options">
                                <label class="checkbox">
                                    <input type="checkbox" class="checkbox-input"> Recordarme
                                </label>
                                <!-- Enlace actualizado -->
                                <a href="olvide_password.php" class="forgot-password">¿Olvidaste tu contraseña?</a>
                            </div>

                            <button type="submit" class="submit-button">
                                <div class="submit-button-glow"></div>
                                <div class="submit-button-inner">Ingresar</div>
                            </button>

                            <div class="register-link">
                                ¿No tenés cuenta? <a href="registro.php">Registrate acá</a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>