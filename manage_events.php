
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
            <p>You do not have permission to manage events.</p>
            <a href="dashboard.php">Return to Student Dashboard</a>
        </main>
        <script src="scriptcomman.js"></script>
    </body>
    </html>
    <?php
    exit();
}

require_once __DIR__ . "/practical11_db.php";

$uploadDir = __DIR__ . "/uploads/event_posters/";
$uploadUrl = "uploads/event_posters/";

if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
    error_log("Unable to create event poster directory.");
}

if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
    error_log("Event poster directory is missing or not writable.");
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "";
$editEvent = null;

function escapeHtml($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function validEventDate($date)
{
    if (!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }

    return checkdate(
        (int)substr($date, 5, 2),
        (int)substr($date, 8, 2),
        (int)substr($date, 0, 4)
    );
}

function validEventFields($name, $date, $venue, $status)
{
    return is_string($name) &&
        is_string($date) &&
        is_string($venue) &&
        trim($name) !== "" &&
        strlen($name) <= 100 &&
        trim($venue) !== "" &&
        strlen($venue) <= 100 &&
        validEventDate($date) &&
        in_array($status, ["open", "closed"], true);
}

function uploadPoster($uploadDir)
{
    if (
        !isset($_FILES["poster"]) ||
        $_FILES["poster"]["error"] === UPLOAD_ERR_NO_FILE
    ) {
        return ["name" => null, "error" => null];
    }

    $file = $_FILES["poster"];

    if ($file["error"] !== UPLOAD_ERR_OK) {
        if (
            $file["error"] === UPLOAD_ERR_INI_SIZE ||
            $file["error"] === UPLOAD_ERR_FORM_SIZE
        ) {
            return ["name" => null, "error" => "Poster size must not exceed 2 MB."];
        }

        return ["name" => null, "error" => "Poster upload failed. Please try again."];
    }

    if (
        !isset($file["tmp_name"], $file["size"]) ||
        !is_string($file["tmp_name"]) ||
        !is_int($file["size"]) ||
        $file["size"] <= 0
    ) {
        return ["name" => null, "error" => "Invalid uploaded file."];
    }

    if ($file["size"] > 2 * 1024 * 1024) {
        return ["name" => null, "error" => "Poster size must not exceed 2 MB."];
    }

    if (!is_uploaded_file($file["tmp_name"])) {
        return ["name" => null, "error" => "Invalid uploaded file."];
    }

    if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
        return ["name" => null, "error" => "Poster upload directory is unavailable."];
    }

    $allowedTypes = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    if (!class_exists("finfo")) {
        return ["name" => null, "error" => "Server file validation is unavailable."];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file["tmp_name"]);

    if (!isset($allowedTypes[$mimeType])) {
        return [
            "name" => null,
            "error" => "Invalid poster type. Only JPG, PNG and WEBP are allowed."
        ];
    }

    if (@getimagesize($file["tmp_name"]) === false) {
        return ["name" => null, "error" => "The selected file is not a valid image."];
    }

    try {
        $newName = bin2hex(random_bytes(16)) . "." . $allowedTypes[$mimeType];
    } catch (Throwable $e) {
        return ["name" => null, "error" => "Unable to generate a poster filename."];
    }

    if (!move_uploaded_file($file["tmp_name"], $uploadDir . $newName)) {
        return ["name" => null, "error" => "Unable to save the poster. Check folder permissions."];
    }

    return ["name" => $newName, "error" => null];
}

function removePoster($uploadDir, $poster)
{
    if (
        is_string($poster) &&
        $poster !== "" &&
        basename($poster) === $poster
    ) {
        $path = $uploadDir . $poster;

        if (is_file($path)) {
            @unlink($path);
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["csrf_token"], $token)
    ) {
        $message = "Invalid request. Please refresh the page and try again.";
        $messageType = "error";
    } else {
        $action = $_POST["action"] ?? "";

        if (!is_string($action)) {
            $action = "";
        }

        if ($action === "add" || $action === "update") {
            $eventId = filter_var(
                $_POST["event_id"] ?? null,
                FILTER_VALIDATE_INT
            );

            $eventName = $_POST["event_name"] ?? "";
            $eventDate = $_POST["event_date"] ?? "";
            $venue = $_POST["venue"] ?? "";
            $description = $_POST["description"] ?? "";
            $category = $_POST["category"] ?? "";
            $status = $_POST["status"] ?? "open";

            if (
                !is_string($eventName) ||
                !is_string($eventDate) ||
                !is_string($venue) ||
                !is_string($description) ||
                !is_string($category) ||
                !is_string($status)
            ) {
                $message = "Invalid form data.";
                $messageType = "error";
            } else {
                $eventName = trim($eventName);
                $eventDate = trim($eventDate);
                $venue = trim($venue);
                $description = trim($description);
                $category = trim($category);

                if ($action === "update" && (!$eventId || $eventId < 1)) {
                    $message = "Invalid event ID.";
                    $messageType = "error";
                } elseif (
                    !validEventFields($eventName, $eventDate, $venue, $status) ||
                    strlen($description) > 5000 ||
                    strlen($category) > 100
                ) {
                    $message = "Enter valid event details. Name and venue must be at most 100 characters.";
                    $messageType = "error";
                } else {
                    $upload = uploadPoster($uploadDir);

                    if ($upload["error"] !== null) {
                        $message = $upload["error"];
                        $messageType = "error";
                    } else {
                        $newPoster = $upload["name"];

                        if ($action === "add") {
                            $stmt = mysqli_prepare(
                                $conn,
                                "INSERT INTO events
                                (event_name, event_date, venue, description, category, poster, status)
                                VALUES (?, ?, ?, ?, ?, ?, ?)"
                            );

                            if ($stmt) {
                                mysqli_stmt_bind_param(
                                    $stmt,
                                    "sssssss",
                                    $eventName,
                                    $eventDate,
                                    $venue,
                                    $description,
                                    $category,
                                    $newPoster,
                                    $status
                                );

                                if (mysqli_stmt_execute($stmt)) {
                                    $message = "Event created successfully.";
                                    $messageType = "success";
                                } else {
                                    $message = "Unable to create event.";
                                    $messageType = "error";
                                    removePoster($uploadDir, $newPoster);
                                }

                                mysqli_stmt_close($stmt);
                            } else {
                                $message = "Unable to prepare the database query.";
                                $messageType = "error";
                                removePoster($uploadDir, $newPoster);
                            }
                        } else {
                            $stmt = mysqli_prepare(
                                $conn,
                                "SELECT poster FROM events WHERE event_id = ?"
                            );

                            $oldPoster = null;
                            $eventExists = false;

                            if ($stmt) {
                                mysqli_stmt_bind_param($stmt, "i", $eventId);
                                mysqli_stmt_execute($stmt);
                                mysqli_stmt_bind_result($stmt, $oldPoster);
                                $eventExists = mysqli_stmt_fetch($stmt);
                                mysqli_stmt_close($stmt);
                            }

                            if (!$eventExists) {
                                $message = "Event not found.";
                                $messageType = "error";
                                removePoster($uploadDir, $newPoster);
                            } else {
                                $finalPoster = $newPoster !== null
                                    ? $newPoster
                                    : $oldPoster;

                                $stmt = mysqli_prepare(
                                    $conn,
                                    "UPDATE events
                                    SET event_name = ?, event_date = ?, venue = ?,
                                        description = ?, category = ?, poster = ?, status = ?
                                    WHERE event_id = ?"
                                );

                                if ($stmt) {
                                    mysqli_stmt_bind_param(
                                        $stmt,
                                        "sssssssi",
                                        $eventName,
                                        $eventDate,
                                        $venue,
                                        $description,
                                        $category,
                                        $finalPoster,
                                        $status,
                                        $eventId
                                    );

                                    if (mysqli_stmt_execute($stmt)) {
                                        $message = "Event updated successfully.";
                                        $messageType = "success";

                                        if ($newPoster !== null && $oldPoster !== $newPoster) {
                                            removePoster($uploadDir, $oldPoster);
                                        }
                                    } else {
                                        $message = "Unable to update event.";
                                        $messageType = "error";
                                        removePoster($uploadDir, $newPoster);
                                    }

                                    mysqli_stmt_close($stmt);
                                } else {
                                    $message = "Unable to prepare the update query.";
                                    $messageType = "error";
                                    removePoster($uploadDir, $newPoster);
                                }
                            }
                        }
                    }
                }
            }
        } elseif ($action === "delete") {
            $eventId = filter_var(
                $_POST["event_id"] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$eventId || $eventId < 1) {
                $message = "Invalid event ID.";
                $messageType = "error";
            } else {
                $stmt = mysqli_prepare(
                    $conn,
                    "SELECT poster FROM events WHERE event_id = ?"
                );

                $poster = null;
                $eventExists = false;

                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "i", $eventId);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $poster);
                    $eventExists = mysqli_stmt_fetch($stmt);
                    mysqli_stmt_close($stmt);
                }

                if (!$eventExists) {
                    $message = "Event not found.";
                    $messageType = "error";
                } else {
                    $stmt = mysqli_prepare(
                        $conn,
                        "DELETE FROM events WHERE event_id = ?"
                    );

                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, "i", $eventId);

                        if (
                            mysqli_stmt_execute($stmt) &&
                            mysqli_stmt_affected_rows($stmt) > 0
                        ) {
                            $message = "Event deleted successfully.";
                            $messageType = "success";
                            removePoster($uploadDir, $poster);
                        } else {
                            $message = "Unable to delete event.";
                            $messageType = "error";
                        }

                        mysqli_stmt_close($stmt);
                    } else {
                        $message = "Unable to prepare the delete query.";
                        $messageType = "error";
                    }
                }
            }
        } elseif ($action === "status") {
            $eventId = filter_var(
                $_POST["event_id"] ?? null,
                FILTER_VALIDATE_INT
            );

            $status = $_POST["status"] ?? "";

            if (
                !$eventId ||
                $eventId < 1 ||
                !is_string($status) ||
                !in_array($status, ["open", "closed"], true)
            ) {
                $message = "Invalid event or status.";
                $messageType = "error";
            } else {
                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE events SET status = ? WHERE event_id = ?"
                );

                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "si", $status, $eventId);

                    if (
                        mysqli_stmt_execute($stmt) &&
                        mysqli_stmt_affected_rows($stmt) >= 0
                    ) {
                        $message = "Event status updated successfully.";
                        $messageType = "success";
                    } else {
                        $message = "Unable to update event status.";
                        $messageType = "error";
                    }

                    mysqli_stmt_close($stmt);
                } else {
                    $message = "Unable to prepare the status query.";
                    $messageType = "error";
                }
            }
        } else {
            $message = "Invalid action.";
            $messageType = "error";
        }
    }
}

if (isset($_GET["edit"])) {
    $editId = filter_var($_GET["edit"], FILTER_VALIDATE_INT);

    if ($editId && $editId > 0) {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT event_id, event_name, event_date, venue, description,
                    category, poster, status
             FROM events WHERE event_id = ?"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $editId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result(
                $stmt,
                $editEventId,
                $editEventName,
                $editEventDate,
                $editVenue,
                $editDescription,
                $editCategory,
                $editPoster,
                $editStatus
            );

            if (mysqli_stmt_fetch($stmt)) {
                $editEvent = [
                    "event_id" => $editEventId,
                    "event_name" => $editEventName,
                    "event_date" => $editEventDate,
                    "venue" => $editVenue,
                    "description" => $editDescription,
                    "category" => $editCategory,
                    "poster" => $editPoster,
                    "status" => $editStatus
                ];
            }

            mysqli_stmt_close($stmt);
        }

        if ($editEvent === null && $message === "") {
            $message = "Event not found.";
            $messageType = "error";
        }
    }
}

$events = [];
$result = mysqli_query(
    $conn,
    "SELECT event_id, event_name, event_date, venue, description,
            category, poster, status
     FROM events ORDER BY event_date DESC, event_id DESC"
);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $events[] = $row;
    }

    mysqli_free_result($result);
} elseif ($message === "") {
    $message = "Unable to load events.";
    $messageType = "error";
}

$totalEvents = count($events);
$editing = $editEvent !== null;

$formName = $editEvent["event_name"] ?? "";
$formDate = $editEvent["event_date"] ?? "";
$formVenue = $editEvent["venue"] ?? "";
$formDescription = $editEvent["description"] ?? "";
$formCategory = $editEvent["category"] ?? "";
$formStatus = $editEvent["status"] ?? "open";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events | StudentHub</title>
    <link rel="stylesheet" href="csscommon.css">

    <style>
        .manage-container {
            width: 92%;
            max-width: 1200px;
            margin: 30px auto;
        }

        .manage-welcome {
            margin-bottom: 25px;
        }

        .manage-welcome h2 {
            color: #003566;
            margin-bottom: 8px;
        }

        body.dark-mode .manage-welcome h2 {
            color: #ffffff;
        }

        .event-section {
            margin-bottom: 30px;
            padding: 24px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .10);
        }

        body.dark-mode .event-section {
            background: #263449;
            color: #ffffff;
        }

        .event-section h2 {
            color: #003566;
            margin-bottom: 18px;
        }

        body.dark-mode .event-section h2 {
            color: #8bdcff;
        }

        .event-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0 18px;
        }

        .event-section label {
            display: block;
            margin: 13px 0 6px;
            font-weight: 600;
        }

        .event-section input,
        .event-section textarea,
        .event-section select {
            width: 100%;
            box-sizing: border-box;
            padding: 11px;
            border: 1px solid #b8c7d9;
            border-radius: 6px;
            font: inherit;
        }

        .event-section textarea {
            min-height: 100px;
            resize: vertical;
        }

        body.dark-mode .event-section input,
        body.dark-mode .event-section textarea,
        body.dark-mode .event-section select {
            background: #18283a;
            color: #ffffff;
            border-color: #53657b;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .action-btn {
            display: inline-block;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            color: #ffffff;
            background: #0077b6;
            text-decoration: none;
            font: inherit;
            cursor: pointer;
        }

        .action-btn:hover {
            background: #003566;
            color: #ffffff;
        }

        .delete-btn {
            background: #c62828;
        }

        .delete-btn:hover {
            background: #991b1b;
        }

        .secondary-btn {
            background: #526579;
        }

        .message {
            padding: 13px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .message.success {
            background: #e1f7e8;
            color: #176534;
            border: 1px solid #9dd9ad;
        }

        .message.error {
            background: #ffeded;
            color: #a51d1d;
            border: 1px solid #f0b3b3;
        }

        .help-text {
            display: block;
            margin-top: 5px;
            font-size: 13px;
            color: #526579;
        }

        body.dark-mode .help-text {
            color: #c4d2e3;
        }

        .current-poster,
        .poster-thumb {
            width: 105px;
            height: 75px;
            object-fit: cover;
            border-radius: 6px;
        }

        .current-poster {
            display: block;
            width: auto;
            max-width: 180px;
            height: auto;
            max-height: 130px;
            margin-top: 8px;
        }

        .event-search {
            margin-bottom: 15px;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .event-table {
            width: 100%;
            border-collapse: collapse;
        }

        .event-table th,
        .event-table td {
            padding: 12px;
            border: 1px solid #d5deea;
            text-align: left;
            vertical-align: top;
        }

        .event-table th {
            background: #0077b6;
            color: #ffffff;
        }

        body.dark-mode .event-table td {
            border-color: #53657b;
        }

        body.dark-mode .event-table th {
            background: #005f91;
        }

        .event-table tr:nth-child(even) {
            background: #f2f7fb;
        }

        body.dark-mode .event-table tr:nth-child(even) {
            background: #1e3045;
        }

        .status-open {
            color: #16733a;
            font-weight: bold;
        }

        .status-closed {
            color: #c62828;
            font-weight: bold;
        }

        body.dark-mode .status-open {
            color: #75e39a;
        }

        body.dark-mode .status-closed {
            color: #ff9999;
        }

        .table-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            align-items: center;
        }

        .table-actions form {
            margin: 0;
        }

        .small-btn {
            padding: 7px 10px;
            font-size: 14px;
        }

        .event-count {
            font-weight: bold;
            margin-bottom: 15px;
        }

        .empty-state {
            text-align: center;
            padding: 25px;
        }

        @media (max-width: 650px) {
            .manage-container {
                width: 95%;
            }

            .event-section {
                padding: 15px;
            }

            .event-grid {
                grid-template-columns: 1fr;
            }

            .full-width {
                grid-column: auto;
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

<main class="manage-container">

    <section class="manage-welcome">
        <h2>Manage Events</h2>
        <p>Add, view, update, delete and manage StudentHub events and posters.</p>
    </section>

    <?php if ($message !== ""): ?>
        <div class="message <?= escapeHtml($messageType) ?>" role="status" aria-live="polite">
            <?= escapeHtml($message) ?>
        </div>
    <?php endif; ?>

    <section class="event-section">
        <h2><?= $editing ? "Update Event" : "Add New Event" ?></h2>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token"
                   value="<?= escapeHtml($_SESSION["csrf_token"]) ?>">
            <input type="hidden" name="action"
                   value="<?= $editing ? "update" : "add" ?>">

            <?php if ($editing): ?>
                <input type="hidden" name="event_id"
                       value="<?= (int)$editEvent["event_id"] ?>">
            <?php endif; ?>

            <div class="event-grid">
                <div>
                    <label for="event_name">Event Name *</label>
                    <input type="text" id="event_name" name="event_name"
                           maxlength="100" required
                           value="<?= escapeHtml($formName) ?>">
                </div>

                <div>
                    <label for="event_date">Event Date *</label>
                    <input type="date" id="event_date" name="event_date"
                           required value="<?= escapeHtml($formDate) ?>">
                </div>

                <div>
                    <label for="venue">Venue *</label>
                    <input type="text" id="venue" name="venue"
                           maxlength="100" required
                           value="<?= escapeHtml($formVenue) ?>">
                </div>

                <div>
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category"
                           maxlength="100"
                           value="<?= escapeHtml($formCategory) ?>">
                </div>

                <div>
                    <label for="status">Event Status *</label>
                    <select id="status" name="status" required>
                        <option value="open" <?= $formStatus === "open" ? "selected" : "" ?>>Open</option>
                        <option value="closed" <?= $formStatus === "closed" ? "selected" : "" ?>>Closed</option>
                    </select>
                </div>

                <div>
                    <label for="poster">Event Poster</label>
                    <input type="file" id="poster" name="poster">
                    <span class="help-text">JPG, PNG or WEBP only. Maximum size: 2 MB. Optional.</span>

                    <?php if ($editing && !empty($editEvent["poster"])): ?>
                        <span class="help-text">Current poster:</span>
                        <img class="current-poster"
                             src="<?= escapeHtml($uploadUrl . basename($editEvent["poster"])) ?>"
                             alt="Current event poster">
                    <?php endif; ?>
                </div>

                <div class="full-width">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"
                              maxlength="5000"><?= escapeHtml($formDescription) ?></textarea>
                </div>
            </div>

            <p>
                <button type="submit" class="action-btn">
                    <?= $editing ? "Update Event" : "Create Event" ?>
                </button>

                <?php if ($editing): ?>
                    <a href="manage_events.php" class="action-btn secondary-btn">Cancel Edit</a>
                <?php endif; ?>
            </p>
        </form>
    </section>

    <section class="event-section">
        <h2>Event List</h2>

        <p class="event-count">Total Events: <?= $totalEvents ?></p>

        <input type="search" id="eventSearch"
               class="event-search"
               placeholder="Search by event name, category or venue..."
               aria-label="Search events">

        <div class="table-wrapper">
            <table class="event-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Poster</th>
                        <th>Event Details</th>
                        <th>Date</th>
                        <th>Venue</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody id="eventTableBody">
                <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">No events found. Add your first event above.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= (int)$event["event_id"] ?></td>

                            <td>
                                <?php if (!empty($event["poster"])): ?>
                                    <img class="poster-thumb"
                                         src="<?= escapeHtml($uploadUrl . basename($event["poster"])) ?>"
                                         alt="<?= escapeHtml($event["event_name"]) ?> poster">
                                <?php else: ?>
                                    No poster
                                <?php endif; ?>
                            </td>

                            <td>
                                <strong><?= escapeHtml($event["event_name"]) ?></strong><br>
                                <small>Category: <?= escapeHtml($event["category"] ?: "General") ?></small><br>
                                <small><?= escapeHtml($event["description"]) ?></small>
                            </td>

                            <td><?= escapeHtml($event["event_date"]) ?></td>
                            <td><?= escapeHtml($event["venue"]) ?></td>

                            <td>
                                <span class="<?= $event["status"] === "open" ? "status-open" : "status-closed" ?>">
                                    <?= escapeHtml(ucfirst($event["status"])) ?>
                                </span>
                            </td>

                            <td>
                                <div class="table-actions">
                                    <a class="action-btn small-btn"
                                       href="manage_events.php?edit=<?= (int)$event["event_id"] ?>">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this event?');">
                                        <input type="hidden" name="csrf_token"
                                               value="<?= escapeHtml($_SESSION["csrf_token"]) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="event_id"
                                               value="<?= (int)$event["event_id"] ?>">

                                        <button type="submit" class="action-btn delete-btn small-btn">
                                            Delete
                                        </button>
                                    </form>

                                    <form method="POST">
                                        <input type="hidden" name="csrf_token"
                                               value="<?= escapeHtml($_SESSION["csrf_token"]) ?>">
                                        <input type="hidden" name="action" value="status">
                                        <input type="hidden" name="event_id"
                                               value="<?= (int)$event["event_id"] ?>">

                                        <select name="status" aria-label="Change event status">
                                            <option value="open" <?= $event["status"] === "open" ? "selected" : "" ?>>Open</option>
                                            <option value="closed" <?= $event["status"] === "closed" ? "selected" : "" ?>>Closed</option>
                                        </select>

                                        <button type="submit" class="action-btn secondary-btn small-btn">
                                            Save Status
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>






<footer>
    <p>&copy; 2026 StudentHub Portal | Designed for Students</p>
</footer>

<script src="scriptcomman.js"></script>

<script>
    const eventSearch = document.getElementById("eventSearch");
    const eventRows = document.querySelectorAll("#eventTableBody tr");

    if (eventSearch) {
        eventSearch.addEventListener("input", function () {
            const query = this.value.toLowerCase().trim();

            eventRows.forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().includes(query)
                    ? ""
                    : "none";
            });
        });
    }
</script>

</body>
</html>
