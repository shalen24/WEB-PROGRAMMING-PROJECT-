<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

$pageTitle = "Create Account";

if (isLoggedIn()) {
    header("Location: student/dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';
    $department = trim($_POST['department'] ?? 'Computer Science');
    $college = trim($_POST['college'] ?? 'Engineering College');
    $grad_year = intval($_POST['graduation_year'] ?? 2026);

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if email already exists
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            $error = "An account with this email address already exists.";
        } else {
            $passHash = password_hash($password, PASSWORD_BCRYPT);
            $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, department, college, graduation_year) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $inserted = $insertStmt->execute([$name, $email, $passHash, $role, $department, $college, $grad_year]);

            if ($inserted) {
                $_SESSION['flash_success'] = "Account created successfully! You can now sign in.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card glass-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="d-inline-flex p-3 rounded-circle bg-primary bg-opacity-10 text-primary mb-3">
                        <i class="fa-solid fa-user-plus fa-2x"></i>
                    </div>
                    <h3 class="fw-bold">Create Account</h3>
                    <p class="text-muted small">Join the AI Placement Preparation & Screening Platform</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="John Doe" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Account Role *</label>
                            <select name="role" class="form-select">
                                <option value="student" selected>Student / Candidate</option>
                                <option value="recruiter">Recruiter / Placement Cell</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address *</label>
                        <input type="email" name="email" class="form-control" placeholder="john.doe@college.edu" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password *</label>
                        <input type="password" name="password" class="form-control" placeholder="At least 6 characters" required>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Department / Stream</label>
                            <input type="text" name="department" class="form-control" placeholder="Computer Science" value="<?= htmlspecialchars($_POST['department'] ?? 'Computer Science & Engineering') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Graduation Year</label>
                            <input type="number" name="graduation_year" class="form-control" value="2026" min="2020" max="2030">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fa-solid fa-check me-2"></i>Register & Get Started
                    </button>
                </form>

                <div class="text-center mt-4 pt-2 border-top">
                    <p class="text-muted small mb-0">Already registered? <a href="login.php" class="text-primary fw-semibold">Sign in here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
