```php
<?php
session_start();

function loginFailed($error = "invalid")
{
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

    header("Location: login.php?error=" . $error);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

$role = strtolower(trim($_POST["role"] ?? ""));
$loginId = trim($_POST["loginId"] ?? "");
$password = $_POST["password"] ?? "";
$remember = isset($_POST["remember"]);

if (
    !in_array($role, ["student", "admin"], true) ||
    $loginId === "" ||
    $password === ""
) {
    loginFailed("invalid");
}

require_once __DIR__ . "/practical9_db.php";

$stmt = $conn->prepare(
    "SELECT id, username, email, password, role, enrollment
     FROM users
     WHERE email = ? OR enrollment = ?
     LIMIT 1"
);

if (!$stmt) {
    error_log($conn->error);
    loginFailed("database");
}

$stmt->bind_param("ss", $loginId, $loginId);

if (!$stmt->execute()) {
    error_log($stmt->error);
    $stmt->close();
    loginFailed("database");
}

$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    loginFailed("invalid");
}

$databaseRole = strtolower(trim((string) $user["role"]));
$storedPassword = (string) $user["password"];

if (!password_verify($password, $storedPassword)) {
    loginFailed("invalid");
}

if ($databaseRole !== $role) {
    loginFailed("invalid");
}

if (!session_regenerate_id(true)) {
    loginFailed("session");
}

$_SESSION["user_id"] = $user["id"];
$_SESSION["username"] = $user["username"];
$_SESSION["email"] = $user["email"];
$_SESSION["role"] = $databaseRole;
$_SESSION["last_activity"] = time();

if ($remember) {
    try {
        $selector = bin2hex(random_bytes(12));
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash("sha256", $token);
        $expiresAt = date(
            "Y-m-d H:i:s",
            time() + (30 * 24 * 60 * 60)
        );

        $deleteStmt = $conn->prepare(
            "DELETE FROM remember_tokens WHERE user_id = ?"
        );

        if (!$deleteStmt) {
            throw new RuntimeException($conn->error);
        }

        $deleteStmt->bind_param("i", $user["id"]);

        if (!$deleteStmt->execute()) {
            throw new RuntimeException($deleteStmt->error);
        }

        $deleteStmt->close();

        $tokenStmt = $conn->prepare(
            "INSERT INTO remember_tokens
             (selector, user_id, token_hash, expires_at)
             VALUES (?, ?, ?, ?)"
        );

        if (!$tokenStmt) {
            throw new RuntimeException($conn->error);
        }

        $tokenStmt->bind_param(
            "siss",
            $selector,
            $user["id"],
            $tokenHash,
            $expiresAt
        );

        if (!$tokenStmt->execute()) {
            throw new RuntimeException($tokenStmt->error);
        }

        $tokenStmt->close();

        $cookieValue = $selector . ":" . $token;

        setcookie("remember_me", $cookieValue, [
            "expires" => time() + (30 * 24 * 60 * 60),
            "path" => "/",
            "secure" => !empty($_SERVER["HTTPS"]) &&
                        $_SERVER["HTTPS"] !== "off",
            "httponly" => true,
            "samesite" => "Lax"
        ]);
    } catch (Throwable $e) {
        error_log($e->getMessage());

        setcookie("remember_me", "", [
            "expires" => time() - 3600,
            "path" => "/",
            "secure" => !empty($_SERVER["HTTPS"]) &&
                        $_SERVER["HTTPS"] !== "off",
            "httponly" => true,
            "samesite" => "Lax"
        ]);
    }
} else {
    setcookie("remember_me", "", [
        "expires" => time() - 3600,
        "path" => "/",
        "secure" => !empty($_SERVER["HTTPS"]) &&
                    $_SERVER["HTTPS"] !== "off",
        "httponly" => true,
        "samesite" => "Lax"
    ]);
}

if ($databaseRole === "admin") {
    header("Location: admin_dashboard.php");
    exit();
}

header("Location: dashboard.php");
exit();
?>
```