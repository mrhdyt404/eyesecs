<?php 
require "auth.php";
require '../api/config/database.php';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EyeSec Admin | Dashboard</title>
    <link rel="icon" type="image/png" href="https://eyesecs.site/assets/icons/logo-eyesec.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php require "sidebar.php";?>

    <!-- Main Content -->
    <div class="main-content">
        <?php
            if($_GET['menu'] == null ) {
                include "dashboard.php";
            } elseif ($_GET['menu'] == 'ApiKeys' ) {
                include "api_keys.php";
            } elseif ($_GET['menu'] == 'logs' ) {
                include "logs.php";
            }
                exit();
            ?>
    </div>

   
</body>
</html>