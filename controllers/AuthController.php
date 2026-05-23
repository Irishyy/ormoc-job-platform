<?php
// controllers/AuthController.php

require_once __DIR__ . "/../config/DatabaseConnection.php";
require_once __DIR__ . "/../models/UserModel.php";

class AuthController {

    // =========================================================
    // 🌐 GOOGLE OAUTH LOGIN
    // =========================================================
    public function handleGoogleLogin($data) {
        $dbObj = new DatabaseConnection();
        $db    = $dbObj->connect();
        $userModel = new UserModel($db);

        if (empty($data["credential"])) {
            echo json_encode(["status" => "error", "message" => "Google token is required."]);
            return;
        }

        $role = isset($data["role"]) ? $data["role"] : "seeker";

        if ($role !== "seeker" && $role !== "employer") {
            echo json_encode(["status" => "error", "message" => "Role must be seeker or employer."]);
            return;
        }

        $verifyUrl      = "https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($data["credential"]);
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

        $user = $userModel->findByEmail($email);

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

            $user = $userModel->findByEmail($email);
        }

        $_SESSION["user_id"]   = $user["id"];
        $_SESSION["user_role"] = $user["role"];

        echo json_encode(["status" => "success", "role" => $user["role"]]);
    }

    // =========================================================
    // 📝 MANUAL SIGN UP
    // =========================================================
    public function handleManualSignUp($data) {
        // Validate all required fields are present and non-empty
        if (empty($data["name"]) || empty($data["email"]) || empty($data["password"]) || empty($data["role"])) {
            echo json_encode(["status" => "error", "message" => "Name, email, password, and role are required."]);
            return;
        }

        if ($data["role"] !== "seeker" && $data["role"] !== "employer") {
            echo json_encode(["status" => "error", "message" => "Role must be seeker or employer."]);
            return;
        }

        $dbObj     = new DatabaseConnection();
        $db        = $dbObj->connect();
        $userModel = new UserModel($db);

        // Check if the email address is already taken
        $existingUser = $userModel->findByEmail($data["email"]);
        if ($existingUser) {
            echo json_encode(["status" => "error", "message" => "Email already registered. Please log in instead."]);
            return;
        }

        // Hash the password securely before storing
        $hashedPassword = password_hash($data["password"], PASSWORD_DEFAULT);

        // Write the base user record
        $newUserId = $userModel->createManual($data["name"], $data["email"], $hashedPassword, $data["role"]);

        if (!$newUserId) {
            echo json_encode(["status" => "error", "message" => "Database write error. Could not create user record."]);
            return;
        }

        // Write the role-specific sub-profile record
        if ($data["role"] === "employer") {
            $saved = $userModel->createEmployer($newUserId);
        } else {
            $saved = $userModel->createJobSeeker($newUserId);
        }

        if ($saved) {
            echo json_encode(["status" => "success", "message" => "Registration successful! You can now log in."]);
        } else {
            echo json_encode(["status" => "error", "message" => "User created but sub-profile setup failed. Contact support."]);
        }
    }

    // =========================================================
    // 🔑 MANUAL LOGIN
    // =========================================================
    public function handleManualLogin($data) {
        if (empty($data["email"]) || empty($data["password"])) {
            echo json_encode(["status" => "error", "message" => "Email and password are required."]);
            return;
        }

        $dbObj     = new DatabaseConnection();
        $db        = $dbObj->connect();
        $userModel = new UserModel($db);

        $user = $userModel->findByEmail($data["email"]);

        if ($user) {
            if (password_verify($data["password"], $user["password"])) {
                $_SESSION["user_id"]   = $user["id"];
                $_SESSION["user_role"] = $user["role"];

                echo json_encode(["status" => "success", "role" => $user["role"]]);
                return;
            }
        }

        echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
    }
}