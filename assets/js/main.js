/**
 * Frontend Interactive Logic & Visualizations
 * AI Resume Analyzer & Placement Platform
 * Group 17 - SDG 8 & SDG 9
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map((el) => new bootstrap.Tooltip(el));

    // 2. Interactive Live Bullet Optimizer Sandbox
    const bulletInput = document.getElementById('live-bullet-input');
    const analyzeBtn = document.getElementById('btn-analyze-bullet');
    const resultBox = document.getElementById('bullet-result-container');

    if (analyzeBtn && bulletInput) {
        analyzeBtn.addEventListener('click', async () => {
            const text = bulletInput.value.trim();
            if (!text) {
                alert('Please enter a bullet point to analyze.');
                return;
            }

            analyzeBtn.disabled = true;
            analyzeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Evaluating...';

            try {
                const response = await fetch('http://127.0.0.1:5000/api/optimize-bullet', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ bullet_text: text })
                });

                const data = await response.json();
                if (data.success && data.data) {
                    renderBulletAnalysis(data.data);
                } else {
                    alert('Error: ' + (data.error || 'Unable to connect to AI engine. Make sure Python AI service is running on port 5000.'));
                }
            } catch (err) {
                console.error(err);
                alert('Connection Error: Make sure the Python AI microservice is running (port 5000).');
            } finally {
                analyzeBtn.disabled = false;
                analyzeBtn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles me-2"></i>Analyze & Optimize';
            }
        });
    }

    function renderBulletAnalysis(res) {
        if (!resultBox) return;
        resultBox.classList.remove('d-none');

        const scoreColor = res.score >= 80 ? 'success' : (res.score >= 50 ? 'warning' : 'danger');
        
        document.getElementById('bullet-score-badge').className = `badge bg-${scoreColor} fs-6 px-3 py-2`;
        document.getElementById('bullet-score-badge').innerText = `Impact Score: ${res.score}/100`;

        // Action Verb Check
        const verbEl = document.getElementById('bullet-verb-status');
        if (res.has_strong_verb) {
            verbEl.innerHTML = `<i class="fa-solid fa-circle-check text-success me-2"></i><strong>Strong Action Verb:</strong> "${res.detected_verb}"`;
        } else if (res.weak_phrase_detected) {
            verbEl.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-danger me-2"></i><strong>Passive Phrase:</strong> "${res.weak_phrase_detected}" (Replace with active verb)`;
        } else {
            verbEl.innerHTML = `<i class="fa-solid fa-circle-info text-warning me-2"></i><strong>Neutral Verb:</strong> Consider a higher-impact verb.`;
        }

        // Metrics Check
        const metricEl = document.getElementById('bullet-metric-status');
        if (res.has_metrics) {
            metricEl.innerHTML = `<i class="fa-solid fa-circle-check text-success me-2"></i><strong>Quantifiable Metrics Found:</strong> ${res.detected_metrics.join(', ')}`;
        } else {
            metricEl.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-danger me-2"></i><strong>Missing Metrics:</strong> No quantifiable numbers or scale detected (Google XYZ Formula).`;
        }

        // Suggested Rewrite
        document.getElementById('bullet-original-display').innerText = res.original_text;
        document.getElementById('bullet-rewrite-display').innerText = res.suggested_rewrite;

        // Feedback / Suggestions
        const suggestionsList = document.getElementById('bullet-suggestions-list');
        suggestionsList.innerHTML = '';
        res.suggestions.forEach(s => {
            const li = document.createElement('li');
            li.className = 'mb-1';
            li.innerHTML = `<i class="fa-solid fa-arrow-right text-primary me-2"></i>${s}`;
            suggestionsList.appendChild(li);
        });

        resultBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // 3. Batch candidate search & filter
    const filterInput = document.getElementById('candidate-search-input');
    if (filterInput) {
        filterInput.addEventListener('keyup', () => {
            const query = filterInput.value.toLowerCase();
            const rows = document.querySelectorAll('.candidate-table-row');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }
});

// Chart.js helper for ATS score report
function renderAtsCharts(scores) {
    // Doughnut breakdown
    const ctxDoughnut = document.getElementById('atsScoreChart');
    if (ctxDoughnut) {
        new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: ['Skill Match (50%)', 'Experience (25%)', 'Education (15%)', 'Formatting (10%)'],
                datasets: [{
                    data: [
                        scores.skill_score * 0.50,
                        scores.experience_score * 0.25,
                        scores.education_score * 0.15,
                        scores.formatting_score * 0.10
                    ],
                    backgroundColor: ['#4f46e5', '#0ea5e9', '#10b981', '#8b5cf6'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    // Radar Chart for competencies
    const ctxRadar = document.getElementById('competencyRadarChart');
    if (ctxRadar) {
        new Chart(ctxRadar, {
            type: 'radar',
            data: {
                labels: ['Technical Skills', 'Role Alignment', 'Experience', 'Education', 'ATS Readability'],
                datasets: [{
                    label: 'Candidate Score',
                    data: [
                        scores.skill_score,
                        scores.overall_score,
                        scores.experience_score,
                        scores.education_score,
                        scores.formatting_score
                    ],
                    fill: true,
                    backgroundColor: 'rgba(79, 70, 229, 0.2)',
                    borderColor: '#4f46e5',
                    pointBackgroundColor: '#4f46e5',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#4f46e5'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    r: {
                        angleLines: { display: true },
                        suggestedMin: 20,
                        suggestedMax: 100
                    }
                }
            }
        });
    }
}
