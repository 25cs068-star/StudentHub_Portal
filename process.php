
<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.php");
    exit();
}

$fullName = trim($_POST["fullName"] ?? "");
$enrollment = trim($_POST["enrollment"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$department = trim($_POST["department"] ?? "");
$year = trim($_POST["year"] ?? "");
$gender = trim($_POST["gender"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirmPassword"] ?? "";

$errors = [];

if ($fullName === "" || !preg_match("/^[A-Za-z ]{2,50}$/", $fullName)) {
    $errors[] = "Please enter a valid full name.";
}

if ($enrollment === "" || !preg_match("/^(?:[Dd])?[0-9]{2}[A-Za-z]{2,4}[0-9]{2,4}$/", $enrollment)) {
    $errors[] = "Please enter a valid enrollment number.";
}

if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
}

if ($mobile === "" || !preg_match("/^[6-9][0-9]{9}$/", $mobile)) {
    $errors[] = "Please enter a valid 10-digit mobile number.";
}

$allowedDepartments = [
    "Computer Science / IT",
    "Electronics & Communication",
    "Electrical Engineering",
    "Mechanical Engineering",
    "Civil Engineering"
];

if (!in_array($department, $allowedDepartments, true)) {
    $errors[] = "Please select a valid department.";
}

$allowedYears = ["1st Year", "2nd Year", "3rd Year", "4th Year"];

if (!in_array($year, $allowedYears, true)) {
    $errors[] = "Please select a valid year.";
}

$allowedGenders = ["Male", "Female", "Other"];

if (!in_array($gender, $allowedGenders, true)) {
    $errors[] = "Please select your gender.";
}

if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[@$!%*?&]).{8,}$/', $password)) {
    $errors[] = "Password must contain at least 8 characters, including uppercase, lowercase, number and special character.";
}

if ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match.";
}

if (!isset($_POST["terms"])) {
    $errors[] = "Please accept the Terms & Conditions.";
}

if (!empty($errors)) {
    $_SESSION["registration_errors"] = $errors;
    header("Location: register.php");
    exit();
}

require_once __DIR__ . "/practical9_db.php";

$check = $conn->prepare(
    "SELECT id FROM users WHERE email = ? OR enrollment = ? LIMIT 1"
);

if (!$check) {
    error_log($conn->error);
    $_SESSION["registration_errors"] = [
        "Unable to check registration details. Please try again."
    ];
    header("Location: register.php");
    exit();
}

$check->bind_param("ss", $email, $enrollment);

if (!$check->execute()) {
    error_log($check->error);
    $check->close();
    $conn->close();

    $_SESSION["registration_errors"] = [
        "Unable to check registration details. Please try again."
    ];
    header("Location: register.php");
    exit();
}

$check->store_result();

if ($check->num_rows > 0) {






    $check->close();
    $conn->close();

    $_SESSION["registration_errors"] = [
        "This email address or enrollment number is already registered."
    ];
    header("Location: register.php");
    exit();
}

$check->close();

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO users
    (username, email, password, enrollment, mobile, department, study_year, gender)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    error_log($conn->error);
    $conn->close();

    $_SESSION["registration_errors"] = [
        "Unable to save your registration. Please try again."
    ];
    header("Location: register.php");
    exit();
}

$stmt->bind_param(
    "ssssssss",
    $fullName,
    $email,
    $hashedPassword,
    $enrollment,
    $mobile,
    $department,
    $year,
    $gender
);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();

    $_SESSION["registration_success"] =
        "Registration Successfully Completed!";

    header("Location: register.php");
    exit();
}

$errorNumber = $stmt->errno;
error_log($stmt->error);

$stmt->close();
$conn->close();

if ($errorNumber === 1062) {
    $_SESSION["registration_errors"] = [
        "This email address or enrollment number is already registered, or another unique field conflicts."
    ];
} else {
    $_SESSION["registration_errors"] = [
        "Registration could not be completed. Please try again."
    ];
}


header("Location: register.php");
exit();
?>
