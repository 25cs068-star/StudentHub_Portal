<?php

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
    $errors[] = "Invalid full name.";
}

if ($enrollment === "" || !preg_match("/^(?:[Dd])?[0-9]{2}[A-Za-z]{2,4}[0-9]{2,4}$/", $enrollment)) {
    $errors[] = "Invalid enrollment number.";
}

if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address.";
}

if ($mobile === "" || !preg_match("/^[6-9][0-9]{9}$/", $mobile)) {
    $errors[] = "Invalid mobile number.";
}

$allowedDepartments = [
    "Computer Science / IT",
    "Electronics & Communication",
    "Electrical Engineering",
    "Mechanical Engineering",
    "Civil Engineering"
];

if (!in_array($department, $allowedDepartments, true)) {
    $errors[] = "Invalid department.";
}

$allowedYears = [
    "1st Year",
    "2nd Year",
    "3rd Year",
    "4th Year"
];

if (!in_array($year, $allowedYears, true)) {
    $errors[] = "Invalid year.";
}

$allowedGenders = [
    "Male",
    "Female",
    "Other"
];

if (!in_array($gender, $allowedGenders, true)) {
    $errors[] = "Invalid gender.";
}

if (!preg_match(
    "/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[@$!%*?&]).{8,}$/",
    $password
)) {
    $errors[] = "Invalid password.";
}

if ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match.";
}

if (!isset($_POST["terms"])) {
    $errors[] = "Please accept the Terms & Conditions.";
}

if (!empty($errors)) {

    echo "<h2>Registration Failed</h2>";

    foreach ($errors as $error) {
        echo "<p>" . htmlspecialchars($error) . "</p>";
    }

    echo '<p><a href="register.php">Go Back</a></p>';

    exit();
}

$file = __DIR__ . "/data.csv";

$isNewFile = !file_exists($file) || filesize($file) === 0;

$handle = fopen($file, "a");

if ($handle === false) {
    die("Unable to open data.csv");
}

if (!flock($handle, LOCK_EX)) {
    fclose($handle);
    die("Unable to lock data.csv");
}

if ($isNewFile) {
    fputcsv($handle, [
        "Full Name",
        "Enrollment",
        "Email",
        "Mobile",
        "Department",
        "Year",
        "Gender",
        "Password",
        "Registered At"
    ]);
}

fputcsv($handle, [
    $fullName,
    $enrollment,
    $email,
    $mobile,
    $department,
    $year,
    $gender,
    $password,
    date("Y-m-d H:i:s")
]);

flock($handle, LOCK_UN);
fclose($handle);

header("Location: register.php?success=1");
exit();

?>