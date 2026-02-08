<?php
require 'auth.php';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EyeSec Admin | Dashboard</title>
    <link rel="icon" type="image/png" href="https://eyesecs.site/assets/icons/logo-eyesec.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        
        .nav-item.active {
            color: #000;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(0, 229, 255, 0.4);
            animation: activePulse 2s infinite;
        }

        @keyframes activePulse {
            0%, 100% {
                box-shadow: 0 5px 15px rgba(0, 229, 255, 0.4);
            }
            50% {
                box-shadow: 0 5px 25px rgba(0, 229, 255, 0.6);
            }
        }
    </style>
</head>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-section">
            <div class="logo">
                <div class="logo-img">
                    <img src="https://eyesecs.site/assets/icons/logo-eyesec.png" alt="EyeSec Logo">
                </div>
                <div class="logo-text">
                    <div class="brand">EyeSec</div>
                    <div class="tagline">Security Admin</div>
                </div>
            </div>
        </div>

        <nav class="nav-menu">
            <!-- Data attribute untuk menyimpan identifikasi menu -->
            <a href="/admin" class="nav-item" >
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            <a href="?menu=ApiKeys" class="nav-item">
                <i class="fas fa-key"></i>
                <span>API Keys</span>
            </a>
            <a href="?menu=logs" class="nav-item">
                <i class="fas fa-scroll"></i>
                <span>API Logs</span>
            </a>
            <!-- <a href="?menu=users" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
            <a href="?menu=settings" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a> -->
        </nav>

        <div class="logout-btn">
            <a href="logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const navItems = document.querySelectorAll(".nav-item");
    const params = new URLSearchParams(window.location.search);
    const currentMenu = params.get("menu");

    navItems.forEach(item => {
        item.classList.remove("active");

        const href = item.getAttribute("href");
        if (!href) return;

        // Dashboard → aktif jika TIDAK ada ?menu
        if (!currentMenu && href === "/admin") {
            item.classList.add("active");
        }

        // Menu lain → cocokkan ?menu=value
        if (currentMenu && href === "?menu=" + currentMenu) {
            item.classList.add("active");
        }
    });
});
</script>
