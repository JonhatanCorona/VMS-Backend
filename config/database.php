<?php

require __DIR__ . '/../vendor/autoload.php';

// Opcional: cargar .env para otras variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad(); // safeLoad evita error si .env no existe

class Database {
    public $pdo;

    public function __construct() {
        // URL completa de conexión MySQL (puedes ponerla en .env o directamente aquí)
        $url = getenv('MYSQL_URL');

        // Parsear URL
        $parts = parse_url($url);
        if (!$parts) {
            die(json_encode(["status" => "error", "message" => "Invalid MySQL URL"]));
        }

        $user = $parts['user'];
        $pass = $parts['pass'];
        $host = $parts['host'];
        $port = $parts['port'];
        $db   = ltrim($parts['path'], '/');

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            // echo json_encode(["status" => "success", "message" => "DB connection OK"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "DB connection error: " . $e->getMessage()
            ]);
            exit;
        }
    }  
}

// Instancia
$db = new Database();
