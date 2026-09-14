<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(['student', 'recruiter', 'admin']);

$user = getCurrentUser();
$pageTitle = "Tailored Interview Preparation Studio";

$analysisId = intval($_GET['analysis_id'] ?? 0);
$candidateSkills = [];
$extractedProjects = [];
$candidateName = $user['name'];

if ($analysisId > 0) {
    $stmt = $pdo->prepare("
        SELECT a.*, r.candidate_name, r.extracted_skills, r.extracted_projects
        FROM resume_analyses a
        JOIN resumes r ON a.resume_id = r.id
        WHERE a.id = ?
    ");
    $stmt->execute([$analysisId]);
    $an = $stmt->fetch();
    if ($an) {
        $candidateSkills = json_decode($an['extracted_skills'], true) ?: [];
        $extractedProjects = json_decode($an['extracted_projects'], true) ?: [];
        $candidateName = $an['candidate_name'] ?: $user['name'];
    }
}

// Fallback to latest resume if not given
if (empty($candidateSkills) && $user['role'] === 'student') {
    $stmt = $pdo->prepare("
        SELECT r.candidate_name, r.extracted_skills, r.extracted_projects
        FROM resumes r
        WHERE r.user_id = ?
        ORDER BY r.uploaded_at DESC LIMIT 1
    ");
    $stmt->execute([$user['id']]);
    $latestRes = $stmt->fetch();
    if ($latestRes) {
        $candidateSkills = json_decode($latestRes['extracted_skills'], true) ?: [];
        $extractedProjects = json_decode($latestRes['extracted_projects'], true) ?: [];
        $candidateName = $latestRes['candidate_name'] ?: $user['name'];
    }
}

// Default skill set if no resume yet
if (empty($candidateSkills)) {
    $candidateSkills = ['React', 'Node.js', 'Python', 'MySQL', 'Git'];
}

// Call Python API or DB for questions
$apiQuestions = getTailoredQuestions($candidateSkills, $extractedProjects);
$questionsData = ($apiQuestions && !empty($apiQuestions['data'])) ? $apiQuestions['data'] : null;

$techQuestions = $questionsData['technical_questions'] ?? [];
$projQuestions = $questionsData['project_questions'] ?? [];
$behQuestions = $questionsData['behavioral_questions'] ?? [];

// Fallback to local DB if API not running
if (empty($techQuestions)) {
    $placeholders = implode(',', array_fill(0, count($candidateSkills), '?'));
    $qStmt = $pdo->prepare("SELECT * FROM interview_questions WHERE skill_or_topic IN ($placeholders) AND question_type = 'technical'");
    $qStmt->execute($candidateSkills);
    $dbTech = $qStmt->fetchAll();
    foreach ($dbTech as $q) {
        $techQuestions[] = [
            'question' => $q['question_text'],
            'difficulty' => $q['difficulty'],
            'eval_criteria' => 'Technical depth, core principles, and performance trade-offs.',
            'answer_hints' => $q['answer_hints'],
            'keywords' => explode(',', $q['key_concepts'])
        ];
    }
}

if (empty($behQuestions)) {
    $bStmt = $pdo->query("SELECT * FROM interview_questions WHERE question_type = 'behavioral'");
    $dbBeh = $bStmt->fetchAll();
    foreach ($dbBeh as $b) {
        $behQuestions[] = [
            'question' => $b['question_text'],
            'framework' => 'STAR Method (Situation -> Task -> Action -> Result)',
            'eval_criteria' => 'Structured communication, problem solving, and impact.',
            'answer_hints' => $b['answer_hints']
        ];
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Interview Prep</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0">AI-Tailored Placement Interview Studio</h2>
            <p class="text-muted small mb-0">
                Personalized technical, project-based, and STAR behavioral questions tailored for <strong><?= htmlspecialchars($candidateName) ?></strong>
            </p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <?php if ($analysisId > 0): ?>
                <a href="view_analysis.php?id=<?= $analysisId ?>" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-chart-simple me-1"></i> Back to Report
                </a>
            <?php endif; ?>
            <a href="skill_roadmap.php<?= $analysisId ? '?analysis_id=' . $analysisId : '' ?>" class="btn btn-outline-success btn-sm">
                <i class="fa-solid fa-map-location-dot me-1"></i> View Learning Roadmap
            </a>
        </div>
    </div>

    <!-- Candidate Verified Skills Badges -->
    <div class="card glass-card p-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <span class="text-muted small fw-semibold d-block mb-1">Questions Verified & Tailored to Your Stack:</span>
                <div class="d-flex flex-wrap">
                    <?php foreach (array_slice($candidateSkills, 0, 8) as $s): ?>
                        <span class="skill-tag skill-tag-matched">
                            <i class="fa-solid fa-check text-success"></i> <?= htmlspecialchars($s) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <span class="badge bg-primary px-3 py-2 fs-6">
                <i class="fa-solid fa-brain me-1"></i> AI Question Engine Active
            </span>
        </div>
    </div>

    <!-- Question Category Tabs -->
    <ul class="nav nav-pills nav-pills-custom mb-4" id="interviewTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="tech-tab" data-bs-toggle="pill" data-bs-target="#tech-content">
                <i class="fa-solid fa-code me-2"></i>Technical Questions (<?= count($techQuestions) ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="proj-tab" data-bs-toggle="pill" data-bs-target="#proj-content">
                <i class="fa-solid fa-diagram-project me-2"></i>Project Deep-Dive (<?= count($projQuestions) ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="beh-tab" data-bs-toggle="pill" data-bs-target="#beh-content">
                <i class="fa-solid fa-users me-2"></i>STAR Behavioral (<?= count($behQuestions) ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="interviewTabsContent">
        <!-- Technical Questions -->
        <div class="tab-pane fade show active" id="tech-content">
            <div class="accordion" id="accordionTech">
                <?php foreach ($techQuestions as $idx => $q): ?>
                    <div class="accordion-item mb-3 border rounded shadow-sm">
                        <h2 class="accordion-header" id="headingTech<?= $idx ?>">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTech<?= $idx ?>">
                                <span class="badge bg-primary-subtle text-primary me-3">Q<?= $idx + 1 ?></span>
                                <span><?= htmlspecialchars($q['question']) ?></span>
                                <span class="badge bg-light text-dark border ms-auto me-3 small">
                                    <?= htmlspecialchars($q['difficulty'] ?? 'Medium') ?>
                                </span>
                            </button>
                        </h2>
                        <div id="collapseTech<?= $idx ?>" class="accordion-collapse collapse" data-bs-parent="#accordionTech">
                            <div class="accordion-body bg-light">
                                <div class="p-3 bg-white rounded border mb-3">
                                    <strong class="text-primary d-block mb-1">
                                        <i class="fa-solid fa-magnifying-glass me-1"></i> What the Interviewer is Evaluating:
                                    </strong>
                                    <p class="text-muted small mb-0"><?= htmlspecialchars($q['eval_criteria'] ?? '') ?></p>
                                </div>

                                <div class="p-3 bg-white rounded border mb-3">
                                    <strong class="text-success d-block mb-1">
                                        <i class="fa-solid fa-lightbulb me-1"></i> Recommended Answer Structure & Key Concepts:
                                    </strong>
                                    <p class="text-muted small mb-0"><?= htmlspecialchars($q['answer_hints'] ?? '') ?></p>
                                </div>

                                <?php if (!empty($q['keywords'])): ?>
                                    <div>
                                        <span class="small fw-semibold text-muted me-2">Must-Mention Keywords:</span>
                                        <?php foreach ($q['keywords'] as $kw): ?>
                                            <span class="badge bg-secondary-subtle text-secondary small me-1"><?= htmlspecialchars(trim($kw)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Project Deep-Dive Questions -->
        <div class="tab-pane fade" id="proj-content">
            <div class="accordion" id="accordionProj">
                <?php if (empty($projQuestions)): ?>
                    <div class="card glass-card p-4 text-center text-muted">
                        <p class="mb-0">No specific project entries extracted. Standard architectural questions will be used during campus rounds.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($projQuestions as $pidx => $pq): ?>
                        <div class="accordion-item mb-3 border rounded shadow-sm">
                            <h2 class="accordion-header" id="headingProj<?= $pidx ?>">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProj<?= $pidx ?>">
                                    <span class="badge bg-info-subtle text-info me-3">Project Q<?= $pidx + 1 ?></span>
                                    <span><?= htmlspecialchars($pq['question']) ?></span>
                                </button>
                            </h2>
                            <div id="collapseProj<?= $pidx ?>" class="accordion-collapse collapse" data-bs-parent="#accordionProj">
                                <div class="accordion-body bg-light">
                                    <div class="p-3 bg-white rounded border mb-3">
                                        <strong class="text-info d-block mb-1"><i class="fa-solid fa-bullseye me-1"></i> Evaluation Focus:</strong>
                                        <p class="text-muted small mb-0"><?= htmlspecialchars($pq['eval_criteria'] ?? '') ?></p>
                                    </div>
                                    <div class="p-3 bg-white rounded border">
                                        <strong class="text-success d-block mb-1"><i class="fa-solid fa-lightbulb me-1"></i> Best Practices for Responding:</strong>
                                        <p class="text-muted small mb-0"><?= htmlspecialchars($pq['answer_hints'] ?? '') ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- STAR Behavioral Questions -->
        <div class="tab-pane fade" id="beh-content">
            <div class="alert alert-primary mb-3">
                <i class="fa-solid fa-info-circle me-2"></i>
                <strong>STAR Method Response Strategy:</strong> Structure your answers with <strong>S</strong>ituation (context), <strong>T</strong>ask (your specific role), <strong>A</strong>ction (concrete steps taken), and <strong>R</strong>esult (quantified positive outcome).
            </div>

            <div class="accordion" id="accordionBeh">
                <?php foreach ($behQuestions as $bidx => $bq): ?>
                    <div class="accordion-item mb-3 border rounded shadow-sm">
                        <h2 class="accordion-header" id="headingBeh<?= $bidx ?>">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBeh<?= $bidx ?>">
                                <span class="badge bg-warning-subtle text-warning me-3">Behavioral Q<?= $bidx + 1 ?></span>
                                <span><?= htmlspecialchars($bq['question']) ?></span>
                            </button>
                        </h2>
                        <div id="collapseBeh<?= $bidx ?>" class="accordion-collapse collapse" data-bs-parent="#accordionBeh">
                            <div class="accordion-body bg-light">
                                <div class="p-3 bg-white rounded border mb-3">
                                    <strong class="text-warning d-block mb-1"><i class="fa-solid fa-compass me-1"></i> Framework Strategy:</strong>
                                    <p class="text-muted small mb-0"><?= htmlspecialchars($bq['framework'] ?? 'STAR Method') ?>: <?= htmlspecialchars($bq['eval_criteria'] ?? '') ?></p>
                                </div>
                                <div class="p-3 bg-white rounded border">
                                    <strong class="text-success d-block mb-1"><i class="fa-solid fa-comment-dots me-1"></i> Model Response Blueprint:</strong>
                                    <p class="text-muted small mb-0"><?= htmlspecialchars($bq['answer_hints'] ?? '') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
