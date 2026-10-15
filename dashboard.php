<?php
session_start();
include_once "conexion.php";

if (!isset($conexion) || $conexion === null) {
    $conexion = conectar();
}

if (isset($_GET['salir'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

$rolUsuario = $_SESSION["rol"] ?? '';
$nombreUsuario = $_SESSION["nombre"] ?? 'Usuario';

// Jerarquía de aprobación: Superadmin -> Director | Director -> Alumno y Ponente
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["accion"], $_POST["target_id"])) {
    $targetId = (int)$_POST["target_id"];
    $accion = $_POST["accion"] === "aprobar" ? "aprobado" : "rechazado";

    if ($rolUsuario === 'superadmin') {
        $sqlAction = "UPDATE usuarios SET estado = ? WHERE id = ? AND rol = 'director'";
    } elseif ($rolUsuario === 'director') {
        $sqlAction = "UPDATE usuarios SET estado = ? WHERE id = ? AND rol IN ('alumno', 'ponente')";
    }

    if (isset($sqlAction)) {
        $stmtAct = $conexion->prepare($sqlAction);
        $stmtAct->bind_param("si", $accion, $targetId);
        $stmtAct->execute();
    }
}

// Consultar usuarios pendientes según el rol
$pendientes = null;
if ($rolUsuario === 'superadmin') {
    $sqlPendientes = "SELECT id, nombre, correo, rol FROM usuarios WHERE estado = 'pendiente' AND rol = 'director'";
    $pendientes = $conexion->query($sqlPendientes);
} elseif ($rolUsuario === 'director') {
    $sqlPendientes = "SELECT id, nombre, correo, rol FROM usuarios WHERE estado = 'pendiente' AND rol IN ('alumno', 'ponente')";
    $pendientes = $conexion->query($sqlPendientes);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control — Escuela</title>
    <style>
        :root {
            --color-chalk-blue: #f0f6ff;
            --color-paper-white: #ffffff;
            --color-ink-black: #111118;
            --color-electric-iris: #2727e6;
            --color-frost-blue: #e1edff;
        }
        body {
            background-color: var(--color-chalk-blue);
            font-family: ui-sans-serif, system-ui, sans-serif;
            color: var(--color-ink-black);
            margin: 0;
            padding: 40px 20px;
        }
        .container { max-width: 900px; margin: 0 auto; }
        .card {
            background: var(--color-paper-white);
            border: 1px solid var(--color-frost-blue);
            border-radius: 24px;
            padding: 32px;
            margin-bottom: 24px;
            box-shadow: 0 2px 0 0 var(--color-ink-black);
        }
        h1 { font-size: 36px; margin-top: 0; }
        h2 { font-size: 22px; border-bottom: 2px solid var(--color-chalk-blue); padding-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 12px 16px; text-align: left; border-bottom: 1px solid var(--color-frost-blue); }
        th { font-weight: 600; color: var(--color-electric-iris); }
        .btn {
            border: none;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-approve { background: #22c55e; color: white; }
        .btn-reject { background: #ef4444; color: white; }
        .btn-logout {
            display: inline-block;
            background: var(--color-ink-black);
            color: var(--color-paper-white);
            padding: 10px 20px;
            border-radius: 48px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 4px 0 0 var(--color-electric-iris);
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Bienvenido, <?php echo htmlspecialchars($nombreUsuario); ?> 👋</h1>
            <p>Rol: <strong><?php echo ucfirst($rolUsuario); ?></strong></p>
        </div>

        <?php if ($pendientes && $pendientes->num_rows > 0): ?>
        <div class="card">
            <h2>Solicitudes Pendientes de Aprobación</h2>
            <table>
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol Solicitado</th>
                    <th>Acción</th>
                </tr>
                <?php while ($fila = $pendientes->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila["nombre"]); ?></td>
                    <td><?php echo htmlspecialchars($fila["correo"]); ?></td>
                    <td><strong><?php echo ucfirst($fila["rol"]); ?></strong></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="target_id" value="<?php echo $fila['id']; ?>">
                            <button type="submit" name="accion" value="aprobar" class="btn btn-approve">Aceptar</button>
                            <button type="submit" name="accion" value="rechazar" class="btn btn-reject">Rechazar</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <?php elseif ($rolUsuario === 'superadmin' || $rolUsuario === 'director'): ?>
        <div class="card">
            <p>No tienes ninguna solicitud pendiente de aprobación en este momento.</p>
        </div>
        <?php endif; ?>

        <div class="card">
            <a href="dashboard.php?salir=true" class="btn-logout">Cerrar sesión</a>
        </div>
    </div>
</body>
</html>