<?php
// Todo el PHP PRIMERO, antes de cualquier HTML o include
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("includes/conexion.php");

// Redirigir si no está logueado
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit();
}

$id      = $_SESSION["id_usuario"];
$mensaje = "";
$tipo    = "";
$tab_activa = "datos";

// Cargar datos actuales
$sql  = "SELECT * FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// ─── PROCESAR FORMULARIOS ───────────────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "";

    // ── Datos personales ──────────────────────────────────────────────────
    if ($accion === "datos") {
        $nombre    = trim($_POST["nombre"]);
        $email     = trim($_POST["email"]);
        $telefono  = trim($_POST["telefono"]);
        $direccion = trim($_POST["direccion"]);

        $chk = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?");
        $chk->bind_param("si", $email, $id);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $mensaje = "Ese email ya está en uso por otra cuenta.";
            $tipo    = "error";
        } else {
            $upd = $conn->prepare("UPDATE usuarios SET nombre=?, email=?, telefono=?, direccion=? WHERE id_usuario=?");
            $upd->bind_param("ssssi", $nombre, $email, $telefono, $direccion, $id);
            $upd->execute();
            $_SESSION["nombre"] = $nombre;
            $_SESSION["email"]  = $email;
            $mensaje = "Datos actualizados correctamente.";
            $tipo    = "exito";
            $stmt->execute();
            $usuario = $stmt->get_result()->fetch_assoc();
        }
    }

    // ── Foto de perfil ────────────────────────────────────────────────────
    if ($accion === "foto" && isset($_FILES["foto"]) && $_FILES["foto"]["error"] === 0) {
        $ext        = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));
        $permitidos = ["jpg", "jpeg", "png", "webp", "gif"];
        if (!in_array($ext, $permitidos)) {
            $mensaje = "Formato no permitido. Usá JPG, PNG o WEBP.";
            $tipo    = "error";
        } elseif ($_FILES["foto"]["size"] > 2 * 1024 * 1024) {
            $mensaje = "La imagen no puede superar los 2MB.";
            $tipo    = "error";
        } else {
            $carpeta = "uploads/fotos_perfil/";
            if (!is_dir($carpeta)) mkdir($carpeta, 0755, true);
            if (!empty($usuario["foto"]) && file_exists($usuario["foto"])) {
                unlink($usuario["foto"]);
            }
            $nombreArchivo = "perfil_" . $id . "_" . time() . "." . $ext;
            $ruta = $carpeta . $nombreArchivo;
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $ruta)) {
                $upd = $conn->prepare("UPDATE usuarios SET foto=? WHERE id_usuario=?");
                $upd->bind_param("si", $ruta, $id);
                $upd->execute();
                $mensaje = "Foto actualizada.";
                $tipo    = "exito";
                $stmt->execute();
                $usuario = $stmt->get_result()->fetch_assoc();
            }
        }
    }

    // ── Cambiar contraseña ────────────────────────────────────────────────
    if ($accion === "password") {
        $tab_activa = "password";
        $actual  = $_POST["password_actual"];
        $nueva   = $_POST["password_nueva"];
        $repetir = $_POST["password_repetir"];

        if ($usuario["password"] !== $actual) {
            $mensaje = "La contraseña actual es incorrecta.";
            $tipo    = "error";
        } elseif (strlen($nueva) < 6) {
            $mensaje = "La nueva contraseña debe tener al menos 6 caracteres.";
            $tipo    = "error";
        } elseif ($nueva !== $repetir) {
            $mensaje = "Las contraseñas nuevas no coinciden.";
            $tipo    = "error";
        } else {
            $upd = $conn->prepare("UPDATE usuarios SET password=? WHERE id_usuario=?");
            $upd->bind_param("si", $nueva, $id);
            $upd->execute();
            $mensaje = "Contraseña actualizada correctamente.";
            $tipo    = "exito";
        }
    }
}
// ───────────────────────────────────────────────────────────────────────────
// Recién acá empieza el HTML
include("includes/header.php");
?>

<link rel="stylesheet" href="css/auth.css">
<style>
    .perfil-page {
        min-height: 100vh;
        padding: 60px 20px 80px;
        position: relative;
        overflow: hidden;
    }

    .perfil-wrap {
        max-width: 760px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }

    .perfil-hero {
        display: flex;
        align-items: center;
        gap: 28px;
        margin-bottom: 40px;
        animation: fadeUp .5s ease both;
    }

    .avatar-ring {
        position: relative;
        width: 96px;
        height: 96px;
        flex-shrink: 0;
    }

    .avatar-img {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(255, 255, 255, .12);
        display: block;
    }

    .avatar-placeholder {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1a1a2e, #16213e);
        border: 2px solid rgba(255, 255, 255, .12);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.4rem;
        font-weight: 700;
        color: rgba(255, 255, 255, .6);
    }

    .avatar-edit-btn {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #fff;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .4);
        transition: transform .2s;
    }

    .avatar-edit-btn:hover {
        transform: scale(1.12);
    }

    .perfil-hero-info h1 {
        font-size: 1.6rem;
        font-weight: 700;
        color: #fff;
        margin: 0 0 4px;
    }

    .perfil-hero-info p {
        color: rgba(255, 255, 255, .4);
        font-size: .9rem;
        margin: 0;
    }

    .tabs {
        display: flex;
        gap: 4px;
        background: rgba(255, 255, 255, .04);
        border-radius: 12px;
        padding: 4px;
        margin-bottom: 28px;
        border: 1px solid rgba(255, 255, 255, .07);
        animation: fadeUp .5s .1s ease both;
    }

    .tab-btn {
        flex: 1;
        padding: 10px;
        border: none;
        background: transparent;
        color: rgba(255, 255, 255, .4);
        border-radius: 9px;
        cursor: pointer;
        font-size: .85rem;
        font-weight: 500;
        transition: all .2s;
    }

    .tab-btn.active {
        background: rgba(255, 255, 255, .1);
        color: #fff;
    }

    .tab-btn:hover:not(.active) {
        color: rgba(255, 255, 255, .7);
    }

    .tab-panel {
        display: none;
        animation: fadeUp .3s ease both;
    }

    .tab-panel.active {
        display: block;
    }

    .perfil-card {
        background: rgba(255, 255, 255, .04);
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 20px;
        padding: 32px;
        position: relative;
        overflow: hidden;
    }

    .perfil-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse at 50% 0%, rgba(255, 255, 255, .03) 0%, transparent 70%);
        pointer-events: none;
    }

    .fields-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0 20px;
    }

    .fields-grid .full {
        grid-column: 1 / -1;
    }

    @media(max-width:520px) {
        .fields-grid {
            grid-template-columns: 1fr;
        }
    }

    .alerta {
        padding: 12px 16px;
        border-radius: 10px;
        font-size: .85rem;
        margin-bottom: 20px;
        text-align: center;
    }

    .alerta.exito {
        color: #4ade80;
        background: rgba(74, 222, 128, .08);
        border: 1px solid rgba(74, 222, 128, .2);
    }

    .alerta.error {
        color: #ff6b6b;
        background: rgba(255, 107, 107, .08);
        border: 1px solid rgba(255, 107, 107, .2);
    }

    .divider {
        border: none;
        border-top: 1px solid rgba(255, 255, 255, .07);
        margin: 24px 0;
    }

    .section-label {
        font-size: .75rem;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, .3);
        margin-bottom: 16px;
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(14px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #foto-input {
        display: none;
    }
</style>

<div class="perfil-page">
    <div class="blob-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="perfil-wrap">

        <!-- Avatar + nombre -->
        <div class="perfil-hero">
            <div class="avatar-ring">
                <?php if (!empty($usuario["foto"]) && file_exists($usuario["foto"])): ?>
                    <img src="<?= htmlspecialchars($usuario["foto"]) ?>" class="avatar-img" alt="Foto de perfil">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <?= mb_strtoupper(mb_substr($usuario["nombre"], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <button class="avatar-edit-btn" onclick="document.getElementById('foto-input').click()" title="Cambiar foto">✏️</button>
            </div>
            <div class="perfil-hero-info">
                <h1><?= htmlspecialchars($usuario["nombre"]) ?></h1>
                <p><?= htmlspecialchars($usuario["email"]) ?></p>
            </div>
        </div>

        <!-- Form oculto para foto -->
        <form method="POST" enctype="multipart/form-data" id="form-foto">
            <input type="hidden" name="accion" value="foto">
            <input type="file" name="foto" id="foto-input" accept="image/*"
                onchange="document.getElementById('form-foto').submit()">
        </form>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn <?= $tab_activa === 'datos' ? 'active' : '' ?>" onclick="switchTab('datos', this)">Datos personales</button>
            <button class="tab-btn <?= $tab_activa === 'password' ? 'active' : '' ?>" onclick="switchTab('password', this)">Contraseña</button>
        </div>

        <!-- Mensaje -->
        <?php if ($mensaje): ?>
            <div class="alerta <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <!-- Panel: Datos personales -->
        <div class="tab-panel <?= $tab_activa === 'datos' ? 'active' : '' ?>" id="panel-datos">
            <div class="perfil-card">
                <p class="section-label">Información de la cuenta</p>
                <form method="POST">
                    <input type="hidden" name="accion" value="datos">
                    <div class="fields-grid">
                        <div class="input-group">
                            <label class="input-label">Nombre completo</label>
                            <input type="text" name="nombre" class="input-field"
                                value="<?= htmlspecialchars($usuario["nombre"]) ?>" required>
                        </div>
                        <div class="input-group">
                            <label class="input-label">Email</label>
                            <input type="email" name="email" class="input-field"
                                value="<?= htmlspecialchars($usuario["email"]) ?>" required>
                        </div>
                        <div class="input-group">
                            <label class="input-label">Teléfono</label>
                            <input type="tel" name="telefono" class="input-field"
                                placeholder="09X XXX XXX"
                                value="<?= htmlspecialchars($usuario["telefono"] ?? "") ?>">
                        </div>
                        <div class="input-group full">
                            <label class="input-label">Dirección</label>
                            <input type="text" name="direccion" class="input-field"
                                placeholder="Calle, número, ciudad"
                                value="<?= htmlspecialchars($usuario["direccion"] ?? "") ?>">
                        </div>
                    </div>
                    <button type="submit" class="submit-button" style="margin-top:8px;">
                        <div class="submit-button-glow"></div>
                        <div class="submit-button-inner">Guardar cambios</div>
                    </button>
                </form>
            </div>
        </div>

        <!-- Panel: Contraseña -->
        <div class="tab-panel <?= $tab_activa === 'password' ? 'active' : '' ?>" id="panel-password">
            <div class="perfil-card">
                <p class="section-label">Cambiar contraseña</p>
                <form method="POST">
                    <input type="hidden" name="accion" value="password">
                    <div class="input-group">
                        <label class="input-label">Contraseña actual</label>
                        <input type="password" name="password_actual" class="input-field" placeholder="••••••••" required>
                    </div>
                    <hr class="divider">
                    <div class="input-group">
                        <label class="input-label">Nueva contraseña</label>
                        <input type="password" name="password_nueva" id="pass_nueva" class="input-field" placeholder="••••••••" required minlength="6">
                    </div>
                    <div id="fuerza-wrap" style="margin:-4px 0 16px; display:none;">
                        <div style="display:flex;gap:4px;margin-bottom:4px;">
                            <div id="fb1" style="flex:1;height:3px;border-radius:2px;background:#333;transition:background .3s;"></div>
                            <div id="fb2" style="flex:1;height:3px;border-radius:2px;background:#333;transition:background .3s;"></div>
                            <div id="fb3" style="flex:1;height:3px;border-radius:2px;background:#333;transition:background .3s;"></div>
                            <div id="fb4" style="flex:1;height:3px;border-radius:2px;background:#333;transition:background .3s;"></div>
                        </div>
                        <p id="fuerza-txt" style="font-size:12px;margin:0;color:#888;"></p>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Repetir nueva contraseña</label>
                        <input type="password" name="password_repetir" id="pass_rep" class="input-field" placeholder="••••••••" required minlength="6">
                    </div>
                    <p id="match-txt" style="font-size:12px;margin:-8px 0 16px;display:none;"></p>
                    <button type="submit" class="submit-button">
                        <div class="submit-button-glow"></div>
                        <div class="submit-button-inner">Actualizar contraseña</div>
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    function switchTab(id, btn) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('panel-' + id).classList.add('active');
        btn.classList.add('active');
    }

    document.getElementById('pass_nueva').addEventListener('input', function() {
        const v = this.value;
        const wrap = document.getElementById('fuerza-wrap');
        if (!v) {
            wrap.style.display = 'none';
            return;
        }
        wrap.style.display = 'block';
        let f = 0;
        if (v.length >= 6) f++;
        if (v.length >= 10) f++;
        if (/[A-Z]/.test(v) && /[0-9]/.test(v)) f++;
        if (/[^A-Za-z0-9]/.test(v)) f++;
        const cols = ['#ff6b6b', '#ffa94d', '#ffd43b', '#4ade80'];
        const txts = ['Muy débil', 'Débil', 'Buena', 'Fuerte'];
        ['fb1', 'fb2', 'fb3', 'fb4'].forEach((id, i) => {
            document.getElementById(id).style.background = i < f ? cols[f - 1] : '#333';
        });
        const txt = document.getElementById('fuerza-txt');
        txt.textContent = txts[f - 1] || '';
        txt.style.color = cols[f - 1] || '#888';
        checkMatch();
    });

    function checkMatch() {
        const n = document.getElementById('pass_nueva').value;
        const r = document.getElementById('pass_rep').value;
        const el = document.getElementById('match-txt');
        if (!r) {
            el.style.display = 'none';
            return;
        }
        el.style.display = 'block';
        if (n === r) {
            el.textContent = '✓ Las contraseñas coinciden';
            el.style.color = '#4ade80';
        } else {
            el.textContent = '✗ No coinciden';
            el.style.color = '#ff6b6b';
        }
    }
    document.getElementById('pass_rep').addEventListener('input', checkMatch);
</script>

<?php include("includes/footer.php"); ?>