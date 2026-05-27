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
        // Prepare the query with ? as a placeholder for the email value
        $stmt = $this->conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

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
        $stmt = $this->conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");

        // "ssss" means four string placeholders, one per ? in order
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        $stmt->execute();

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

        // since napasa naman na ang uban values sa db through the user gamit lang ta placeholder aria 
        $stmt = $this->conn->prepare("INSERT INTO employers (user_id) VALUES (?)");

        // "i" means the placeholder expects an integer type
        $stmt->bind_param("i", $userId);

        return $stmt->execute();
    }

    // -----------------------------------------------------------
    // Create a row in the "job_seekers" table for a new seeker
    // -----------------------------------------------------------
    public function createJobSeeker($userId) {
        $userId = (int) $userId;

        $stmt = $this->conn->prepare("INSERT INTO job_seekers (user_id) VALUES (?)");

        $stmt->bind_param("i", $userId);

        return $stmt->execute();
    }
}