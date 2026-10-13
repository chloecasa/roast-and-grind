<?php

if (session_status() === PHP_SESSION_NONE) {
    session_name('roast_and_grind');
    session_start();
}
