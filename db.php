<?php
class Database {
    private $host = "localhost";
    private $dbname = "est_task";
    private $user = "root";
    private $pass = "";

    public $conn;

    public function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
}
?>
