
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
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            "",
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
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
        <title>Unauthorized Access | StudentHub</title>
        <link rel="stylesheet" href="csscommon.css">
    </head>
    <body>
        <main style="max-width:600px;margin:80px auto;padding:30px;text-align:center;">
            <h1>Unauthorized Access</h1>
            <p>You do not have permission to access the Admin Dashboard.</p>
            <a href="dashboard.php">Return to Student Dashboard</a>
        </main>
    </body>
    </html>
    <?php
    exit();
}

require_once __DIR__ . "/practical10_db.php";

try {
    $stmt = $pdo->prepare(
        "SELECT id, username, email, enrollment, department, study_year
         FROM users
         WHERE LOWER(role) = :role
         ORDER BY id DESC"
    );

    $stmt->execute(["role" => "student"]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalStudents = count($students);
    $databaseError = false;
} catch (PDOException $e) {
    error_log($e->getMessage());
    $students = [];
    $totalStudents = 0;
    $databaseError = true;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | StudentHub</title>

    <link rel="stylesheet" href="csscommon.css">

    <style>
        .admin-container {
            width: 92%;
            max-width: 1200px;
            margin: 30px auto;
        }

        .admin-welcome {
            margin-bottom: 25px;
        }

        .admin-welcome h2 {
            margin-bottom: 8px;
            color: #003566;
        }

        .admin-date {
            margin-top: 10px;
            font-size: 16px;
        }

        body.dark-mode .admin-welcome h2 {
            color: #ffffff;
        }

        .admin-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .admin-card {
            padding: 24px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.10);
        }

        .admin-card h3 {
            margin-bottom: 12px;
            color: #003566;
        }

        .admin-card p {
            font-size: 22px;
            font-weight: bold;
            overflow-wrap: anywhere;
        }

        body.dark-mode .admin-card {
            background: #263449;
            color: #ffffff;
        }

        body.dark-mode .admin-card h3 {
            color: #8bdcff;
        }

        .admin-section {
            margin-bottom: 30px;
            padding: 24px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.10);
            scroll-margin-top: 20px;
        }

        body.dark-mode .admin-section {
            background: #263449;
            color: #ffffff;
        }

        .admin-section h2 {
            margin-bottom: 18px;
            color: #003566;
        }

        body.dark-mode .admin-section h2 {
            color: #8bdcff;
        }

        .admin-nav {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
        }

        .admin-nav a {
            text-decoration: none;
        }

        .admin-search {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid #b8c7d9;
            border-radius: 6px;
            font-size: 15px;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .student-table {
            width: 100%;
            border-collapse: collapse;
        }

        .student-table th,
        .student-table td {
            padding: 12px;
            border: 1px solid #d5deea;
            text-align: left;
        }

        .student-table th {
            background: #0077b6;
            color: #ffffff;
        }

        body.dark-mode .student-table td {
            border-color: #53657b;
        }

        body.dark-mode .student-table th {
            background: #005f91;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 15px;
        }

        .quick-actions a {
            display: block;
            padding: 16px;
            text-align: center;
            background: #0077b6;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
        }

        .quick-actions a:hover {
            background: #003566;
        }

        .admin-message {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        @media (max-width: 600px) {
            .admin-container {
                width: 95%;
            }

            .admin-section {
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
        <a href="#students">Student Directory</a>
        <a href="manage_events.php">Manage Events</a>
        <a href="manage_feedback.php">Manage Feedback</a>
        <a href="contact_messages.php">Contact Messages</a>
        <a href="manage_faqs.php">Manage FAQs</a>
        <a href="admin_profile.php">Admin Profile</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="admin-container">

    <section class="admin-welcome">
        <h2>Welcome, Admin!</h2>

        <p>Manage StudentHub Portal from your dashboard.</p>

        <p class="admin-date">
            Today's Date: <span id="currentDate"></span>
        </p>
    </section>

    <section class="admin-cards">
        <div class="admin-card">
            <h3>Total Students</h3>
            <p><?= $totalStudents ?></p>
        </div>

        <div class="admin-card">
            <h3>Logged-in Admin</h3>
            <p><?= htmlspecialchars($_SESSION["username"] ?? "Admin", ENT_QUOTES, "UTF-8") ?></p>
        </div>

        <div class="admin-card">
            <h3>Portal Status</h3>
            <p>Active</p>
        </div>
    </section>

    <section class="admin-section" id="students">
        <h2>Student Directory</h2>

        <?php if ($databaseError): ?>
            <p class="admin-message">
                Student data could not be loaded. Please check the database connection.
            </p>
        <?php endif; ?>

        <input
            type="text"
            id="studentSearch"
            class="admin-search"
            placeholder="Search by name, email, enrollment, department or year..."
            aria-label="Search students"
        >

        <div class="table-wrapper">
            <table class="student-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Enrollment</th>
                        <th>Department</th>
                        <th>Year</th>
                    </tr>
                </thead>

                <tbody id="studentTableBody">
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="6">
                                <?= $databaseError
                                    ? "Student records are currently unavailable."
                                    : "No student records found." ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($student["id"] ?? ""), ENT_QUOTES, "UTF-8") ?></td>
                                <td><?= htmlspecialchars((string)($student["username"] ?? ""), ENT_QUOTES, "UTF-8") ?></td>
                                <td><?= htmlspecialchars((string)($student["email"] ?? ""), ENT_QUOTES, "UTF-8") ?></td>
                                <td><?= htmlspecialchars((string)($student["enrollment"] ?? ""), ENT_QUOTES, "UTF-8") ?></td>
                                <td><?= htmlspecialchars((string)($student["department"] ?? ""), ENT_QUOTES, "UTF-8") ?></td>
                                <td><?= htmlspecialchars((string)($student["study_year"] ?? ""), ENT_QUOTES, "UTF-8") ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="admin-section">
        <h2>Quick Actions</h2>

        <div class="quick-actions">
            <a href="manage_events.php">Manage Events</a>
            <a href="manage_feedback.php">Manage Feedback</a>
            <a href="contact_messages.php">View Contact Messages</a>
            <a href="manage_faqs.php">Manage FAQs</a>
            <a href="admin_profile.php">Admin Profile</a>
        </div>
    </section>

</main>

<footer>
    <p>&copy; 2026 StudentHub Portal | Designed for Students</p>
</footer>

<script src="scriptcomman.js"></script>

<script>
    const currentDate = document.getElementById("currentDate");

    if (currentDate) {
        currentDate.textContent = new Date().toLocaleDateString();
    }

    const studentSearch = document.getElementById("studentSearch");
    const studentRows = document.querySelectorAll("#studentTableBody tr");

    if (studentSearch) {
        studentSearch.addEventListener("input", function () {
            const searchText = this.value.toLowerCase().trim();

            studentRows.forEach(function (row) {
                const rowText = row.textContent.toLowerCase();
                row.style.display = rowText.includes(searchText) ? "" : "none";
            });
        });
    }
</script>

</body>
</html>
