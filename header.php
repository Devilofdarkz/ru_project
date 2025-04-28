<!-- header.php -->
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rajendra University, Balangir</title>
    <!-- Bootstrap CSS -->
    <link 
      rel="stylesheet" 
      href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
    >
    <!-- Optional: Additional styling -->
    <style>
      body {
        margin: 0;
        padding: 0;
      }
      .custom-logo {
        width: 200px;
        height: 200px;
      }
      .univ-title {
        font-size: 1.5rem;
        font-weight: bold;
        text-align: center;
      }
      .univ-motto {
        font-size: 0.9rem;
        text-align: center;
        margin-top: -5px;
        color: #555;
      }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container text-center d-flex flex-column">
    <!-- Top Center Logo and Title -->
    <img src="images/ru_logo.png" alt="RU Logo" class="custom-logo mx-auto d-block">
    <div class="univ-title">RAJENDRA UNIVERSITY, BALANGIR</div>
    <div class="univ-motto">आरोह तमसो ज्योतिः</div>
  </div>
</nav>

<!-- Simple Navbar for navigation (optional) -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php">Home</a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" 
            data-target="#navbarNav" aria-controls="navbarNav" 
            aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon">&#9776;</span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ml-auto">
        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
          <li class="nav-item">
            <a class="nav-link" href="admin_dashboard.php">Admin Dashboard</a>
          </li>
        <?php elseif (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student'): ?>
          <li class="nav-item">
            <a class="nav-link" href="student_dashboard.php">My Dashboard</a>
          </li>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item">
            <a class="nav-link" href="logout.php">Logout</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="login.php">Student Login</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="admin_login.php">Admin Login</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container mt-4">
