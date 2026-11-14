// report-system.js — BuligDiretso

// ── Emergency type selection ──────────────────────────────────
document.querySelectorAll('.emergency-box').forEach(function(box) {
    box.addEventListener('click', function() {
        document.querySelectorAll('.emergency-box').forEach(b => b.classList.remove('selected'));
        this.classList.add('selected');
        document.getElementById('emergency_type').value = this.getAttribute('data-type');
    });
});

// ── Priority level selection ──────────────────────────────────
document.querySelectorAll('.priority-box').forEach(function(box) {
    box.addEventListener('click', function() {
        document.querySelectorAll('.priority-box').forEach(b => b.classList.remove('selected'));
        this.classList.add('selected');
        document.getElementById('severity').value = this.getAttribute('data-severity');
    });
});

// ── File Upload with Preview ──────────────────────────────────
const uploadZone  = document.getElementById('uploadZone');
const fileInput   = document.getElementById('file_upload');
const previewGrid = document.getElementById('previewGrid');
const fileCount   = document.getElementById('fileCount');

const MAX_FILES   = 5;
const MAX_MB      = 10;
const MAX_BYTES   = MAX_MB * 1024 * 1024;

// We maintain our own file list because FileList is read-only
let selectedFiles = [];

// ── Drag & drop visual feedback ───────────────────────────────
uploadZone.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.classList.add('drag-over');
});

uploadZone.addEventListener('dragleave', function(e) {
    // Only remove if leaving the zone itself (not a child)
    if (!this.contains(e.relatedTarget)) {
        this.classList.remove('drag-over');
    }
});

uploadZone.addEventListener('drop', function(e) {
    e.preventDefault();
    this.classList.remove('drag-over');
    handleFiles(e.dataTransfer.files);
});

// ── File input change ─────────────────────────────────────────
fileInput.addEventListener('change', function() {
    handleFiles(this.files);
    // Reset input so the same file can be re-added after removal
    this.value = '';
});

// ── Handle incoming files ─────────────────────────────────────
function handleFiles(incoming) {
    const errors = [];

    Array.from(incoming).forEach(function(file) {
        // Check total cap
        if (selectedFiles.length >= MAX_FILES) {
            errors.push('Maximum ' + MAX_FILES + ' files allowed.');
            return;
        }

        // Duplicate check (by name + size)
        const isDupe = selectedFiles.some(f => f.name === file.name && f.size === file.size);
        if (isDupe) return;

        // Type check
        if (!file.type.startsWith('image/') && !file.type.startsWith('video/')) {
            errors.push('"' + file.name + '" is not a supported image or video file.');
            return;
        }

        // Size check
        if (file.size > MAX_BYTES) {
            errors.push('"' + file.name + '" exceeds the ' + MAX_MB + ' MB limit.');
            return;
        }

        selectedFiles.push(file);
    });

    if (errors.length > 0) {
        // Show first error only to avoid overwhelming
        showUploadError(errors[0]);
    }

    renderPreviews();
    updateCounter();
}

// ── Render preview grid ───────────────────────────────────────
function renderPreviews() {
    previewGrid.innerHTML = '';

    selectedFiles.forEach(function(file, index) {
        const item = document.createElement('div');
        item.className = 'preview-item';

        if (file.type.startsWith('image/')) {
            // Image preview with FileReader
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = file.name;

                const label = document.createElement('div');
                label.className = 'file-label';
                label.textContent = file.name;

                item.appendChild(img);
                item.appendChild(label);
            };
            reader.readAsDataURL(file);
        } else {
            // Video placeholder
            const thumb = document.createElement('div');
            thumb.className = 'video-thumb';
            thumb.innerHTML = '<i class="ri-video-line"></i><span>' + escapeHtml(file.name) + '</span>';
            item.appendChild(thumb);
        }

        // Remove button
        const removeBtn = document.createElement('button');
        removeBtn.className = 'remove-btn';
        removeBtn.type = 'button';
        removeBtn.innerHTML = '&#x2715;';
        removeBtn.title = 'Remove file';
        removeBtn.addEventListener('click', function() {
            removeFile(index);
        });

        item.appendChild(removeBtn);
        previewGrid.appendChild(item);
    });
}

// ── Remove a file by index ────────────────────────────────────
function removeFile(index) {
    selectedFiles.splice(index, 1);
    renderPreviews();
    updateCounter();
}

// ── File counter ──────────────────────────────────────────────
function updateCounter() {
    const count = selectedFiles.length;

    if (count === 0) {
        fileCount.style.display = 'none';
        return;
    }

    fileCount.style.display = 'block';

    let cls, msg;
    if (count < MAX_FILES) {
        cls = 'fc-ok';
        msg = count + ' file' + (count > 1 ? 's' : '') + ' selected — you can add up to ' + (MAX_FILES - count) + ' more.';
    } else {
        cls = 'fc-warn';
        msg = MAX_FILES + ' files selected (maximum reached).';
    }

    fileCount.innerHTML = '<span class="' + cls + '">' + msg + '</span>';
}

// ── Error flash ───────────────────────────────────────────────
function showUploadError(msg) {
    fileCount.style.display = 'block';
    fileCount.innerHTML = '<span class="fc-err"><i class="ri-error-warning-line"></i> ' + escapeHtml(msg) + '</span>';
    setTimeout(function() {
        updateCounter();
    }, 3500);
}

function escapeHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Capture browser location and submit the report ────────────
const emergencyForm = document.getElementById('emergencyForm');
const locationStatus = document.getElementById('locationStatus');
const detectLocationButton = document.getElementById('detectLocationButton');
let submittingReport = false;

function captureLocation(onComplete) {
    if (!navigator.geolocation) {
        locationStatus.textContent = 'Geolocation is not supported by this browser.';
        onComplete(false);
        return;
    }

    locationStatus.textContent = 'Requesting location permission…';
    navigator.geolocation.getCurrentPosition(function(position) {
        const latitude = position.coords.latitude.toFixed(7);
        const longitude = position.coords.longitude.toFixed(7);
        document.getElementById('latitude').value = latitude;
        document.getElementById('longitude').value = longitude;

        if (!document.getElementById('location_address').value.trim()) {
            document.getElementById('location_address').value = 'GPS coordinates: ' + latitude + ', ' + longitude;
        }
        locationStatus.textContent = 'Location captured: ' + latitude + ', ' + longitude;
        onComplete(true);
    }, function(error) {
        const reasons = {
            1: 'Location permission was denied.',
            2: 'Your location could not be determined.',
            3: 'Location request timed out.'
        };
        locationStatus.textContent = (reasons[error.code] || 'GPS location is unavailable.')
            + ' Enter a nearby address if needed.';
        onComplete(false);
    }, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
    });
}

detectLocationButton.addEventListener('click', function() {
    detectLocationButton.disabled = true;
    captureLocation(function() {
        detectLocationButton.disabled = false;
    });
});

emergencyForm.addEventListener('submit', function(e) {
    e.preventDefault();
    if (submittingReport) return;

    const type = document.getElementById('emergency_type').value;
    if (!type) {
        alert('Please select an emergency type!');
        return;
    }

    const btn = document.getElementById('submitBtn');
    submittingReport = true;
    btn.disabled = true;
    btn.textContent = 'Getting location…';

    function submitWithAvailableLocation() {
        const addressInput = document.getElementById('location_address');
        const hasCoordinates = document.getElementById('latitude').value
            && document.getElementById('longitude').value;
        if (!hasCoordinates && !addressInput.value.trim()) {
            locationStatus.textContent = 'GPS is unavailable. Enter a nearby address before submitting.';
            submittingReport = false;
            btn.disabled = false;
            btn.textContent = 'SUBMIT';
            addressInput.focus();
            return;
        }
        if (selectedFiles.length > 0) {
            if (typeof DataTransfer === 'function') {
                try {
                    const uploadTransfer = new DataTransfer();
                    selectedFiles.forEach(function(file) {
                        uploadTransfer.items.add(file);
                    });
                    fileInput.files = uploadTransfer.files;
                } catch (error) {
                    console.error('Could not prepare emergency report attachments:', error);
                    locationStatus.textContent = 'Attachments could not be prepared; submitting the report without them.';
                }
            } else {
                locationStatus.textContent = 'This browser cannot attach files; submitting the report without them.';
            }
        }
        emergencyForm.submit();
    }

    function finishSubmission() {
        submitWithAvailableLocation();
    }

    if (document.getElementById('latitude').value && document.getElementById('longitude').value) {
        finishSubmission();
    } else {
        captureLocation(finishSubmission);
    }
});