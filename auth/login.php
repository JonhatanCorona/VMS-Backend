<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../utils/helpers.php";

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$db = new Database();
$pdo = $db->pdo;

$input = json_input();
if (!$input) send_json(["status"=>"error","message"=>"JSON inválido."], 400);

$email = $input['email'] ?? null;
$password = $input['password'] ?? null;

if (!$email || !$password) {
    send_json(["status"=>"error","message"=>"Email y contraseña requeridos."], 400);
}

// 1) Buscar usuario por email
$sql = "SELECT id, nombre, email, password, rol, almacen_id FROM t_usuarios WHERE email = :email LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

if (!$user) {
    // No revelar si email o password es incorrecto
    send_json(["status"=>"error","message"=>"Credenciales inválidas."], 401);
}

// 2) Verificar contraseña (password_verify)
if (!password_verify($password, $user['password'])) {
    send_json(["status"=>"error","message"=>"Credenciales inválidas."], 401);
}

// 3) Obtener rol y almacen_id (ya traídos en la consulta)

// 4) Si usuario no tiene almacén → rechazar
if ($user['almacen_id'] === null) {
    send_json(["status"=>"error","message"=>"Usuario sin almacén asignado."], 403);
}

// 5) Generar token de sesión y guardarlo (tabla t_tokens)
$token = bin2hex(random_bytes(32));
$expires_at = (new DateTime("+24 hours"))->format('Y-m-d H:i:s');

$insert = "INSERT INTO t_tokens (token, usuario_id, expires_at, created_at) VALUES (:token, :usuario_id, :expires_at, NOW())";
$stmt = $pdo->prepare($insert);
$stmt->execute([
    ':token' => $token,
    ':usuario_id' => $user['id'],
    ':expires_at' => $expires_at
]);

// Respuesta exitosa
send_json([
    "status" => "success",
    "user" => [
        "id" => (int)$user['id'],
        "nombre" => $user['nombre'],
        "rol" => $user['rol'],
        "almacen_id" => (int)$user['almacen_id']
    ],
    "token" => $token
]);
