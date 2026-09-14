<?php
$appRoot = (strpos($_SERVER['REQUEST_URI'] ?? '', 'WEB%20PROGRAMMING') !== false || strpos($_SERVER['REQUEST_URI'] ?? '', 'WEB PROGRAMMING') !== false) ? '/WEB PROGRAMMING' : '';
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['user_role'] ?? '';
$userName = $_SESSION['user_name'] ?? 'User';
?>

<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $appRoot ?>/index.php">
            <span class="badge bg-primary rounded-3 p-2 text-white">
                <i class="fa-solid fa-brain fa-lg"></i>
            </span>
            <span class="brand-gradient">PlacementAI</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $appRoot ?>/index.php"><i class="fa-solid fa-house me-1"></i> Home</a>
                </li>

                <?php if ($isLoggedIn && $userRole === 'student'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $appRoot ?>/student/dashboard.php"><i class="fa-solid fa-gauge-high me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $appRoot ?>/student/upload_resume.php"><i class="fa-solid fa-file-arrow-up me-1"></i> Analyze Resume</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $appRoot ?>/student/bullet_optimizer.php"><i class="fa-solid fa-wand-magic-sparkles me-1"></i> Bullet Optimizer</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $appRoot ?>/student/skill_roadmap.php"><i class="fa-solid fa-map-location-dot me-1"></i> Learning Roadmap</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $appRoot ?>/student/interview_prep.php"><i class="fa-solid fa-comments-question-check me-1"></i> Interview Prep</a>
                    </li>
                <?php endif; ?>

                <?php if ($isLoggedIn && ($userRole === 'recruiter' || $userRole === 'admin')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $appRoot ?>/recruiter/dashboard.php"><i class="fa-solid fa-briefcase me-1"></i> Drives & Jobs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $appRoot ?>/recruiter/post_job.php"><i class="fa-solid fa-plus me-1"></i> Post Drive</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $appRoot ?>/recruiter/batch_screen.php"><i class="fa-solid fa-users-viewfinder me-1"></i> Batch Screening</a>
                    </li>
                <?php endif; ?>

                <?php if ($isLoggedIn && $userRole === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $appRoot ?>/admin/dashboard.php"><i class="fa-solid fa-chart-pie me-1"></i> Admin Analytics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $appRoot ?>/admin/manage_users.php"><i class="fa-solid fa-users-gear me-1"></i> Users</a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if ($isLoggedIn): ?>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle border d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                            <span class="badge bg-<?= $userRole === 'admin' ? 'danger' : ($userRole === 'recruiter' ? 'success' : 'primary') ?> text-uppercase">
                                <?= htmlspecialchars($userRole) ?>
                            </span>
                            <span class="fw-semibold text-dark"><?= htmlspecialchars($userName) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li><h6 class="dropdown-header">Signed in as <?= htmlspecialchars($userName) ?></h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= $appRoot ?>/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Sign Out</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= $appRoot ?>/login.php" class="btn btn-outline-primary px-3">Sign In</a>
                    <a href="<?= $appRoot ?>/register.php" class="btn btn-primary px-3">Get Started</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
