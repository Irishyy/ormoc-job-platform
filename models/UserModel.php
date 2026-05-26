<?php
// models/UserModel.php
// This file handles everything related to the "users" table.
// It finds users, creates users, and sets up their profile rows.

class UserModel {

    private $conn;

    // Receive the database connection when this class is created
    public function __construct($db) {
        $this->conn = $db;
    }

    // -----------------------------------------------------------
    // Find a user by their email address
    // Returns the user row as an array, or false if not found
    // -----------------------------------------------------------
    public function findByEmail($email) {
        $email  = $this->conn->real_escape_string($email);
        $result = $this->conn->query("SELECT id, name, email, password, role FROM users WHERE email = '$email' LIMIT 1");

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return false;
    }

    // -----------------------------------------------------------
    // Create a new user row in the "users" table
    // Returns the new user's ID, or false if it failed
    // -----------------------------------------------------------
    public function createUser($name, $email, $password, $role) {
        $name     = $this->conn->real_escape_string($name);
        $email    = $this->conn->real_escape_string($email);
        $password = $this->conn->real_escape_string($password);
        $role     = $this->conn->real_escape_string($role);

        $this->conn->query("INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')");

        if ($this->conn->insert_id) {
            return $this->conn->insert_id;
        }

        return false;
    }

    // -----------------------------------------------------------
    // Create a row in the "employers" table for a new employer
    // -----------------------------------------------------------
    public function createEmployer($userId) {
        $userId = (int) $userId;
        return $this->conn->query("INSERT INTO employers (user_id, company_name, company_logo_url) VALUES ($userId, '', '')");
    }

    // -----------------------------------------------------------
    // Create a row in the "job_seekers" table for a new seeker
    // -----------------------------------------------------------
    public function createJobSeeker($userId) {
        $userId = (int) $userId;
        return $this->conn->query("INSERT INTO job_seekers (user_id) VALUES ($userId)");
    }
}