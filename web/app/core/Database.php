<?php
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $db_name = DB_NAME;

    private $dbh;
    private $stmt;

    public function __construct() {
        $portStr = (defined('DB_PORT') && DB_PORT) ? ';port=' . DB_PORT : '';
        $dsn = 'mysql:host=' . $this->host . $portStr . ';dbname=' . $this->db_name . ';charset=utf8mb4';
        
        $option = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+07:00'"
        ];

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $option);
            $this->dbh->exec("SET time_zone = '+07:00'");
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            die("Terjadi kesalahan pada koneksi database. Silakan hubungi administrator.");
        }
    }

    public function query($query) {
        $this->stmt = $this->dbh->prepare($query);
    }

    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute() {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            $logFile = dirname(__DIR__) . '/storage/logs/error.log';
            $msg = '[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . PHP_EOL;
            @file_put_contents($logFile, $msg, FILE_APPEND);
            throw $e; // re-throw agar exception handler utama menangkap
        }
    }

    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    public function rowCount() {
        return $this->stmt->rowCount();
    }

    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }
}
