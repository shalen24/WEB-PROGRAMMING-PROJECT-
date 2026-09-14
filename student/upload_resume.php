<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(['student', 'admin']);

$user = getCurrentUser();
$pageTitle = "Upload & Analyze Resume";

$selectedJobId = intval($_GET['job_id'] ?? 0);

// Fetch available jobs for dropdown
$jobsStmt = $pdo->query("SELECT id, title, company, description, required_skills FROM job_postings WHERE status = 'open' ORDER BY id DESC");
$availableJobs = $jobsStmt->fetchAll();

$error = '';
$warning = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetOption = $_POST['target_option'] ?? 'existing';
    $jobDescription = '';
    $requiredSkills = [];
    $targetRole = 'General Software Engineering';
    $jobId = null;

    if ($targetOption === 'existing') {
        $chosenJobId = intval($_POST['job_id'] ?? 0);
        if ($chosenJobId > 0) {
            $jStmt = $pdo->prepare("SELECT * FROM job_postings WHERE id = ?");
            $jStmt->execute([$chosenJobId]);
            $jobData = $jStmt->fetch();
            if ($jobData) {
                $jobId = $jobData['id'];
                $targetRole = $jobData['title'] . ' (' . $jobData['company'] . ')';
                $jobDescription = $jobData['description'];
                $requiredSkills = json_decode($jobData['required_skills'], true) ?: [];
            }
        }
    } else {
        $targetRole = trim($_POST['custom_title'] ?? 'Software Developer');
        $jobDescription = trim($_POST['custom_description'] ?? '');
        $skillsRaw = trim($_POST['custom_skills'] ?? '');
        if (!empty($skillsRaw)) {
            $requiredSkills = array_map('trim', explode(',', $skillsRaw));
        }
    }

    if (empty($jobDescription) && empty($requiredSkills)) {
        $error = "Please select a target job opening or provide a job description.";
    } elseif (!isset($_FILES['resume_file']) || $_FILES['resume_file']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please select a valid resume file (PDF, DOCX, or TXT) to upload.";
    } else {
        $file = $_FILES['resume_file'];
        $origName = basename($file['name']);
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if (!in_array($ext, ['pdf', 'docx', 'txt'])) {
            $error = "Unsupported file format. Please upload a PDF, DOCX, or TXT document.";
        } else {
            $uploadDir = dirname(__DIR__) . '/uploads/resumes/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $uniqueName = 'resume_' . $user['id'] . '_' . time() . '.' . $ext;
            $destination = $uploadDir . $uniqueName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Call Python AI microservice
                $apiResult = analyzeResumeFull($destination, $jobDescription, $requiredSkills);

                if (!$apiResult || empty($apiResult['success'])) {
                    $error = "AI Engine Notice: " . ($apiResult['error'] ?? 'Could not communicate with Python AI microservice. Ensure "python ai_service/app.py" is running on port 5000.');
                } else {
                    $aiData = $apiResult['data'];
                    $parsed = $aiData['parsed_candidate'];
                    $match = $aiData['match_analysis'];
                    $bulletOpt = $aiData['bullet_optimization'];

                    // Save to resumes table
                    $resStmt = $pdo->prepare("
                        INSERT INTO resumes (user_id, file_name, file_path, file_type, parsed_text, candidate_name, candidate_email, candidate_phone, extracted_skills, extracted_education, extracted_projects, extracted_experience)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $resStmt->execute([
                        $user['id'],
                        $origName,
                        $destination,
                        $ext,
                        $aiData['match_analysis']['parsed_resume']['raw_text'] ?? '',
                        $parsed['name'] ?? $user['name'],
                        $parsed['contact']['email'] ?? $user['email'],
                        $parsed['contact']['phone'] ?? '',
                        json_encode($parsed['skills']['skills_list'] ?? []),
                        json_encode($parsed['education'] ?? []),
                        json_encode($parsed['projects'] ?? []),
                        json_encode([])
                    ]);
                    $resumeId = $pdo->lastInsertId();

                    // Save to resume_analyses table
                    $anStmt = $pdo->prepare("
                        INSERT INTO resume_analyses (resume_id, user_id, job_id, target_role, overall_score, skill_score, experience_score, education_score, formatting_score, matched_skills, missing_skills, feedback_summary, bullet_analysis)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $anStmt->execute([
                        $resumeId,
                        $user['id'],
                        $jobId,
                        $targetRole,
                        $match['overall_score'],
                        $match['skill_score'],
                        $match['experience_score'],
                        $match['education_score'],
                        $match['formatting_score'],
                        json_encode($match['matched_skills']),
                        json_encode($match['missing_skills']),
                        json_encode($match['recommendations']),
                        json_encode($bulletOpt)
                    ]);
                    $analysisId = $pdo->lastInsertId();

                    // If linked to a job, also add to job_shortlists as under_review
                    if ($jobId) {
                        $shortStmt = $pdo->prepare("INSERT INTO job_shortlists (job_id, user_id, resume_id, match_score, status) VALUES (?, ?, ?, ?, 'under_review')");
                        $shortStmt->execute([$jobId, $user['id'], $resumeId, $match['overall_score']]);
                    }

                    $_SESSION['flash_success'] = "Resume analyzed successfully! View your detailed score report below.";
                    header("Location: view_analysis.php?id=" . $analysisId);
                    exit();
                }
            } else {
                $error = "Failed to save uploaded file on server. Check folder permissions.";
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex align-items-center gap-2 mb-3">
                <a href="dashboard.php" class="btn btn-sm btn-light border"><i class="fa-solid fa-arrow-left"></i> Back</a>
                <h3 class="fw-bold mb-0">Upload & Analyze Resume</h3>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card glass-card p-4 p-md-5">
                <form method="POST" action="upload_resume.php" enctype="multipart/form-data">
                    <!-- Step 1: Upload File -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">1. Select Your Resume File</label>
                        <div class="p-4 border border-2 border-dashed rounded-3 text-center bg-light">
                            <i class="fa-solid fa-cloud-arrow-up fa-3x text-primary mb-3"></i>
                            <h6 class="fw-semibold mb-1">Drag and drop or click to choose file</h6>
                            <p class="text-muted small mb-3">Supported Formats: PDF, DOCX, TXT (Max: 10MB)</p>
                            <input type="file" name="resume_file" class="form-control w-75 mx-auto" accept=".pdf,.docx,.txt" required>
                        </div>
                    </div>

                    <!-- Step 2: Choose Target Job Description -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">2. Target Job Role & Benchmarking Criteria</label>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="target_option" id="opt-existing" value="existing" checked onchange="toggleJobOption()">
                            <label class="form-check-label fw-semibold" for="opt-existing">
                                Select from Active Campus Placement Drives
                            </label>
                        </div>

                        <div id="existing-job-box" class="ms-4 mb-3">
                            <select name="job_id" class="form-select">
                                <option value="">-- Choose Target Campus Drive --</option>
                                <?php foreach ($availableJobs as $job): ?>
                                    <option value="<?= $job['id'] ?>" <?= ($selectedJobId === $job['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($job['title']) ?> - <?= htmlspecialchars($job['company']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="target_option" id="opt-custom" value="custom" onchange="toggleJobOption()">
                            <label class="form-check-label fw-semibold" for="opt-custom">
                                Paste a Custom Job Description
                            </label>
                        </div>

                        <div id="custom-job-box" class="ms-4 d-none">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Target Role Title</label>
                                <input type="text" name="custom_title" class="form-control" placeholder="e.g. Data Analyst / Backend Engineer">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Mandatory Skills (comma-separated)</label>
                                <input type="text" name="custom_skills" class="form-control" placeholder="e.g. Python, SQL, Tableau, Pandas">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Paste Full Job Description Text</label>
                                <textarea name="custom_description" rows="5" class="form-control" placeholder="Paste the recruiter requirements or JD here..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid pt-3 border-top">
                        <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                            <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Run AI ATS Analysis & Readiness Diagnostic
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleJobOption() {
    const isCustom = document.getElementById('opt-custom').checked;
    document.getElementById('existing-job-box').classList.toggle('d-none', isCustom);
    document.getElementById('custom-job-box').classList.toggle('d-none', !isCustom);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
