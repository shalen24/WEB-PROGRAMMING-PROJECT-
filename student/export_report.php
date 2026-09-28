<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireRole();

$user = getCurrentUser();
$analysisId = intval($_GET['id'] ?? 0);

if ($analysisId <= 0) {
    die("Invalid report ID.");
}

$stmt = $pdo->prepare("
    SELECT a.*, r.file_name, r.candidate_name, r.candidate_email, r.candidate_phone, 
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
    die("Analysis record not found or access denied.");
}

$matchedSkills = json_decode($analysis['matched_skills'], true) ?: [];
$missingSkills = json_decode($analysis['missing_skills'], true) ?: [];
$recommendations = json_decode($analysis['feedback_summary'], true) ?: [];
$bulletData = json_decode($analysis['bullet_analysis'], true) ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Placement Readiness Report - <?= htmlspecialchars($analysis['candidate_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .report-sheet { background: #ffffff; max-width: 850px; margin: 30px auto; padding: 40px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        @media print {
            body { background: #fff; }
            .report-sheet { box-shadow: none; margin: 0; padding: 20px; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container my-3 no-print text-center">
    <button onclick="window.print()" class="btn btn-primary px-4 fw-semibold">
        <i class="fa-solid fa-print me-2"></i> Print or Save as PDF
    </button>
    <a href="view_analysis.php?id=<?= $analysis['id'] ?>" class="btn btn-outline-secondary ms-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
    </a>
</div>

<div class="report-sheet border">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
        <div>
            <h3 class="fw-bold mb-0 text-primary">Placement Readiness & ATS Diagnostic</h3>
            <p class="text-muted small mb-0">AI-Powered Resume Evaluation • Group 17</p>
        </div>
        <div class="text-end">
            <span class="badge bg-danger mb-1">SDG 8: Decent Work</span><br>
            <span class="badge bg-warning text-dark">SDG 9: Innovation</span>
        </div>
    </div>

    <!-- Candidate & Target Profile Info -->
    <div class="row g-3 mb-4">
        <div class="col-6">
            <h6 class="text-muted small text-uppercase mb-1">Candidate Details:</h6>
            <h5 class="fw-bold mb-0"><?= htmlspecialchars($analysis['candidate_name']) ?></h5>
            <p class="text-muted small mb-0">
                Email: <?= htmlspecialchars($analysis['candidate_email']) ?><br>
                Phone: <?= htmlspecialchars($analysis['candidate_phone'] ?: 'Verified on File') ?>
            </p>
        </div>
        <div class="col-6 text-end">
            <h6 class="text-muted small text-uppercase mb-1">Evaluation Target:</h6>
            <h5 class="fw-bold mb-0"><?= htmlspecialchars($analysis['target_role']) ?></h5>
            <p class="text-muted small mb-0">
                File: <?= htmlspecialchars($analysis['file_name']) ?><br>
                Date: <?= date('M d, Y', strtotime($analysis['analysis_date'])) ?>
            </p>
        </div>
    </div>

    <!-- Overall Match Score Box -->
    <div class="p-4 bg-light rounded text-center border mb-4">
        <h6 class="text-muted text-uppercase small mb-2">Overall ATS Placement Readiness Score</h6>
        <h1 class="display-4 fw-bold text-<?= $analysis['overall_score'] >= 80 ? 'success' : ($analysis['overall_score'] >= 60 ? 'warning' : 'danger') ?> mb-2">
            <?= $analysis['overall_score'] ?>%
        </h1>
        <span class="badge bg-<?= $analysis['overall_score'] >= 80 ? 'success' : ($analysis['overall_score'] >= 60 ? 'warning' : 'danger') ?> px-3 py-1 fs-6">
            <?= $analysis['overall_score'] >= 80 ? 'Placement Ready (Top Tier)' : ($analysis['overall_score'] >= 60 ? 'Competitive (Minor Gaps)' : 'Requires Skill Enhancement') ?>
        </span>
    </div>

    <!-- 4-Factor Criteria Grid -->
    <div class="row g-3 mb-4">
        <div class="col-3 text-center">
            <div class="p-2 border rounded">
                <small class="text-muted d-block">Skill Match (50%)</small>
                <strong class="fs-5 text-primary"><?= $analysis['skill_score'] ?>%</strong>
            </div>
        </div>
        <div class="col-3 text-center">
            <div class="p-2 border rounded">
                <small class="text-muted d-block">Experience (25%)</small>
                <strong class="fs-5 text-info"><?= $analysis['experience_score'] ?>%</strong>
            </div>
        </div>
        <div class="col-3 text-center">
            <div class="p-2 border rounded">
                <small class="text-muted d-block">Education (15%)</small>
                <strong class="fs-5 text-success"><?= $analysis['education_score'] ?>%</strong>
            </div>
        </div>
        <div class="col-3 text-center">
            <div class="p-2 border rounded">
                <small class="text-muted d-block">Formatting (10%)</small>
                <strong class="fs-5 text-purple"><?= $analysis['formatting_score'] ?>%</strong>
            </div>
        </div>
    </div>

    <!-- Skills Breakdown -->
    <div class="mb-4">
        <h6 class="fw-bold mb-2">Matched Competencies:</h6>
        <div class="mb-3">
            <?php foreach ($matchedSkills as $ms): ?>
                <span class="badge bg-success me-1 mb-1"><?= htmlspecialchars($ms) ?></span>
            <?php endforeach; ?>
        </div>

        <h6 class="fw-bold mb-2">Missing Priority Skills (Preparation Needed):</h6>
        <div>
            <?php foreach ($missingSkills as $miss): ?>
                <span class="badge bg-danger me-1 mb-1"><?= htmlspecialchars($miss) ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Optimization Recommendations -->
    <div class="pt-3 border-top">
        <h6 class="fw-bold mb-2">Actionable Next Steps:</h6>
        <ul class="small text-muted mb-0">
            <?php foreach ($recommendations as $rec): ?>
                <li class="mb-1"><?= htmlspecialchars($rec) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

</body>
</html>
