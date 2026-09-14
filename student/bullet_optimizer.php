<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(['student', 'recruiter', 'admin']);

$pageTitle = "Google XYZ Bullet-Point Optimizer";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Bullet-Point Optimizer</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0">Google XYZ Bullet-Point Optimizer</h2>
            <p class="text-muted small mb-0">
                Transform weak resume bullets into quantifiable achievements: <em>"Accomplished [X], as measured by [Y], by doing [Z]"</em>
            </p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Return to Dashboard
        </a>
    </div>

    <!-- Sandbox Card -->
    <div class="row g-4 mb-5">
        <div class="col-lg-7">
            <div class="card glass-card p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-keyboard text-primary me-2"></i>Interactive Bullet Sandbox</h5>

                <!-- Sample Presets -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Try a sample bullet point:</label>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="loadSample(1)">
                            Weak Passive Bullet
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="loadSample(2)">
                            Moderate Bullet
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="loadSample(3)">
                            Strong XYZ Bullet
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Enter or Paste Your Resume Bullet Point:</label>
                    <textarea id="live-bullet-input" rows="4" class="form-control" placeholder="e.g. Worked on the frontend website using React and helped improve user interface."></textarea>
                    <div class="form-text small">Tip: Focus on one specific project achievement or job responsibility.</div>
                </div>

                <div class="d-grid">
                    <button type="button" id="btn-analyze-bullet" class="btn btn-primary py-2 fw-semibold shadow-sm">
                        <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Analyze & Optimize
                    </button>
                </div>

                <!-- Analysis Results Box -->
                <div id="bullet-result-container" class="mt-4 pt-3 border-top d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Evaluation Diagnostics</h6>
                        <span id="bullet-score-badge" class="badge bg-primary fs-6">Score: 0/100</span>
                    </div>

                    <!-- Rule Checks -->
                    <div class="p-3 bg-light rounded border mb-3 small">
                        <div id="bullet-verb-status" class="mb-2"></div>
                        <div id="bullet-metric-status" class="mb-1"></div>
                    </div>

                    <!-- Comparison Box -->
                    <div class="mb-3">
                        <span class="small fw-semibold text-muted d-block mb-1">Original Input:</span>
                        <div class="diff-box diff-before py-2 px-3 small" id="bullet-original-display"></div>
                    </div>

                    <div class="mb-3">
                        <span class="small fw-semibold text-success d-block mb-1">
                            <i class="fa-solid fa-sparkles me-1"></i> AI Enhanced Version (Google XYZ):
                        </span>
                        <div class="diff-box diff-after py-2 px-3 small fw-semibold" id="bullet-rewrite-display"></div>
                    </div>

                    <!-- Actionable Suggestions -->
                    <div>
                        <span class="small fw-semibold text-muted d-block mb-1">Recommendations:</span>
                        <ul id="bullet-suggestions-list" class="list-unstyled small mb-0"></ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formula Guide & Action Verbs Library -->
        <div class="col-lg-5">
            <div class="card glass-card p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-book-bookmark text-primary me-2"></i>The Google XYZ Formula</h5>
                
                <div class="p-3 bg-primary bg-opacity-10 rounded border border-primary border-opacity-25 mb-4">
                    <p class="small mb-1 fw-bold text-primary">Formula Breakdown:</p>
                    <ul class="small mb-0 ps-3">
                        <li><strong>[X] Accomplished:</strong> High-impact active verb + concrete deliverable.</li>
                        <li><strong>[Y] Measured by:</strong> Quantifiable metric (%, numbers, scale, time saved).</li>
                        <li><strong>[Z] By doing:</strong> Technologies, tools, algorithms, or design patterns used.</li>
                    </ul>
                </div>

                <h6 class="fw-bold mb-2">High-Impact Action Verbs Library</h6>
                
                <div class="mb-3">
                    <small class="fw-semibold text-primary d-block mb-1">Engineering & Architecture:</small>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach (['Architected', 'Engineered', 'Deployed', 'Refactored', 'Automated', 'Configured', 'Scaled'] as $v): ?>
                            <span class="badge bg-light text-dark border"><?= $v ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <small class="fw-semibold text-success d-block mb-1">Optimization & Performance:</small>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach (['Optimized', 'Accelerated', 'Streamlined', 'Consolidated', 'Elevated', 'Pruned'] as $v): ?>
                            <span class="badge bg-light text-dark border"><?= $v ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <small class="fw-semibold text-warning d-block mb-1">Leadership & Execution:</small>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach (['Spearheaded', 'Orchestrated', 'Championed', 'Mentored', 'Pioneered', 'Instituted'] as $v): ?>
                            <span class="badge bg-light text-dark border"><?= $v ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="p-3 bg-danger bg-opacity-10 rounded border border-danger border-opacity-25 mt-3">
                    <small class="fw-bold text-danger d-block mb-1">❌ Passive Phrases to Avoid:</small>
                    <small class="text-muted">
                        "Worked on", "Helped with", "Responsible for", "Assisted in", "Was part of", "Took care of".
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loadSample(type) {
    const input = document.getElementById('live-bullet-input');
    if (type === 1) {
        input.value = "Worked on the website and helped with frontend development.";
    } else if (type === 2) {
        input.value = "Built an e-commerce web application using React and Node.js with payment integration.";
    } else if (type === 3) {
        input.value = "Architected and deployed a distributed microservice using Docker and Python, reducing latency by 45% for 50,000 active users.";
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
