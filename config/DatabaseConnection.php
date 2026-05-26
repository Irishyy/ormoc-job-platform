<?php
// config/DatabaseConnection.php
// This file creates the connection to your MySQL database.

class DatabaseConnection {

    // Your database login details — change these to match your setup
    private $host = "localhost";
    private $user = "root";
    private $pass = "";         // Leave empty if you have no password (default XAMPP)
    private $db   = "ormoc_job_db";

    public $conn; // The connection object other files will use

    // Connect to MySQL and return the connection
    public function connect() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db);

        // If the connection fails, stop and send an error message
        if ($this->conn->connect_error) {
            echo json_encode([
                "status"  => "error",
                "message" => "Could not connect to database. Make sure MySQL is running in XAMPP."
            ]);
            exit;
        }

        return $this->conn;
    }

    // Return the Google Client ID used for Google Sign-In
    public function getGoogleClientId() {
        return "457247832144-vos0rhcnost6rau41c29iaejba4i9981.apps.googleusercontent.com";
    }
}