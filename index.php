<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

$pageTitle = "AI Resume Analyzer & Placement Preparation Platform";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- Hero Banner -->
<section class="py-5">
    <div class="container">
        <div class="hero-gradient p-5 shadow-lg">
            <div class="row align-items-center gy-4">
                <div class="col-lg-7">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="sdg-badge-8">
                            <i class="fa-solid fa-chart-line"></i> SDG 8: Decent Work & Economic Growth
                        </span>
                        <span class="sdg-badge-9">
                            <i class="fa-solid fa-microchip"></i> SDG 9: Industry & Innovation
                        </span>
                        <span class="badge bg-light text-dark px-3 py-2 fw-semibold">
                            Group 17
                        </span>
                    </div>

                    <h1 class="display-4 fw-extrabold mb-3 text-white">
                        AI-Powered Resume Analysis & Placement Readiness
                    </h1>
                    <p class="lead text-light text-opacity-75 mb-4">
                        Eliminate manual recruiter screening bias and unlock role-tailored career preparation. Optimize
                        project bullet points with metrics, bridge skill gaps with curated roadmaps, and practice
                        tailored interview questions.
                    </p>

                    <div class="d-flex flex-wrap gap-3">
                        <?php if (isLoggedIn()): ?>
                            <a href="student/upload_resume.php" class="btn btn-primary btn-lg px-4 fw-semibold shadow">
                                <i class="fa-solid fa-file-arrow-up me-2"></i>Analyze My Resume
                            </a>
                            <a href="student/bullet_optimizer.php" class="btn btn-outline-light btn-lg px-4 fw-semibold">
                                <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Bullet Optimizer
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary btn-lg px-4 fw-semibold shadow">
                                <i class="fa-solid fa-bolt me-2"></i>Try Instant Analysis
                            </a>
                            <a href="register.php" class="btn btn-outline-light btn-lg px-4 fw-semibold">
                                <i class="fa-solid fa-user-plus me-2"></i>Create Free Account
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-5 text-center">
                    <div class="glass-card p-4 text-dark bg-white shadow-lg text-start">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span
                                class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 fw-bold">
                                <i class="fa-solid fa-circle-check me-1"></i> ATS Match: 88.5%
                            </span>
                            <small class="text-muted"><i class="fa-solid fa-robot me-1"></i> NLP Evaluator</small>
                        </div>
                        <h5 class="fw-bold mb-1">Junior Full Stack Developer</h5>
                        <p class="text-muted small mb-3">Target Profile: TechCorp Solutions</p>

                        <!-- Score Progress Bars -->
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small fw-semibold mb-1">
                                <span>Skill Alignment (50%)</span>
                                <span class="text-primary">92%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" style="width: 92%"></div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <div class="d-flex justify-content-between small fw-semibold mb-1">
                                <span>Experience Relevance (25%)</span>
                                <span class="text-info">85%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-info" style="width: 85%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between small fw-semibold mb-1">
                                <span>Google XYZ Metric Compliance (10%)</span>
                                <span class="text-success">90%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: 90%"></div>
                            </div>
                        </div>

                        <div class="p-2 bg-light rounded small border">
                            <strong class="text-success"><i class="fa-solid fa-wand-magic-sparkles me-1"></i> AI Bullet
                                Polish:</strong>
                            <div class="text-muted mt-1">
                                <em>"Architected microservice with Docker & Python, reducing latency by 45% for 50k
                                    users."</em>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3 Placement Readiness Tools Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-semibold mb-2">
                Beyond Standard Static Parsers
            </span>
            <h2 class="fw-bold">Three Integrated Placement Readiness Engines</h2>
            <p class="text-muted mx-auto" style="max-width: 650px;">
                Traditional parsers stop at cold match scores. Our platform equips candidates with concrete pathways to
                upgrade their employability.
            </p>
        </div>

        <div class="row g-4">
            <!-- Tool 1 -->
            <div class="col-md-4">
                <div class="feature-card shadow-sm">
                    <div class="feature-icon-wrapper bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Bullet-Point Optimizer</h4>
                    <p class="text-muted small mb-3">
                        Evaluates resume bullet points using the <strong>Google XYZ Formula</strong> (<em>Accomplished
                            [X] as measured by [Y], by doing [Z]</em>). Detects weak passive phrasing and suggests
                        quantifiable impact metrics.
                    </p>
                    <a href="<?= isLoggedIn() ? 'student/bullet_optimizer.php' : 'login.php' ?>"
                        class="btn btn-sm btn-outline-primary fw-semibold">
                        Try Sandbox <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- Tool 2 -->
            <div class="col-md-4">
                <div class="feature-card shadow-sm">
                    <div class="feature-icon-wrapper bg-success bg-opacity-10 text-success">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Skill Gap Roadmap</h4>
                    <p class="text-muted small mb-3">
                        Pinpoints technical proficiencies missing from target Job Descriptions. Automatically maps each
                        missing skill to curated tutorials, official documentation, and estimated study hours.
                    </p>
                    <a href="<?= isLoggedIn() ? 'student/skill_roadmap.php' : 'login.php' ?>"
                        class="btn btn-sm btn-outline-success fw-semibold">
                        Explore Roadmaps <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- Tool 3 -->
            <div class="col-md-4">
                <div class="feature-card shadow-sm">
                    <div class="feature-icon-wrapper bg-warning bg-opacity-10 text-warning">
                        <i class="fa-solid fa-comments-question-check"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Tailored Interview Gen</h4>
                    <p class="text-muted small mb-3">
                        Dynamically generates technical questions on candidate's verified skills, deep-dive questions on
                        candidate's actual projects, and behavioral scenarios structured via the <strong>STAR
                            Method</strong>.
                    </p>
                    <a href="<?= isLoggedIn() ? 'student/interview_prep.php' : 'login.php' ?>"
                        class="btn btn-sm btn-outline-warning fw-semibold">
                        Start Practice <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>



<!-- SDG Impact Section -->
<section class="py-5">
    <div class="container">
        <div class="card bg-white border rounded-4 p-4 p-md-5 shadow-sm">
            <div class="row align-items-center gy-4">
                <div class="col-lg-8">
                    <div class="d-flex gap-2 mb-3">
                        <span class="sdg-badge-8"><i class="fa-solid fa-chart-line"></i> SDG 8</span>
                        <span class="sdg-badge-9"><i class="fa-solid fa-microchip"></i> SDG 9</span>
                    </div>
                    <h3 class="fw-bold mb-2">Sustainable Development Goals (SDG) Alignment</h3>
                    <p class="text-muted mb-0">
                        This project directly advances <strong>SDG 8 (Decent Work and Economic Growth)</strong> by
                        promoting objective, bias-free recruitment and upskilling youth for productive employment. It
                        further satisfies <strong>SDG 9 (Industry, Innovation and Infrastructure)</strong> through the
                        application of advanced Natural Language Processing to foster innovative digital career
                        infrastructure for educational institutions.
                    </p>
                </div>
                <div class="col-lg-4 text-center text-lg-end">
                    <a href="register.php" class="btn btn-primary btn-lg px-4 shadow">
                        Get Started Today <i class="fa-solid fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>