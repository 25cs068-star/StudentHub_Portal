
<?php
session_start();

if (isset($_COOKIE["remember_me"])) {
    $cookieParts = explode(":", $_COOKIE["remember_me"], 2);

    if (
        count($cookieParts) === 2 &&
        preg_match('/^[a-f0-9]{24}$/', $cookieParts[0])
    ) {
        try {
            require_once __DIR__ . "/practical9_db.php";

            $stmt = $conn->prepare(
                "DELETE FROM remember_tokens WHERE selector = ?"
            );

            $stmt->bind_param("s", $cookieParts[0]);
            $stmt->execute();
            $stmt->close();
        } catch (Throwable $e) {
            error_log($e->getMessage());
        }
    }
}

setcookie("remember_me", "", [
    "expires" => time() - 3600,
    "path" => "/",
    "httponly" => true,
    "samesite" => "Lax"
]);

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

header("Location: login.php?error=loggedout");
exit();
?>
