<?php
// includes/session.php - ADDED (FR-17)
// Gives this app its own session name, so its cart (and later its login) is not
// shared with other projects on localhost, which all use the default "PHPSESSID".
if (session_status() === PHP_SESSION_NONE) {
    session_name('roast_and_grind');
    session_start();
}