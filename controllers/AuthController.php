<?php
// controllers/AuthController.php

require_once __DIR__ . "/../config/DatabaseConnection.php";
require_once __DIR__ . "/../models/UserModel.php";

class AuthController {

  // Inside controllers/AuthController.php

  public function handleGoogleLogin($data) {
    $dbObj = new DatabaseConnection();
    $db = $dbObj->connect();
    $userModel = new UserModel($db);

    if (empty($data["credential"])) {
      echo json_encode(["status" => "error", "message" => "Google token is required."]);
      return;
    }

    $role = $data["role"] ?? "seeker";

    if ($role !== "seeker" && $role !== "employer") {
      echo json_encode(["status" => "error", "message" => "Role must be seeker or employer."]);
      return;
    }

    $verifyUrl = "https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($data["credential"]);
    $googleResponse = file_get_contents($verifyUrl);

    if ($googleResponse === false) {
      echo json_encode(["status" => "error", "message" => "Could not verify Google token."]);
      return;
    }

    $googleData = json_decode($googleResponse, true);

    if (!$googleData || isset($googleData["error_description"])) {
      echo json_encode(["status" => "error", "message" => "Invalid Google token."]);
      return;
    }

    if ($googleData["aud"] !== $dbObj->google_client_id) {
      echo json_encode(["status" => "error", "message" => "Google client ID does not match."]);
      return;
    }

    $email = $googleData["email"];
    $name  = $googleData["name"];

    // 1. Check if they already exist
    $user = $userModel->findByEmail($email);

    // 2. If they don't, save them cleanly using your existing Model method
    if (!$user) {
      $newUserId = $userModel->createManual($name, $email, "OAUTH_GOOGLE_ACCOUNT", $role);

      if (!$newUserId) {
        echo json_encode(["status" => "error", "message" => "Failed to register Google user."]);
        return;
      }

      if ($role === "employer") {
        $saved = $userModel->createEmployer($newUserId);
      } else {
        $saved = $userModel->createJobSeeker($newUserId);
      }

      if (!$saved) {
        echo json_encode(["status" => "error", "message" => "User saved but profile setup failed."]);
        return;
      }

      $user = $userModel->findByEmail($email); // Get their new ID
    }

    // 3. Log them in!
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["user_role"] = $user["role"];

    echo json_encode(["status" => "success", "role" => $user["role"]]);
  }

  public function handleManualSignUp($data) {
    if (empty($data["name"]) || empty($data["email"]) || empty($data["password"]) || empty($data["role"])) {
      echo json_encode(["status" => "error", "message" => "Name, email, password, and role are required."]);
      return;
    }

    if ($data["role"] !== "seeker" && $data["role"] !== "employer") {
      echo json_encode(["status" => "error", "message" => "Role must be seeker or employer."]);
      return;
    }

    $dbObj = new DatabaseConnection();
    $db = $dbObj->connect();
    $userModel = new UserModel($db);

    // Check if email already exists using the model
    $existingUser = $userModel->findByEmail($data["email"]);
    if ($existingUser) {
      echo json_encode(["status" => "error", "message" => "Email already registered."]);
      return;
    }

    // Secure hash configuration
    $hashed_password = password_hash($data["password"], PASSWORD_DEFAULT);

    // Tell the model to save the user records
    $newUserId = $userModel->createManual($data["name"], $data["email"], $hashed_password, $data["role"]);

    if ($newUserId) {
      if ($data["role"] === "employer") {
        $saved = $userModel->createEmployer($newUserId);
      } else {
        $saved = $userModel->createJobSeeker($newUserId);
      }

      if ($saved) {
        echo json_encode(["status" => "success", "message" => "Registration successful! You can now Log In."]);
      } else {
        echo json_encode(["status" => "error", "message" => "User saved but profile setup failed."]);
      }
    } else {
      echo json_encode(["status" => "error", "message" => "Database write error."]);
    }
  }

  // 🔑 HANDLING MANUAL LOGIN
  public function handleManualLogin($data) {
    if (empty($data["email"]) || empty($data["password"])) {
      echo json_encode(["status" => "error", "message" => "Email and password are required."]);
      return;
    }

    $dbObj = new DatabaseConnection();
    $db = $dbObj->connect();
    $userModel = new UserModel($db);

    // Pull the user record via model
    $user = $userModel->findByEmail($data["email"]);

    if ($user) {
      // Check if the manual password matches the scrambled hash
      if (password_verify($data["password"], $user["password"])) {

        // Establish secure server memory
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_role"] = $user["role"];

        echo json_encode(["status" => "success", "role" => $user["role"]]);
        return;
      }
    }

    echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
  }
}
