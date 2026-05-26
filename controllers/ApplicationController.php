<?php
// controllers/ApplicationController.php
// This file handles everything related to job applications:
// - Employers viewing who applied to their jobs
// - Employers updating an applicant's status
// - Seekers viewing their own applications

class ApplicationController {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // -----------------------------------------------------------
    // Get all applications sent to the logged-in employer's jobs
    // -----------------------------------------------------------
    public function handleGetEmployerApplications() {
        if (!isset($_SESSION["user_id"])) {
            echo json_encode(["status" => "error", "message" => "You are not logged in."]);
            return;
        }

        $userId = (int) $_SESSION["user_id"];

        // Join across 4 tables to get the applicant name, job title, resume, and status
        $result = $this->conn->query("
            SELECT
                a.id         AS application_id,
                a.resume_url,
                a.status,
                a.applied_at,
                j.title      AS job_title,
                u.name       AS applicant_name
            FROM applications a
            INNER JOIN jobs j        ON a.job_id    = j.id
            INNER JOIN employers e   ON j.employer_id = e.id
            INNER JOIN job_seekers js ON a.seeker_id = js.id
            INNER JOIN users u       ON js.user_id   = u.id
            WHERE e.user_id = $userId
            ORDER BY a.applied_at DESC
        ");

        $applications = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $applications[] = $row;
            }
        }

        echo json_encode(["status" => "success", "data" => $applications]);
    }

    // -----------------------------------------------------------
    // Update the status of one application
    // (e.g. change from "pending" to "accepted")
    // -----------------------------------------------------------
    public function handleUpdateStatus($data) {
        if (!isset($_SESSION["user_id"])) {
            echo json_encode(["status" => "error", "message" => "You are not logged in."]);
            return;
        }

        $applicationId = (int) ($data["application_id"] ?? 0);
        $status        = $this->conn->real_escape_string($data["status"] ?? "");

        // Only allow these four status values (they match the database column)
        $allowedStatuses = ["pending", "reviewed", "accepted", "rejected"];

        if (!in_array($status, $allowedStatuses)) {
            echo json_encode(["status" => "error", "message" => "Invalid status value."]);
            return;
        }

        $this->conn->query("UPDATE applications SET status = '$status' WHERE id = $applicationId");

        echo json_encode(["status" => "success", "message" => "Status updated to: $status"]);
    }

    // -----------------------------------------------------------
    // Get all applications submitted by the logged-in seeker
    // -----------------------------------------------------------
    public function handleGetSeekerApplications() {
        if (!isset($_SESSION["user_id"])) {
            echo json_encode(["status" => "success", "data" => []]);
            return;
        }

        $userId = (int) $_SESSION["user_id"];

        $result = $this->conn->query("
            SELECT
                a.id         AS application_id,
                a.status,
                a.applied_at,
                j.title      AS job_title,
                e.company_name
            FROM applications a
            INNER JOIN jobs j       ON a.job_id    = j.id
            INNER JOIN employers e  ON j.employer_id = e.id
            INNER JOIN job_seekers js ON a.seeker_id = js.id
            WHERE js.user_id = $userId
            ORDER BY a.id DESC
        ");

        $applications = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $applications[] = $row;
            }
        }

        echo json_encode(["status" => "success", "data" => $applications]);
    }
}