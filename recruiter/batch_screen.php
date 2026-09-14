<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(['recruiter', 'admin']);

$user = getCurrentUser();
$pageTitle = "Batch Resume Screening & Ranking";

$jobId = intval($_GET['job_id'] ?? 0);

// Fetch all available jobs for dropdown
$jobsStmt = $pdo->query("SELECT id, title, company, required_skills, description, min_match_score FROM job_postings ORDER BY created_at DESC");
$allJobs = $jobsStmt->fetchAll();

if ($jobId <= 0 && !empty($allJobs)) {
    $jobId = $allJobs[0]['id'];
}

// Fetch selected job details
$selectedJob = null;
foreach ($allJobs as $j) {
    if ($j['id'] == $jobId) {
        $selectedJob = $j;
        break;
    }
}

// Handle status updates via POST (Shortlist / Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $shortlistId = intval($_POST['shortlist_id'] ?? 0);
    $newStatus = $_POST['status'] ?? 'under_review';
    if ($shortlistId > 0 && in_array($newStatus, ['shortlisted', 'under_review', 'interview_scheduled', 'rejected'])) {
        $upStmt = $pdo->prepare("UPDATE job_shortlists SET status = ? WHERE id = ?");
        $upStmt->execute([$newStatus, $shortlistId]);
        flashMessage('success', "Candidate status updated to " . ucfirst(str_replace('_', ' ', $newStatus)) . ".");
        header("Location: batch_screen.php?job_id=" . $jobId);
        exit();
    }
}

// Fetch candidates who applied or are screened for this job
$candidates = [];
if ($selectedJob) {
    $cStmt = $pdo->prepare("
        SELECT js.id as shortlist_id, js.match_score, js.status as shortlist_status, js.applied_at,
               u.id as user_id, u.name as candidate_name, u.email as candidate_email, u.department, u.graduation_year,
               r.id as resume_id, r.file_name, r.file_path, r.extracted_skills,
               a.id as analysis_id, a.matched_skills, a.missing_skills, a.overall_score
        FROM job_shortlists js
        JOIN users u ON js.user_id = u.id
        JOIN resumes r ON js.resume_id = r.id
        LEFT JOIN resume_analyses a ON (a.resume_id = r.id AND a.job_id = js.job_id)
        WHERE js.job_id = ?
        ORDER BY js.match_score DESC
    ");
    $cStmt->execute([$jobId]);
    $candidates = $cStmt->fetchAll();

    // If no direct applicants yet, simulate batch pool screening from all student resumes in DB
    if (empty($candidates)) {
        $allResumesStmt = $pdo->query("
            SELECT r.id as resume_id, r.user_id, r.file_name, r.file_path, r.candidate_name, r.candidate_email, r.extracted_skills,
                   u.name, u.email, u.department, u.graduation_year
            FROM resumes r
            JOIN users u ON r.user_id = u.id
            WHERE u.role = 'student'
        ");
        $poolResumes = $allResumesStmt->fetchAll();
        $targetSkills = json_decode($selectedJob['required_skills'], true) ?: [];

        foreach ($poolResumes as $res) {
            $cSkills = json_decode($res['extracted_skills'], true) ?: [];
            $matched = array_values(array_intersect($cSkills, $targetSkills));
            $missing = array_values(array_diff($targetSkills, $cSkills));
            
            $matchPct = count($targetSkills) > 0 ? (count($matched) / count($targetSkills)) * 100 : 70.0;
            $overall = round((0.6 * $matchPct) + 30.0, 1); // standard baseline approximation

            // Insert into job_shortlists
            $ins = $pdo->prepare("INSERT INTO job_shortlists (job_id, user_id, resume_id, match_score, status) VALUES (?, ?, ?, ?, ?)");
            $status = $overall >= floatval($selectedJob['min_match_score']) ? 'shortlisted' : 'under_review';
            $ins->execute([$jobId, $res['user_id'], $res['resume_id'], $overall, $status]);
        }

        // Re-fetch
        $cStmt->execute([$jobId]);
        $candidates = $cStmt->fetchAll();
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">
    <?php displayFlash(); ?>

    <!-- Top Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="dashboard.php">Recruiter Dashboard</a></li>
                    <li class="breadcrumb-item active">Batch Screening</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0">Automated Candidate Screening & Ranking Engine</h2>
            <p class="text-muted small mb-0">
                Objective, bias-free applicant ranking against target job competencies (SDG 8 & SDG 9)
            </p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="export_shortlist.php?job_id=<?= $jobId ?>" class="btn btn-success shadow-sm">
                <i class="fa-solid fa-file-excel me-1"></i> Export Shortlist (CSV)
            </a>
            <a href="post_job.php" class="btn btn-outline-primary">
                <i class="fa-solid fa-plus me-1"></i> Post Another Drive
            </a>
        </div>
    </div>

    <!-- Drive Selector Card -->
    <div class="card glass-card p-3 mb-4">
        <form method="GET" action="batch_screen.php" class="row g-3 align-items-center">
            <div class="col-md-7">
                <label class="form-label small fw-semibold mb-1">Select Campus Placement Drive:</label>
                <select name="job_id" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($allJobs as $job): ?>
                        <option value="<?= $job['id'] ?>" <?= ($jobId == $job['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($job['title']) ?> - <?= htmlspecialchars($job['company']) ?> (Cutoff: <?= $job['min_match_score'] ?>%)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-5">
                <label class="form-label small fw-semibold mb-1">Live Filter by Name or Skill:</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" id="candidate-search-input" class="form-control" placeholder="Search candidate pool...">
                </div>
            </div>
        </form>
    </div>

    <!-- Candidate Leaderboard -->
    <div class="card glass-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold mb-1">
                    <i class="fa-solid fa-trophy text-warning me-2"></i>Ranked Candidate Leaderboard
                </h5>
                <p class="text-muted small mb-0">
                    Total Applicants Evaluated: <strong><?= count($candidates) ?></strong> • Cutoff Score: <strong><?= $selectedJob['min_match_score'] ?? 60 ?>%</strong>
                </p>
            </div>
            <span class="badge bg-primary px-3 py-2">
                <i class="fa-solid fa-filter me-1"></i> Auto-Ranked by Match Score
            </span>
        </div>

        <?php if (empty($candidates)): ?>
            <div class="p-4 text-center text-muted">
                <i class="fa-solid fa-inbox fa-3x text-muted mb-2"></i>
                <p class="mb-0">No resumes have been submitted for this drive yet.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Rank</th>
                            <th>Candidate Details</th>
                            <th>Match Score</th>
                            <th>Competencies</th>
                            <th>Recruitment Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($candidates as $rank => $c): ?>
                            <?php 
                                $score = floatval($c['match_score']);
                                $cutoff = floatval($selectedJob['min_match_score'] ?? 60);
                                $isQualified = $score >= $cutoff;
                                $matchedSkills = json_decode($c['matched_skills'] ?? '[]', true) ?: [];
                                $missingSkills = json_decode($c['missing_skills'] ?? '[]', true) ?: [];
                            ?>
                            <tr class="candidate-table-row">
                                <td>
                                    <?php if ($rank === 0): ?>
                                        <span class="badge bg-warning text-dark fs-6"><i class="fa-solid fa-crown"></i> #1</span>
                                    <?php elseif ($rank === 1): ?>
                                        <span class="badge bg-secondary fs-6">#2</span>
                                    <?php elseif ($rank === 2): ?>
                                        <span class="badge bg-info fs-6">#3</span>
                                    <?php else: ?>
                                        <span class="text-muted fw-bold">#<?= $rank + 1 ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="fs-6"><?= htmlspecialchars($c['candidate_name']) ?></strong><br>
                                    <span class="text-muted"><?= htmlspecialchars($c['candidate_email']) ?></span><br>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($c['department'] ?: 'CSE') ?> • <?= $c['graduation_year'] ?: '2026' ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="score-circle <?= $score >= 80 ? 'score-high' : ($score >= 60 ? 'score-medium' : 'score-low') ?>" style="width: 54px; height: 54px; border-width: 4px;">
                                            <span class="fs-6"><?= $score ?>%</span>
                                        </div>
                                        <div>
                                            <span class="badge bg-<?= $isQualified ? 'success' : 'danger' ?>-subtle text-<?= $isQualified ? 'success' : 'danger' ?> border">
                                                <?= $isQualified ? 'Meets Cutoff' : 'Below Cutoff' ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small mb-1">
                                        <strong class="text-success"><i class="fa-solid fa-check me-1"></i>Matched:</strong> 
                                        <?= count($matchedSkills) ?> skills
                                    </div>
                                    <div class="small">
                                        <strong class="text-danger"><i class="fa-solid fa-xmark me-1"></i>Missing:</strong> 
                                        <?= count($missingSkills) ?> skills
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $c['shortlist_status'] === 'shortlisted' ? 'success' : ($c['shortlist_status'] === 'rejected' ? 'danger' : 'warning') ?> text-uppercase">
                                        <?= str_replace('_', ' ', $c['shortlist_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="candidate_detail.php?user_id=<?= $c['user_id'] ?>&job_id=<?= $jobId ?>" class="btn btn-sm btn-outline-primary" title="View Deep Dive Profile">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        <!-- Quick Shortlist / Reject POST Form -->
                                        <form method="POST" action="batch_screen.php?job_id=<?= $jobId ?>" class="d-inline">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="shortlist_id" value="<?= $c['shortlist_id'] ?>">
                                            <?php if ($c['shortlist_status'] !== 'shortlisted'): ?>
                                                <button type="submit" name="status" value="shortlisted" class="btn btn-sm btn-success" title="Shortlist Candidate">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($c['shortlist_status'] !== 'rejected'): ?>
                                                <button type="submit" name="status" value="rejected" class="btn btn-sm btn-outline-danger" title="Reject Candidate">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            <?php endif; ?>
                                        </form>
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
