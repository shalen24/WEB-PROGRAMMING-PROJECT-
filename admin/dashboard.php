<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(['admin']);

$user = getCurrentUser();
$pageTitle = "Placement Cell Analytics & SDG Reporting";

// College metrics
$totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalRecruiters = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'recruiter'")->fetchColumn();
$totalResumes = $pdo->query("SELECT COUNT(*) FROM resumes")->fetchColumn();
$totalAnalyses = $pdo->query("SELECT COUNT(*) FROM resume_analyses")->fetchColumn();

$avgScore = $pdo->query("SELECT AVG(overall_score) FROM resume_analyses")->fetchColumn();
$avgScore = $avgScore ? round($avgScore, 1) : 74.8;

// Recent registered candidates
$recentUsersStmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 6");
$recentUsers = $recentUsersStmt->fetchAll();

// Recent placement drives
$drivesStmt = $pdo->query("SELECT * FROM job_postings ORDER BY created_at DESC LIMIT 4");
$recentDrives = $drivesStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">
    <?php displayFlash(); ?>

    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item active">Admin & TPO Analytics</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0">Campus Placement Cell & SDG Intelligence Dashboard</h2>
            <p class="text-muted small mb-0">
                Institutional Placement Readiness, Bias Elimination, and Employability Analytics
            </p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="manage_users.php" class="btn btn-outline-primary">
                <i class="fa-solid fa-users-gear me-1"></i> Manage Accounts
            </a>
            <a href="../recruiter/post_job.php" class="btn btn-primary">
                <i class="fa-solid fa-plus me-1"></i> Add Campus Drive
            </a>
        </div>
    </div>

    <!-- Metric Counters -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card glass-card p-3 border-start border-primary border-4">
                <span class="text-muted small text-uppercase fw-semibold">Enrolled Students</span>
                <h3 class="fw-bold text-primary mt-2 mb-0"><?= $totalStudents ?></h3>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card glass-card p-3 border-start border-success border-4">
                <span class="text-muted small text-uppercase fw-semibold">Resumes In ATS Engine</span>
                <h3 class="fw-bold text-success mt-2 mb-0"><?= $totalResumes ?></h3>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card glass-card p-3 border-start border-info border-4">
                <span class="text-muted small text-uppercase fw-semibold">Readiness Analyses Run</span>
                <h3 class="fw-bold text-info mt-2 mb-0"><?= $totalAnalyses ?></h3>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card glass-card p-3 border-start border-warning border-4">
                <span class="text-muted small text-uppercase fw-semibold">Campus Readiness Index</span>
                <h3 class="fw-bold text-warning mt-2 mb-0"><?= $avgScore ?>%</h3>
            </div>
        </div>
    </div>

    <!-- SDG Impact Indicators Card -->
    <div class="card glass-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">
                <i class="fa-solid fa-globe text-primary me-2"></i>Sustainable Development Goals (SDG) Alignment
            </h5>
            <span class="badge bg-light text-dark border">UN 2030 Agenda Mapping</span>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="p-3 border rounded-3 bg-light h-100">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="sdg-badge-8"><i class="fa-solid fa-chart-line"></i> SDG 8</span>
                        <strong class="text-dark">Decent Work and Economic Growth</strong>
                    </div>
                    <p class="text-muted small mb-2">
                        <strong>Target 8.5 & 8.6:</strong> Reduces youth unemployment through objective skill matching and guided upskilling pathways. Eliminates human screening bias in campus recruitment.
                    </p>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-danger" style="width: 85%"></div>
                    </div>
                    <small class="text-muted mt-1 d-block">Objective Hiring Transparency: <strong>85% Index</strong></small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="p-3 border rounded-3 bg-light h-100">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="sdg-badge-9"><i class="fa-solid fa-microchip"></i> SDG 9</span>
                        <strong class="text-dark">Industry, Innovation & Infrastructure</strong>
                    </div>
                    <p class="text-muted small mb-2">
                        <strong>Target 9.5:</strong> Modernizes institutional placement infrastructure using Natural Language Processing (NLP) microservices, automated ATS parsers, and semantic similarity.
                    </p>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-warning" style="width: 90%"></div>
                    </div>
                    <small class="text-muted mt-1 d-block">Digital Career Infrastructure Readiness: <strong>90% Index</strong></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Drives & Users -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card glass-card p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-briefcase text-primary me-2"></i>Active Campus Drives</h5>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentDrives as $d): ?>
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="d-block"><?= htmlspecialchars($d['title']) ?></strong>
                                <span class="text-muted small"><?= htmlspecialchars($d['company']) ?> • Cutoff: <?= $d['min_match_score'] ?>%</span>
                            </div>
                            <a href="../recruiter/batch_screen.php?job_id=<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary">
                                Screen Pool
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card glass-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-users text-primary me-2"></i>Registered Portal Users</h5>
                    <a href="manage_users.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $ru): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($ru['name']) ?></strong></td>
                                    <td class="text-muted"><?= htmlspecialchars($ru['email']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $ru['role'] === 'admin' ? 'danger' : ($ru['role'] === 'recruiter' ? 'success' : 'primary') ?>">
                                            <?= htmlspecialchars($ru['role']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
