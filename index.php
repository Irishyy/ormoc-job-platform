<?php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  require_once "routes/api.php";
} else {
  header("Location: views/login.php");
  exit;
}

?>
