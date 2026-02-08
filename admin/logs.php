<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
require 'auth.php';
require '../api/config/database.php';

// Konfigurasi pagination
$limit = 50; // Jumlah log per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

/* ================= DELETE LOG ================= */
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM api_logs WHERE id = ?");
        $stmt->execute([$id]);
        
        // Set success message in session
        $_SESSION['success_message'] = "Log berhasil dihapus!";
        
        // Redirect back to logs page
        header("Location: ?menu=logs&page=" . $page);
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Gagal menghapus log: " . $e->getMessage();
        header("Location: ?menu=logs&page=" . $page);
        exit;
    }
}

/* ================= FETCH LOGS ================= */
// Hitung total data
$totalLogs = (int) $pdo->query("SELECT COUNT(*) FROM api_logs")->fetchColumn();
$totalPages = ceil($totalLogs / $limit);

// Query dengan pagination
$stmt = $pdo->prepare("
    SELECT 
        l.id,
        l.client_ip,
        l.method,
        l.endpoint,
        l.user_agent,
        l.created_at,
        k.owner AS api_owner,
        k.type AS api_type,
        u.url
    FROM api_logs l
    LEFT JOIN api_keys k ON l.api_key_id = k.id
    LEFT JOIN urls u ON l.url_id = u.id
    ORDER BY l.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get current menu from URL parameter
$currentMenu = isset($_GET['menu']) ? $_GET['menu'] : 'logs';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EyeSec Admin | API Logs</title>
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
            --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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
        }

        /* Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 1px solid var(--border-color);
            animation: slideInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.4s both;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header-title h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--accent-blue) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-title p {
            color: var(--text-secondary);
            font-size: 15px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
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
        }

        /* Table Container */
        .table-container {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--border-color);
            margin-bottom: 30px;
            overflow-x: auto;
            animation: slideInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.9s both;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 22px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title i {
            color: var(--accent-blue);
        }

        .stats-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 20px;
            border: 1px solid var(--border-color);
            margin-bottom: 25px;
            animation: slideInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.4s both;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 13px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border-color);
            white-space: nowrap;
        }

        .data-table tbody tr {
            border-bottom: 1px solid var(--border-color);
            transition: var(--transition-smooth);
        }

        .data-table tbody tr:hover {
            background: rgba(0, 229, 255, 0.05);
            transform: translateY(-2px);
        }

        .data-table td {
            padding: 15px 20px;
            color: var(--text-primary);
            vertical-align: top;
            font-size: 14px;
        }

        /* Badges */
        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.3px;
            display: inline-block;
            transition: var(--transition-smooth);
        }

        .badge:hover {
            transform: scale(1.05);
        }

        .badge-admin {
            background: rgba(44, 62, 80, 0.2);
            color: #7C4DFF;
            border: 1px solid rgba(124, 77, 255, 0.3);
        }

        .badge-guest {
            background: rgba(52, 152, 219, 0.2);
            color: #00E5FF;
            border: 1px solid rgba(0, 229, 255, 0.3);
        }

        .method-badge {
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .method-get {
            background: rgba(46, 204, 113, 0.15);
            color: var(--success);
            border: 1px solid rgba(46, 204, 113, 0.3);
        }

        .method-post {
            background: rgba(52, 152, 219, 0.15);
            color: #3498db;
            border: 1px solid rgba(52, 152, 219, 0.3);
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            font-family: monospace;
        }

        .status-200 { background: rgba(46, 204, 113, 0.15); color: var(--success); border: 1px solid rgba(46, 204, 113, 0.3); }
        .status-400 { background: rgba(241, 196, 15, 0.15); color: #f1c40f; border: 1px solid rgba(241, 196, 15, 0.3); }
        .status-401 { background: rgba(231, 76, 60, 0.15); color: #e74c3c; border: 1px solid rgba(231, 76, 60, 0.3); }
        .status-403 { background: rgba(231, 76, 60, 0.15); color: #e74c3c; border: 1px solid rgba(231, 76, 60, 0.3); }
        .status-404 { background: rgba(231, 76, 60, 0.15); color: #e74c3c; border: 1px solid rgba(231, 76, 60, 0.3); }
        .status-500 { background: rgba(155, 89, 182, 0.15); color: #9b59b6; border: 1px solid rgba(155, 89, 182, 0.3); }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition-smooth);
            text-decoration: none;
        }

        .btn-danger {
            background: rgba(255, 82, 82, 0.1);
            color: var(--error);
            border: 1px solid rgba(255, 82, 82, 0.2);
        }

        .btn-danger:hover {
            background: rgba(255, 82, 82, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 82, 82, 0.1);
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: #000;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 229, 255, 0.3);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid var(--border-color);
        }

        .pagination-info {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .pagination-buttons {
            display: flex;
            gap: 10px;
        }

        .page-btn {
            padding: 10px 20px;
            background: rgba(36, 46, 66, 0.8);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition-smooth);
        }

        .page-btn:hover:not(:disabled) {
            background: var(--primary-gradient);
            color: #000;
            border-color: transparent;
            transform: translateY(-2px);
        }

        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .page-number {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .current-page {
            background: var(--primary-gradient);
            color: #000;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }

        /* Alerts */
        .alert {
            padding: 16px 24px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: rgba(0, 230, 118, 0.15);
            border: 1px solid rgba(0, 230, 118, 0.3);
            color: var(--success);
        }

        .alert-error {
            background: rgba(255, 82, 82, 0.15);
            border: 1px solid rgba(255, 82, 82, 0.3);
            color: var(--error);
        }

        /* Truncate Text */
        .truncate {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-agent {
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            cursor: help;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 14, 23, 0.95);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal.active {
            display: flex;
            opacity: 1;
            animation: modalFadeIn 0.3s ease forwards;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .modal-content {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
            border: 1px solid var(--border-color);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }

        .modal.active .modal-content {
            transform: scale(1);
            animation: modalScaleIn 0.3s ease;
        }

        @keyframes modalScaleIn {
            from {
                transform: scale(0.9);
            }
            to {
                transform: scale(1);
            }
        }

        .modal-header {
            text-align: center;
            margin-bottom: 25px;
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
        }

        .modal-header h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--text-primary);
        }

        .modal-header p {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .modal-body {
            background: rgba(36, 46, 66, 0.5);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .data-preview {
            font-family: monospace;
            color: var(--text-primary);
            word-break: break-all;
            font-size: 14px;
            padding: 10px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            border-left: 3px solid var(--error);
        }

        .modal-actions {
            display: flex;
            gap: 15px;
        }

        .modal-actions .btn {
            flex: 1;
            justify-content: center;
        }

        /* Loading State */
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

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: var(--text-primary);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .section-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            .data-table {
                display: block;
                overflow-x: auto;
            }
            .pagination {
                flex-direction: column;
                gap: 15px;
            }
            .modal-content {
                padding: 20px;
            }
            .modal-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

        <!-- Header -->
        <header class="page-header">
            <div class="header-title">
                <h1>API Logs Monitor</h1>
                <p>Monitor and analyze all API requests in real-time</p>
            </div>
            <div class="header-actions">
                <div class="user-profile">
                    <div class="user-avatar">A</div>
                    <div class="user-info">
                        <span class="user-name">Administrator</span>
                        <span class="user-role">Super Admin</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Alerts -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></span>
            </div>
        <?php endif; ?>

        <!-- Stats Overview -->
        <div class="stats-card">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value"><?php echo number_format($totalLogs); ?></div>
                    <div class="stat-label">Total Logs</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $limit; ?></div>
                    <div class="stat-label">Per Page</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $totalPages; ?></div>
                    <div class="stat-label">Total Pages</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo date('Y-m-d'); ?></div>
                    <div class="stat-label">Today</div>
                </div>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="table-container">
            <div class="section-header">
                <div class="section-title">
                    <i class="fas fa-history"></i>
                    Recent API Activity
                </div>
                <div class="table-actions">
                    <button class="btn btn-primary" onclick="exportLogs()">
                        <i class="fas fa-download"></i>
                        Export Logs
                    </button>
                    <button class="btn btn-danger" onclick="clearOldLogs()">
                        <i class="fas fa-trash"></i>
                        Clear Old Logs
                    </button>
                </div>
            </div>

            <?php if (empty($logs)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No API logs found</h3>
                    <p>Start using the API to see logs appear here</p>
                </div>
            <?php else: ?>
                <table class="data-table" id="logsTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Type</th>
                            <th>URL</th>
                            <th>IP Address</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Endpoint</th>
                            <th>User Agent</th>
                            <th>Timestamp</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $i => $l): ?>
                            <tr>
                                <td><?php echo $offset + $i + 1; ?></td>
                                <td>
                                    <?php if ($l['api_type'] === 'admin'): ?>
                                        <span class="badge badge-admin">ADMIN</span>
                                    <?php else: ?>
                                        <span class="badge badge-guest">GUEST</span>
                                    <?php endif; ?>
                                </td>
                                <td class="truncate" title="<?php echo htmlspecialchars($l['url'] ?? '-'); ?>">
                                    <?php echo htmlspecialchars($l['url'] ?? '-'); ?>
                                </td>
                                <td>
                                    <code><?php echo htmlspecialchars($l['client_ip']); ?></code>
                                </td>
                                <td>
                                    <?php
                                    $methodClass = strtolower($l['method']) === 'get' ? 'method-get' : 'method-post';
                                    ?>
                                    <span class="method-badge <?php echo $methodClass; ?>">
                                        <?php echo htmlspecialchars($l['method']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if (isset($l['status_code'])): 
                                        $statusCode = $l['status_code'];
                                        if ($statusCode >= 200 && $statusCode < 300) {
                                            $statusClass = 'status-200';
                                        } elseif ($statusCode >= 400 && $statusCode < 500) {
                                            $statusClass = 'status-400';
                                        } else {
                                            $statusClass = 'status-500';
                                        }
                                    ?>
                                        <span class="status-badge <?php echo $statusClass; ?>">
                                            <?php echo $statusCode; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-200">200</span>
                                    <?php endif; ?>
                                </td>
                                <td class="truncate" title="<?php echo htmlspecialchars($l['endpoint']); ?>">
                                    <?php echo htmlspecialchars($l['endpoint']); ?>
                                </td>
                                <td class="user-agent" title="<?php echo htmlspecialchars($l['user_agent']); ?>">
                                    <?php echo htmlspecialchars(substr($l['user_agent'], 0, 40)); ?>...
                                </td>
                                <td>
                                    <span title="<?php echo $l['created_at']; ?>">
                                        <?php echo date('M d, H:i', strtotime($l['created_at'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-danger" 
                                                onclick="openDeleteModal(<?php echo $l['id']; ?>, '<?php echo addslashes($l['endpoint']); ?>')"
                                                title="Delete Log">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="pagination">
                    <div class="pagination-info">
                        Showing <?php echo min($limit, count($logs)); ?> of <?php echo number_format($totalLogs); ?> logs
                    </div>
                    <div class="pagination-buttons">
                        <button class="page-btn" 
                                onclick="goToPage(<?php echo $page - 1; ?>)"
                                <?php echo $page <= 1 ? 'disabled' : ''; ?>>
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        
                        <div class="page-number">
                            <span>Page</span>
                            <span class="current-page"><?php echo $page; ?></span>
                            <span>of <?php echo $totalPages; ?></span>
                        </div>
                        
                        <button class="page-btn" 
                                onclick="goToPage(<?php echo $page + 1; ?>)"
                                <?php echo $page >= $totalPages ? 'disabled' : ''; ?>>
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="deleteModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3>Delete Log</h3>
                    <p>Are you sure you want to delete this log? This action cannot be undone.</p>
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
        // Set active menu
        document.addEventListener('DOMContentLoaded', function() {
            const menuItems = document.querySelectorAll('.nav-item');
            const currentPage = window.location.pathname.split('/').pop();
            const urlParams = new URLSearchParams(window.location.search);
            const currentMenu = urlParams.get('menu') || 'logs';
            
            menuItems.forEach(item => {
                item.classList.remove('active');
                const menuType = item.getAttribute('data-menu');
                if (menuType === currentMenu) {
                    item.classList.add('active');
                }
            });
        });

        // Delete modal functionality
        let currentDeleteId = null;
        let currentPageNumber = <?php echo $page; ?>;

        function openDeleteModal(id, endpoint) {
            currentDeleteId = id;
            const modal = document.getElementById('deleteModal');
            const dataPreview = document.getElementById('dataPreview');
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            
            // Set the endpoint preview
            dataPreview.textContent = endpoint;
            
            // Set the delete URL
            confirmBtn.href = `?menu=logs&delete=${id}&page=${currentPageNumber}`;
            
            // Show modal with animation
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.remove('active');
            setTimeout(() => {
                document.body.style.overflow = 'auto';
            }, 300);
        }

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Navigation functions
        function goToPage(page) {
            if (page >= 1 && page <= <?php echo $totalPages; ?>) {
                window.location.href = `?menu=logs&page=${page}`;
            }
        }

        // Export function
        function exportLogs() {
            const btn = event.target.closest('.btn');
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<span class="loading"></span> Exporting...';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = originalContent;
                btn.disabled = false;
                alert('Export functionality would generate a CSV file with all logs.');
            }, 1000);
        }

        // Clear old logs function
        function clearOldLogs() {
            if (confirm('This will delete all logs older than 30 days.\n\nAre you sure you want to continue?')) {
                const btn = event.target.closest('.btn');
                const originalContent = btn.innerHTML;
                btn.innerHTML = '<span class="loading"></span> Cleaning...';
                btn.disabled = true;
                
                setTimeout(() => {
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                    alert('In production, this would delete old logs and refresh the page.');
                }, 1500);
            }
        }

        // Auto-hide alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Table row click for details
        document.querySelectorAll('.data-table tbody tr').forEach(row => {
            row.addEventListener('click', function(e) {
                if (!e.target.closest('.btn')) {
                    const cells = this.querySelectorAll('td');
                    const details = {
                        type: cells[1].textContent.trim(),
                        url: cells[2].title,
                        ip: cells[3].textContent.trim(),
                        method: cells[4].textContent.trim(),
                        status: cells[5].textContent.trim(),
                        endpoint: cells[6].title,
                        userAgent: cells[7].title,
                        timestamp: cells[8].title
                    };
                    
                    alert(`Log Details:\n\nType: ${details.type}\nURL: ${details.url}\nIP: ${details.ip}\nMethod: ${details.method}\nStatus: ${details.status}\nEndpoint: ${details.endpoint}\nTime: ${details.timestamp}\n\nUser Agent:\n${details.userAgent}`);
                }
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + E to export
            if (e.ctrlKey && e.key === 'e') {
                e.preventDefault();
                exportLogs();
            }
        });

        // Add animation to table rows
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.data-table tbody tr');
            rows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.05}s`;
            });
        });
    </script>
</body>
</html>