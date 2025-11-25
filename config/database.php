<?php

class Database {
    private $host = '127.0.0.1';
    private $port = 3307; 
    private $db   = "wms_db";
    private $user = "root";
    private $pass = "";
    private $charset = "utf8mb4";
    public $pdo;

    public function __construct() {
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
