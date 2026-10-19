
<?php
session_start();

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    !is_numeric($_SESSION["user_id"])
) {
    header("Location: login.php");
    exit;
}

if (
    !isset($_SESSION["last_activity"]) ||
    !is_numeric($_SESSION["last_activity"]) ||
    time() - (int) $_SESSION["last_activity"] > 300
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

    header("Location: login.php?timeout=1");
    exit;
}

$role = strtolower(trim((string) $_SESSION["role"]));

if ($role === "admin") {
    header("Location: admin_dashboard.php");
    exit;
}

if ($role !== "student") {
    header("Location: login.php");
    exit;
}

$_SESSION["last_activity"] = time();

require_once __DIR__ . "/practical10_db.php";

$stmt = $pdo->prepare(
    "SELECT username, enrollment, email, mobile, department, study_year, gender
     FROM users
     WHERE id = ?"
);

$stmt->execute([(int) $_SESSION["user_id"]]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION = [];
    session_destroy();

    header("Location: login.php");
    exit;
}

function e($value)
{
    return htmlspecialchars(
        (string) ($value ?? "Not available"),
        ENT_QUOTES,
        "UTF-8"
    );
}

$yearLabels = [
    "1" => "1st Year",
    "2" => "2nd Year",
    "3" => "3rd Year",
    "4" => "4th Year",
    "1st" => "1st Year",
    "2nd" => "2nd Year",
    "3rd" => "3rd Year",
    "4th" => "4th Year",
    "1st Year" => "1st Year",
    "2nd Year" => "2nd Year",
    "3rd Year" => "3rd Year",
    "4th Year" => "4th Year"
];

$studyYear = $yearLabels[$user["study_year"] ?? ""]
    ?? ($user["study_year"] ?? "Not available");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StudentHub Portal - Profile</title>

    <link rel="stylesheet" href="csscommon.css">

    <style>
        .profile-container {
            width: 95%;
            max-width: 1300px;
            margin: 35px auto;
            display: grid;
            gap: 30px;
        }

        .profile-hero {
            background: white;
            padding: 35px;
            text-align: center;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, .15);
        }

        .profile-hero h2 {
            font-size: 38px;
            color: #003566;
            margin-bottom: 10px;
        }

        .profile-hero p {
            font-size: 17px;
            color: #555;
            line-height: 1.6;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }

        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, .15);
            transition: .3s;
        }

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, .2);
        }

        .profile-card h2 {
            color: #003566;
            margin-bottom: 20px;
            text-align: center;
        }

        .profile-card p {
            margin: 14px 0;
            font-size: 16px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .profile-card b {
            color: #0077b6;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .summary-card {
            background: white;
            padding: 30px 20px;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, .15);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: .3s;
            text-align: center;
        }

        .summary-card:hover {
            background: #0077b6;
            color: white;
            transform: translateY(-5px);
        }

        .summary-card h3 {
            margin-bottom: 10px;
            font-size: 18px;
        }

        .summary-card h2 {
            font-size: 22px;
            overflow-wrap: anywhere;
        }

        body.dark-mode .profile-hero,
        body.dark-mode .profile-card,
        body.dark-mode .summary-card {
            background: #1e1e1e;
            color: white;
        }

        body.dark-mode .profile-hero h2,
        body.dark-mode .profile-card h2 {
            color: #00b4d8;
        }

        body.dark-mode .profile-hero p {
            color: #ddd;
        }

        body.dark-mode .profile-card b {
            color: #00b4d8;
        }

        @media (max-width: 900px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }

            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }

            .profile-hero {
                padding: 25px;
            }

            .profile-hero h2 {
                font-size: 30px;
            }

            .profile-card {
                padding: 22px;
            }
        }
    </style>
</head>

<body>

    <header>
        <button id="themeToggle" class="theme-btn" type="button">
            🌙 Dark Mode
        </button>

        <button id="menuToggle" class="menu-btn" type="button">
            ☰
        </button>

        <div class="logo-section">
            <img src="logo.jfif" alt="StudentHub Logo">
            <h1>StudentHub Portal</h1>
            <p>Your Digital Campus and Academic Companion</p>
        </div>

        <nav>
            <a href="index.html">Home</a>
            <a href="about.html">About</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="events.html">Events</a>
            <a href="profile.php">Profile</a>
            <a href="contact.html">Contact</a>
            <a href="faq.html">FAQ</a>
            <a href="feedback.html">Feedback</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main class="profile-container">

        <section class="profile-hero">
            <h2>Student Profile 👤</h2>
            <p>
                View your personal information and academic details.
            </p>
        </section>

        <section class="profile-grid">

            <div class="profile-card">
                <h2>👤 Personal Details</h2>

                <p><b>Full Name:</b> <?= e($user["username"]) ?></p>
                <p><b>Enrollment No:</b> <?= e($user["enrollment"]) ?></p>
                <p><b>Email:</b> <?= e($user["email"]) ?></p>
                <p><b>Phone:</b> <?= e($user["mobile"]) ?></p>
                <p><b>Gender:</b> <?= e($user["gender"]) ?></p>
            </div>

            <div class="profile-card">
                <h2>🎓 Academic Information</h2>

                <p><b>Department:</b> <?= e($user["department"]) ?></p>
                <p><b>Study Year:</b> <?= e($studyYear) ?></p>
                <p><b>Role:</b> Student</p>
                <p><b>Enrollment:</b> <?= e($user["enrollment"]) ?></p>
            </div>

        </section>

        <section class="summary-grid">

            <div class="summary-card">
                <h3>👤 Account</h3>
                <h2>Active</h2>
            </div>

            <div class="summary-card">
                <h3>🎓 Department</h3>
                <h2><?= e($user["department"]) ?></h2>
            </div>

            <div class="summary-card">
                <h3>📚 Study Year</h3>
                <h2><?= e($studyYear) ?></h2>
            </div>

            <div class="summary-card">
                <h3>🔐 Role</h3>
                <h2>Student</h2>
            </div>

        </section>

    </main>

    <footer>
        <p>&copy; 2026 StudentHub Portal | Designed for Students</p>
    </footer>

    <script src="scriptcomman.js"></script>

</body>
</html>
