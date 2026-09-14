<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(['recruiter', 'admin']);

$user = getCurrentUser();
$pageTitle = "Post Campus Placement Drive";

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $location = trim($_POST['location'] ?? 'Remote / Hybrid');
    $jobType = $_POST['job_type'] ?? 'Full-time';
    $experience = trim($_POST['experience_level'] ?? '0-2 Years (Entry Level)');
    $minScore = floatval($_POST['min_match_score'] ?? 65.0);
    $skillsRaw = trim($_POST['required_skills'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($title) || empty($company) || empty($description) || empty($skillsRaw)) {
        $error = "Please fill in all mandatory fields.";
    } else {
        $skillsArray = array_values(array_filter(array_map('trim', explode(',', $skillsRaw))));
        
        $stmt = $pdo->prepare("
            INSERT INTO job_postings (recruiter_id, title, company, location, job_type, description, required_skills, experience_level, min_match_score)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $saved = $stmt->execute([
            $user['id'],
            $title,
            $company,
            $location,
            $jobType,
            $description,
            json_encode($skillsArray),
            $experience,
            $minScore
        ]);

        if ($saved) {
            $_SESSION['flash_success'] = "Placement drive posted successfully! Candidates can now benchmark their resumes.";
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Failed to create job drive. Please check your inputs.";
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center gap-2 mb-3">
                <a href="dashboard.php" class="btn btn-sm btn-light border"><i class="fa-solid fa-arrow-left"></i> Back</a>
                <h3 class="fw-bold mb-0">Post New Campus Placement Drive</h3>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card glass-card p-4 p-md-5">
                <form method="POST" action="post_job.php">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Job / Role Title *</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Associate Software Engineer" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company Name *</label>
                            <input type="text" name="company" class="form-control" placeholder="e.g. Google, Infosys, TechCorp" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Location</label>
                            <input type="text" name="location" class="form-control" value="Bangalore / Hybrid">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Job Type</label>
                            <select name="job_type" class="form-select">
                                <option value="Full-time" selected>Full-time</option>
                                <option value="Internship">Internship</option>
                                <option value="Contract">Contract</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Min. Cutoff Score (%)</label>
                            <input type="number" name="min_match_score" class="form-control" value="65" min="40" max="95">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mandatory Technical Skills (comma-separated) *</label>
                        <input type="text" name="required_skills" class="form-control" placeholder="e.g. Python, React, MySQL, Docker, REST API, Git" required>
                        <div class="form-text small">Our AI NLP engine uses these keywords to benchmark candidate skills and identify skill gaps.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Job Description & Candidate Requirements *</label>
                        <textarea name="description" rows="6" class="form-control" placeholder="Paste the complete job description, responsibilities, and expected competencies..." required></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary py-2 fw-semibold">
                            <i class="fa-solid fa-cloud-arrow-up me-2"></i>Publish Placement Drive
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
