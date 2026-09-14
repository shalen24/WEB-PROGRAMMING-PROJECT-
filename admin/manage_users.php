<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(['admin']);

$pageTitle = "Manage User Accounts";

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $delId = intval($_GET['delete_id']);
    if ($delId > 1) { // Prevent deleting primary admin
        $delStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $delStmt->execute([$delId]);
        flashMessage('success', "User account successfully removed.");
    }
    header("Location: manage_users.php");
    exit();
}

// Fetch users
$roleFilter = $_GET['role'] ?? 'all';
if ($roleFilter !== 'all' && in_array($roleFilter, ['student', 'recruiter', 'admin'])) {
    $uStmt = $pdo->prepare("SELECT * FROM users WHERE role = ? ORDER BY id DESC");
    $uStmt->execute([$roleFilter]);
} else {
    $uStmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
}
$usersList = $uStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">
    <?php displayFlash(); ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="dashboard.php">Admin Dashboard</a></li>
                    <li class="breadcrumb-item active">Manage Users</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0">Registered User Accounts & Access Control</h2>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Analytics
        </a>
    </div>

    <!-- Filter Buttons -->
    <div class="card glass-card p-3 mb-4">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="small fw-semibold text-muted me-2">Filter by Role:</span>
            <a href="manage_users.php?role=all" class="btn btn-sm <?= $roleFilter === 'all' ? 'btn-primary' : 'btn-light border' ?>">All (<?= count($usersList) ?>)</a>
            <a href="manage_users.php?role=student" class="btn btn-sm <?= $roleFilter === 'student' ? 'btn-primary' : 'btn-light border' ?>">Students</a>
            <a href="manage_users.php?role=recruiter" class="btn btn-sm <?= $roleFilter === 'recruiter' ? 'btn-primary' : 'btn-light border' ?>">Recruiters</a>
            <a href="manage_users.php?role=admin" class="btn btn-sm <?= $roleFilter === 'admin' ? 'btn-primary' : 'btn-light border' ?>">Administrators</a>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card glass-card p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department / College</th>
                        <th>Graduation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usersList as $u): ?>
                        <tr>
                            <td>#<?= $u['id'] ?></td>
                            <td><strong class="fs-6"><?= htmlspecialchars($u['name']) ?></strong></td>
                            <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="badge bg-<?= $u['role'] === 'admin' ? 'danger' : ($u['role'] === 'recruiter' ? 'success' : 'primary') ?> text-uppercase">
                                    <?= htmlspecialchars($u['role']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($u['department'] ?: 'General') ?> • <?= htmlspecialchars($u['college'] ?: 'Engineering College') ?></td>
                            <td><?= $u['graduation_year'] ?: '2026' ?></td>
                            <td>
                                <?php if ($u['id'] > 1): ?>
                                    <a href="manage_users.php?delete_id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this user account?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">System Protected</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
