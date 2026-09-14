<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(['student', 'recruiter', 'admin']);

$user = getCurrentUser();
$pageTitle = "Skill Gap Learning Roadmap";

$analysisId = intval($_GET['analysis_id'] ?? 0);
$missingSkills = [];
$targetRole = 'Target Technology Stack';

if ($analysisId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM resume_analyses WHERE id = ?");
    $stmt->execute([$analysisId]);
    $an = $stmt->fetch();
    if ($an) {
        $missingSkills = json_decode($an['missing_skills'], true) ?: [];
        $targetRole = $an['target_role'];
    }
}

// Fallback: If no analysis_id provided, look up the latest analysis
if (empty($missingSkills) && $user['role'] === 'student') {
    $stmt = $pdo->prepare("SELECT * FROM resume_analyses WHERE user_id = ? ORDER BY analysis_date DESC LIMIT 1");
    $stmt->execute([$user['id']]);
    $latest = $stmt->fetch();
    if ($latest) {
        $missingSkills = json_decode($latest['missing_skills'], true) ?: [];
        $targetRole = $latest['target_role'];
        $analysisId = $latest['id'];
    }
}

// If still empty (e.g. fresh student with no resume yet), provide default placement stack skills
if (empty($missingSkills)) {
    $missingSkills = ['Docker', 'React', 'Node.js', 'Kubernetes', 'MySQL'];
}

// Fetch curated learning resources from database or Python API
$roadmapItems = [];
$totalHours = 0;

$placeholders = implode(',', array_fill(0, count($missingSkills), '?'));
$resStmt = $pdo->prepare("SELECT * FROM learning_resources WHERE skill_name IN ($placeholders)");
$resStmt->execute($missingSkills);
$dbResources = $resStmt->fetchAll();

$mappedSkills = [];
foreach ($dbResources as $r) {
    $mappedSkills[] = $r['skill_name'];
    $totalHours += $r['estimated_hours'];
    $roadmapItems[] = [
        'skill' => $r['skill_name'],
        'category' => $r['category'],
        'priority' => 'High',
        'title' => $r['resource_title'],
        'type' => $r['resource_type'],
        'url' => $r['resource_url'],
        'estimated_hours' => $r['estimated_hours'],
        'difficulty' => $r['difficulty_level'],
        'practice_project' => "Build an end-to-end demo project applying {$r['skill_name']} design patterns."
    ];
}

// For skills not found in local table, call Python API or build structured fallback
$unmapped = array_diff($missingSkills, $mappedSkills);
if (!empty($unmapped)) {
    $apiGap = getSkillGapRoadmap(array_values($unmapped));
    if (!empty($apiGap['success']) && !empty($apiGap['data']['roadmap'])) {
        foreach ($apiGap['data']['roadmap'] as $item) {
            $totalHours += $item['estimated_hours'];
            $roadmapItems[] = $item;
        }
    } else {
        foreach ($unmapped as $u) {
            $hrs = 8;
            $totalHours += $hrs;
            $roadmapItems[] = [
                'skill' => $u,
                'category' => 'Technical Proficiency',
                'priority' => 'Medium',
                'title' => "Mastering {$u} Industry Standards",
                'type' => 'Official Guide',
                'url' => "https://www.google.com/search?q=" . urlencode($u . " official tutorial documentation"),
                'estimated_hours' => $hrs,
                'difficulty' => 'Intermediate',
                'practice_project' => "Build a sample module demonstrating proficiency in {$u}."
            ];
        }
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
                    <li class="breadcrumb-item active">Learning Roadmap</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0">Skill Gap Analyzer & Curated Learning Pathways</h2>
            <p class="text-muted small mb-0">
                Direct bridges for missing proficiencies required by: <strong><?= htmlspecialchars($targetRole) ?></strong>
            </p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <?php if ($analysisId > 0): ?>
                <a href="view_analysis.php?id=<?= $analysisId ?>" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-chart-simple me-1"></i> Back to Report
                </a>
            <?php endif; ?>
            <a href="interview_prep.php<?= $analysisId ? '?analysis_id=' . $analysisId : '' ?>" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-comments-question-check me-1"></i> Practice Interview Questions
            </a>
        </div>
    </div>

    <!-- Summary Stats Card -->
    <div class="card glass-card p-4 mb-4">
        <div class="row align-items-center gy-3 text-center text-md-start">
            <div class="col-md-4 border-end-md">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3">
                    <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-circle">
                        <i class="fa-solid fa-triangle-exclamation fa-2x"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-danger"><?= count($roadmapItems) ?></h3>
                        <span class="text-muted small">Missing Proficiencies Detected</span>
                    </div>
                </div>
            </div>

            <div class="col-md-4 border-end-md">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3">
                    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle">
                        <i class="fa-solid fa-hourglass-half fa-2x"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-primary">~<?= $totalHours ?> Hours</h3>
                        <span class="text-muted small">Estimated Study & Hands-On Time</span>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3">
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle">
                        <i class="fa-solid fa-certificate fa-2x"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-success" id="completed-count">0 / <?= count($roadmapItems) ?></h3>
                        <span class="text-muted small">Roadmap Modules Completed</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Roadmap Cards Grid -->
    <div class="row g-4">
        <?php foreach ($roadmapItems as $index => $item): ?>
            <div class="col-lg-6">
                <div class="card glass-card p-4 h-100 d-flex flex-column justify-content-between" id="card-skill-<?= $index ?>">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1">
                                Missing: <?= htmlspecialchars($item['skill']) ?>
                            </span>
                            <span class="badge bg-light text-muted border">
                                <i class="fa-regular fa-clock me-1"></i> <?= $item['estimated_hours'] ?> hrs
                            </span>
                        </div>

                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($item['title']) ?></h5>
                        <p class="text-muted small mb-3">
                            <span class="text-primary fw-semibold"><?= htmlspecialchars($item['category']) ?></span> • 
                            Level: <strong><?= htmlspecialchars($item['difficulty'] ?? 'Intermediate') ?></strong> • 
                            Format: <em><?= htmlspecialchars($item['type']) ?></em>
                        </p>

                        <!-- Hands-on Project Suggestion -->
                        <div class="p-3 bg-light rounded border mb-3 small">
                            <strong class="text-dark d-block mb-1">
                                <i class="fa-solid fa-hammer text-warning me-1"></i> Recommended Hands-On Project:
                            </strong>
                            <span class="text-muted"><?= htmlspecialchars($item['practice_project']) ?></span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                        <a href="<?= htmlspecialchars($item['url']) ?>" target="_blank" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open Learning Resource
                        </a>

                        <div class="form-check">
                            <input class="form-check-input module-check" type="checkbox" id="check-<?= $index ?>" onchange="toggleModuleDone(<?= $index ?>)">
                            <label class="form-check-label small fw-semibold" for="check-<?= $index ?>">
                                Mark as Practiced
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function toggleModuleDone(index) {
    const card = document.getElementById('card-skill-' + index);
    const checkbox = document.getElementById('check-' + index);
    if (checkbox.checked) {
        card.style.opacity = '0.75';
        card.style.borderColor = '#10b981';
    } else {
        card.style.opacity = '1';
        card.style.borderColor = '#e2e8f0';
    }

    const checkedBoxes = document.querySelectorAll('.module-check:checked').length;
    const total = document.querySelectorAll('.module-check').length;
    document.getElementById('completed-count').innerText = `${checkedBoxes} / ${total}`;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
