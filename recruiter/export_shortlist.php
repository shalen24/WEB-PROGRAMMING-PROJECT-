<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(['recruiter', 'admin']);

$jobId = intval($_GET['job_id'] ?? 0);

if ($jobId <= 0) {
    die("Invalid job ID.");
}

$jStmt = $pdo->prepare("SELECT title, company FROM job_postings WHERE id = ?");
$jStmt->execute([$jobId]);
$job = $jStmt->fetch();

if (!$job) {
    die("Job not found.");
}

$cStmt = $pdo->prepare("
    SELECT js.match_score, js.status, js.recruiter_notes, js.applied_at,
           u.name as candidate_name, u.email as candidate_email, u.department, u.graduation_year,
           r.file_name
    FROM job_shortlists js
    JOIN users u ON js.user_id = u.id
    JOIN resumes r ON js.resume_id = r.id
    WHERE js.job_id = ?
    ORDER BY js.match_score DESC
");
$cStmt->execute([$jobId]);
$candidates = $cStmt->fetchAll();

$cleanTitle = preg_replace('/[^A-Za-z0-9_-]/', '_', $job['title']);
$filename = "shortlist_{$cleanTitle}_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=\"$filename\"");

$output = fopen('php://output', 'w');

// CSV Headers
fputcsv($output, [
    'Rank',
    'Candidate Name',
    'Email Address',
    'Department',
    'Graduation Year',
    'ATS Match Score (%)',
    'Application Status',
    'Resume File',
    'Recruiter Notes',
    'Applied At'
]);

foreach ($candidates as $idx => $c) {
    fputcsv($output, [
        $idx + 1,
        $c['candidate_name'],
        $c['candidate_email'],
        $c['department'],
        $c['graduation_year'],
        $c['match_score'],
        ucwords(str_replace('_', ' ', $c['status'])),
        $c['file_name'],
        $c['recruiter_notes'] ?? '',
        $c['applied_at']
    ]);
}

fclose($output);
exit();
