<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../api/config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: login.php');
  exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->execute([$username]);
$admin = $stmt->fetch();

if ($admin && password_verify($password, $admin['password'])) {
  $_SESSION['admin'] = $admin['username'];
  header("Location: /admin");
  exit;
}

$_SESSION['login_error'] = 'Username atau password salah';
header("Location: login.php");
exit;
