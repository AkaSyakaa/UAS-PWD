<?php
/**
 * TASKFLOW - Main Page (CLEAN VERSION)
 * UAS Pemrograman Web Dasar
 * NIM: A12.2024.07226 | Nama: Syahril Kanu Alyyu
 */

require_once 'config/database.php';

// Get filter and search parameters
$filter = isset($_GET['filter']) ? sanitizeInput($_GET['filter']) : 'all';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? sanitizeInput($_GET['category']) : 'all';

// Build SQL query with filters
$sql = "SELECT * FROM tasks WHERE 1=1";

// Apply status filter
if ($filter === 'completed') {
    $sql .= " AND completed = TRUE";
} elseif ($filter === 'pending') {
    $sql .= " AND completed = FALSE";
} elseif ($filter === 'overdue') {
    $sql .= " AND deadline < CURDATE() AND completed = FALSE";
}

// Apply category filter
if ($category_filter !== 'all' && in_array($category_filter, ['kerja', 'kuliah', 'pribadi', 'belanja', 'lainnya'])) {
    $sql .= " AND category = '$category_filter'";
}

// Apply search
if (!empty($search)) {
    $sql .= " AND (title LIKE '%$search%' OR description LIKE '%$search%')";
}

// Order by
$sql .= " ORDER BY 
    CASE 
        WHEN deadline < CURDATE() AND completed = FALSE THEN 0
        WHEN deadline = CURDATE() AND completed = FALSE THEN 1
        ELSE 2 
    END,
    deadline ASC,
    CASE priority 
        WHEN 'high' THEN 1
        WHEN 'medium' THEN 2
        WHEN 'low' THEN 3
    END,
    created_at DESC";

// Execute query
$result = executeQuery($sql);
$tasks = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
}

// Get statistics
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN completed = TRUE THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN completed = FALSE THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN deadline < CURDATE() AND completed = FALSE THEN 1 ELSE 0 END) as overdue,
    SUM(CASE WHEN deadline = CURDATE() AND completed = FALSE THEN 1 ELSE 0 END) as due_today
FROM tasks";

$stats_result = executeQuery($stats_sql);
$stats = $stats_result->fetch_assoc();

// Get category counts
$category_sql = "SELECT category, COUNT(*) as count FROM tasks GROUP BY category";
$category_result = executeQuery($category_sql);
$category_counts = [];
if ($category_result) {
    while ($row = $category_result->fetch_assoc()) {
        $category_counts[$row['category']] = $row['count'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Aplikasi Todo List</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1><i class="fas fa-tasks"></i> TaskFlow</h1>
                <p class="subtitle">Sistem Pencatatan Tugas - UAS Pemrograman Web Dasar</p>
                <div class="db-status">
                    <span class="db-indicator active"></span>
                    MySQL Connected
                </div>
            </div>
        </header>

        <!-- Statistics Dashboard -->
        <div class="dashboard">
            <div class="stats-grid">
                <div class="stat-card total">
                    <div class="stat-icon">
                        <i class="fas fa-list-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total'] ?? 0; ?></h3>
                        <p>Total Tugas</p>
                    </div>
                </div>
                
                <div class="stat-card pending">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['pending'] ?? 0; ?></h3>
                        <p>Belum Selesai</p>
                    </div>
                </div>
                
                <div class="stat-card completed">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['completed'] ?? 0; ?></h3>
                        <p>Selesai</p>
                    </div>
                </div>
                
                <div class="stat-card overdue">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['overdue'] ?? 0; ?></h3>
                        <p>Terlambat</p>
                    </div>
                </div>
                
                <div class="stat-card today">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['due_today'] ?? 0; ?></h3>
                        <p>Deadline Hari Ini</p>
                    </div>
                </div>
                <!-- HAPUS: Database card (Records in DB) -->
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Left Sidebar - Add Task Form -->
            <div class="sidebar">
                <div class="form-container">
                    <h2><i class="fas fa-plus-circle"></i> Tambah Tugas Baru</h2>
                    
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert success">
                            <i class="fas fa-check-circle"></i>
                            <?php
                            $messages = [
                                'added' => 'Tugas berhasil ditambahkan!',
                                'updated' => 'Status tugas diperbarui!',
                                'deleted' => 'Tugas dihapus!'
                            ];
                            echo $messages[$_GET['success']] ?? 'Operasi berhasil!';
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php
                            $errors = [
                                'required' => 'Judul dan deadline wajib diisi!',
                                'database' => 'Terjadi kesalahan database!'
                            ];
                            echo $errors[$_GET['error']] ?? 'Terjadi kesalahan!';
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="add_task.php" method="POST" id="taskForm">
                        <div class="form-group">
                            <label for="title"><i class="fas fa-heading"></i> Judul Tugas *</label>
                            <input type="text" id="title" name="title" required 
                                   placeholder="Masukkan judul tugas...">
                        </div>
                        
                        <div class="form-group">
                            <label for="description"><i class="fas fa-align-left"></i> Deskripsi</label>
                            <textarea id="description" name="description" rows="3" 
                                      placeholder="Deskripsi detail tugas..."></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="category"><i class="fas fa-tag"></i> Kategori</label>
                                <select id="category" name="category">
                                    <option value="kuliah">📚 Kuliah</option>
                                    <option value="kerja">💼 Kerja</option>
                                    <option value="pribadi">👤 Pribadi</option>
                                    <option value="belanja">🛒 Belanja</option>
                                    <option value="lainnya">📌 Lainnya</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="priority"><i class="fas fa-flag"></i> Prioritas</label>
                                <select id="priority" name="priority">
                                    <option value="low">🟢 Rendah</option>
                                    <option value="medium" selected>🟡 Sedang</option>
                                    <option value="high">🔴 Tinggi</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="deadline"><i class="fas fa-calendar-alt"></i> Deadline *</label>
                            <input type="date" id="deadline" name="deadline" required 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-plus"></i> Tambah Tugas
                        </button>
                    </form>
                </div>
                
                <!-- Category Statistics -->
                <div class="category-stats">
                    <h3><i class="fas fa-chart-pie"></i> Statistik Kategori</h3>
                    <div class="category-list">
                        <?php foreach ($category_counts as $category => $count): 
                            $category_names = [
                                'kerja' => '💼 Kerja',
                                'kuliah' => '📚 Kuliah', 
                                'pribadi' => '👤 Pribadi',
                                'belanja' => '🛒 Belanja',
                                'lainnya' => '📌 Lainnya'
                            ];
                        ?>
                        <div class="category-item">
                            <span class="category-name"><?php echo $category_names[$category] ?? ucfirst($category); ?></span>
                            <span class="category-count"><?php echo $count; ?> tugas</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Right Content - Tasks List -->
            <div class="content">
                <!-- Filter Controls -->
                <div class="filter-controls">
                    <div class="filter-group">
                        <h3><i class="fas fa-filter"></i> Filter</h3>
                        <div class="filter-buttons">
                            <a href="?filter=all&category=<?php echo $category_filter; ?>" 
                               class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
                                Semua
                            </a>
                            <a href="?filter=pending&category=<?php echo $category_filter; ?>" 
                               class="filter-btn <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                                Belum Selesai
                            </a>
                            <a href="?filter=completed&category=<?php echo $category_filter; ?>" 
                               class="filter-btn <?php echo $filter === 'completed' ? 'active' : ''; ?>">
                                Selesai
                            </a>
                            <a href="?filter=overdue&category=<?php echo $category_filter; ?>" 
                               class="filter-btn <?php echo $filter === 'overdue' ? 'active' : ''; ?>">
                                Terlambat
                            </a>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <h3><i class="fas fa-tags"></i> Kategori</h3>
                        <div class="category-filters">
                            <a href="?filter=<?php echo $filter; ?>&category=all" 
                               class="category-filter <?php echo $category_filter === 'all' ? 'active' : ''; ?>">
                                Semua Kategori
                            </a>
                            <?php 
                            $categories = [
                                'kuliah' => '📚 Kuliah',
                                'kerja' => '💼 Kerja',
                                'pribadi' => '👤 Pribadi',
                                'belanja' => '🛒 Belanja'
                            ];
                            foreach ($categories as $key => $name): 
                            ?>
                            <a href="?filter=<?php echo $filter; ?>&category=<?php echo $key; ?>" 
                               class="category-filter <?php echo $category_filter === $key ? 'active' : ''; ?>">
                                <?php echo $name; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="search-box">
                        <form method="GET">
                            <input type="hidden" name="filter" value="<?php echo $filter; ?>">
                            <input type="hidden" name="category" value="<?php echo $category_filter; ?>">
                            <input type="text" name="search" placeholder="Cari tugas..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                            <?php if (!empty($search)): ?>
                                <a href="?filter=<?php echo $filter; ?>&category=<?php echo $category_filter; ?>" 
                                   class="clear-search" title="Hapus pencarian">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Tasks List -->
                <div class="tasks-header">
                    <h2><i class="fas fa-tasks"></i> Daftar Tugas 
                        <span class="tasks-count">(<?php echo count($tasks); ?>)</span>
                    </h2>
                    <div class="tasks-actions">
                        <button onclick="exportToCSV()" class="action-btn">
                            <i class="fas fa-download"></i> Ekspor CSV
                        </button>
                        <button onclick="printTasks()" class="action-btn">
                            <i class="fas fa-print"></i> Cetak
                        </button>
                    </div>
                </div>
                
                <?php if (empty($tasks)): ?>
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list"></i>
                        <h3>Tidak ada tugas</h3>
                        <p>
                            <?php if (!empty($search)): ?>
                                Tidak ditemukan tugas dengan kata kunci "<?php echo htmlspecialchars($search); ?>"
                            <?php else: ?>
                                Tambahkan tugas baru menggunakan form di sebelah kiri
                            <?php endif; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="tasks-container">
                        <?php foreach ($tasks as $task): 
                            $isOverdue = !$task['completed'] && $task['deadline'] < date('Y-m-d');
                            $isToday = $task['deadline'] == date('Y-m-d');
                            $daysLeft = floor((strtotime($task['deadline']) - time()) / (60 * 60 * 24));
                        ?>
                        <div class="task-card 
                            <?php echo $task['completed'] ? 'completed' : ''; ?>
                            <?php echo $isOverdue ? 'overdue' : ''; ?>
                            <?php echo $isToday ? 'today' : ''; ?>
                            priority-<?php echo $task['priority']; ?>">
                            
                            <!-- Task Status Toggle -->
                            <div class="task-status">
                                <form action="update_status.php" method="POST" class="status-form">
                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                    <input type="hidden" name="completed" value="<?php echo $task['completed'] ? '0' : '1'; ?>">
                                    <button type="submit" class="status-toggle">
                                        <?php if ($task['completed']): ?>
                                            <div class="toggle-circle checked">
                                                <i class="fas fa-check"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="toggle-circle"></div>
                                        <?php endif; ?>
                                    </button>
                                </form>
                            </div>
                            
                            <!-- Task Content -->
                            <div class="task-content">
                                <div class="task-header">
                                    <h3 class="task-title"><?php echo htmlspecialchars($task['title']); ?></h3>
                                    <div class="task-meta">
                                        <span class="task-category category-<?php echo $task['category']; ?>">
                                            <i class="fas fa-tag"></i>
                                            <?php 
                                                $categoryNames = [
                                                    'kerja' => 'Kerja',
                                                    'kuliah' => 'Kuliah',
                                                    'pribadi' => 'Pribadi',
                                                    'belanja' => 'Belanja',
                                                    'lainnya' => 'Lainnya'
                                                ];
                                                echo $categoryNames[$task['category']];
                                            ?>
                                        </span>
                                        
                                        <span class="task-priority priority-<?php echo $task['priority']; ?>">
                                            <i class="fas fa-flag"></i>
                                            <?php 
                                                $priorityNames = [
                                                    'low' => 'Rendah',
                                                    'medium' => 'Sedang',
                                                    'high' => 'Tinggi'
                                                ];
                                                echo $priorityNames[$task['priority']];
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <?php if (!empty($task['description'])): ?>
                                <div class="task-description">
                                    <p><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <div class="task-footer">
                                    <div class="task-deadline">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span class="deadline-text <?php echo $isOverdue ? 'overdue-text' : ''; ?>">
                                            <?php 
                                                $deadlineFormatted = date('d M Y', strtotime($task['deadline']));
                                                if ($task['completed']) {
                                                    echo "<s>$deadlineFormatted</s>";
                                                } elseif ($isToday) {
                                                    echo "<strong>Hari ini</strong>";
                                                } elseif ($isOverdue) {
                                                    echo "Terlambat: $deadlineFormatted";
                                                } else {
                                                    echo "$deadlineFormatted";
                                                }
                                                
                                                if (!$task['completed'] && !$isOverdue && $daysLeft >= 0) {
                                                    echo " <span class='days-left'>($daysLeft hari lagi)</span>";
                                                }
                                            ?>
                                        </span>
                                    </div>
                                    
                                    <div class="task-actions">
                                        <span class="task-date">
                                            <i class="far fa-clock"></i>
                                            <?php echo date('d M Y', strtotime($task['created_at'])); ?>
                                        </span>
                                        
                                        <form action="delete_task.php" method="POST" 
                                              onsubmit="return confirm('Hapus tugas ini?');">
                                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                            <button type="submit" class="delete-btn" title="Hapus Tugas">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer - DIUPDATE (Hapus bagian Teknologi yang Digunakan) -->
        <footer class="footer">
            <div class="footer-content">
                <div class="footer-info">
                    <h4><i class="fas fa-graduation-cap"></i> UAS Pemrograman Web Dasar</h4>
                    <p>NIM: A12.2024.07226 | Nama: Syahril Kanu Alyyu | Jurusan: Sistem Informasi</p>
                    <p>Dosen: Lalang Erawan, M.Kom | Universitas Dian Nuswantoro</p>
                </div>
                
                <!-- HAPUS: Bagian Teknologi yang Digunakan -->
                <!-- <div class="footer-tech"> ... </div> -->
                
                <div class="footer-links">
                    <h4><i class="fas fa-link"></i> Links</h4>
                     <a href="https://github.com/akasyakaa" target="_blank">
        <i class="fab fa-github"></i> Repository GitHub
    </a>
                    <a href="#" onclick="showDatabaseInfo()">
                        <i class="fas fa-info-circle"></i> Info Database
                    </a>
                    <a href="#" onclick="exportToCSV()">
                        <i class="fas fa-file-export"></i> Ekspor Data
                    </a>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>© 2026 TaskFlow - Aplikasi Todo List. Dibuat untuk tujuan pendidikan.</p>
                <p class="timestamp">Halaman dimuat pada: <?php echo date('d F Y H:i:s'); ?></p>
            </div>
        </footer>
    </div>

    <!-- Database Info Modal -->
    <div id="dbModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><i class="fas fa-database"></i> Database Information</h2>
            <div class="db-info-content">
                <div class="db-stat">
                    <h3>Tables</h3>
                    <p>1 table (tasks)</p>
                </div>
                <div class="db-stat">
                    <h3>Total Records</h3>
                    <p><?php echo $stats['total'] ?? 0; ?> tasks</p>
                </div>
                <div class="db-stat">
                    <h3>Database Size</h3>
                    <p><?php echo round(($stats['total'] ?? 0) * 0.5, 2); ?> KB (estimated)</p>
                </div>
                <div class="db-stat">
                    <h3>Connection</h3>
                    <p class="connected">Connected ✓</p>
                </div>
            </div>
            <div class="sql-example">
                <h3>Contoh Query SQL:</h3>
                <code>SELECT * FROM tasks WHERE completed = FALSE ORDER BY deadline ASC</code>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
<?php
// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>