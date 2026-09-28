<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireRole();

$user = getCurrentUser();
$pageTitle = "Student Dashboard";

// Fetch user's latest analysis
$stmt = $pdo->prepare("
    SELECT a.*, r.file_name, r.uploaded_at as resume_upload_time 
    FROM resume_analyses a 
    JOIN resumes r ON a.resume_id = r.id 
    WHERE a.user_id = ? 
    ORDER BY a.analysis_date DESC 
    LIMIT 1
");
$stmt->execute([$user['id']]);
$latestAnalysis = $stmt->fetch();

// Fetch all analysis history for this user
$histStmt = $pdo->prepare("
    SELECT a.*, r.file_name 
    FROM resume_analyses a 
    JOIN resumes r ON a.resume_id = r.id 
    WHERE a.user_id = ? 
    ORDER BY a.analysis_date DESC 
    LIMIT 5
");
$histStmt->execute([$user['id']]);
$analysisHistory = $histStmt->fetchAll();

// Fetch open job drives
$jobStmt = $pdo->query("SELECT * FROM job_postings WHERE status = 'open' ORDER BY created_at DESC LIMIT 3");
$openJobs = $jobStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">
    <?php displayFlash(); ?>

    <!-- Welcome Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-bold mb-1">Candidate Placement Dashboard</h2>
            <p class="text-muted mb-0">
                Welcome, <strong><?= htmlspecialchars($user['name']) ?></strong> (<?= htmlspecialchars($user['department'] ?: 'Computer Science') ?>)
            </p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="upload_resume.php" class="btn btn-primary shadow-sm">
                <i class="fa-solid fa-file-arrow-up me-2"></i>Analyze New Resume
            </a>
            <a href="bullet_optimizer.php" class="btn btn-outline-primary">
                <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Bullet Optimizer
            </a>
        </div>
    </div>

    <!-- Readiness Score & Quick Stats Grid -->
    <div class="row g-4 mb-4">
        <!-- Main Score Gauge Card -->
        <div class="col-lg-4">
            <div class="card glass-card p-4 text-center h-100">
                <h5 class="fw-bold text-muted mb-3">Overall Placement Readiness</h5>
                
                <?php if ($latestAnalysis): ?>
                    <?php 
                        $score = floatval($latestAnalysis['overall_score']);
                        $scoreClass = $score >= 80 ? 'score-high' : ($score >= 60 ? 'score-medium' : 'score-low');
                    ?>
                    <div class="score-circle <?= $scoreClass ?> mb-3">
                        <span class="score-number"><?= $score ?>%</span>
                        <span class="score-label">ATS Score</span>
                    </div>

                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($latestAnalysis['target_role']) ?></h6>
                    <p class="text-muted small mb-3">
                        Last Analyzed: <?= date('M d, Y', strtotime($latestAnalysis['analysis_date'])) ?>
                    </p>

                    <div class="d-grid">
                        <a href="view_analysis.php?id=<?= $latestAnalysis['id'] ?>" class="btn btn-sm btn-outline-primary fw-semibold">
                            <i class="fa-solid fa-chart-simple me-1"></i> View Full Breakdown
                        </a>
                    </div>
                <?php else: ?>
                    <div class="p-4 bg-light rounded-3 mb-3">
                        <i class="fa-solid fa-file-circle-question fa-3x text-muted mb-3"></i>
                        <p class="text-muted small mb-0">No resume analyzed yet. Upload your first resume to generate your ATS score and preparation roadmap.</p>
                    </div>
                    <a href="upload_resume.php" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-upload me-1"></i> Upload Resume Now
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- 3 Integrated Readiness Cards -->
        <div class="col-lg-8">
            <div class="row g-3 h-100">
                <div class="col-md-4">
                    <div class="feature-card shadow-sm d-flex flex-column justify-content-between">
                        <div>
                            <div class="feature-icon-wrapper bg-primary bg-opacity-10 text-primary mb-2">
                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Bullet Optimizer</h6>
                            <p class="text-muted small">
                                Benchmark your project bullet points against Google's XYZ formula and action verbs.
                            </p>
                        </div>
                        <a href="bullet_optimizer.php" class="btn btn-sm btn-outline-primary w-100">Open Sandbox</a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card shadow-sm d-flex flex-column justify-content-between">
                        <div>
                            <div class="feature-icon-wrapper bg-success bg-opacity-10 text-success mb-2">
                                <i class="fa-solid fa-map-location-dot"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Learning Roadmap</h6>
                            <p class="text-muted small">
                                Bridge identified skill gaps with curated courses, official docs, and estimated study hours.
                            </p>
                        </div>
                        <a href="skill_roadmap.php" class="btn btn-sm btn-outline-success w-100">View Roadmap</a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card shadow-sm d-flex flex-column justify-content-between">
                        <div>
                            <div class="feature-icon-wrapper bg-warning bg-opacity-10 text-warning mb-2">
                                <i class="fa-solid fa-comments-question-check"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Interview Prep</h6>
                            <p class="text-muted small">
                                Practice tailored technical, project-based, and STAR behavioral interview questions.
                            </p>
                        </div>
                        <a href="interview_prep.php" class="btn btn-sm btn-outline-warning w-100">Practice Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Analysis History & Available Campus Drives -->
    <div class="row g-4">
        <!-- Analysis History -->
        <div class="col-lg-7">
            <div class="card glass-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Resume Analysis History</h5>
                    <a href="upload_resume.php" class="btn btn-sm btn-outline-primary">+ New Analysis</a>
                </div>

                <?php if (empty($analysisHistory)): ?>
                    <p class="text-muted small py-4 text-center">No previous analyses found. Upload your resume to begin tracking your score progression.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle small mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Target Role</th>
                                    <th>Overall</th>
                                    <th>Skills</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($analysisHistory as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($item['target_role']) ?></strong><br>
                                            <span class="text-muted"><?= htmlspecialchars($item['file_name']) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $item['overall_score'] >= 75 ? 'success' : ($item['overall_score'] >= 50 ? 'warning' : 'danger') ?>">
                                                <?= $item['overall_score'] ?>%
                                            </span>
                                        </td>
                                        <td><?= $item['skill_score'] ?>%</td>
                                        <td class="text-muted"><?= date('M d, Y', strtotime($item['analysis_date'])) ?></td>
                                        <td>
                                            <a href="view_analysis.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-light border">
                                                View Report
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Campus Drives / Job Postings -->
        <div class="col-lg-5">
            <div class="card glass-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-briefcase me-2 text-primary"></i>Active Campus Drives</h5>
                    <span class="badge bg-primary-subtle text-primary">Live</span>
                </div>

                <div class="list-group list-group-flush">
                    <?php foreach ($openJobs as $job): ?>
                        <div class="list-group-item px-0 py-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($job['title']) ?></h6>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($job['job_type']) ?></span>
                            </div>
                            <p class="text-muted small mb-2">
                                <i class="fa-regular fa-building me-1"></i> <?= htmlspecialchars($job['company']) ?> • 
                                <i class="fa-solid fa-location-dot me-1"></i> <?= htmlspecialchars($job['location']) ?>
                            </p>
                            <div class="mb-2">
                                <?php 
                                    $skills = json_decode($job['required_skills'], true) ?: [];
                                    foreach (array_slice($skills, 0, 4) as $s): 
                                ?>
                                    <span class="badge bg-secondary-subtle text-secondary small me-1"><?= htmlspecialchars($s) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <a href="upload_resume.php?job_id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-primary">
                                Match Resume to this Drive <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
