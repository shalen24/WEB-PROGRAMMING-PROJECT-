<?php
$appRoot = (strpos($_SERVER['REQUEST_URI'] ?? '', 'WEB%20PROGRAMMING') !== false || strpos($_SERVER['REQUEST_URI'] ?? '', 'WEB PROGRAMMING') !== false) ? '/WEB PROGRAMMING' : '';
?>
<footer class="py-4 mt-auto">
    <div class="container">
        <div class="row align-items-center gy-3">
            <div class="col-md-6 text-center text-md-start">
                <div class="d-flex align-items-center gap-2 mb-2 justify-content-center justify-content-md-start">
                    <span class="badge bg-primary p-2 text-white">
                        <i class="fa-solid fa-brain"></i>
                    </span>
                    <strong class="brand-gradient fs-5">PlacementAI Platform</strong>
                </div>
                <p class="text-muted small mb-0">
                    AI-Powered Resume Analysis & Role-Tailored Placement Readiness Engine.<br>
                    <strong>Group 17:</strong> Sanoj P V (48) • Shalen Ann Regi (49) • Shifa Usman (50)
                </p>
            </div>

            <div class="col-md-6 text-center text-md-end">
                <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-end mb-2">
                    <span class="sdg-badge-8" title="Decent Work and Economic Growth">
                        <i class="fa-solid fa-chart-line"></i> SDG 8: Decent Work
                    </span>
                    <span class="sdg-badge-9" title="Industry, Innovation and Infrastructure">
                        <i class="fa-solid fa-industry"></i> SDG 9: Innovation
                    </span>
                </div>
                <small class="text-muted">Built with HTML5, CSS3, JavaScript, PHP, MySQL, Python & NLP</small>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js for visualizations -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Custom Application Scripts -->
<script src="<?= $appRoot ?>/assets/js/main.js"></script>
</body>
</html>
