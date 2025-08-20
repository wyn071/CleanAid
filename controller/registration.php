<?php
include("../dB/config.php");
session_start();

if (isset($_POST['registration'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['cpassword']);

    // ✅ Check if any field is empty
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $_SESSION['message'] = "All fields are required";
        $_SESSION['code'] = "error";
        header("Location: ../registration.php");
        exit();
    }

    // ✅ Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Invalid email format";
        $_SESSION['code'] = "error";
        header("Location: ../registration.php");
        exit();
    }

    // ✅ Validate password rules: 8 chars min, starts with capital, at least 1 number
    if (!preg_match('/^(?=.*\d)[A-Z][A-Za-z\d]{7,}$/', $password)) {
        $_SESSION['message'] = "Password must be at least 8 characters, start with a capital letter, and contain at least one number.";
        $_SESSION['code'] = "error";
        header("Location: ../registration.php");
        exit();
    }

    // ✅ Check if confirm password matches
    if ($password !== $confirmPassword) {
        $_SESSION['message'] = "Passwords do not match";
        $_SESSION['code'] = "error";
        header("Location: ../registration.php");
        exit();
    }

    // Escape user input to prevent SQL injection
    $name = mysqli_real_escape_string($conn, $name);
    $email = mysqli_real_escape_string($conn, $email);
    $password = mysqli_real_escape_string($conn, $password);

    // ⚠️ Secure practice: hash the password before saving
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // ✅ Validate if email already exists
    $query = "SELECT email FROM user WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['message'] = "Email already exists";
        $_SESSION['code'] = "error";
        header("Location: ../registration.php");
        exit();
    }

    // Default role is 'admin'
    $role = 'admin';

    // ✅ Insert user data into the database (using hashed password)
    $query = "INSERT INTO `user` (`name`, `email`, `password`, `role`) 
              VALUES ('$name', '$email', '$hashedPassword', '$role')";

    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Registered Successfully";
        $_SESSION['code'] = "success";
        header("Location: ../login.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>
