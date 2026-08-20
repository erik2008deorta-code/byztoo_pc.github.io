<?php
session_start();
include("includes/conexion.php");

$mensaje = "";
$tipo = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    // Verificar si el email existe en la BD
    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // Generar token único
        $token = bin2hex(random_bytes(32));
        $expiracion = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Guardar token en la BD
        $sqlToken = "INSERT INTO recuperacion_password (email, token, expiracion) VALUES (?, ?, ?)";
        $stmtToken = $conn->prepare($sqlToken);
        $stmtToken->bind_param("sss", $email, $token, $expiracion);
        $stmtToken->execute();

        // Construir link de reseteo
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $link = "$protocol://$host/resetear_password.php?token=$token";

        // Enviar email
        $asunto = "Recuperación de contraseña";
        $cuerpo = "
        <html>
        <body style='font-family: Arial, sans-serif; background: #0f0f0f; color: #fff; padding: 40px;'>
            <div style='max-width: 480px; margin: 0 auto; background: #1a1a1a; border-radius: 16px; padding: 40px; border: 1px solid #2a2a2a;'>
                <h2 style='color: #fff; margin-bottom: 8px;'>Recuperar contraseña</h2>
                <p style='color: #aaa; margin-bottom: 24px;'>Recibimos una solicitud para restablecer la contraseña de tu cuenta.</p>
                <a href='$link' style='display: inline-block; background: #fff; color: #000; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: bold;'>
                    Restablecer contraseña
                </a>
                <p style='color: #666; font-size: 13px; margin-top: 24px;'>Este enlace expira en 1 hora. Si no solicitaste esto, ignorá este mensaje.</p>
            </div>
        </body>
        </html>
        ";

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: tu-tienda@tudominio.com\r\n";

        mail($email, $asunto, $cuerpo, $headers);

        $mensaje = "Si el email está registrado, recibirás un enlace para restablecer tu contraseña.";
        $tipo = "exito";
    } else {
        // Por seguridad, mismo mensaje aunque no exista el email
        $mensaje = "Si el email está registrado, recibirás un enlace para restablecer tu contraseña.";
        $tipo = "exito";
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
                                <h2 class="form-title">¿Olvidaste tu contraseña?</h2>
                            </div>
                            <p class="form-subtitle" style="text-align: center;">
                                Ingresá tu email y te enviamos un enlace para restablecerla.
                            </p>
                        </div>

                        <?php if ($mensaje): ?>
                            <p style="
                                color: <?= $tipo === 'exito' ? '#4ade80' : '#ff6b6b' ?>;
                                font-size: 0.875rem;
                                text-align: center;
                                margin-bottom: 1rem;
                                background: <?= $tipo === 'exito' ? 'rgba(74,222,128,0.08)' : 'rgba(255,107,107,0.08)' ?>;
                                padding: 12px;
                                border-radius: 8px;
                                border: 1px solid <?= $tipo === 'exito' ? 'rgba(74,222,128,0.2)' : 'rgba(255,107,107,0.2)' ?>;
                            ">
                                <?= htmlspecialchars($mensaje) ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($tipo !== 'exito'): ?>
                            <form method="POST">
                                <div class="input-group">
                                    <label class="input-label">Email</label>
                                    <input
                                        type="email"
                                        name="email"
                                        class="input-field"
                                        placeholder="ejemplo@correo.com"
                                        required
                                        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                                </div>

                                <button type="submit" class="submit-button">
                                    <div class="submit-button-glow"></div>
                                    <div class="submit-button-inner">Enviar enlace</div>
                                </button>
                            </form>
                        <?php endif; ?>

                        <div class="register-link" style="margin-top: 1.2rem;">
                            <a href="login.php">← Volver al inicio de sesión</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>