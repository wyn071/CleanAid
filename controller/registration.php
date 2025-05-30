<?php
include("../dB/config.php");
session_start();

if (isset($_POST['registration'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['cpassword'];

    // Validate if confirm password and password match
    if ($password !== $confirmPassword) {
        $_SESSION['message'] = "Passwords do not match";
        $_SESSION['code'] = "error";
        header("location:../registration.php");
        exit();
    }

    // Escape user input to prevent SQL injection
    $name = mysqli_real_escape_string($conn, $name);
    $email = mysqli_real_escape_string($conn, $email);
    $password = mysqli_real_escape_string($conn, $password);

    // Validate if email already exists
    $query = "SELECT email FROM user WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['message'] = "Email already exists";
        $_SESSION['code'] = "error";
        header("location:../registration.php");
        exit();
    }

    // Default role is 'admin'
    $role = 'admin';

    // Insert user data into the database
    $query = "INSERT INTO `user` (`name`, `email`, `password`, `role`) 
              VALUES ('$name', '$email', '$password', '$role')";

    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Registered Successfully";
        $_SESSION['code'] = "success";
        header("location:../login.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>
