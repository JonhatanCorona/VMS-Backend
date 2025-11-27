<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

class Database {
    private $host;
    private $port;
    private $db;
    private $user;
    private $pass;
    private $charset;
    public $pdo;

    public function __construct() {
        // Tomar configuración desde variables de entorno
        $this->host = getenv('MYSQLHOST') ?: '127.0.0.1';
        $this->port = getenv('MYSQLPORT') ?: 3307; 
        $this->db   = getenv('MYSQLDATABASE') ?: 'wms_db';
        $this->user = getenv('MYSQLUSER') ?: 'root';
        $this->pass = getenv('MYSQLPASSWORD') ?: '';
        $this->charset = 'utf8mb4';

        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db};charset={$this->charset}";
        $opts = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $opts);
            // Para pruebas:
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

// Instancia la clase después de definirla
$db = new Database();
