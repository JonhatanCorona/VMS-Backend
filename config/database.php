<?php

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
        $this->host = getenv('DB_HOST') ?: '127.0.0.1';
        $this->port = getenv('DB_PORT') ?: 3306;
        $this->db   = getenv('DB_NAME') ?: 'wms_db';
        $this->user = getenv('DB_USER') ?: 'root';
        $this->pass = getenv('DB_PASS') ?: '';
        $this->charset = 'utf8mb4';

        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db};charset={$this->charset}";
        $opts = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $opts);
            // Para pruebas: descomenta la siguiente línea
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
