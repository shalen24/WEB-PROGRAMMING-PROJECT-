<?php
/**
 * Python AI Microservice Client Helper
 * Communicates with Flask NLP API running on port 5000.
 * Group 17 - AI Resume Analyzer
 */

define('AI_API_BASE_URL', 'http://127.0.0.1:5000');

function checkAiServiceHealth() {
    $url = AI_API_BASE_URL . '/api/health';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        return json_decode($response, true);
    }
    return false;
}

function callAiService($endpoint, $payload = [], $method = 'POST') {
    $url = AI_API_BASE_URL . $endpoint;
    $ch = curl_init($url);
    
    $jsonData = json_encode($payload);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData)
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    }
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'error' => 'cURL Error: ' . $error
        ];
    }
    
    $decoded = json_decode($response, true);
    if ($httpCode >= 400 || !$decoded) {
        return [
            'success' => false,
            'error' => $decoded['error'] ?? ('HTTP Error ' . $httpCode)
        ];
    }
    
    return $decoded;
}

function analyzeResumeFull($filePath, $jobDescription, $requiredSkills = []) {
    return callAiService('/api/analyze-full', [
        'file_path' => $filePath,
        'job_description' => $jobDescription,
        'required_skills' => $requiredSkills
    ]);
}

function optimizeBulletPoint($bulletText = null, $bulletsList = null) {
    $payload = [];
    if ($bulletText !== null) $payload['bullet_text'] = $bulletText;
    if ($bulletsList !== null) $payload['bullets_list'] = $bulletsList;
    return callAiService('/api/optimize-bullet', $payload);
}

function getSkillGapRoadmap($missingSkills) {
    return callAiService('/api/skill-gap', [
        'missing_skills' => $missingSkills
    ]);
}

function getTailoredQuestions($candidateSkills, $extractedProjects = []) {
    return callAiService('/api/generate-questions', [
        'candidate_skills' => $candidateSkills,
        'extracted_projects' => $extractedProjects
    ]);
}

function batchScreenCandidates($candidates, $jobDescription, $requiredSkills = []) {
    return callAiService('/api/batch-screen', [
        'candidates' => $candidates,
        'job_description' => $jobDescription,
        'required_skills' => $requiredSkills
    ]);
}
