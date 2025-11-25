<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../utils/helpers.php";

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

$db = new Database();
$pdo = $db->pdo;

$token = get_bearer_token();
if (!$token) {
    send_json(["status"=>"error","message"=>"Token requerido (Authorization: Bearer <token>)."], 401);
}

// Buscar token válido y no expirado
$sql = "SELECT t.usuario_id, t.expires_at, u.id, u.nombre, u.email, u.rol, u.almacen_id
        FROM t_tokens t
        JOIN t_usuarios u ON u.id = t.usuario_id
        WHERE t.token = :token
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':token' => $token]);
$row = $stmt->fetch();

if (!$row) {
    send_json(["status"=>"error","message"=>"Token inválido."], 401);
}

// Verificar expiración
$now = new DateTime();
$expires = new DateTime($row['expires_at']);
if ($now > $expires) {
    send_json(["status"=>"error","message"=>"Token expirado."], 401);
}

// Responder con info del usuario + su almacén
send_json([
    "status" => "success",
    "user" => [
        "id" => (int)$row['id'],
        "nombre" => $row['nombre'],
        "email" => $row['email'],
        "rol" => $row['rol'],
        "almacen_id" => $row['almacen_id'] !== null ? (int)$row['almacen_id'] : null
    ]
]);
