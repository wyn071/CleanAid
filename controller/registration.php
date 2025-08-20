<?php
include("../dB/config.php");
session_start();

if (isset($_POST['registration'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['cpassword']);

    // Save old values in session for repopulation
    $_SESSION['old_name'] = $name;
    $_SESSION['old_email'] = $email;
    $_SESSION['old_password'] = $password;
    $_SESSION['old_cpassword'] = $confirmPassword;

    // ✅ Check if any field is empty
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $_SESSION['message'] = "All fields are required";
        $_SESSION['code'] = "error";

        // Clear only the empty fields
        if (empty($name)) unset($_SESSION['old_name']);
        if (empty($email)) unset($_SESSION['old_email']);
        if (empty($password)) unset($_SESSION['old_password']);
        if (empty($confirmPassword)) unset($_SESSION['old_cpassword']);

        header("Location: ../registration.php");
        exit();
    }

    // ✅ Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Invalid email format";
        $_SESSION['code'] = "error";

        unset($_SESSION['old_email']); // clear only email
        header("Location: ../registration.php");
        exit();
    }

    // ✅ Validate password rules: 8 chars min, starts with capital, at least 1 number
    if (!preg_match('/^(?=.*\d)[A-Z][A-Za-z\d]{7,}$/', $password)) {
        $_SESSION['message'] = "Password must be at least 8 characters, start with a capital letter, and contain at least one number.";
        $_SESSION['code'] = "error";

        unset($_SESSION['old_password'], $_SESSION['old_cpassword']); // clear only password fields
        header("Location: ../registration.php");
        exit();
    }

    // ✅ Check if confirm password matches
    if ($password !== $confirmPassword) {
        $_SESSION['message'] = "Passwords do not match";
        $_SESSION['code'] = "error";

        unset($_SESSION['old_cpassword']); // clear confirm password only
        header("Location: ../registration.php");
        exit();
    }

    // Escape user input
    $name = mysqli_real_escape_string($conn, $name);
    $email = mysqli_real_escape_string($conn, $email);

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // ✅ Check if email already exists
    $query = "SELECT email FROM user WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['message'] = "Email already exists";
        $_SESSION['code'] = "error";

        unset($_SESSION['old_email']); // clear email only
        header("Location: ../registration.php");
        exit();
    }

    // Default role
    $role = 'admin';

    // ✅ Insert user data into the database
    $query = "INSERT INTO `user` (`name`, `email`, `password`, `role`) 
              VALUES ('$name', '$email', '$hashedPassword', '$role')";

    if (mysqli_query($conn, $query)) {
        // Clear old values on success
        unset($_SESSION['old_name'], $_SESSION['old_email'], $_SESSION['old_password'], $_SESSION['old_cpassword']);

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
