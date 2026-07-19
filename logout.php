<?php
/**
 * Logout: menghapus session lalu redirect ke halaman login.
 */
require_once __DIR__ . '/config/config.php';

session_unset();
session_destroy();

redirect('/login.php');
