<?php
// model/User.php

class UserModel {
  protected $conn;

  // The database connection link is injected through the constructor
  public function __construct($db) {
    $this->conn = $db;
  }

  // ==========================================
  // 🔐 AUTHENTICATION METHODS (Google & Manual)
  // ==========================================

  /**
   * Look up a user profile by email address
   */
  public function findByEmail($email) {
    $safeEmail = $this->conn->real_escape_string($email);
    $sql = "SELECT * FROM users WHERE email = '$safeEmail' LIMIT 1";
    $result = $this->conn->query($sql);

    if ($result && $result->num_rows > 0) {
      return $result->fetch_assoc();
    }
    return null;
  }

  /**
   * Insert a brand new user into the database
   */
  public function createManual($name, $email, $password, $role) {
    $safeName = $this->conn->real_escape_string($name);
    $safeEmail = $this->conn->real_escape_string($email);
    $safePassword = $this->conn->real_escape_string($password);
    $safeRole = $this->conn->real_escape_string($role);

    $sql = "INSERT INTO users (name, email, password, role)
            VALUES ('$safeName', '$safeEmail', '$safePassword', '$safeRole')";

    if ($this->conn->query($sql)) {
      return $this->conn->insert_id;
    }
    return false;
  }

  /**
   * Create complementary sub-profile inside the employers table
   */
  public function createEmployer($userId) {
    $userId = (int) $userId;
    $sql = "INSERT INTO employers (user_id, company_name) VALUES ($userId, '')";
    return $this->conn->query($sql);
  }

  /**
   * Create complementary sub-profile inside the job_seekers table
   */
  public function createJobSeeker($userId) {
    $userId = (int) $userId;
    $sql = "INSERT INTO job_seekers (user_id) VALUES ($userId)";
    return $this->conn->query($sql);
  }

  // ==========================================
  // 🛠️ STANDARD CRUD METHODS (For Dashboard Management)
  // ==========================================

  /**
   * READ: Get a single user's information by their database ID
   */
  public function getUser($id) {
    $id = (int) $id;
    $sql = "SELECT id, name, email, role, created_at FROM users WHERE id = $id LIMIT 1";
    $result = $this->conn->query($sql);

    if ($result && $result->num_rows > 0) {
      return $result->fetch_assoc();
    }
    return null;
  }

  /**
   * READ ALL: Display all users in the system (e.g., Admin Panel view)
   */
  public function displayUser() {
    $sql = "SELECT id, name, email, role, created_at FROM users";
    $result = $this->conn->query($sql);

    $usersList = [];
    if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $usersList[] = $row;
      }
    }
    return $usersList;
  }

  /**
   * UPDATE: Modify an existing user's basic profile details
   */
  public function updateUser($name, $email, $id) {
    $safeName = $this->conn->real_escape_string($name);
    $safeEmail = $this->conn->real_escape_string($email);
    $id = (int) $id;

    $sql = "UPDATE users SET name = '$safeName', email = '$safeEmail' WHERE id = $id";
    if ($this->conn->query($sql)) {
      return ["status" => "Update Successful"];
    }
    return ["status" => "Error"];
  }

  /**
   * DELETE: Permanently remove a user from the platform
   */
  public function deleteUser($id) {
    $id = (int) $id;
    $sql = "DELETE FROM users WHERE id = $id";

    if ($this->conn->query($sql)) {
      return ["status" => "Delete Successful"];
    }
    return ["status" => "Error"];
  }
}
