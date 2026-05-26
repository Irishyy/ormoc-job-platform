<?php

class DatabaseConnection {
  private $host = "localhost";
  private $user = "root";
  private $pass = "";
  private $db = "ormoc_job_db";

  public $google_client_id = "457247832144-vos0rhcnost6rau41c29iaejba4i9981.apps.googleusercontent.com";

  public $conn;

  public function connect()
  {
    mysqli_report(MYSQLI_REPORT_OFF);

    $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db);

    if ($this->conn->connect_error) {
      echo json_encode([
        "status" => "error",
        "message" => "Database connection failed. Start MySQL in XAMPP Control Panel."
      ]);
      exit;
    }

    return $this->conn;
  }
}
