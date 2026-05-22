<?php
require_once __DIR__ . "/../models/JobModel.php";

class JobController {

  private $conn;
  private $jobModel;

  // Multi-line explicit constructor passing the MySQLi connection instance
  public function __construct($db) {
    $this->conn = $db;
    $this->jobModel = new JobModel($this->conn);
  }

  /**
   * Action: GET all jobs across the platform (for Seeker Dashboard Map/Feed)
   */
  public function handleGetAllJobs() {
      $jobs = $this->jobModel->getAllJobs();
      
      echo json_encode(["status" => "success", "data" => $jobs]);
      exit;
  }

  /**
   * Action: GET jobs belonging ONLY to the logged-in employer
   */
  public function handleGetEmployerJobs() {
    if (!isset($_SESSION['user_id'])) {
      echo json_encode(["status" => "error", "message" => "Session missing or unauthorized access."]);
      exit;
    }

    // Find the active sub-profile matching this user account ID
    $employer = $this->jobModel->getEmployerByUserId($_SESSION['user_id']);
    if (!$employer) {
      echo json_encode(["status" => "success", "data" => []]);
      exit;
    }

    // Fetch jobs using the correct relational employers.id primary key
    $jobs = $this->jobModel->getJobsByEmployer($employer['id']);
    
    echo json_encode(["status" => "success","data" => $jobs]);
    exit;
  }

  /**
   * Action: POST publish a new job vacancy with map markers
   */
  public function handlePublishJob($jsonData) {
    if (!isset($_SESSION['user_id'])) {
      echo json_encode(["status" => "error", "message" => "Session missing or expired."]);
      exit;
    }

    // Validate that the user actually owns an employer sub-profile record
    $employer = $this->jobModel->getEmployerByUserId($_SESSION['user_id']);
    if (!$employer) {
      echo json_encode(["status" => "error", "message" => "Profile mismatch. Account is not registered as an employer."]);
      exit;
    }

    $title = $jsonData['title'] ?? '';
    $description = $jsonData['description'] ?? '';
    $latitude = $jsonData['latitude'] ?? null;
    $longitude = $jsonData['longitude'] ?? null;

    if (empty($title) || empty($description)) {
      echo json_encode(["status" => "error", "message" => "Job title and description are mandatory fields."]);
      exit;
    }

    // Fire database creation method inside our model
    $newJobId = $this->jobModel->createJob($employer['id'], $title, $description, $latitude, $longitude);

    if ($newJobId) {
      echo json_encode([
        "status" => "success",
        "message" => "Vacancy published successfully!",
        "job_id" => $newJobId
      ]);
    } else {
      echo json_encode(["status" => "error", "message" => "Database write insertion fault."]);
    }
    exit;
  }

  // Inside controllers/JobController.php

    /**
     * Action: POST save or update employer profile metadata (Company Name, Cloudinary Logo URL)
     * @param array $jsonData - The decoded JSON payload arriving from Axios
     */
    public function handleSaveEmployerProfile($jsonData) {
      // 1. Session Authorization Safeguard Check
      if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "Session expired or unauthorized request context."]);
        exit;
      }

      $userId = (int) $_SESSION['user_id'];
      
      // 2. Extract values safely from incoming JSON data packets
      $companyName = isset($jsonData['company_name']) ? trim($jsonData['company_name']) : '';
      $logoUrl = isset($jsonData['company_logo_url']) ? trim($jsonData['company_logo_url']) : '';

      // Fallback validation rules
      if (empty($companyName) || $companyName === "Loading...") {
        $companyName = "My Corporate Entity"; // Safe structural fallback string
      }

      // Sanitization Layer to completely neutralize malicious string characters
      $safeCompanyName = $this->conn->real_escape_string($companyName);
      $safeLogoUrl = $this->conn->real_escape_string($logoUrl);

      // 3. Check if this user already has an existing sub-profile record
      $checkSql = "SELECT id FROM employers WHERE user_id = $userId LIMIT 1";
      $checkResult = $this->conn->query($checkSql);

      if ($checkResult && $checkResult->num_rows > 0) {
        // OPTION A: Row exists! Let's modify the profile parameters with an UPDATE query
        $sql = "UPDATE employers 
                SET company_name = '$safeCompanyName', company_logo_url = '$safeLogoUrl' 
                WHERE user_id = $userId";
      } else {
          // OPTION B: Fresh user! Let's establish a new profile row slot via an INSERT query
          $sql = "INSERT INTO employers (user_id, company_name, company_logo_url) 
                  VALUES ($userId, '$safeCompanyName', '$safeLogoUrl')";
      }

      // 4. Fire the query statement at your MySQL cluster
      $executionSuccess = $this->conn->query($sql);

      if ($executionSuccess) {
          echo json_encode([
              "status" => "success",
              "message" => "Employer profile adjustments synchronized successfully!"
          ]);
      } else {
          // DB tracking log fail fallback info
          echo json_encode([
              "status" => "error", 
              "message" => "Database modification error statement: " . $this->conn->error
          ]);
      }
      exit;
    }

    /**
     * Action: POST delete an existing vacancy listing
     */
    public function handleDeleteJob($jsonData) {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(["status" => "error", "message" => "Session missing or unauthorized access."]);
            exit;
        }

        $jobId = isset($jsonData['job_id']) ? (int)$jsonData['job_id'] : 0;

        if ($jobId <= 0) {
            echo json_encode(["status" => "error", "message" => "Invalid or missing Job identifier parameter."]);
            exit;
        }

        // 1. Fetch the logged-in employer's record
        $employer = $this->jobModel->getEmployerByUserId($_SESSION['user_id']);
        if (!$employer) {
            echo json_encode(["status" => "error", "message" => "Employer sub-profile record missing."]);
            exit;
        }

        // 2. Double-check ownership structure using your built-in model safety check
        $belongsToUs = $this->jobModel->jobBelongsToEmployer($jobId, $employer['id']);
        if (!$belongsToUs) {
            echo json_encode(["status" => "error", "message" => "Security Alert: Unauthorized deletion request."]);
            exit;
        }

        // 3. Execute the flat deletion statement inside your model
        $affectedRows = $this->jobModel->deleteJob($jobId);

        if ($affectedRows > 0) {
            echo json_encode([
                "status" => "success",
                "message" => "Job listing removed permanently."
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Deletion process failed or item already removed."]);
        }
        exit;
    }
}