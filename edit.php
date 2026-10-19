
<?php
session_start();

$timeoutDuration = 300;

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if (
    !isset($_SESSION["last_activity"]) ||
    (time() - $_SESSION["last_activity"]) > $timeoutDuration
) {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $cookieParams = session_get_cookie_params();

        setcookie(
            session_name(),
            "",
            time() - 42000,
            $cookieParams["path"],
            $cookieParams["domain"],
            $cookieParams["secure"],
            $cookieParams["httponly"]
        );
    }

    session_destroy();
    header("Location: login.php?error=timeout");
    exit();
}

$_SESSION["last_activity"] = time();

if (strtolower($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Access denied. Only administrators can edit student records.");
}

require_once __DIR__ . "/practical11_db.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";

$studentId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $studentId = filter_input(INPUT_POST, "student_id", FILTER_VALIDATE_INT);
}

if (!$studentId || $studentId < 1) {
    mysqli_close($conn);
    header("Location: students.php");
    exit();
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT student_id, name, email, course, status
     FROM students
     WHERE student_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $studentId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$student) {
    mysqli_close($conn);
    header("Location: students.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";

    if (!hash_equals($_SESSION["csrf_token"], $token)) {
        $message = "Invalid request. Please refresh the page.";
    } else {
        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $course = trim($_POST["course"] ?? "");

        if (
            $name === "" ||
            $email === "" ||
            $course === "" ||
            !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            $message = "Please enter a valid name, email and course.";
        } else {
            $check = mysqli_prepare(
                $conn,
                "SELECT student_id FROM students
                 WHERE email = ? AND student_id <> ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $check,
                "si",
                $email,
                $studentId
            );

            mysqli_stmt_execute($check);
            $checkResult = mysqli_stmt_get_result($check);

            if (mysqli_num_rows($checkResult) > 0) {
                $message = "This email is already used by another student.";
            } else {
                $update = mysqli_prepare(
                    $conn,
                    "UPDATE students
                     SET name = ?, email = ?, course = ?
                     WHERE student_id = ?"
                );

                mysqli_stmt_bind_param(
                    $update,
                    "sssi",
                    $name,
                    $email,
                    $course,
                    $studentId
                );

                if (mysqli_stmt_execute($update)) {
                    mysqli_stmt_close($update);
                    mysqli_stmt_close($check);
                    mysqli_close($conn);

                    header("Location: students.php?message=updated");
                    exit();
                } else {
                    $message = "Unable to update student details.";
                }

                mysqli_stmt_close($update);
            }

            mysqli_stmt_close($check);
        }

        $student["name"] = $name;
        $student["email"] = $email;
        $student["course"] = $course;
    }
}

function escapeHtml($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - StudentHub</title>
    <link rel="stylesheet" href="csscommon.css">

    <style>
        .edit-container {
            width: 92%;
            max-width: 650px;
            margin: 35px auto;
        }

        .edit-card {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
        }

        .edit-card h2 {
            color: #003566;
            margin-top: 0;
        }

        .edit-field {
            margin-bottom: 18px;
        }

        .edit-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 7px;
        }

        .edit-field input {
            width: 100%;
            box-sizing: border-box;
            padding: 11px;
            border: 1px solid #b8c7d9;
            border-radius: 7px;
            font-size: 15px;
        }

        .edit-btn {
            display: inline-block;
            padding: 11px 17px;
            border: none;
            border-radius: 7px;
            background: #0066a1;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 15px;
            margin-right: 8px;
        }

        .edit-btn:hover {
            background: #004b78;
        }

        .edit-message {
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 7px;
            color: #842029;
            background: #f8d7da;
        }

        body.dark-mode .edit-card {
            background: #1e293b;
            color: #e5e7eb;
        }

        body.dark-mode .edit-card h2 {
            color: #7dd3fc;
        }

        body.dark-mode .edit-field input {
            background: #0f172a;
            color: #f8fafc;
            border-color: #475569;
        }
    </style>
</head>

<body>

<header>
    <div class="logo-section">
        <img src="logo.jfif" alt="StudentHub Logo">
        <div>
            <h1>StudentHub Portal</h1>
            <p>Your Digital Campus and Academic Companion</p>
        </div>
    </div>

    <button id="themeToggle" class="theme-btn" type="button">
        🌙 Dark Mode
    </button>

    <button id="menuToggle" class="menu-btn" type="button">
        ☰
    </button>

    <nav class="admin-nav">
        <a href="admin_dashboard.php">Admin Dashboard</a>
        <a href="students.php">Student Management</a>
        <a href="manage_events.php">Manage Events</a>
        <a href="manage_feedback.php">Manage Feedback</a>
        <a href="contact_messages.php">Contact Messages</a>
        <a href="manage_faqs.php">Manage FAQs</a>
        <a href="admin_profile.php">Admin Profile</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="edit-container">
    <section class="edit-card">
        <h2>Edit Student Details</h2>

        <?php if ($message !== ""): ?>
            <div class="edit-message">
                <?php echo escapeHtml($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit.php?id=<?php echo (int)$studentId; ?>">
            <input type="hidden" name="student_id"
                   value="<?php echo (int)$studentId; ?>">

            <input type="hidden" name="csrf_token"
                   value="<?php echo escapeHtml($_SESSION["csrf_token"]); ?>">

            <div class="edit-field">
                <label for="name">Student Name</label>
                <input type="text" id="name" name="name"
                       maxlength="100"
                       value="<?php echo escapeHtml($student["name"]); ?>"
                       required>
            </div>

            <div class="edit-field">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       maxlength="150"
                       value="<?php echo escapeHtml($student["email"]); ?>"
                       required>
            </div>

            <div class="edit-field">
                <label for="course">Course</label>
                <input type="text" id="course" name="course"
                       maxlength="100"
                       value="<?php echo escapeHtml($student["course"]); ?>"
                       required>
            </div>

            <button type="submit" class="edit-btn">
                Update Student
            </button>

            <a href="students.php" class="edit-btn">
                Back to Students
            </a>
        </form>
    </section>
</main>

<footer>
    <p>&copy; 2026 StudentHub Portal | Designed for Students</p>
</footer>

<script src="scriptcomman.js"></script>

</body>
</html>

<?php
mysqli_close($conn);
?>
