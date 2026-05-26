<?php
// controllers/AuthController.php
// This file handles login and sign-up for both Google and manual accounts.

require_once __DIR__ . "/../config/DatabaseConnection.php";
require_once __DIR__ . "/../models/UserModel.php";

class AuthController {

    // -----------------------------------------------------------
    // Google Sign-In
    // Google sends us a "credential" token. We verify it with Google,
    // get the user's name and email, then log them in (or create an account).
    // -----------------------------------------------------------
    public function handleGoogleLogin($data) {
        $db        = (new DatabaseConnection())->connect();
        $userModel = new UserModel($db);

        // Make sure we received the Google token
        if (empty($data["credential"])) {
            echo json_encode(["status" => "error", "message" => "No Google token received."]);
            return;
        }

        $role = $data["role"] ?? "seeker";

        // Ask Google to verify the token and give us the user's info
        $googleUrl      = "https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($data["credential"]);
        $googleResponse = file_get_contents($googleUrl);

        if (!$googleResponse) {
            echo json_encode(["status" => "error", "message" => "Could not reach Google to verify the token."]);
            return;
        }

        $googleData = json_decode($googleResponse, true);

        // Make sure the token is valid and meant for our app
        $dbConfig = new DatabaseConnection();
        if (!$googleData || ($googleData["aud"] ?? "") !== $dbConfig->getGoogleClientId()) {
            echo json_encode(["status" => "error", "message" => "Google token is invalid or expired."]);
            return;
        }

        $email = $googleData["email"];
        $name  = $googleData["name"];

        // Check if this person already has an account
        $user = $userModel->findByEmail($email);

        // If they don't have an account yet, create one
        if (!$user) {
            $newUserId = $userModel->createUser($name, $email, "GOOGLE_OAUTH", $role);

            if ($role === "employer") {
                $userModel->createEmployer($newUserId);
            } else {
                $userModel->createJobSeeker($newUserId);
            }

            $user = $userModel->findByEmail($email);
        }

        // Save the user info in the session so they stay logged in
        $_SESSION["user_id"]   = $user["id"];
        $_SESSION["user_role"] = $user["role"];

        echo json_encode(["status" => "success", "role" => $user["role"]]);
    }

    // -----------------------------------------------------------
    // Manual Sign-Up
    // The user fills in their name, email, and password to create an account.
    // -----------------------------------------------------------
    public function handleManualSignUp($data) {
        if (empty($data["name"]) || empty($data["email"]) || empty($data["password"]) || empty($data["role"])) {
            echo json_encode(["status" => "error", "message" => "Please fill in all fields: name, email, password, and role."]);
            return;
        }

        $db  = (new DatabaseConnection())->connect();
        $userModel = new UserModel($db);

        // Check if someone already registered with this email
        if ($userModel->findByEmail($data["email"])) {
            echo json_encode(["status" => "error", "message" => "That email is already registered. Try logging in instead."]);
            return;
        }

        // Hash the password so we never store it in plain text
        $hashedPassword = password_hash($data["password"], PASSWORD_DEFAULT);

        // Create the user account
        $newUserId = $userModel->createUser($data["name"], $data["email"], $hashedPassword, $data["role"]);

        if (!$newUserId) {
            echo json_encode(["status" => "error", "message" => "Something went wrong creating your account. Try again."]);
            return;
        }

        // Create the matching profile row (employer or seeker)
        if ($data["role"] === "employer") {
            $userModel->createEmployer($newUserId);
        } else {
            $userModel->createJobSeeker($newUserId);
        }

        echo json_encode(["status" => "success", "message" => "Account created! You can now log in."]);
    }

    // -----------------------------------------------------------
    // Manual Login
    // The user enters their email and password to log in.
    // -----------------------------------------------------------
    public function handleManualLogin($data) {
        if (empty($data["email"]) || empty($data["password"])) {
            echo json_encode(["status" => "error", "message" => "Please enter your email and password."]);
            return;
        }

        $db = (new DatabaseConnection())->connect();
        $userModel = new UserModel($db);

        $user = $userModel->findByEmail($data["email"]);

        // Check if the user exists and the password matches
        if ($user && password_verify($data["password"], $user["password"])) {
            $_SESSION["user_id"]   = $user["id"];
            $_SESSION["user_role"] = $user["role"];

            echo json_encode(["status" => "success", "role" => $user["role"]]);
            return;
        }

        echo json_encode(["status" => "error", "message" => "Incorrect email or password."]);
    }
}