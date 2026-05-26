<?php

class JobModel {

  private $conn; // Renamed to $conn to match class standard visibility guidelines

  // Accept the database connection from the controller
  public function __construct($db) {
    $this->conn = $db;
  }


  // -------------------------------------------------------
  // CREATE
  // -------------------------------------------------------

  // Insert a new job listing tied to an employer record
  public function createJob($employer_id, $title, $description, $latitude, $longitude) {
    $employer_id = (int) $employer_id;
    $safeTitle = $this->conn->real_escape_string($title);
    $safeDescription = $this->conn->real_escape_string($description);
    
    // Convert coordinate inputs to float or string SQL format safely
    $safeLatitude = !empty($latitude) ? (float)$latitude : "NULL";
    $safeLongitude = !empty($longitude) ? (float)$longitude : "NULL";

    $sql = "INSERT INTO jobs (employer_id, title, description, latitude, longitude, created_at)
            VALUES ($employer_id, '$safeTitle', '$safeDescription', $safeLatitude, $safeLongitude, NOW())";

    $result = $this->conn->query($sql);

    // Return the newly created job's ID so the controller can use it if query succeeded
    if ($result) {
      return $this->conn->insert_id;
    }
    return false;
  }


  // -------------------------------------------------------
  // READ
  // -------------------------------------------------------

  // Pull all jobs belonging to a specific employer (for the dashboard table)
  public function getJobsByEmployer($employer_id) {
    $employer_id = (int) $employer_id;
    
    $sql = "SELECT * FROM jobs
            WHERE employer_id = $employer_id
            ORDER BY created_at DESC";

    $result = $this->conn->query($sql);

    // Return all matching rows as an associative array list
    $jobsList = [];
    if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $jobsList[] = $row;
      }
    }
    return $jobsList;
  }

  // Pull a single job row by its ID (for editing or viewing detail)
  public function getJobById($job_id) {
    $job_id = (int) $job_id;

    $sql = "SELECT * FROM jobs WHERE id = $job_id LIMIT 1";

    $result = $this->conn->query($sql);

    if ($result && $result->num_rows > 0) {
      return $result->fetch_assoc();
    }
    return null;
  }

  // Pull all jobs across all employers (for seeker dashboard map feed)
  public function getAllJobs() {
    $sql = "SELECT
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
            ORDER BY jobs.created_at DESC";

    $result = $this->conn->query($sql);

    $allJobsList = [];
    if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $allJobsList[] = $row;
      }
    }
    return $allJobsList;
  }


  // -------------------------------------------------------
  // UPDATE
  // -------------------------------------------------------

  // Update the core details of an existing job listing
  public function updateJob($job_id, $title, $description, $latitude, $longitude) {
    $job_id = (int) $job_id;
    $safeTitle = $this->conn->real_escape_string($title);
    $safeDescription = $this->conn->real_escape_string($description);
    
    $safeLatitude = !empty($latitude) ? (float)$latitude : "NULL";
    $safeLongitude = !empty($longitude) ? (float)$longitude : "NULL";

    $sql = "UPDATE jobs
            SET title       = '$safeTitle',
                description = '$safeDescription',
                latitude    = $safeLatitude,
                longitude   = $safeLongitude
            WHERE id = $job_id";

    $result = $this->conn->query($sql);

    // Return how many rows were actually changed
    if ($result) {
      return $this->conn->affected_rows;
    }
    return 0;
  }


  // -------------------------------------------------------
  // DELETE
  // -------------------------------------------------------

  // Remove a job listing permanently by its ID
  public function deleteJob($job_id) {
    $job_id = (int) $job_id;

    $sql = "DELETE FROM jobs WHERE id = $job_id";

    $result = $this->conn->query($sql);

    // affected_rows > 0 means the delete actually hit a record
    if ($result) {
      return $this->conn->affected_rows;
    }
    return 0;
  }


  // -------------------------------------------------------
  // HELPER LOOKUPS
  // -------------------------------------------------------

  // Find the employer record linked to a users.id
  // (Needed because the session stores user_id, not employer_id)
  public function getEmployerByUserId($user_id) {
    $user_id = (int) $user_id;

    $sql = "SELECT * FROM employers WHERE user_id = $user_id LIMIT 1";

    $result = $this->conn->query($sql);

    if ($result && $result->num_rows > 0) {
      return $result->fetch_assoc();
    }
    return null;
  }

  // Confirm that a job actually belongs to the employer making the request
  // (Prevents one employer from editing another employer's listing)
  public function jobBelongsToEmployer($job_id, $employer_id) {
    $job_id = (int) $job_id;
    $employer_id = (int) $employer_id;

    $sql = "SELECT id FROM jobs
            WHERE id = $job_id AND employer_id = $employer_id
            LIMIT 1";

    $result = $this->conn->query($sql);

    // Returns the row if ownership checks out, false if it doesn't
    if ($result && $result->num_rows > 0) {
      return $result->fetch_assoc();
    }
    return false;
  }

  // Inside models/JobModel.php

    // 1. Record a new applicant file submission tracking line
// Inside models/JobModel.php

  // 1. Record a new applicant file submission tracking line
  public function createApplication($jobId, $userId, $resumeUrl) {
    $jobId = (int) $jobId;
    $userId = (int) $userId;
    $safeUrl = $this->conn->real_escape_string($resumeUrl);
    
    // 🔥 FIXED: Matches your table fields exactly
    $sql = "INSERT INTO applications (job_id, seeker_id, resume_url, status) 
            VALUES ($jobId, $userId, '$safeUrl', 'pending')";
            
    return $this->conn->query($sql);
  }

  // 2. Gather application history logs for a specific seeker profile
  public function getApplicationsBySeeker($userId) {
    $userId = (int) $userId;
    
    // 🔥 FIXED: Changed a.created_at to a.applied_at to match your schema fields
    $sql = "SELECT a.status, a.applied_at, j.title AS job_title, e.company_name 
            FROM applications a
            JOIN jobs j ON a.job_id = j.id
            LEFT JOIN employers e ON j.user_id = e.user_id
            WHERE a.seeker_id = $userId
            ORDER BY a.id DESC";
            
    $result = $this->conn->query($sql);
    
    $apps = [];
    while ($row = $result->fetch_assoc()) {
        $apps[] = $row;
    }
    return $apps;
  }

public function getApplicationsByEmployer($employerId) {
    $employerId = (int) $employerId;
    
    // Test alternative: If your 'jobs' table uses 'user_id' directly instead of 'employer_id', 
    // change `j.employer_id` to `j.user_id` below.
    $sql = "SELECT 
              a.id AS application_id,
              a.status,
              a.resume_url,
              a.applied_at,
              j.title AS job_title,
              u.name AS applicant_name
            FROM applications a
            INNER JOIN jobs j ON a.job_id = j.id
            LEFT JOIN users u ON a.seeker_id = u.id
            WHERE j.employer_id = $employerId 
               OR j.user_id = $employerId
            ORDER BY a.id DESC";
            
    $result = $this->conn->query($sql);
    
    $intakeList = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $intakeList[] = $row;
        }
    }
    return $intakeList;
}

  // Update a candidate's status track in the pipeline
  public function updateApplicationStatus($applicationId, $newStatus) {
    $applicationId = (int) $applicationId;
    $safeStatus = $this->conn->real_escape_string($newStatus);
    
    $sql = "UPDATE applications 
            SET status = '$safeStatus' 
            WHERE id = $applicationId";
            
    return $this->conn->query($sql);
  }

  public function getSeekerApplications($seekerId) {
    $seekerId = (int) $seekerId;
    
    // Ensure all joined matching tables exist exactly like this in your DB
    $sql = "SELECT 
              a.id AS application_id,
              a.status, 
              a.applied_at, 
              j.title AS job_title, 
              e.company_name 
            FROM applications a
            INNER JOIN jobs j ON a.job_id = j.id
            LEFT JOIN employers e ON j.user_id = e.user_id
            WHERE a.seeker_id = $seekerId
            ORDER BY a.id DESC";
            
    $result = $this->conn->query($sql);
    
    $apps = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $apps[] = $row;
        }
    }
    return $apps;
  }
} 