<?php
session_start();
include("includes/conexion.php");

$token = isset($_GET["token"]) ? trim($_GET["token"]) : "";
$mensaje = "";
$tipo = "";
$tokenValido = false;
$emailUsuario = "";

// Validar el token
if ($token) {
    $ahora = date("Y-m-d H:i:s");
    $sql = "SELECT * FROM recuperacion_password WHERE token = ? AND expiracion > ? AND usado = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $token, $ahora);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        $tokenValido = true;
        $emailUsuario = $fila["email"];
    } else {
        $mensaje = "El enlace es inválido o ya expiró. Solicitá uno nuevo.";
        $tipo = "error";
    }
} else {
    $mensaje = "Token no proporcionado.";
    $tipo = "error";
}

// Procesar nueva contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST" && $tokenValido) {
    $nueva = $_POST["nueva_password"];
    $confirmar = $_POST["confirmar_password"];

    if (strlen($nueva) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
        $tipo = "error";
    } elseif ($nueva !== $confirmar) {
        $mensaje = "Las contraseñas no coinciden.";
        $tipo = "error";
    } else {
        // Actualizar contraseña en la BD (texto plano, como está tu sistema actual)
        $sqlUpdate = "UPDATE usuarios SET password = ? WHERE email = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ss", $nueva, $emailUsuario);
        $stmtUpdate->execute();

        // Marcar el token como usado
        $sqlUsado = "UPDATE recuperacion_password SET usado = 1 WHERE token = ?";
        $stmtUsado = $conn->prepare($sqlUsado);
        $stmtUsado->bind_param("s", $token);
        $stmtUsado->execute();

        $mensaje = "¡Contraseña actualizada exitosamente! Ya podés iniciar sesión.";
        $tipo = "exito";
        $tokenValido = false; // Ocultar el formulario
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
                                <h2 class="form-title">Nueva contraseña</h2>
                            </div>
                            <?php if ($tokenValido): ?>
                                <p class="form-subtitle" style="text-align: center;">
                                    Elegí una nueva contraseña para tu cuenta.
                                </p>
                            <?php endif; ?>
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

                        <?php if ($tokenValido): ?>
                            <form method="POST" action="resetear_password.php?token=<?= htmlspecialchars($token) ?>">

                                <div class="input-group" style="position: relative;">
                                    <label class="input-label">Nueva contraseña</label>
                                    <input
                                        type="password"
                                        name="nueva_password"
                                        id="nueva_password"
                                        class="input-field"
                                        placeholder="••••••••"
                                        required
                                        minlength="6">
                                    <span onclick="togglePass('nueva_password', this)" style="
                                    position: absolute; right: 14px; top: 38px;
                                    cursor: pointer; color: #888; font-size: 13px;
                                    user-select: none;
                                ">Mostrar</span>
                                </div>

                                <div class="input-group" style="position: relative;">
                                    <label class="input-label">Confirmar contraseña</label>
                                    <input
                                        type="password"
                                        name="confirmar_password"
                                        id="confirmar_password"
                                        class="input-field"
                                        placeholder="••••••••"
                                        required
                                        minlength="6">
                                    <span onclick="togglePass('confirmar_password', this)" style="
                                    position: absolute; right: 14px; top: 38px;
                                    cursor: pointer; color: #888; font-size: 13px;
                                    user-select: none;
                                ">Mostrar</span>
                                </div>

                                <!-- Indicador de seguridad -->
                                <div id="fuerza-container" style="margin-bottom: 1rem; display: none;">
                                    <div style="display: flex; gap: 4px; margin-bottom: 4px;">
                                        <div class="barra-fuerza" id="b1" style="flex:1; height:4px; border-radius:2px; background:#333;"></div>
                                        <div class="barra-fuerza" id="b2" style="flex:1; height:4px; border-radius:2px; background:#333;"></div>
                                        <div class="barra-fuerza" id="b3" style="flex:1; height:4px; border-radius:2px; background:#333;"></div>
                                        <div class="barra-fuerza" id="b4" style="flex:1; height:4px; border-radius:2px; background:#333;"></div>
                                    </div>
                                    <p id="texto-fuerza" style="font-size: 12px; color: #888; margin: 0;"></p>
                                </div>

                                <button type="submit" class="submit-button">
                                    <div class="submit-button-glow"></div>
                                    <div class="submit-button-inner">Guardar contraseña</div>
                                </button>
                            </form>
                        <?php endif; ?>

                        <div class="register-link" style="margin-top: 1.2rem;">
                            <?php if ($tipo === 'exito'): ?>
                                <a href="login.php">Ir al inicio de sesión →</a>
                            <?php elseif ($tipo === 'error' && !$tokenValido): ?>
                                <a href="olvide_password.php">Solicitar nuevo enlace</a>
                            <?php else: ?>
                                <a href="login.php">← Volver al inicio de sesión</a>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePass(id, el) {
        const input = document.getElementById(id);
        if (input.type === 'password') {
            input.type = 'text';
            el.textContent = 'Ocultar';
        } else {
            input.type = 'password';
            el.textContent = 'Mostrar';
        }
    }

    // Indicador de fuerza de contraseña
    document.getElementById('nueva_password').addEventListener('input', function() {
        const val = this.value;
        const container = document.getElementById('fuerza-container');
        const barras = ['b1', 'b2', 'b3', 'b4'];
        const texto = document.getElementById('texto-fuerza');

        if (val.length === 0) {
            container.style.display = 'none';
            return;
        }
        container.style.display = 'block';

        let fuerza = 0;
        if (val.length >= 6) fuerza++;
        if (val.length >= 10) fuerza++;
        if (/[A-Z]/.test(val) && /[0-9]/.test(val)) fuerza++;
        if (/[^A-Za-z0-9]/.test(val)) fuerza++;

        const colores = ['#ff6b6b', '#ffa94d', '#ffd43b', '#4ade80'];
        const textos = ['Muy débil', 'Débil', 'Buena', 'Fuerte'];

        barras.forEach((id, i) => {
            document.getElementById(id).style.background = i < fuerza ? colores[fuerza - 1] : '#333';
        });
        texto.textContent = textos[fuerza - 1] || '';
        texto.style.color = colores[fuerza - 1] || '#888';
    });
</script>

<?php include("includes/footer.php"); ?>