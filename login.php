<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

$pageTitle = "Sign In";

if (isLoggedIn()) {
    $role = $_SESSION['user_role'] ?? 'student';
    header("Location: " . ($role === 'recruiter' ? 'recruiter/dashboard.php' : ($role === 'admin' ? 'admin/dashboard.php' : 'student/dashboard.php')));
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_department'] = $user['department'] ?? '';

            if ($user['role'] === 'recruiter') {
                header("Location: recruiter/dashboard.php");
            } elseif ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: student/dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid email or password. Please try again.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card glass-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="d-inline-flex p-3 rounded-circle bg-primary bg-opacity-10 text-primary mb-3">
                        <i class="fa-solid fa-lock fa-2x"></i>
                    </div>
                    <h3 class="fw-bold">Welcome Back</h3>
                    <p class="text-muted small">Sign in to your AI Placement & Preparation account</p>
                </div>

                <?php displayFlash(); ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-regular fa-envelope text-muted"></i></span>
                            <input type="email" name="email" id="login-email" class="form-control" placeholder="name@college.edu" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-key text-muted"></i></span>
                            <input type="password" name="password" id="login-password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Sign In
                    </button>
                </form>

                <!-- Quick Demo Login Credentials for Evaluators / Viva -->
                <div class="mt-4 pt-3 border-top">
                    <p class="text-muted small fw-semibold text-center mb-2">⚡ Quick 1-Click Demo Logins for Evaluation:</p>
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="fillDemo('sanoj@student.edu', 'password123')">
                            <i class="fa-solid fa-user-graduate me-1"></i> Student
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="fillDemo('recruiter@techcorp.com', 'password123')">
                            <i class="fa-solid fa-building me-1"></i> Recruiter
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="fillDemo('admin@placement.edu', 'password123')">
                            <i class="fa-solid fa-user-shield me-1"></i> Admin / TPO
                        </button>
                    </div>
                </div>

                <div class="text-center mt-4 pt-2">
                    <p class="text-muted small mb-0">Don't have an account yet? <a href="register.php" class="text-primary fw-semibold">Create one now</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillDemo(email, pass) {
    document.getElementById('login-email').value = email;
    document.getElementById('login-password').value = pass;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
