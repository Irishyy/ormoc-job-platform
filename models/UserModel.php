<?php
// models/UserModel.php

class UserModel {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // =========================================================
    // 🔍 FIND USER BY EMAIL ADDRESS
    // Returns the full user row as an associative array, or false.
    // =========================================================
    public function findByEmail($email) {
        $safeEmail = $this->conn->real_escape_string($email);
        $sql       = "SELECT id, name, email, password, role FROM users WHERE email = '$safeEmail' LIMIT 1";
        $result    = $this->conn->query($sql);

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return false;
    }

    // =========================================================
    // ✍️ CREATE A BASE USER RECORD
    // Returns the new user's auto-incremented ID, or false on failure.
    // =========================================================
    public function createManual($name, $email, $password, $role) {
        $safeName     = $this->conn->real_escape_string($name);
        $safeEmail    = $this->conn->real_escape_string($email);
        $safePassword = $this->conn->real_escape_string($password);
        $safeRole     = $this->conn->real_escape_string($role);

        $sql = "INSERT INTO users (name, email, password, role)
                VALUES ('$safeName', '$safeEmail', '$safePassword', '$safeRole')";

        $success = $this->conn->query($sql);

        if ($success) {
            return $this->conn->insert_id;
        }

        return false;
    }

    // =========================================================
    // 🏢 CREATE EMPLOYER SUB-PROFILE ROW
    // Called right after createManual() for employer accounts.
    // Returns true on success, false on failure.
    // =========================================================
    public function createEmployer($userId) {
        $safeUserId = (int) $userId;

        $sql     = "INSERT INTO employers (user_id, company_name, company_logo_url)
                    VALUES ($safeUserId, '', '')";
        $success = $this->conn->query($sql);

        return $success ? true : false;
    }

    // =========================================================
    // 👤 CREATE JOB SEEKER SUB-PROFILE ROW
    // Called right after createManual() for seeker accounts.
    // Returns true on success, false on failure.
    // =========================================================
    public function createJobSeeker($userId) {
        $safeUserId = (int) $userId;

        $sql     = "INSERT INTO job_seekers (user_id)
                    VALUES ($safeUserId)";
        $success = $this->conn->query($sql);

        return $success ? true : false;
    }
}