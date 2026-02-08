<?php
require 'auth.php';
require '../api/config/database.php';

/* ================= CREATE API KEY ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $owner = trim($_POST['owner'] ?? '');
    $type  = $_POST['type'] ?? 'guest';
    $rate  = (int) ($_POST['rate_limit'] ?? 10);

    $apiKey = bin2hex(random_bytes(32)); // 64 char

    $stmt = $pdo->prepare("
        INSERT INTO api_keys (api_key, owner, type, rate_limit)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$apiKey, $owner, $type, $rate]);

    header("Location: api_keys.php");
    exit;
}

/* ================= TOGGLE STATUS ================= */
if (isset($_GET['toggle'])) {
    $id = (int) $_GET['toggle'];

    $pdo->query("
        UPDATE api_keys
        SET status = IF(status='active','inactive','active')
        WHERE id = {$id}
    ");

    echo"<script>window.location.href = '?menu=ApiKeys';</script>";
    exit;
}

/* ================= DELETE KEY ================= */
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    
    $stmt = $pdo->prepare("DELETE FROM api_keys WHERE id = ?");
    $stmt->execute([$id]);
    
    echo"<script>window.location.href = '?menu=ApiKeys';</script>";
    exit;
}

/* ================= LIST API KEYS ================= */
$keys = $pdo->query("
    SELECT *
    FROM api_keys
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Hitung statistik
$totalKeys = count($keys);
$activeKeys = count(array_filter($keys, fn($k) => $k['status'] === 'active'));
$inactiveKeys = $totalKeys - $activeKeys;
$adminKeys = count(array_filter($keys, fn($k) => $k['type'] === 'admin'));
$guestKeys = $totalKeys - $adminKeys;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EyeSec Admin | API Keys Management</title>
    <link rel="icon" type="image/png" href="https://eyesecs.site/assets/icons/logo-eyesec.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #00E5FF 0%, #7C4DFF 100%);
            --secondary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --dark-bg: #0A0E17;
            --card-bg: rgba(26, 34, 56, 0.95);
            --sidebar-bg: rgba(20, 25, 40, 0.95);
            --accent-blue: #00E5FF;
            --accent-purple: #7C4DFF;
            --text-primary: #FFFFFF;
            --text-secondary: #94A3B8;
            --text-muted: #64748B;
            --border-color: rgba(45, 55, 72, 0.6);
            --success: #00E676;
            --error: #FF5252;
            --warning: #FFB74D;
            --info: #2196F3;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--dark-bg);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            opacity: 0;
            animation: pageFadeIn 0.8s ease forwards;
        }

        @keyframes pageFadeIn {
            to { opacity: 1; }
        }


        .logo-section {
            padding: 0 25px 30px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 30px;
            opacity: 0;
            animation: logoFadeIn 0.8s ease 0.4s forwards;
        }

        @keyframes logoFadeIn {
            to { opacity: 1; }
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .logo-img {
            width: 50px;
            height: 50px;
            background: var(--primary-gradient);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 20px rgba(0, 229, 255, 0.3);
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-5px) rotate(2deg); }
        }

        .logo-img img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
        }

        .logo-text {
            display: flex;
            flex-direction: column;
        }

        .logo-text .brand {
            font-size: 24px;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo-text .tagline {
            font-size: 12px;
            color: var(--text-secondary);
            letter-spacing: 0.5px;
        }

        @keyframes navItemSlide {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }


        @keyframes activePulse {
            0%, 100% { box-shadow: 0 5px 15px rgba(0, 229, 255, 0.4); }
            50% { box-shadow: 0 5px 25px rgba(0, 229, 255, 0.6); }
        }

        .logout-btn {
            margin-top: auto;
            margin-top: 30px;
            padding: 0 20px;
            opacity: 0;
            animation: fadeInUp 0.8s ease 1.2s forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes contentFadeIn {
            to { opacity: 1; }
        }

        /* Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 25px;
            border-bottom: 1px solid var(--border-color);
            position: relative;
        }

        .page-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-gradient);
            animation: headerLineExpand 1.2s ease 0.8s forwards;
        }

        @keyframes headerLineExpand {
            to { width: 100%; }
        }

        .header-title h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--accent-blue) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            opacity: 0;
            transform: translateY(20px);
            animation: titleSlideIn 0.8s ease 0.9s forwards;
        }

        @keyframes titleSlideIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header-title p {
            color: var(--text-secondary);
            font-size: 15px;
            opacity: 0;
            animation: subtitleFadeIn 0.8s ease 1.0s forwards;
        }

        @keyframes subtitleFadeIn {
            to { opacity: 1; }
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            opacity: 0;
            transform: translateY(20px);
            animation: profileSlideIn 0.8s ease 1.1s forwards;
        }

        @keyframes profileSlideIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            animation: avatarFloat 4s ease-in-out infinite;
        }

        @keyframes avatarFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
        }

        .user-role {
            font-size: 12px;
            color: var(--text-secondary);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 25px;
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(30px);
        }

        .stat-card:nth-child(1) { animation: cardSlideUp 0.8s ease 1.0s forwards; }
        .stat-card:nth-child(2) { animation: cardSlideUp 0.8s ease 1.1s forwards; }
        .stat-card:nth-child(3) { animation: cardSlideUp 0.8s ease 1.2s forwards; }
        .stat-card:nth-child(4) { animation: cardSlideUp 0.8s ease 1.3s forwards; }

        @keyframes cardSlideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--accent-blue);
            box-shadow: 0 15px 35px rgba(0, 229, 255, 0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: rgba(0, 229, 255, 0.1);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: var(--accent-blue);
            font-size: 24px;
            transition: all 0.4s ease;
        }

        .stat-card:hover .stat-icon {
            animation: iconBounce 0.6s ease;
        }

        @keyframes iconBounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        .stat-content h3 {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-content .value {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        /* Create API Key Form */
        .create-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--border-color);
            margin-bottom: 30px;
            opacity: 0;
            transform: translateY(30px);
            animation: tableSlideUp 0.8s ease 1.4s forwards;
        }

        @keyframes tableSlideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .create-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
        }

        .create-header i {
            color: var(--accent-blue);
            font-size: 24px;
            animation: iconPulse 2s ease-in-out infinite;
        }

        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .create-header h3 {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: rgba(36, 46, 66, 0.8);
            border: 2px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(0, 229, 255, 0.15);
            background: rgba(36, 46, 66, 0.95);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:active::after {
            width: 300px;
            height: 300px;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: #000;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 229, 255, 0.3);
        }

        .form-note {
            margin-top: 20px;
            padding: 15px;
            background: rgba(255, 183, 77, 0.1);
            border: 1px solid rgba(255, 183, 77, 0.3);
            border-radius: 10px;
            color: var(--warning);
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* API Keys Table */
        .table-container {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--border-color);
            overflow-x: auto;
            opacity: 0;
            transform: translateY(30px);
            animation: tableSlideUp 0.8s ease 1.5s forwards;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .table-title {
            font-size: 22px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .table-title i {
            color: var(--accent-blue);
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .data-table thead {
            background: rgba(36, 46, 66, 0.8);
        }

        .data-table th {
            padding: 18px 20px;
            text-align: left;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border-color);
        }

        .data-table tbody tr {
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(10px);
        }

        .data-table tbody tr {
            animation: rowSlideIn 0.6s ease forwards;
        }

        @keyframes rowSlideIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .data-table tbody tr:nth-child(1) { animation-delay: 1.6s; }
        .data-table tbody tr:nth-child(2) { animation-delay: 1.65s; }
        .data-table tbody tr:nth-child(3) { animation-delay: 1.7s; }
        .data-table tbody tr:nth-child(4) { animation-delay: 1.75s; }
        .data-table tbody tr:nth-child(5) { animation-delay: 1.8s; }

        .data-table tbody tr:hover {
            background: rgba(0, 229, 255, 0.05);
        }

        .data-table td {
            padding: 20px;
            color: var(--text-primary);
            vertical-align: middle;
            font-size: 14px;
        }

        /* Badge Styles */
        .badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.3px;
            animation: badgeFloat 3s ease-in-out infinite;
        }

        @keyframes badgeFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-2px); }
        }

        .badge.active {
            background: rgba(0, 230, 118, 0.15);
            color: var(--success);
            border: 1px solid rgba(0, 230, 118, 0.3);
        }

        .badge.inactive {
            background: rgba(255, 82, 82, 0.15);
            color: var(--error);
            border: 1px solid rgba(255, 82, 82, 0.3);
        }

        .badge.admin {
            background: rgba(0, 229, 255, 0.15);
            color: var(--accent-blue);
            border: 1px solid rgba(0, 229, 255, 0.3);
        }

        .badge.guest {
            background: rgba(148, 163, 184, 0.15);
            color: var(--text-secondary);
            border: 1px solid rgba(148, 163, 184, 0.3);
        }

        .badge.unlimited {
            background: rgba(124, 77, 255, 0.15);
            color: var(--accent-purple);
            border: 1px solid rgba(124, 77, 255, 0.3);
        }

        /* API Key Display */
        .api-key-display {
            font-family: monospace;
            background: rgba(36, 46, 66, 0.5);
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            letter-spacing: 1px;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            text-decoration: none;
        }

        .btn-toggle {
            background: rgba(255, 183, 77, 0.1);
            color: var(--warning);
            border: 1px solid rgba(255, 183, 77, 0.2);
        }

        .btn-toggle:hover {
            background: rgba(255, 183, 77, 0.2);
            transform: scale(1.1);
            animation: shake 0.5s ease;
        }

        .btn-delete {
            background: rgba(255, 82, 82, 0.1);
            color: var(--error);
            border: 1px solid rgba(255, 82, 82, 0.2);
        }

        .btn-delete:hover {
            background: rgba(255, 82, 82, 0.2);
            transform: scale(1.1);
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0) scale(1.1); }
            25% { transform: translateX(-2px) scale(1.1); }
            75% { transform: translateX(2px) scale(1.1); }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: var(--text-muted);
        }

        .empty-state h4 {
            font-size: 20px;
            margin-bottom: 10px;
            color: var(--text-primary);
        }

        /* Modal untuk konfirmasi delete */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 14, 23, 0);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            transition: background 0.4s ease;
        }

        .modal.active {
            display: flex;
            background: rgba(10, 14, 23, 0.95);
            backdrop-filter: blur(10px);
        }

        .modal-content {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
            border: 1px solid var(--border-color);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            transform: scale(0.9) translateY(20px);
            opacity: 0;
            transition: all 0.4s ease;
        }

        .modal.active .modal-content {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

        .modal-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 82, 82, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--error);
            font-size: 36px;
            border: 2px solid rgba(255, 82, 82, 0.3);
            animation: modalIconShake 0.5s ease;
        }

        @keyframes modalIconShake {
            0%, 100% { transform: rotate(0); }
            25% { transform: rotate(-10deg); }
            75% { transform: rotate(10deg); }
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .modal-actions .btn {
            flex: 1;
            justify-content: center;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .main-content {
                margin-left: 80px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
                animation: none;
                opacity: 1;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
            
            /* Reset animasi di mobile */
            .stat-card,
            .data-table tbody tr,
            .table-container,
            .create-card,
            .header-title h1,
            .header-title p,
            .user-profile,
            .logo-section {
                animation: none !important;
                opacity: 1 !important;
                transform: none !important;
            }
            
            .badge,
            .stat-icon,
            .logo-img,
            .user-avatar,
            .create-header i,
            .table-title i {
                animation: none !important;
            }
        }

        /* Animasi scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(36, 46, 66, 0.5);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-gradient);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        ::-webkit-scrollbar-thumb:hover {
            animation: scrollbarThumbPulse 1s ease-in-out infinite;
        }

        @keyframes scrollbarThumbPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: var(--accent-blue);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
        <!-- Header -->
        <header class="page-header">
            <div class="header-title">
                <h1>API Keys Management</h1>
                <p>Manage and monitor API access keys for external integrations</p>
            </div>
            <div class="user-profile">
                <div class="user-avatar">A</div>
                <div class="user-info">
                    <span class="user-name">Administrator</span>
                    <span class="user-role">API Manager</span>
                </div>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-key"></i>
                </div>
                <div class="stat-content">
                    <h3>Total API Keys</h3>
                    <div class="value"><?php echo $totalKeys; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>Active Keys</h3>
                    <div class="value" style="color: var(--success);"><?php echo $activeKeys; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-pause-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>Inactive Keys</h3>
                    <div class="value" style="color: var(--error);"><?php echo $inactiveKeys; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-content">
                    <h3>Admin Keys</h3>
                    <div class="value" style="color: var(--accent-blue);"><?php echo $adminKeys; ?></div>
                </div>
            </div>
        </div>

        <!-- Create API Key Form -->
        <div class="create-card">
            <div class="create-header">
                <i class="fas fa-plus-circle"></i>
                <h3>Generate New API Key</h3>
            </div>
            
            <form method="post">
                <input type="hidden" name="create" value="1">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Owner Name</label>
                        <input type="text" name="owner" class="form-control" 
                               placeholder="Enter owner name" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Key Type</label>
                        <select name="type" class="form-control" required>
                            <option value="guest">Guest (Limited Access)</option>
                            <option value="admin">Admin (Full Access)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Rate Limit (per hour)</label>
                        <input type="number" name="rate_limit" class="form-control" 
                               value="10" min="1" max="1000" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-key"></i>
                    Generate API Key
                </button>
                
                <div class="form-note">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>API key will be displayed only once after generation. Make sure to copy and save it securely.</span>
                </div>
            </form>
        </div>

        <!-- API Keys List -->
        <div class="table-container">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-list"></i>
                    <h3>API Keys List</h3>
                </div>
                <div class="table-info">
                    <span style="color: var(--text-secondary); font-size: 14px;">
                        Showing <?php echo count($keys); ?> keys
                    </span>
                </div>
            </div>

            <?php if (empty($keys)): ?>
                <div class="empty-state">
                    <i class="fas fa-key"></i>
                    <h4>No API Keys Found</h4>
                    <p>Generate your first API key to get started</p>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <!-- <th>ID</th> -->
                            <th>API Key</th>
                            <th>Owner</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Rate Limit</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($keys as $k): ?>
                            <tr>
                                <!-- <td>#<?php echo $k['id']; ?></td> -->
                                <td>
                                    <div class="api-key-display">
                                        <?php 
                                        $fullKey = $k['api_key'];
                                        $maskedKey = substr($fullKey, 0, 6) . '••••••' . substr($fullKey, -4);
                                        echo $maskedKey;
                                        ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($k['owner'] ?: 'Unnamed'); ?></td>
                                <td>
                                    <span class="badge <?php echo $k['type']; ?>">
                                        <?php echo strtoupper($k['type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $k['status']; ?>">
                                        <?php echo strtoupper($k['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($k['type'] === 'admin'): ?>
                                        <span class="badge unlimited">UNLIMITED</span>
                                    <?php else: ?>
                                        <?php echo $k['rate_limit']; ?>/hour
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo date("M d, Y", strtotime($k['created_at'])); ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?menu=ApiKeys&toggle=<?php echo $k['id']; ?>" 
                                           class="btn-icon btn-toggle"
                                           title="<?php echo $k['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>">
                                            <i class="fas fa-power-off"></i>
                                        </a>
                                        <a href="#" 
                                           onclick="confirmDelete(<?php echo $k['id']; ?>, '<?php echo htmlspecialchars($k['owner'] ?: 'Key #' . $k['id']); ?>')"
                                           class="btn-icon btn-delete"
                                           title="Delete Key">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3>Delete API Key</h3>
                <p>Are you sure you want to delete this API key? This action cannot be undone.</p>
            </div>
            <div class="modal-body">
                <div class="data-preview" id="dataPreview"></div>
            </div>
            <div class="modal-actions">
                <button class="btn" onclick="closeModal()">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                    Delete Permanently
                </a>
            </div>
        </div>
    </div>

    <script>
        // Confirm delete function
        function confirmDelete(id, owner) {
            const modal = document.getElementById('deleteModal');
            const dataPreview = document.getElementById('dataPreview');
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            
            dataPreview.textContent = `Key Owner: ${owner}`;
            confirmBtn.href = `?menu=ApiKeys&delete=${id}`;
            
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);
        }

        // Close modal function
        function closeModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.remove('active');
            
            setTimeout(() => {
                modal.style.display = 'none';
            }, 400);
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Close modal on background click
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Form submission feedback
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<div class="loading"></div> Processing...';
                    submitBtn.disabled = true;
                    
                    // Re-enable button after 3 seconds if form doesn't submit
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 3000);
                }
            });
        });

        // Copy API key on click (placeholder for future enhancement)
        const apiKeyDisplays = document.querySelectorAll('.api-key-display');
        apiKeyDisplays.forEach(display => {
            display.addEventListener('click', function() {
                // This would copy the full key if we had access to it
                const originalText = this.textContent;
                this.textContent = 'Copied!';
                this.style.color = 'var(--success)';
                
                setTimeout(() => {
                    this.textContent = originalText;
                    this.style.color = '';
                }, 2000);
            });
        });

        // Hover effects for table rows
        const tableRows = document.querySelectorAll('.data-table tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(5px)';
                this.style.transition = 'transform 0.3s ease';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
            });
        });
    </script>
</body>
</html>