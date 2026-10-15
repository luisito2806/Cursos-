<?php
session_start();
include_once "conexion.php";

if (!isset($conexion) || $conexion === null) {
    $conexion = conectar();
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = trim($_POST["correo"]);
    $password = $_POST["password"];

    $sql = "SELECT * FROM usuarios WHERE correo = ?";
    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        die("Error en la consulta: " . $conexion->error);
    }

    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        if ($usuario["estado"] === "pendiente") {
            $mensaje = "Tu cuenta está pendiente de aprobación.";
        } elseif ($usuario["estado"] === "rechazado") {
            $mensaje = "Tu solicitud fue rechazada.";
        } elseif (password_verify($password, $usuario["password"]) || $password === $usuario["password"]) {
            session_regenerate_id(true);
            $_SESSION["usuario_id"] = $usuario["id"];
            $_SESSION["nombre"] = $usuario["nombre"];
            $_SESSION["rol"] = $usuario["rol"];
            header("Location: dashboard.php");
            exit();
        } else {
            $mensaje = "Contraseña incorrecta.";
        }
    } else {
        $mensaje = "Usuario no encontrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión — Escuela</title>
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
            height: 100vh;
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
        h1 { font-size: 35px; margin-top: 0; }
        label { display: block; font-size: 16px; margin-bottom: 8px; font-weight: 500; }
        input {
            width: 100%;
            background: var(--color-chalk-blue);
            border: 1px solid var(--color-frost-blue);
            border-radius: 5000px;
            padding: 12px 20px;
            margin-bottom: 20px;
            font-size: 16px;
            box-sizing: border-box;
            outline: none;
        }
        button {
            background: var(--color-electric-iris);
            color: var(--color-paper-white);
            border: none;
            border-radius: 48px;
            padding: 12px 24px;
            font-size: 18px;
            width: 100%;
            cursor: pointer;
            box-shadow: 0 4px 0 0 var(--color-ink-black);
            font-weight: bold;
        }
        p { font-size: 15px; text-align: center; color: #ff4141; font-weight: bold; }
        .footer-link { text-align: center; margin-top: 20px; }
        a { color: var(--color-electric-iris); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Iniciar sesión</h1>
        <form method="POST">
            <label>Correo electrónico</label>
            <input type="email" name="correo" required>
            
            <label>Contraseña</label>
            <input type="password" name="password" required>
            
            <button type="submit">Entrar →</button>
        </form>
        <p><?php echo htmlspecialchars($mensaje); ?></p>
        <div class="footer-link">
            <a href="registro.php">Crear cuenta nueva</a>
        </div>
    </div>
</body>
</html>