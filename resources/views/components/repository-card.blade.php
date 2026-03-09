<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<div id="repo-card" class="repo-card hidden">

    <div class="repo-card-header">

        <div>
            <div id="repo-card-name" class="repo-card-title"></div>
            <div id="repo-card-owner" class="repo-card-owner"></div>
        </div>

        <div class="repo-score-box">
            <div id="repo-card-score" class="repo-score-big"></div>
            <div id="repo-card-grade" class="repo-grade"></div>
        </div>

    </div>


    <div class="repo-metrics">

        <div class="repo-metric">
            <i class="fa-solid fa-star"></i>
            <div class="repo-metric-value" id="repo-card-stars"></div>
            <div class="repo-metric-label">Stars</div>
        </div>

        <div class="repo-metric">
            <i class="fa-solid fa-users"></i>
            <div class="repo-metric-value" id="repo-card-contributors"></div>
            <div class="repo-metric-label">Contributors</div>
        </div>

        <div class="repo-metric">
            <i class="fa-solid fa-code-branch"></i>
            <div class="repo-metric-value" id="repo-card-size"></div>
            <div class="repo-metric-label">Size</div>
        </div>

    </div>


    <div class="repo-section-title">Code Quality</div>

    <div class="repo-quality">

        <div class="quality-item">
            <span>Documentation</span>
            <div class="quality-bar">
                <div id="repo-doc-bar"></div>
            </div>
        </div>

        <div class="quality-item">
            <span>Tests</span>
            <div class="quality-bar">
                <div id="repo-test-bar"></div>
            </div>
        </div>

        <div class="quality-item">
            <span>Structure</span>
            <div class="quality-bar">
                <div id="repo-structure-bar"></div>
            </div>
        </div>

        <div class="quality-item">
            <span>Maintainability</span>
            <div class="quality-bar">
                <div id="repo-maintain-bar"></div>
            </div>
        </div>

    </div>


    <div class="repo-card-actions">

        <a id="repo-card-link" class="repo-btn-primary">
            <i class="fa-solid fa-chart-line"></i>
            View analysis
        </a>

        <button id="repo-card-close" class="repo-btn-close">
            <i class="fa-solid fa-xmark"></i>
        </button>

    </div>

</div>