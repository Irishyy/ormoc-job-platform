<?php
<<<<<<< HEAD
// models/JobModel.php
// This file handles everything related to jobs and applications in the database.
=======
>>>>>>> 344b2991fd1404c4b197cd3d915c2c32fe6b433c

class JobModel {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // -----------------------------------------------------------
    // Get all jobs from all employers (shown on the seeker map)
    // -----------------------------------------------------------
    public function getAllJobs() {
        $result = $this->conn->query("
            SELECT
                jobs.id,
                jobs.title,
                jobs.description,
                jobs.latitude,
                jobs.longitude,
                jobs.created_at,
                employers.company_name,
                employers.company_logo_url
            FROM jobs
            INNER JOIN employers ON jobs.employer_id = employers.id
            ORDER BY jobs.created_at DESC
        ");

        $jobs = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $jobs[] = $row;
            }
        }
        return $jobs;
    }

    // -----------------------------------------------------------
    // Get all jobs posted by one specific employer
    // -----------------------------------------------------------
    public function getJobsByEmployer($employerId) {
        $employerId = (int) $employerId;
        $result = $this->conn->query("SELECT * FROM jobs WHERE employer_id = $employerId ORDER BY created_at DESC");

        $jobs = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $jobs[] = $row;
            }
        }
        return $jobs;
    }

    // -----------------------------------------------------------
    // Create a new job listing
    // Returns the new job's ID, or false if it failed
    // -----------------------------------------------------------
    public function createJob($employerId, $title, $description, $latitude, $longitude) {
        $employerId  = (int) $employerId;
        $title       = $this->conn->real_escape_string($title);
        $description = $this->conn->real_escape_string($description);
        $latitude    = !empty($latitude)  ? (float) $latitude  : "NULL";
        $longitude   = !empty($longitude) ? (float) $longitude : "NULL";

        $this->conn->query("
            INSERT INTO jobs (employer_id, title, description, latitude, longitude, created_at)
            VALUES ($employerId, '$title', '$description', $latitude, $longitude, NOW())
        ");

        return $this->conn->insert_id ?: false;
    }

    // -----------------------------------------------------------
    // Delete a job by its ID
    // Returns number of rows deleted
    // -----------------------------------------------------------
    public function deleteJob($jobId) {
        $jobId = (int) $jobId;
        $this->conn->query("DELETE FROM jobs WHERE id = $jobId");
        return $this->conn->affected_rows;
    }

    // -----------------------------------------------------------
    // Check that a job belongs to a specific employer
    // Used before deleting to prevent one employer deleting another's job
    // -----------------------------------------------------------
    public function jobBelongsToEmployer($jobId, $employerId) {
        $jobId      = (int) $jobId;
        $employerId = (int) $employerId;
        $result = $this->conn->query("SELECT id FROM jobs WHERE id = $jobId AND employer_id = $employerId LIMIT 1");
        return ($result && $result->num_rows > 0);
    }

    // -----------------------------------------------------------
    // Find an employer's profile row using their user ID
    // (The session stores user_id, but jobs use employer_id)
    // -----------------------------------------------------------
    public function getEmployerByUserId($userId) {
        $userId = (int) $userId;
        $result = $this->conn->query("SELECT * FROM employers WHERE user_id = $userId LIMIT 1");

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    // -----------------------------------------------------------
    // Find a job seeker's profile row using their user ID
    // -----------------------------------------------------------
    public function getSeekerByUserId($userId) {
        $userId = (int) $userId;
        $result = $this->conn->query("SELECT * FROM job_seekers WHERE user_id = $userId LIMIT 1");

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    // -----------------------------------------------------------
    // Save a job application (seeker applies to a job)
    // -----------------------------------------------------------
    public function createApplication($jobId, $seekerId, $resumeUrl) {
        $jobId     = (int) $jobId;
        $seekerId  = (int) $seekerId;
        $resumeUrl = $this->conn->real_escape_string($resumeUrl);

        return $this->conn->query("
            INSERT INTO applications (job_id, seeker_id, resume_url, status)
            VALUES ($jobId, $seekerId, '$resumeUrl', 'pending')
        ");
    }

    // -----------------------------------------------------------
    // Get all applications submitted by a seeker
    // -----------------------------------------------------------
    public function getSeekerApplications($seekerId) {
        $seekerId = (int) $seekerId;
        $result = $this->conn->query("
            SELECT
                a.id AS application_id,
                a.status,
                a.applied_at,
                j.title AS job_title,
                e.company_name
            FROM applications a
            INNER JOIN jobs j ON a.job_id = j.id
            LEFT JOIN employers e ON j.employer_id = e.id
            WHERE a.seeker_id = $seekerId
            ORDER BY a.id DESC
        ");

        $apps = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $apps[] = $row;
            }
        }
        return $apps;
    }

    // -----------------------------------------------------------
    // Update the status of an application (pending, accepted, etc.)
    // -----------------------------------------------------------
    public function updateApplicationStatus($applicationId, $status) {
        $applicationId = (int) $applicationId;
        $status        = $this->conn->real_escape_string($status);

        return $this->conn->query("UPDATE applications SET status = '$status' WHERE id = $applicationId");
    }
}