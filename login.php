<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userID = trim($_POST['id']);         // input name="id"
    $password = $_POST['password'];       // input name="password"

    $stmt = $conn->prepare("SELECT userID, name, role, password FROM user WHERE userID = ?");
    $stmt->bind_param("s", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Password checking (assuming plain text for now, or use password_verify if hashed)
        if ($password === $user['password']) {
            $_SESSION['userID'] = $user['userID'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: handle maintenance request.html");
            } elseif ($user['role'] === 'therapist') {
                header("Location: SubmitMaintenanceRequest1.html");
            } else {
                // Default redirect if role doesn't match any condition
                header("Location: default_page.html");
            }
            exit();
        } else {
            echo "<script>alert('Incorrect password.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('User not found.'); window.history.back();</script>";
    }
}
?>