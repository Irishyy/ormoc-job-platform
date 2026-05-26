<?php
// controllers/JobController.php
// This file handles all job-related actions: posting jobs, fetching jobs, and deleting jobs.
// It also handles saving the employer's company profile.

require_once __DIR__ . "/../models/JobModel.php";

class JobController {

    private $conn;
    private $jobModel;

    public function __construct($db) {
        $this->conn     = $db;
        $this->jobModel = new JobModel($db);
    }

    // -----------------------------------------------------------
    // Get all jobs across the platform
    // Used by the seeker dashboard to show pins on the map
    // -----------------------------------------------------------
    public function handleGetAllJobs() {
        $jobs = $this->jobModel->getAllJobs();
        echo json_encode(["status" => "success", "data" => $jobs]);
    }

    // -----------------------------------------------------------
    // Get only the jobs posted by the logged-in employer
    // -----------------------------------------------------------
    public function handleGetEmployerJobs() {
        if (!isset($_SESSION["user_id"])) {
            echo json_encode(["status" => "error", "message" => "You are not logged in."]);
            return;
        }

        // Find this employer's profile row using their user ID
        $employer = $this->jobModel->getEmployerByUserId($_SESSION["user_id"]);

        if (!$employer) {
            echo json_encode(["status" => "success", "data" => []]);
            return;
        }

        $jobs = $this->jobModel->getJobsByEmployer($employer["id"]);
        echo json_encode(["status" => "success", "data" => $jobs]);
    }

    // -----------------------------------------------------------
    // Post a new job listing
    // -----------------------------------------------------------
    public function handlePublishJob($data) {
        if (!isset($_SESSION["user_id"])) {
            echo json_encode(["status" => "error", "message" => "You are not logged in."]);
            return;
        }

        if (empty($data["title"]) || empty($data["description"])) {
            echo json_encode(["status" => "error", "message" => "Job title and description are required."]);
            return;
        }

        if (empty($data["latitude"]) || empty($data["longitude"])) {
            echo json_encode(["status" => "error", "message" => "Please pin a location on the map."]);
            return;
        }

        // Get the employer's profile row
        $employer = $this->jobModel->getEmployerByUserId($_SESSION["user_id"]);

        if (!$employer) {
            echo json_encode(["status" => "error", "message" => "Employer profile not found."]);
            return;
        }

        $newJobId = $this->jobModel->createJob(
            $employer["id"],
            $data["title"],
            $data["description"],
            $data["latitude"],
            $data["longitude"]
        );

        if ($newJobId) {
            echo json_encode(["status" => "success", "message" => "Job posted successfully!", "job_id" => $newJobId]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to save the job. Try again."]);
        }
    }

    // -----------------------------------------------------------
    // Delete a job listing
    // Only the employer who owns the job can delete it
    // -----------------------------------------------------------
    public function handleDeleteJob($data) {
        if (!isset($_SESSION["user_id"])) {
            echo json_encode(["status" => "error", "message" => "You are not logged in."]);
            return;
        }

        $jobId    = (int) ($data["job_id"] ?? 0);
        $employer = $this->jobModel->getEmployerByUserId($_SESSION["user_id"]);

        if (!$employer) {
            echo json_encode(["status" => "error", "message" => "Employer profile not found."]);
            return;
        }

        // Make sure this job actually belongs to this employer
        if (!$this->jobModel->jobBelongsToEmployer($jobId, $employer["id"])) {
            echo json_encode(["status" => "error", "message" => "You do not have permission to delete this job."]);
            return;
        }

        $deleted = $this->jobModel->deleteJob($jobId);

        if ($deleted > 0) {
            echo json_encode(["status" => "success", "message" => "Job deleted."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Could not delete the job."]);
        }
    }

    // -----------------------------------------------------------
    // Save or update the employer's company name and logo
    // -----------------------------------------------------------
    public function handleSaveEmployerProfile($data) {
        if (!isset($_SESSION["user_id"])) {
            echo json_encode(["status" => "error", "message" => "You are not logged in."]);
            return;
        }

        $userId      = (int) $_SESSION["user_id"];
        $companyName = $this->conn->real_escape_string(trim($data["company_name"] ?? ""));
        $logoUrl     = $this->conn->real_escape_string(trim($data["company_logo_url"] ?? ""));

        // Check if this employer already has a profile row
        $result = $this->conn->query("SELECT id FROM employers WHERE user_id = $userId LIMIT 1");

        if ($result && $result->num_rows > 0) {
            // Update the existing row
            $this->conn->query("UPDATE employers SET company_name = '$companyName', company_logo_url = '$logoUrl' WHERE user_id = $userId");
        } else {
            // Create a new row
            $this->conn->query("INSERT INTO employers (user_id, company_name, company_logo_url) VALUES ($userId, '$companyName', '$logoUrl')");
        }

        echo json_encode(["status" => "success", "message" => "Profile saved."]);
    }
}