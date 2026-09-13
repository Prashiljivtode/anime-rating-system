<?php
// Simple session-based CSRF protection helper
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function csrf_token(): string {
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function verify_csrf(): void {
    $token = $_POST["csrf_token"] ?? "";
    if (!$token || empty($_SESSION["csrf_token"]) || !hash_equals($_SESSION["csrf_token"], $token)) {
        http_response_code(403);
        exit("Invalid security token. Please go back and try again.");
    }
}
?>
