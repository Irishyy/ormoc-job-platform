<?php
// models/JobModel.php

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

}