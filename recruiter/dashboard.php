<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(['recruiter', 'admin']);

$user = getCurrentUser();
$pageTitle = "Recruiter & Placement Drive Portal";

// Fetch jobs created by recruiter (or all if admin)
if ($user['role'] === 'admin') {
    $jobsStmt = $pdo->query("SELECT * FROM job_postings ORDER BY created_at DESC");
} else {
    $jobsStmt = $pdo->prepare("SELECT * FROM job_postings WHERE recruiter_id = ? ORDER BY created_at DESC");
    $jobsStmt->execute([$user['id']]);
}
$jobs = $jobsStmt->fetchAll();

// Calculate metrics
$totalJobs = count($jobs);
$totalShortlisted = $pdo->query("SELECT COUNT(*) FROM job_shortlists WHERE status = 'shortlisted'")->fetchColumn();
$totalCandidates = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM resumes")->fetchColumn();
$avgScore = $pdo->query("SELECT AVG(overall_score) FROM resume_analyses")->fetchColumn();
$avgScore = $avgScore ? round($avgScore, 1) : 74.5;

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">
    <?php displayFlash(); ?>

    <!-- Welcome Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-bold mb-1">Recruiter & Campus Placement Cell Portal</h2>
            <p class="text-muted mb-0">
                Logged in as: <strong><?= htmlspecialchars($user['name']) ?></strong> (<?= htmlspecialchars($user['role'] === 'admin' ? 'Placement Officer / Admin' : 'Corporate Recruiter') ?>)
            </p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="post_job.php" class="btn btn-primary shadow-sm">
                <i class="fa-solid fa-plus me-1"></i> Post New Placement Drive
            </a>
            <a href="batch_screen.php" class="btn btn-outline-primary">
                <i class="fa-solid fa-users-viewfinder me-1"></i> Batch Screen Resumes
            </a>
        </div>
    </div>

    <!-- Metrics Row -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card glass-card p-3 border-start border-primary border-4">
                <span class="text-muted small text-uppercase fw-semibold">Active Placement Drives</span>
                <h3 class="fw-bold text-primary mt-2 mb-0"><?= $totalJobs ?></h3>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card glass-card p-3 border-start border-success border-4">
                <span class="text-muted small text-uppercase fw-semibold">Candidates In Pool</span>
                <h3 class="fw-bold text-success mt-2 mb-0"><?= $totalCandidates ?></h3>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card glass-card p-3 border-start border-info border-4">
                <span class="text-muted small text-uppercase fw-semibold">Shortlisted for Rounds</span>
                <h3 class="fw-bold text-info mt-2 mb-0"><?= $totalShortlisted ?></h3>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card glass-card p-3 border-start border-warning border-4">
                <span class="text-muted small text-uppercase fw-semibold">Batch Avg ATS Readiness</span>
                <h3 class="fw-bold text-warning mt-2 mb-0"><?= $avgScore ?>%</h3>
            </div>
        </div>
    </div>

    <!-- Active Drives Table -->
    <div class="card glass-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-briefcase text-primary me-2"></i>Campus Placement Drives</h5>
            <a href="post_job.php" class="btn btn-sm btn-outline-primary">+ Add New Drive</a>
        </div>

        <?php if (empty($jobs)): ?>
            <p class="text-muted small py-4 text-center">No drives posted yet. Create your first drive to start receiving and screening candidate resumes.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Job Title & Company</th>
                            <th>Job Type</th>
                            <th>Required Core Skills</th>
                            <th>Cutoff</th>
                            <th>Date Posted</th>
                            <th>Screening & Shortlists</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $j): ?>
                            <tr>
                                <td>
                                    <strong class="fs-6"><?= htmlspecialchars($j['title']) ?></strong><br>
                                    <span class="text-muted"><i class="fa-regular fa-building me-1"></i><?= htmlspecialchars($j['company']) ?> • <?= htmlspecialchars($j['location']) ?></span>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($j['job_type']) ?></span></td>
                                <td>
                                    <?php 
                                        $req = json_decode($j['required_skills'], true) ?: [];
                                        foreach (array_slice($req, 0, 4) as $skill): 
                                    ?>
                                        <span class="badge bg-secondary-subtle text-secondary small me-1"><?= htmlspecialchars($skill) ?></span>
                                    <?php endforeach; ?>
                                </td>
                                <td><span class="badge bg-primary"><?= $j['min_match_score'] ?>%</span></td>
                                <td class="text-muted"><?= date('M d, Y', strtotime($j['created_at'])) ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="batch_screen.php?job_id=<?= $j['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-users-viewfinder me-1"></i> Screen Pool
                                        </a>
                                        <a href="export_shortlist.php?job_id=<?= $j['id'] ?>" class="btn btn-sm btn-outline-success" title="Export Shortlist to CSV">
                                            <i class="fa-solid fa-file-csv"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
