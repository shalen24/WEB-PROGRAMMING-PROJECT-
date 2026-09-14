<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(['recruiter', 'admin']);

$userId = intval($_GET['user_id'] ?? 0);
$jobId = intval($_GET['job_id'] ?? 0);

if ($userId <= 0) {
    header("Location: batch_screen.php");
    exit();
}

// Fetch user and resume info
$uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$uStmt->execute([$userId]);
$candidate = $uStmt->fetch();

if (!$candidate) {
    die("Candidate record not found.");
}

$rStmt = $pdo->prepare("SELECT * FROM resumes WHERE user_id = ? ORDER BY uploaded_at DESC LIMIT 1");
$rStmt->execute([$userId]);
$resume = $rStmt->fetch();

// Fetch job details
$jStmt = $pdo->prepare("SELECT * FROM job_postings WHERE id = ?");
$jStmt->execute([$jobId]);
$job = $jStmt->fetch();

// Fetch shortlist record
$sStmt = $pdo->prepare("SELECT * FROM job_shortlists WHERE user_id = ? AND job_id = ? LIMIT 1");
$sStmt->execute([$userId, $jobId]);
$shortlist = $sStmt->fetch();

// Handle status or note updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'] ?? 'under_review';
    $notes = trim($_POST['recruiter_notes'] ?? '');

    if ($shortlist) {
        $up = $pdo->prepare("UPDATE job_shortlists SET status = ?, recruiter_notes = ? WHERE id = ?");
        $up->execute([$newStatus, $notes, $shortlist['id']]);
    } else {
        $ins = $pdo->prepare("INSERT INTO job_shortlists (job_id, user_id, resume_id, match_score, status, recruiter_notes) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->execute([$jobId, $userId, $resume['id'] ?? 0, 75.0, $newStatus, $notes]);
    }

    flashMessage('success', "Candidate evaluation updated.");
    header("Location: candidate_detail.php?user_id=$userId&job_id=$jobId");
    exit();
}

$pageTitle = "Candidate Profile - " . htmlspecialchars($candidate['name']);
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$candSkills = json_decode($resume['extracted_skills'] ?? '[]', true) ?: [];
$candProjects = json_decode($resume['extracted_projects'] ?? '[]', true) ?: [];
$jobSkills = json_decode($job['required_skills'] ?? '[]', true) ?: [];

$matched = array_intersect($candSkills, $jobSkills);
$missing = array_diff($jobSkills, $candSkills);
?>

<div class="container py-4">
    <?php displayFlash(); ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="dashboard.php">Drives</a></li>
                    <li class="breadcrumb-item"><a href="batch_screen.php?job_id=<?= $jobId ?>">Screening Leaderboard</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($candidate['name']) ?></li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0"><?= htmlspecialchars($candidate['name']) ?> - Evaluation Dossier</h2>
        </div>
        <a href="batch_screen.php?job_id=<?= $jobId ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Leaderboard
        </a>
    </div>

    <div class="row g-4">
        <!-- Left: Candidate Profile & Extracted Resume Info -->
        <div class="col-lg-7">
            <div class="card glass-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h4 class="fw-bold mb-1"><?= htmlspecialchars($candidate['name']) ?></h4>
                        <p class="text-muted small mb-0">
                            <i class="fa-regular fa-envelope me-1"></i> <?= htmlspecialchars($candidate['email']) ?> • 
                            <i class="fa-solid fa-graduation-cap me-1"></i> <?= htmlspecialchars($candidate['department'] ?: 'CSE') ?> (Class of <?= $candidate['graduation_year'] ?: '2026' ?>)
                        </p>
                    </div>
                    <?php if ($shortlist): ?>
                        <span class="badge bg-<?= $shortlist['status'] === 'shortlisted' ? 'success' : ($shortlist['status'] === 'rejected' ? 'danger' : 'warning') ?> fs-6 text-uppercase px-3 py-2">
                            <?= str_replace('_', ' ', $shortlist['status']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <hr>

                <!-- Competencies Found -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-tags me-1"></i> Extracted Skills & Tools</h6>
                    <div class="d-flex flex-wrap">
                        <?php foreach ($candSkills as $sk): ?>
                            <span class="skill-tag skill-tag-neutral"><?= htmlspecialchars($sk) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Extracted Projects -->
                <div class="mb-3">
                    <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-diagram-project me-1"></i> Extracted Projects</h6>
                    <?php if (empty($candProjects)): ?>
                        <p class="text-muted small">Academic engineering coursework and full-stack projects listed on resume.</p>
                    <?php else: ?>
                        <?php foreach ($candProjects as $cp): ?>
                            <div class="p-3 bg-light rounded border mb-2">
                                <strong class="d-block mb-1"><?= htmlspecialchars($cp['title'] ?? 'Project') ?></strong>
                                <?php if (!empty($cp['description'])): ?>
                                    <p class="text-muted small mb-0"><?= htmlspecialchars(implode(' ', $cp['description'])) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: Matching Against Target Job & Recruiter Decisions -->
        <div class="col-lg-5">
            <!-- Job Criteria Comparison -->
            <div class="card glass-card p-4 mb-4">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-bullseye text-primary me-2"></i>Job Fit Comparison</h5>
                <h6 class="fw-semibold text-muted mb-1">Target Position:</h6>
                <p class="fw-bold mb-3"><?= htmlspecialchars($job['title'] ?? 'Full Stack Developer') ?> (<?= htmlspecialchars($job['company'] ?? 'TechCorp') ?>)</p>

                <!-- Matched Skills -->
                <div class="mb-3">
                    <span class="small fw-bold text-success d-block mb-1">
                        <i class="fa-solid fa-check me-1"></i> Matched Job Skills (<?= count($matched) ?>):
                    </span>
                    <div class="d-flex flex-wrap">
                        <?php foreach ($matched as $ms): ?>
                            <span class="skill-tag skill-tag-matched"><?= htmlspecialchars($ms) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Missing Skills -->
                <div class="mb-3">
                    <span class="small fw-bold text-danger d-block mb-1">
                        <i class="fa-solid fa-xmark me-1"></i> Missing Required Skills (<?= count($missing) ?>):
                    </span>
                    <div class="d-flex flex-wrap">
                        <?php foreach ($missing as $ms): ?>
                            <span class="skill-tag skill-tag-missing"><?= htmlspecialchars($ms) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Recruiter Decision Form -->
            <div class="card glass-card p-4">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-gavel text-primary me-2"></i>Recruitment Decision</h5>

                <form method="POST" action="candidate_detail.php?user_id=<?= $userId ?>&job_id=<?= $jobId ?>">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Update Application Status:</label>
                        <select name="status" class="form-select">
                            <option value="under_review" <?= ($shortlist && $shortlist['status'] === 'under_review') ? 'selected' : '' ?>>Under Review</option>
                            <option value="shortlisted" <?= ($shortlist && $shortlist['status'] === 'shortlisted') ? 'selected' : '' ?>>Shortlisted for Next Round</option>
                            <option value="interview_scheduled" <?= ($shortlist && $shortlist['status'] === 'interview_scheduled') ? 'selected' : '' ?>>Interview Scheduled</option>
                            <option value="rejected" <?= ($shortlist && $shortlist['status'] === 'rejected') ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Interviewer Notes & Evaluation Comments:</label>
                        <textarea name="recruiter_notes" rows="4" class="form-control" placeholder="Add confidential notes on candidate strengths or interview feedback..."><?= htmlspecialchars($shortlist['recruiter_notes'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Recruiter Decision
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
