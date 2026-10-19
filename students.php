
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
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied - StudentHub</title>
        <link rel="stylesheet" href="csscommon.css">
    </head>
    <body>
        <main class="crud-container">
            <h2>Access Denied</h2>
            <p>Only administrators can access Student Management.</p>
            <a href="dashboard.php">Return to Dashboard</a>
        </main>
        <script src="scriptcomman.js"></script>
    </body>
    </html>
    <?php
    exit();
}

require_once __DIR__ . "/practical11_db.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "";

if (isset($_GET["message"])) {
    if ($_GET["message"] === "added") {
        $message = "Student added successfully.";
        $messageType = "success";
    } elseif ($_GET["message"] === "updated") {
        $message = "Student updated successfully.";
        $messageType = "success";
    } elseif ($_GET["message"] === "inactive") {
        $message = "Student marked as inactive successfully.";
        $messageType = "success";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";

    if (!hash_equals($_SESSION["csrf_token"], $token)) {
        $message = "Invalid request. Please refresh the page and try again.";
        $messageType = "error";
    } else {
        $action = $_POST["action"] ?? "";

        if ($action === "add") {
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
                $messageType = "error";
            } else {
                $check = mysqli_prepare(
                    $conn,
                    "SELECT student_id FROM students WHERE email = ? LIMIT 1"
                );

                mysqli_stmt_bind_param($check, "s", $email);
                mysqli_stmt_execute($check);
                $checkResult = mysqli_stmt_get_result($check);

                if (mysqli_num_rows($checkResult) > 0) {
                    $message = "This email already exists.";
                    $messageType = "error";
                } else {
                    $stmt = mysqli_prepare(
                        $conn,
                        "INSERT INTO students (name, email, course, status)
                         VALUES (?, ?, ?, 'active')"
                    );

                    mysqli_stmt_bind_param(
                        $stmt,
                        "sss",
                        $name,
                        $email,
                        $course
                    );

                    if (mysqli_stmt_execute($stmt)) {
                        header("Location: students.php?message=added");
                        exit();
                    } else {
                        $message = "Unable to add student.";
                        $messageType = "error";
                    }

                    mysqli_stmt_close($stmt);
                }

                mysqli_stmt_close($check);
            }
        } elseif ($action === "delete") {
            $studentId = filter_input(
                INPUT_POST,
                "student_id",
                FILTER_VALIDATE_INT
            );

            if ($studentId === false || $studentId === null || $studentId < 1) {
                $message = "Invalid student ID.";
                $messageType = "error";
            } else {
                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE students SET status = 'inactive'
                     WHERE student_id = ? AND status = 'active'"
                );

                mysqli_stmt_bind_param($stmt, "i", $studentId);

                if (
                    mysqli_stmt_execute($stmt) &&
                    mysqli_stmt_affected_rows($stmt) > 0
                ) {
                    header("Location: students.php?message=inactive");
                    exit();
                } else {
                    $message = "Student was not found or is already inactive.";
                    $messageType = "error";
                }

                mysqli_stmt_close($stmt);
            }
        }
    }
}

$search = trim($_GET["search"] ?? "");
$courseFilter = trim($_GET["course"] ?? "");
$statusFilter = trim($_GET["status"] ?? "active");

if (!in_array($statusFilter, ["active", "inactive", "all"], true)) {
    $statusFilter = "active";
}

$where = [];
$params = [];
$types = "";

if ($search !== "") {
    $where[] = "(name LIKE ? OR email LIKE ? OR course LIKE ?)";
    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $types .= "sss";
}

if ($courseFilter !== "") {
    $where[] = "course = ?";
    $params[] = $courseFilter;
    $types .= "s";
}

if ($statusFilter !== "all") {
    $where[] = "status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

$sql = "SELECT student_id, name, email, course, status FROM students";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY student_id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!empty($params)) {
    $bindParams = [$stmt, $types];

    foreach ($params as $key => $value) {
        $params[$key] = $value;
        $bindParams[] = &$params[$key];
    }

    call_user_func_array("mysqli_stmt_bind_param", $bindParams);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$students = [];

while ($row = mysqli_fetch_assoc($result)) {
    $students[] = $row;
}

mysqli_stmt_close($stmt);

$courseResult = mysqli_query(
    $conn,
    "SELECT DISTINCT course FROM students ORDER BY course"
);

$courses = [];

while ($courseRow = mysqli_fetch_assoc($courseResult)) {
    $courses[] = $courseRow["course"];
}

if (isset($_GET["export"]) && $_GET["export"] === "csv") {
    header("Content-Type: text/csv; charset=UTF-8");
    header('Content-Disposition: attachment; filename="studenthub_students.csv"');

    $output = fopen("php://output", "w");

    fwrite($output, "\xEF\xBB\xBF");

    fputcsv(
        $output,
        ["Student ID", "Name", "Email", "Course", "Status"]
    );

    foreach ($students as $student) {
        fputcsv($output, [
            $student["student_id"],
            $student["name"],
            $student["email"],
            $student["course"],
            $student["status"]
        ]);
    }

    fclose($output);
    mysqli_close($conn);
    exit();
}

function escapeHtml($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$queryParams = [
    "search" => $search,
    "course" => $courseFilter,
    "status" => $statusFilter,
    "export" => "csv"
];

$exportUrl = "students.php?" . http_build_query($queryParams);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - StudentHub</title>
    <link rel="stylesheet" href="csscommon.css">

    <style>
        .crud-container {
            width: 94%;
            max-width: 1200px;
            margin: 30px auto;
        }

        .crud-card {
            background: #ffffff;
            padding: 22px;
            margin-bottom: 24px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
        }

        .crud-card h2,
        .crud-card h3 {
            margin-top: 0;
            color: #003566;
        }

        .crud-form-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .crud-field label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
        }

        .crud-field input,
        .crud-field select {
            width: 100%;
            box-sizing: border-box;
            padding: 11px;
            border: 1px solid #b8c7d9;
            border-radius: 7px;
            font-size: 15px;
            background: #ffffff;
            color: #172b4d;
        }

        .crud-btn {
            display: inline-block;
            border: none;
            border-radius: 7px;
            padding: 10px 16px;
            background: #0066a1;
            color: #ffffff;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            margin: 4px 3px 4px 0;
        }

        .crud-btn:hover {
            background: #004b78;
        }

        .crud-btn-danger {
            background: #b42318;
        }

        .crud-btn-danger:hover {
            background: #8f1c13;
        }

        .crud-btn-secondary {
            background: #596579;
        }

        .crud-actions {
            margin-top: 18px;
        }

        .crud-filter-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 12px;
            align-items: end;
        }

        .crud-table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .crud-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .crud-table th,
        .crud-table td {
            padding: 12px;
            border: 1px solid #d6dfeb;
            text-align: left;
        }

        .crud-table th {
            background: #003566;
            color: #ffffff;
        }

        .crud-table tr:nth-child(even) {
            background: #f1f6fc;
        }

        .crud-message {
            padding: 13px 16px;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .crud-success {
            color: #155724;
            background: #d4edda;
            border: 1px solid #b7dfc0;
        }

        .crud-error {
            color: #842029;
            background: #f8d7da;
            border: 1px solid #f1b8bd;
        }

        .crud-status-active {
            color: #147a36;
            font-weight: 700;
        }

        .crud-status-inactive {
            color: #b42318;
            font-weight: 700;
        }

        .crud-count {
            margin-top: 14px;
            font-weight: 600;
        }

        body.dark-mode .crud-card {
            background: #1e293b;
            color: #e5e7eb;
        }

        body.dark-mode .crud-card h2,
        body.dark-mode .crud-card h3 {
            color: #7dd3fc;
        }

        body.dark-mode .crud-field input,
        body.dark-mode .crud-field select {
            background: #0f172a;
            color: #f8fafc;
            border-color: #475569;
        }

        body.dark-mode .crud-table td {
            background: #1e293b;
            color: #e5e7eb;
            border-color: #475569;
        }

        body.dark-mode .crud-table th {
            background: #075985;
        }

        body.dark-mode .crud-table tr:nth-child(even) td {
            background: #263449;
        }

        @media (max-width: 750px) {
            .crud-form-grid,
            .crud-filter-grid {
                grid-template-columns: 1fr;
            }

            .crud-card {
                padding: 15px;
            }
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

<main class="crud-container">

    <section class="crud-card">
        <h2>Student Management</h2>
        <br>
        <p>Add, search, filter, update and manage student records.</p>
        <br>


        <?php if ($message !== ""): ?>
            <div class="crud-message <?php echo $messageType === "success" ? "crud-success" : "crud-error"; ?>">
                <?php echo escapeHtml($message); ?>
            </div>
        <?php endif; ?>

        <h3>Add New Student</h3>
        <br>

        <form method="POST" action="students.php">
            <input type="hidden" name="csrf_token"
                   value="<?php echo escapeHtml($_SESSION["csrf_token"]); ?>">
            <input type="hidden" name="action" value="add">

            <div class="crud-form-grid">
                <div class="crud-field">
                    <label for="name">Student Name</label>
                    <input type="text" id="name" name="name"
                           maxlength="100" required>
                </div>

                <div class="crud-field">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           maxlength="150" required>
                </div>

                <div class="crud-field">
                    <label for="course">Course</label>
                    <input type="text" id="course" name="course"
                           maxlength="100" required>
                </div>
            </div>

            <div class="crud-actions">
                <button type="submit" class="crud-btn">Add Student</button>
            </div>
        </form>
    </section>

    <section class="crud-card">
        <h3>Search and Filter Students</h3>
        <br>
        

        <form method="GET" action="students.php">
            <div class="crud-filter-grid">
                <div class="crud-field">
                    <label for="search">Search by Name, Email or Course</label>
                    <input type="search" id="search" name="search"
                           value="<?php echo escapeHtml($search); ?>"
                           placeholder="Enter name, email or course">
                </div>

                <div class="crud-field">
                    <label for="courseFilter">Filter by Course</label>
                    <select id="courseFilter" name="course">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo escapeHtml($course); ?>"
                                <?php echo $courseFilter === $course ? "selected" : ""; ?>>
                                <?php echo escapeHtml($course); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="crud-field">
                    <label for="statusFilter">Filter by Status</label>
                    <select id="statusFilter" name="status">
                        <option value="active" <?php echo $statusFilter === "active" ? "selected" : ""; ?>>
                            Active
                        </option>
                        <option value="inactive" <?php echo $statusFilter === "inactive" ? "selected" : ""; ?>>
                            Inactive
                        </option>
                        <option value="all" <?php echo $statusFilter === "all" ? "selected" : ""; ?>>
                            All Students
                        </option>
                    </select>
                </div>
            </div>

            <div class="crud-actions">
                <button type="submit" class="crud-btn">Search / Filter</button>
                <a href="students.php" class="crud-btn crud-btn-secondary">Reset</a>
                <a href="<?php echo escapeHtml($exportUrl); ?>"
                   class="crud-btn">Export CSV</a>
            </div>
        </form>
    </section>

    <section class="crud-card">
        <h3>Student Records</h3>

        <div class="crud-table-wrap">
            <table class="crud-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($students) > 0): ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo escapeHtml($student["student_id"]); ?></td>
                                <td><?php echo escapeHtml($student["name"]); ?></td>
                                <td><?php echo escapeHtml($student["email"]); ?></td>
                                <td><?php echo escapeHtml($student["course"]); ?></td>
                                <td>
                                    <span class="<?php echo $student["status"] === "active" ? "crud-status-active" : "crud-status-inactive"; ?>">
                                        <?php echo escapeHtml(ucfirst($student["status"])); ?>
                                    </span>
                                </td>
                                <td>
                                    <a class="crud-btn"
                                       href="edit.php?id=<?php echo (int)$student["student_id"]; ?>">
                                        Edit
                                    </a>

                                    <?php if ($student["status"] === "active"): ?>
                                        <form method="POST" action="students.php"
                                              style="display:inline"
                                              onsubmit="return confirm('Are you sure you want to mark this student as inactive?');">
                                            <input type="hidden" name="csrf_token"
                                                   value="<?php echo escapeHtml($_SESSION["csrf_token"]); ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="student_id"
                                                   value="<?php echo (int)$student["student_id"]; ?>">
                                            <button type="submit"
                                                    class="crud-btn crud-btn-danger">
                                                Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No student records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <p class="crud-count">
            Records displayed: <?php echo count($students); ?>
        </p>
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
