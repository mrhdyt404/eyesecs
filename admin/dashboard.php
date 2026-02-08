<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
require 'auth.php';
require '../api/config/database.php';

// Konfigurasi pagination
$limit = 20; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

/* ================= STATISTIK ================= */
$totalRequest = (int) $pdo->query("SELECT COUNT(*) FROM api_logs")->fetchColumn();
$totalUrl     = (int) $pdo->query("SELECT COUNT(*) FROM urls")->fetchColumn();
$safeUrl      = (int) $pdo->query("SELECT COUNT(*) FROM urls WHERE is_phishing = 0")->fetchColumn();
$dangerUrl    = (int) $pdo->query("SELECT COUNT(*) FROM urls WHERE is_phishing = 1")->fetchColumn();
$dangerRate = $totalUrl > 0 ? round(($dangerUrl / $totalUrl) * 100, 1) : 0;

/* ================= PAGINATION DATA ================= */
// Hitung total data untuk pagination
$totalData = (int) $pdo->query("SELECT COUNT(*) FROM urls")->fetchColumn();
$totalPages = ceil($totalData / $limit);

// Query data dengan limit dan offset
$stmt = $pdo->prepare("
    SELECT u.id, u.url, u.domain, u.risk_score, u.is_phishing, u.checked_at
    FROM urls u
    ORDER BY u.id DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$recent = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete request
if (isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    $deleteStmt = $pdo->prepare("DELETE FROM urls WHERE id = ?");
    if ($deleteStmt->execute([$deleteId])) {
        $successMessage = "Data berhasil dihapus!";
    } else {
        $errorMessage = "Gagal menghapus data.";
    }
    // Refresh halaman
    header("Location: dashboard.php");
    exit();
}
?>
        <!-- Header -->
        <header class="page-header">
            <div class="header-title">
                <h1>Security Dashboard</h1>
                <p>Monitor and manage all security activities in real-time</p>
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
        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($successMessage); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($errorMessage); ?></span>
            </div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="stat-content">
                    <h3>Total Security Checks</h3>
                    <div class="value"><?php echo number_format($totalUrl); ?></div>
                    <div class="trend positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>Last 24h</span>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>Safe URLs</h3>
                    <div class="value safe"><?php echo number_format($safeUrl); ?></div>
                    <div class="trend positive">
                        <i class="fas fa-arrow-up"></i>
                        <span><?php echo $totalUrl > 0 ? round(($safeUrl / $totalUrl) * 100, 1) : 0; ?>% safe rate</span>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h3>Threats Detected</h3>
                    <div class="value danger"><?php echo number_format($dangerUrl); ?></div>
                    <div class="trend negative">
                        <i class="fas fa-arrow-up"></i>
                        <span><?php echo $dangerRate; ?>% threat rate</span>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="stat-content">
                    <h3>API Requests</h3>
                    <div class="value"><?php echo number_format($totalRequest); ?></div>
                    <div class="trend positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>Real-time monitoring</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Table -->
        <div class="table-container">
            <div class="section-header">
                <button class="btn btn-primary" onclick="exportData()">
                    <i class="fas fa-download"></i>
                    Export Data
                </button>
                <h2 class="section-title">
                    <i class="fas fa-history"></i>
                    Recent Security Scans
                </h2>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>URL</th>
                        <th>Domain</th>
                        <th>Risk Score</th>
                        <th>Status</th>
                        <th>Scan Time</th>
                        <!-- <th>Actions</th> -->
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                                No data available
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent as $r): ?>
                            <tr>
                                <td class="url-cell" title="<?php echo htmlspecialchars($r['url']); ?>">
                                    <?php echo htmlspecialchars(substr($r['url'], 0, 50)); ?>
                                    <?php if (strlen($r['url']) > 50): ?>...<?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($r['domain']); ?></td>
                                <td>
                                    <?php
                                    $riskScore = round($r['risk_score'] * 100);
                                    $riskClass = 'high';
                                    if ($riskScore <= 30) $riskClass = 'low';
                                    elseif ($riskScore <= 70) $riskClass = 'medium';
                                    ?>
                                    <span class="risk-score <?php echo $riskClass; ?>">
                                        <?php echo $riskScore; ?>%
                                    </span>
                                </td>
                                <td>
                                    <?php if ($r['is_phishing']): ?>
                                        <span class="badge badge-danger">THREAT DETECTED</span>
                                    <?php else: ?>
                                        <span class="badge badge-safe">SAFE</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo date("M d, Y H:i", strtotime($r['checked_at'])); ?>
                                </td>
                                <!-- <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon btn-view" 
                                                onclick="viewDetails(<?php echo $r['id']; ?>)"
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-danger" 
                                                onclick="confirmDelete(<?php echo $r['id']; ?>, '<?php echo addslashes($r['url']); ?>')"
                                                title="Delete Record">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </td> -->
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination">
                <div class="pagination-info">
                    Showing <?php echo min($limit, count($recent)); ?> of <?php echo number_format($totalData); ?> records
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
        </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3>Confirm Deletion</h3>
                <p>Are you sure you want to delete this security record? This action cannot be undone.</p>
            </div>
            <div class="modal-body">
                <div class="data-preview" id="dataPreview"></div>
            </div>
            <div class="modal-actions">
                <button class="btn" onclick="closeModal()">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="delete_id" id="deleteId">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i>
                        Delete Permanently
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Navigation functions
        function goToPage(page) {
            if (page >= 1 && page <= <?php echo $totalPages; ?>) {
                window.location.href = `?page=${page}`;
            }
        }

        // Modal functions
        function confirmDelete(id, url) {
            document.getElementById('deleteId').value = id;
            document.getElementById('dataPreview').textContent = url;
            document.getElementById('deleteModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        // View details function
        function viewDetails(id) {
            alert(`Viewing details for record #${id}\nThis would typically open a detailed view modal.`);
            // You can implement AJAX call here to fetch and display detailed information
        }

        // Export function
        function exportData() {
            alert('Export functionality would be implemented here.\nOptions: CSV, Excel, PDF');
        }

        // Bulk delete function
        function deleteSelected() {
            alert('Bulk delete functionality would be implemented here.\nYou would need to add checkboxes to each row first.');
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

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
