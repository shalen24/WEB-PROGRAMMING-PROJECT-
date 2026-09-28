<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireRole();

$user = getCurrentUser();
$analysisId = intval($_GET['id'] ?? 0);

if ($analysisId <= 0) {
    header("Location: dashboard.php");
    exit();
}

$stmt = $pdo->prepare("
    SELECT a.*, r.file_name, r.file_type, r.candidate_name, r.candidate_email, r.candidate_phone, 
           r.extracted_skills, r.extracted_education, r.extracted_projects
    FROM resume_analyses a
    JOIN resumes r ON a.resume_id = r.id
    WHERE a.id = ? " . ($user['role'] === 'student' ? "AND a.user_id = ?" : "") . "
    LIMIT 1
");

$params = ($user['role'] === 'student') ? [$analysisId, $user['id']] : [$analysisId];
$stmt->execute($params);
$analysis = $stmt->fetch();

if (!$analysis) {
    $_SESSION['flash_error'] = "Analysis report not found or access denied.";
    header("Location: dashboard.php");
    exit();
}

$pageTitle = "ATS Placement Report - " . htmlspecialchars($analysis['target_role']);

$matchedSkills = json_decode($analysis['matched_skills'], true) ?: [];
$missingSkills = json_decode($analysis['missing_skills'], true) ?: [];
$recommendations = json_decode($analysis['feedback_summary'], true) ?: [];
$bulletData = json_decode($analysis['bullet_analysis'], true) ?: [];
$extractedProjects = json_decode($analysis['extracted_projects'], true) ?: [];

$overallScore = floatval($analysis['overall_score']);
$scoreColor = $overallScore >= 80 ? 'score-high' : ($overallScore >= 60 ? 'score-medium' : 'score-low');
$badgeColor = $overallScore >= 80 ? 'success' : ($overallScore >= 60 ? 'warning' : 'danger');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">
    <?php displayFlash(); ?>

    <!-- Breadcrumb & Top Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Analysis Report #<?= $analysis['id'] ?></li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0">Placement Readiness & ATS Match Report</h2>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="export_report.php?id=<?= $analysis['id'] ?>" target="_blank" class="btn btn-outline-secondary">
                <i class="fa-solid fa-print me-1"></i> Print / Save PDF
            </a>
            <a href="skill_roadmap.php?analysis_id=<?= $analysis['id'] ?>" class="btn btn-success">
                <i class="fa-solid fa-map-location-dot me-1"></i> View Learning Roadmap
            </a>
            <a href="interview_prep.php?analysis_id=<?= $analysis['id'] ?>" class="btn btn-primary">
                <i class="fa-solid fa-comments-question-check me-1"></i> Practice Interview Questions
            </a>
        </div>
    </div>

    <!-- Overview Score Banner -->
    <div class="card glass-card p-4 mb-4">
        <div class="row align-items-center gy-4">
            <div class="col-md-3 text-center border-end-md">
                <div class="score-circle <?= $scoreColor ?> mb-2">
                    <span class="score-number"><?= $overallScore ?>%</span>
                    <span class="score-label">Overall Match</span>
                </div>
                <span class="badge bg-<?= $badgeColor ?> fs-6 px-3 py-1">
                    <?= $overallScore >= 80 ? 'Placement Ready' : ($overallScore >= 60 ? 'Competitive Match' : 'Gap Bridging Needed') ?>
                </span>
            </div>

            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h4 class="fw-bold mb-1"><?= htmlspecialchars($analysis['target_role']) ?></h4>
                        <p class="text-muted small mb-2">
                            Resume: <strong><?= htmlspecialchars($analysis['file_name']) ?></strong> • Analyzed on <?= date('F j, Y, g:i a', strtotime($analysis['analysis_date'])) ?>
                        </p>
                    </div>
                </div>

                <!-- 4 Criteria Breakdown Bars -->
                <div class="row g-3">
                    <div class="col-sm-6 col-lg-3">
                        <div class="p-3 bg-light rounded text-center border">
                            <span class="text-muted small d-block mb-1">Skill Match (50%)</span>
                            <h4 class="fw-bold text-primary mb-1"><?= $analysis['skill_score'] ?>%</h4>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: <?= $analysis['skill_score'] ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="p-3 bg-light rounded text-center border">
                            <span class="text-muted small d-block mb-1">Experience (25%)</span>
                            <h4 class="fw-bold text-info mb-1"><?= $analysis['experience_score'] ?>%</h4>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-info" style="width: <?= $analysis['experience_score'] ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="p-3 bg-light rounded text-center border">
                            <span class="text-muted small d-block mb-1">Education (15%)</span>
                            <h4 class="fw-bold text-success mb-1"><?= $analysis['education_score'] ?>%</h4>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: <?= $analysis['education_score'] ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="p-3 bg-light rounded text-center border">
                            <span class="text-muted small d-block mb-1">Formatting (10%)</span>
                            <h4 class="fw-bold text-purple mb-1" style="color: #8b5cf6;"><?= $analysis['formatting_score'] ?>%</h4>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" style="background-color: #8b5cf6; width: <?= $analysis['formatting_score'] ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visual Analytics & Skill Alignment -->
    <div class="row g-4 mb-4">
        <!-- Visual Radar & Doughnut Charts -->
        <div class="col-lg-5">
            <div class="card glass-card p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Competency Radar & Breakdown</h5>
                <div class="mb-4 text-center">
                    <canvas id="competencyRadarChart" style="max-height: 240px;"></canvas>
                </div>
                <div class="text-center">
                    <canvas id="atsScoreChart" style="max-height: 180px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Matched vs Missing Skills -->
        <div class="col-lg-7">
            <div class="card glass-card p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-tags me-2 text-primary"></i>Skill Competency Alignment</h5>
                
                <!-- Matched Skills -->
                <div class="mb-4">
                    <h6 class="fw-semibold text-success d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-circle-check"></i> Matched Required Skills (<?= count($matchedSkills) ?>)
                    </h6>
                    <div class="d-flex flex-wrap">
                        <?php if (empty($matchedSkills)): ?>
                            <span class="text-muted small">No direct keyword overlap detected.</span>
                        <?php else: ?>
                            <?php foreach ($matchedSkills as $s): ?>
                                <span class="skill-tag skill-tag-matched">
                                    <i class="fa-solid fa-check text-success"></i> <?= htmlspecialchars($s) ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Missing Skills -->
                <div class="mb-4">
                    <h6 class="fw-semibold text-danger d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-circle-xmark"></i> Missing Critical Skills (<?= count($missingSkills) ?>)
                    </h6>
                    <div class="d-flex flex-wrap mb-2">
                        <?php if (empty($missingSkills)): ?>
                            <span class="badge bg-success-subtle text-success p-2">Zero Skill Gaps! All target skills are matched.</span>
                        <?php else: ?>
                            <?php foreach ($missingSkills as $s): ?>
                                <span class="skill-tag skill-tag-missing">
                                    <i class="fa-solid fa-xmark text-danger"></i> <?= htmlspecialchars($s) ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($missingSkills)): ?>
                        <div class="mt-2">
                            <a href="skill_roadmap.php?analysis_id=<?= $analysis['id'] ?>" class="btn btn-sm btn-outline-success">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open Curated Study Roadmap for these skills
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Actionable Checklist -->
                <div class="pt-3 border-top">
                    <h6 class="fw-semibold mb-2"><i class="fa-solid fa-lightbulb text-warning me-2"></i>ATS Optimization Tips</h6>
                    <ul class="list-unstyled small text-muted mb-0">
                        <?php foreach ($recommendations as $rec): ?>
                            <li class="mb-2 d-flex align-items-start gap-2">
                                <i class="fa-solid fa-circle-arrow-right text-primary mt-1"></i>
                                <span><?= htmlspecialchars($rec) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Readiness Tool 1: Bullet Point Optimizer Results -->
    <div class="card glass-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold mb-1">
                    <i class="fa-solid fa-wand-magic-sparkles me-2 text-primary"></i>Resume Bullet-Point Diagnostics (Google XYZ Formula)
                </h5>
                <p class="text-muted small mb-0">
                    Formula: <em>Accomplished [X] as measured by [Y], by doing [Z]</em>. Scores impact and flags weak passive wording.
                </p>
            </div>
            <a href="bullet_optimizer.php" class="btn btn-sm btn-outline-primary">
                Open Interactive Sandbox <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>

        <?php 
            $bulletList = $bulletData['bullet_analyses'] ?? [];
            if (empty($bulletList)):
        ?>
            <p class="text-muted small py-3">No specific project bullet points were detected for detailed formatting critique.</p>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach (array_slice($bulletList, 0, 4) as $idx => $b): ?>
                    <div class="col-md-6">
                        <div class="card h-100 border p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-light text-dark border">Bullet #<?= $idx + 1 ?></span>
                                <span class="badge bg-<?= $b['score'] >= 75 ? 'success' : ($b['score'] >= 50 ? 'warning' : 'danger') ?>">
                                    Score: <?= $b['score'] ?>/100
                                </span>
                            </div>

                            <div class="diff-box diff-before py-2 px-3 small mb-2">
                                <strong>Original:</strong> <?= htmlspecialchars($b['original_text']) ?>
                            </div>

                            <div class="diff-box diff-after py-2 px-3 small mb-2">
                                <strong>AI Suggested Rewrite (XYZ):</strong><br>
                                <?= htmlspecialchars($b['suggested_rewrite']) ?>
                            </div>

                            <div class="small text-muted mt-auto pt-1">
                                <?php if (!empty($b['critiques'])): ?>
                                    <span class="text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i><?= htmlspecialchars($b['critiques'][0]) ?></span>
                                <?php else: ?>
                                    <span class="text-success"><i class="fa-solid fa-check me-1"></i>Strong action verb and quantifiable metric.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const scores = {
        overall_score: <?= json_encode($analysis['overall_score']) ?>,
        skill_score: <?= json_encode($analysis['skill_score']) ?>,
        experience_score: <?= json_encode($analysis['experience_score']) ?>,
        education_score: <?= json_encode($analysis['education_score']) ?>,
        formatting_score: <?= json_encode($analysis['formatting_score']) ?>
    };
    renderAtsCharts(scores);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
