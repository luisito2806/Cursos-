<?php
include_once "conexion.php";

if (!isset($conexion) || $conexion === null) {
    $conexion = conectar();
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"]);
    $correo = trim($_POST["correo"]);
    $password = $_POST["password"];
    $rol = $_POST["rol"];

    if ($rol === 'superadmin') {
        $mensaje = "No es posible registrar un Superadmin libremente.";
    } else {
        $sqlCheck = "SELECT id FROM usuarios WHERE correo = ?";
        $stmtCheck = $conexion->prepare($sqlCheck);
        $stmtCheck->bind_param("s", $correo);
        $stmtCheck->execute();
        
        if ($stmtCheck->get_result()->num_rows > 0) {
            $mensaje = "El correo electrónico ya está registrado.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $estado = 'pendiente';

            $sql = "INSERT INTO usuarios (nombre, correo, password, rol, estado) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("sssss", $nombre, $correo, $passwordHash, $rol, $estado);

            if ($stmt->execute()) {
                $mensaje = "¡Registro exitoso! Tu cuenta está en espera de aprobación.";
            } else {
                $mensaje = "Error al registrar el usuario.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro — Escuela</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: var(--color-paper-white);
            border: 1px solid var(--color-frost-blue);
            border-radius: 24px;
            padding: 32px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 4px 0 0 var(--color-ink-black);
        }
        h1 { font-size: 32px; margin-top: 0; }
        label { display: block; font-size: 15px; margin-bottom: 6px; font-weight: 500; }
        input, select {
            width: 100%;
            background: var(--color-chalk-blue);
            border: 1px solid var(--color-frost-blue);
            border-radius: 5000px;
            padding: 12px 20px;
            margin-bottom: 16px;
            font-size: 15px;
            box-sizing: border-box;
            outline: none;
        }
        select { border-radius: 12px; }
        button {
            background: var(--color-electric-iris);
            color: var(--color-paper-white);
            border: none;
            border-radius: 48px;
            padding: 12px 24px;
            font-size: 16px;
            width: 100%;
            cursor: pointer;
            box-shadow: 0 4px 0 0 var(--color-ink-black);
            font-weight: bold;
        }
        p { font-size: 14px; text-align: center; color: var(--color-electric-iris); margin-top: 15px; font-weight: bold; }
        .footer-link { text-align: center; margin-top: 15px; }
        a { color: var(--color-electric-iris); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Crear cuenta</h1>
        <form method="POST">
            <label>Nombre completo</label>
            <input type="text" name="nombre" required>
            
            <label>Correo electrónico</label>
            <input type="email" name="correo" required>
            
            <label>Contraseña</label>
            <input type="password" name="password" required>
            
            <label>Tipo de usuario</label>
            <select name="rol" required>
                <option value="director">Director</option>
                <option value="alumno">Alumno</option>
                <option value="ponente">Ponente</option>
            </select>
            
            <button type="submit">Registrarme →</button>
        </form>
        <p><?php echo htmlspecialchars($mensaje); ?></p>
        <div class="footer-link">
            <a href="login.php">¿Ya tienes cuenta? Inicia sesión</a>
        </div>
    </div>
</body>
</html>