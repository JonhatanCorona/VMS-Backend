<?php
require_once __DIR__ . "/../config/database.php";

$db = new Database();
$pdo = $db->pdo;

$nombre = "Carlos Pérez";
$email = "operador@otp.com";
$password_plain = "123456"; 
$rol = "operador";
$almacen_id = 2;

// Generar hash válido
$password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

$sql = "INSERT INTO t_usuarios (nombre, email, password, rol, almacen_id)
        VALUES (:nombre, :email, :password, :rol, :almacen_id)";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([
        ':nombre' => $nombre,
        ':email' => $email,
        ':password' => $password_hash,
        ':rol' => $rol,
        ':almacen_id' => $almacen_id
    ]);
    echo "Usuario creado correctamente con hash válido.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
