<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/api.php';

$analyses = $pdo->query("SELECT a.id, a.resume_id, a.bullet_analysis, r.file_path FROM resume_analyses a JOIN resumes r ON a.resume_id = r.id")->fetchAll();

foreach ($analyses as $an) {
    echo "Updating Analysis ID #{$an['id']}...\n";
    $filePath = $an['file_path'];
    if (file_exists($filePath)) {
        // Call Python parser and bullet optimizer
        $parseResult = callAiService('/api/parse-resume', ['file_path' => $filePath]);
        if (!empty($parseResult['success'])) {
            $parsed = $parseResult['data'];
            $bullets = $parsed['bullet_points'] ?? [];
            $optResult = callAiService('/api/optimize-bullet', ['bullets_list' => $bullets]);
            if (!empty($optResult['success'])) {
                $newBulletAnalysis = json_encode($optResult['data']);
                $up = $pdo->prepare("UPDATE resume_analyses SET bullet_analysis = ? WHERE id = ?");
                $up->execute([$newBulletAnalysis, $an['id']]);
                echo "Successfully updated Analysis ID #{$an['id']} with new concise bullet diagnostics!\n";
            }
        }
    }
}
echo "All analyses updated successfully.\n";
