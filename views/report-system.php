<?php require_once VIEW_PATH . 'includes/header.php'; ?>
<link rel="stylesheet" href="<?php echo ASSETS_PATH . 'css/report-system.css'; ?>" />

<div class="page-wrapper">

    <!-- Top Bar -->
    <div class="top-bar">
        <div class="banner-text">
            <i class="ri-error-warning-line"></i>
            <h2>Emergency Response System</h2>
        </div>
        <div class="banner-icon">
            <i class="ri-group-fill"></i>
            <i class="ri-pulse-line"></i>
        </div>
    </div>

    <!-- Page Content -->
    <div class="page">

        <a class="back-link" href="index.php?action=dashboard">← Back to Home</a>

        <div class="section-head">
            <span class="sh-icon"><i class="ri-alert-line"></i></span>
            Report Emergency
        </div>

        <div class="card">
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="report-error" role="alert">
                    <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form id="emergencyForm" action="<?php echo BASE_URL; ?>submit_emergency.php" method="POST" enctype="multipart/form-data">

                <!-- Report fields -->
                <input type="hidden" name="emergency_type" id="emergency_type" required>
                <input type="hidden" name="severity" id="severity" value="moderate" required>
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">

                <!-- Priority Level -->
                <p class="priority-label">Priority level</p>
                <div class="priority-grid">
                    <button type="button" class="priority-box pb-critical selected" data-severity="critical">
                        <span class="pb-badge">CRITICAL</span>
                        <p class="pb-desc">Life-threatening emergency</p>
                    </button>
                    <button type="button" class="priority-box pb-high" data-severity="moderate">
                        <span class="pb-badge">HIGH PRIORITY</span>
                        <p class="pb-desc">Urgent response needed</p>
                    </button>
                    <button type="button" class="priority-box pb-moderate" data-severity="moderate">
                        <span class="pb-badge">MODERATE</span>
                        <p class="pb-desc">Important but stable</p>
                    </button>
                    <button type="button" class="priority-box pb-low" data-severity="minor">
                        <span class="pb-badge">LOW PRIORITY</span>
                        <p class="pb-desc">Non-urgent assistance</p>
                    </button>
                </div>

                <!-- Emergency Type -->
                <p class="type-label">Select Emergency Type</p>
                <div class="emergency-grid">
                    <button type="button" class="emergency-box" data-type="medical">
                        <span class="box-icon">🏥</span>
                        <p>Medical Emergency</p>
                    </button>
                    <button type="button" class="emergency-box" data-type="accident">
                        <span class="box-icon">🚗</span>
                        <p>Accident</p>
                    </button>
                    <button type="button" class="emergency-box" data-type="animal">
                        <span class="box-icon">🐾</span>
                        <p>Animal Attack</p>
                    </button>
                    <button type="button" class="emergency-box" data-type="disaster">
                        <span class="box-icon">🌪️</span>
                        <p>Natural Disaster</p>
                    </button>
                    <button type="button" class="emergency-box" data-type="fire">
                        <span class="box-icon">🔥</span>
                        <p>Fire</p>
                    </button>
                    <button type="button" class="emergency-box" data-type="other">
                        <span class="box-icon">⚡</span>
                        <p>Another Emergency</p>
                    </button>
                </div>

                <!-- Location -->
                <div class="location-alert">
                    <span class="la-icon">📍</span>
                    <div>
                        <strong>Browser Geolocation API</strong>
                        <span id="locationStatus" aria-live="polite">Location is requested when you submit. You can also test it now.</span>
                        <button type="button" class="location-detect-button" id="detectLocationButton">Detect my location</button>
                    </div>
                </div>
                <div class="form-field">
                    <label for="location_address">Location <span class="optional-tag">(Required if GPS is unavailable)</span></label>
                    <input type="text" name="address" id="location_address" maxlength="255"
                           placeholder="Enter a nearby address if GPS is unavailable">
                </div>

                <!-- Additional Details -->
                <div class="form-field">
                    <label>Additional Details</label>
                    <textarea name="additional_details" rows="4" placeholder="Describe the emergency situation..."></textarea>
                </div>

                <!-- Upload -->
                <div class="form-field">
                    <label>Upload photos/videos <span class="optional-tag">(Optional)</span></label>

                    <!-- Drop Zone -->
                    <div class="upload-zone" id="uploadZone">
                        <input
                            type="file"
                            name="file_uploads[]"
                            id="file_upload"
                            multiple
                            accept="image/*,video/*"
                            class="upload-input"
                        >
                        <div class="upload-zone-inner" id="uploadPrompt">
                            <div class="uz-icon"><i class="ri-upload-cloud-2-line"></i></div>
                            <p class="uz-main">Drag & drop files here, or <span class="uz-browse">browse</span></p>
                            <p class="uz-sub">Supports JPG, PNG, GIF, MP4, MOV &nbsp;·&nbsp; Max 10 MB per file &nbsp;·&nbsp; Up to 5 files</p>
                        </div>
                    </div>

                    <!-- Preview Grid -->
                    <div class="preview-grid" id="previewGrid"></div>

                    <!-- File count indicator -->
                    <p class="file-count" id="fileCount" style="display:none;"></p>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">SUBMIT</button>

            </form>
        </div>

    </div>

</div>

<?php require_once VIEW_PATH . 'includes/footer.php'; ?>
<script src="<?php echo ASSETS_PATH . 'js/report-system.js?v=' . filemtime(ROOT_PATH . 'assets/js/report-system.js'); ?>"></script>