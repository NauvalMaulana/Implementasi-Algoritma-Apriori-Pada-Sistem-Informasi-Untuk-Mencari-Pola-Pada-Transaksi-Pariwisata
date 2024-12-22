<?php
session_start();
include_once "fungsi.php";
include_once "database.php";

// Fungsi untuk memfilter input data
function filter_input_data($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_input_data($_POST['username']);
    $password = filter_input_data($_POST['password']);
    $confirm_password = filter_input_data($_POST['confirm_password']);

    // Validasi input data
    if (empty($username) || empty($password) || empty($confirm_password)) {
        header("Location: register.php?register=empty_fields");
        exit();
    }

    if ($password !== $confirm_password) {
        header("Location: register.php?register=password_mismatch");
        exit();
    }

    // Hashing password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Koneksi ke database
    $conn = open_connection();

    // Periksa apakah username sudah ada
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        header("Location: register.php?register=db_error");
        exit();
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header("Location: register.php?register=username_taken");
        $stmt->close();
        $conn->close();
        exit();
    }

    // Insert user baru ke database
    $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        header("Location: register.php?register=db_error");
        exit();
    }
    $stmt->bind_param("ss", $username, $hashed_password);

    if ($stmt->execute()) {
        header("Location: login.php?register=success");
    } else {
        header("Location: register.php?register=insert_failed");
    }

    $stmt->close();
    $conn->close();
}
?>
