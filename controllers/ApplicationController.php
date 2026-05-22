<?php
class ApplicationController {

  private $conn;

  // Standard constructor layout passing the shared MySQLi connection
  public function __construct($db) {
    $this->conn = $db;
  }

  /**
   * Action: GET all applications submitted to an employer's job listings
   */
  public function handleGetEmployerApplications() {
    if (!isset($_SESSION['user_id'])) {
      echo json_encode(["status" => "error", "message" => "Session missing or unauthorized access."]);
      exit;
    }

    $userId = (int) $_SESSION['user_id'];

    // Clean procedural flat query to gather incoming user applicant listings
    $sql = "SELECT 
          a.id AS application_id,
          a.resume_url,
          a.status,
          a.applied_at,
          j.title AS job_title,
          u.name AS applicant_name
      FROM applications a
      INNER JOIN jobs j ON a.job_id = j.id
      INNER JOIN employers e ON j.employer_id = e.id
      INNER JOIN job_seekers js ON a.seeker_id = js.id
      INNER JOIN users u ON js.user_id = u.id
      WHERE e.user_id = $userId
      ORDER BY a.applied_at DESC";

    $result = $this->conn->query($sql);
    $applicationsList = [];

    if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $applicationsList[] = $row;
      }
    }

    echo json_encode([
      "status" => "success",
      "data" => $applicationsList
    ]);
    exit;
  }

  /**
   * Action: POST update an applicant's evaluation review state status
   */
  public function handleUpdateStatus($jsonData) {
      if (!isset($_SESSION['user_id'])) {
          echo json_encode(["status" => "error", "message" => "Session missing or unauthorized access."]);
          exit;
      }

      $appId = isset($jsonData['application_id']) ? (int) $jsonData['application_id'] : 0;
      $status = isset($jsonData['status']) ? $this->conn->real_escape_string($jsonData['status']) : '';

      // Constrain input arrays to valid enum configurations matching database schema blueprint
      if (!in_array($status, ['pending', 'reviewed', 'accepted', 'rejected'])) {
          echo json_encode(["status" => "error", "message" => "Invalid status attribute modification parameters."]);
          exit;
      }

      $sql = "UPDATE applications SET status = '$status' WHERE id = $appId";
      $success = $this->conn->query($sql);

      if ($success) {
          echo json_encode([
              "status" => "success",
              "message" => "Applicant status tracking state updated to: " . $status
          ]);
      } else {
          echo json_encode(["status" => "error", "message" => "Database mutation execution error."]);
      }
      exit;
  }

  /**
   * Action: GET all applications submitted by a single job seeker
   */
  public function handleGetSeekerApplications() {
      if (!isset($_SESSION['user_id'])) {
          echo json_encode(["status" => "error", "message" => "Session missing."]);
          exit;
      }

      $userId = (int) $_SESSION['user_id'];

      $sql = "SELECT 
                  a.id as application_id, a.status, a.applied_at,
                  j.title, em.company_name
              FROM applications a
              INNER JOIN jobs j ON a.job_id = j.id
              INNER JOIN employers em ON j.employer_id = em.id
              INNER JOIN job_seekers js ON a.seeker_id = js.id
              WHERE js.user_id = $userId";

      $result = $this->conn->query($sql);
      $myApps = [];

      if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              $myApps[] = $row;
          }
      }

      echo json_encode(["status" => "success", "data" => $myApps]);
      exit;
  }
}