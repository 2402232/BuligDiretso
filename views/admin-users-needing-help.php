<link rel="stylesheet" href="<?php echo ASSETS_PATH . 'css/admin-users-needing-help.css?v=' . filemtime(ROOT_PATH . 'assets/css/admin-users-needing-help.css'); ?>">
<link rel="stylesheet" href="<?php echo ASSETS_PATH . 'css/assign-modal.css'; ?>">

<?php require_once VIEW_PATH . 'includes/header.php'; ?>

<div class="admin-wrapper">
    <?php if (!empty($_SESSION['sms_success'])): ?>
        <div class="sms-flash success" role="status">
            <?php echo htmlspecialchars($_SESSION['sms_success']); ?>
        </div>
        <?php unset($_SESSION['sms_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['sms_error'])): ?>
        <div class="sms-flash error" role="alert">
            <?php echo htmlspecialchars($_SESSION['sms_error']); ?>
        </div>
        <?php unset($_SESSION['sms_error']); ?>
    <?php endif; ?>

    <div class="admin-page-header">
        <div class="admin-page-heading">
            <span class="admin-page-icon"><i class="ri-shield-line"></i></span>
            <div>
                <h1>Users Needing Help</h1>
                <p>Review emergency reports and coordinate responder assignments.</p>
            </div>
        </div>
        <div class="report-total">
            <strong><?php echo count($emergencies); ?></strong>
            <span>total reports</span>
        </div>
    </div>

    <section class="dashboard-card">
        <div class="emergency-toolbar">
            <div>
                <h2>Emergency Reports</h2>
                <p>Prioritize verification and response actions.</p>
            </div>
            <div class="filter-buttons" aria-label="Filter emergency reports">
                <button type="button" class="filter-btn active">All Emergencies</button>
                <button type="button" class="filter-btn">Critical</button>
                <button type="button" class="filter-btn">Pending Assignment</button>
                <button type="button" class="filter-btn">Responding</button>
            </div>
        </div>

        <div class="emergency-list">
            <?php if (empty($emergencies)): ?>
                <div class="no-emergencies">
                    <i class="ri-checkbox-circle-line"></i>
                    <strong>No emergency reports found</strong>
                    <span>New reports will appear here.</span>
                </div>
            <?php else: ?>
                <?php foreach ($emergencies as $emergency): ?>
                    <?php
                    $severity = strtolower((string)($emergency['severity'] ?? 'moderate'));
                    $status = strtolower((string)($emergency['status'] ?? 'pending'));
                    $statusClass = str_replace('_', '-', $status);
                    $isResponding = in_array($status, ['dispatched', 'en_route', 'on_scene'], true);
                    $fullName = trim(($emergency['first_name'] ?? '') . ' ' . ($emergency['last_name'] ?? ''));
                    $reportCode = (string)($emergency['report_code'] ?? '');
                    ?>
                    <article class="emergency-card" data-severity="<?php echo htmlspecialchars($severity, ENT_QUOTES, 'UTF-8'); ?>" data-status="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="card-header">
                            <div class="emergency-id">
                                <span class="badge badge-severity badge-<?php echo htmlspecialchars($severity, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars(strtoupper($severity)); ?>
                                </span>
                                <?php if ($status === 'pending_verification'): ?>
                                    <span class="badge badge-status badge-pending-verification badge-pending_verification badge-pending">Needs Verification</span>
                                <?php else: ?>
                                    <span class="badge badge-status badge-<?php echo htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?> <?php echo in_array($status, ['pending', 'dispatched', 'en_route', 'on_scene'], true) ? 'badge-pending' : ''; ?>">
                                        <?php echo htmlspecialchars(str_replace('_', ' ', strtoupper($status))); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-reference">
                                <span>Report reference</span>
                                <h3><?php echo htmlspecialchars($reportCode); ?></h3>
                            </div>
                            <div class="card-created">
                                <span>Submitted</span>
                                <time datetime="<?php echo htmlspecialchars((string)$emergency['created_at'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars(date('M j, Y · g:i A', strtotime($emergency['created_at']))); ?>
                                </time>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <i class="ri-user-line" aria-hidden="true"></i>
                                    <div>
                                        <span class="label">Reporter</span>
                                        <span class="value"><?php echo htmlspecialchars($fullName ?: 'Unknown reporter'); ?></span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <i class="ri-phone-line" aria-hidden="true"></i>
                                    <div>
                                        <span class="label">Contact number</span>
                                        <span class="value"><?php echo htmlspecialchars((string)($emergency['phone'] ?? 'Not provided')); ?></span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <i class="ri-alarm-warning-line" aria-hidden="true"></i>
                                    <div>
                                        <span class="label">Emergency type</span>
                                        <span class="value"><?php echo htmlspecialchars(ucfirst((string)$emergency['emergency_type'])); ?></span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <i class="ri-map-pin-line" aria-hidden="true"></i>
                                    <div>
                                        <span class="label">Location</span>
                                        <span class="value"><?php echo htmlspecialchars((string)$emergency['location']); ?></span>
                                        <?php if ($emergency['latitude'] !== null && $emergency['longitude'] !== null): ?>
                                            <span class="value info-coordinates">GPS <?php echo htmlspecialchars($emergency['latitude'] . ', ' . $emergency['longitude']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="info-item info-item-wide">
                                    <i class="ri-file-text-line" aria-hidden="true"></i>
                                    <div>
                                        <span class="label">Additional details</span>
                                        <span class="value"><?php echo htmlspecialchars($emergency['description'] ?: 'No additional details provided.'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="responder-section">
                                <span class="responder-label"><i class="ri-user-star-line" aria-hidden="true"></i> Responder</span>
                                <?php if ($isResponding): ?>
                                    <span class="responder-name">Response in progress; assignment details are not recorded.</span>
                                <?php else: ?>
                                    <span class="no-responder">Not assigned yet</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card-actions" data-report="<?php echo htmlspecialchars($reportCode, ENT_QUOTES, 'UTF-8'); ?>" data-status="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php if ($status === 'pending_verification'): ?>
                                <div class="verify-section">
                                    <p class="verify-prompt">Is this a real emergency?</p>
                                    <div class="verify-btns">
                                        <button type="button" class="btn btn-real" onclick="verifyEmergency('<?php echo htmlspecialchars($reportCode, ENT_QUOTES, 'UTF-8'); ?>', 'verify', this)">
                                            <i class="ri-checkbox-circle-line" aria-hidden="true"></i> Confirm real
                                        </button>
                                        <button type="button" class="btn btn-fake" onclick="verifyEmergency('<?php echo htmlspecialchars($reportCode, ENT_QUOTES, 'UTF-8'); ?>', 'fake', this)">
                                            <i class="ri-close-circle-line" aria-hidden="true"></i> Mark fake
                                        </button>
                                    </div>
                                </div>
                            <?php elseif ($status === 'fake'): ?>
                                <span class="fake-label"><i class="ri-spam-2-line" aria-hidden="true"></i> Marked as fake report</span>
                            <?php elseif ($status === 'resolved' || $status === 'cancelled'): ?>
                                <span class="resolved-label"><i class="ri-checkbox-circle-line" aria-hidden="true"></i> Report <?php echo htmlspecialchars($status); ?></span>
                            <?php else: ?>
                                <button
                                    type="button"
                                    class="btn btn-assign"
                                    data-report-code="<?php echo htmlspecialchars($reportCode, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-user-name="<?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-phone="<?php echo htmlspecialchars((string)$emergency['phone'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-location="<?php echo htmlspecialchars((string)$emergency['location'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-emergency-type="<?php echo htmlspecialchars(ucfirst((string)$emergency['emergency_type']) . ' Emergency', ENT_QUOTES, 'UTF-8'); ?>"
                                    data-followup-type="<?php echo htmlspecialchars($severity === 'critical' ? 'medical' : '', ENT_QUOTES, 'UTF-8'); ?>"
                                    onclick="openAssignModalFromButton(this)">
                                    <i class="ri-user-add-line" aria-hidden="true"></i> Assign responder
                                </button>
                                <button type="button" class="btn btn-real" onclick="resolveEmergency('<?php echo htmlspecialchars($reportCode, ENT_QUOTES, 'UTF-8'); ?>', this)">
                                    <i class="ri-checkbox-circle-line" aria-hidden="true"></i> Mark resolved
                                </button>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<div id="assignModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h2><i class="ri-user-star-line"></i> Assign Responder &amp; Send SMS</h2>
            <button type="button" class="modal-close" onclick="closeAssignModal()" aria-label="Close">✕</button>
        </div>
        <div class="modal-summary">
            <span id="modal-em-id" class="em-tag"></span>
            <span id="modal-em-type"></span>
        </div>
        <p class="modal-user-info">👤 <strong id="modal-user-name"></strong> | 📍 <span id="modal-user-loc"></span></p>

        <form method="POST" action="<?php echo BASE_URL; ?>index.php?action=sms-assign-responder">
            <input type="hidden" name="emergency_id" id="f-emergency-id">
            <input type="hidden" name="user_phone" id="f-user-phone">
            <input type="hidden" name="user_name" id="f-user-name">
            <input type="hidden" name="location" id="f-location">
            <input type="hidden" name="emergency_type" id="f-emergency-type">

            <div class="modal-section">
                <h4><i class="ri-user-star-line"></i> Responder Details</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="inp-responder-name">Responder Name</label>
                        <input type="text" name="responder_name" id="inp-responder-name" required>
                    </div>
                    <div class="form-group">
                        <label for="inp-responder-phone">Responder Phone</label>
                        <input type="text" name="responder_phone" id="inp-responder-phone" placeholder="09171234567 (optional)">
                    </div>
                </div>
            </div>

            <div class="modal-section">
                <h4><i class="ri-time-line"></i> Estimated Time of Arrival</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="eta-select">ETA</label>
                        <select name="eta_minutes" id="eta-select" onchange="updateEtaLabel(this.value)">
                            <option value="5">5 minutes</option>
                            <option value="10" selected>10 minutes</option>
                            <option value="15">15 minutes</option>
                            <option value="20">20 minutes</option>
                            <option value="30">30 minutes</option>
                            <option value="45">45 minutes</option>
                            <option value="60">1 hour</option>
                            <option value="custom">Custom...</option>
                        </select>
                    </div>
                    <div class="form-group" id="custom-eta-group" style="display:none;">
                        <label for="eta-custom">Custom (minutes)</label>
                        <input type="number" name="eta_custom" id="eta-custom" min="1" max="240" placeholder="25">
                    </div>
                </div>
            </div>

            <div class="modal-section">
                <h4><i class="ri-message-2-line"></i> SMS Preview</h4>
                <div class="sms-preview" id="sms-preview-box"></div>
            </div>

            <div class="modal-section" id="followup-section" style="display:none;">
                <h4><i class="ri-hospital-line"></i> Follow-up Clinic Reminder</h4>
                <label class="toggle-label">
                    <input type="checkbox" name="send_followup" id="send-followup-chk" onchange="toggleFollowupMsg()">
                    Send Barangay clinic follow-up SMS
                </label>
                <div id="followup-msg-area" style="display:none;margin-top:10px;">
                    <div class="form-group">
                        <label for="followup-msg-text">Follow-up Message</label>
                        <textarea name="followup_message" id="followup-msg-text" rows="4"></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeAssignModal()">Cancel</button>
                <button type="submit" class="btn-confirm"><i class="ri-send-plane-line"></i> Assign &amp; Send SMS</button>
            </div>
        </form>
    </div>
</div>

<?php require_once VIEW_PATH . 'includes/footer.php'; ?>
<script src="<?php echo ASSETS_PATH . 'js/admin-users-needing-help.js?v=' . filemtime(ROOT_PATH . 'assets/js/admin-users-needing-help.js'); ?>"></script>
<script>
let _md = {};
function openAssignModalFromButton(button) {
    openAssignModal(
        button.dataset.reportCode,
        button.dataset.userName,
        button.dataset.phone,
        button.dataset.location,
        button.dataset.emergencyType,
        Boolean(button.dataset.followupType),
        button.dataset.followupType
    );
}
function openAssignModal(id, name, phone, location, type, needsFollowup, followupType) {
    _md = { id, name, phone, location, type, needsFollowup, followupType };
    document.getElementById('f-emergency-id').value = id;
    document.getElementById('f-user-phone').value = phone;
    document.getElementById('f-user-name').value = name;
    document.getElementById('f-location').value = location;
    document.getElementById('f-emergency-type').value = type;
    document.getElementById('modal-em-id').textContent = id;
    document.getElementById('modal-em-type').textContent = type;
    document.getElementById('modal-user-name').textContent = name;
    document.getElementById('modal-user-loc').textContent = location;

    document.getElementById('followup-section').style.display = needsFollowup ? 'block' : 'none';
    if (needsFollowup) {
        const msgs = {
            pregnancy: `BuligDiretso: Hi ${name}, as a follow-up, please visit your Barangay Health Center for a prenatal check-up. Bring your records and follow your health worker's advice.`,
            children: `BuligDiretso: Hi ${name}, as a follow-up, your child may need a check-up at the Barangay Health Center within 24-48 hours.`,
            medical: `BuligDiretso: Hi ${name}, as a follow-up, please visit your Barangay Health Center within 24 hours for further assessment.`
        };
        document.getElementById('followup-msg-text').value = msgs[followupType] || msgs.medical;
    }
    updateSmsPreview();
    document.getElementById('assignModal').style.display = 'flex';
}
function closeAssignModal() { document.getElementById('assignModal').style.display = 'none'; }
function updateEtaLabel(val) {
    document.getElementById('custom-eta-group').style.display = val === 'custom' ? 'block' : 'none';
    updateSmsPreview();
}
function getEta() {
    const sel = document.getElementById('eta-select').value;
    if (sel === 'custom') {
        const v = document.getElementById('eta-custom').value;
        return v ? `${v} minutes` : '?';
    }
    return parseInt(sel, 10) >= 60 ? '1 hour' : `${sel} minutes`;
}
function updateSmsPreview() {
    const responder = document.getElementById('inp-responder-name').value || '[Responder Name]';
    const eta = getEta();
    document.getElementById('sms-preview-box').textContent =
        `BuligDiretso: Hi ${_md.name || ''}, your ${_md.type || 'emergency'} report (#${_md.id || ''}) has been received. ` +
        `Responder ${responder} is now ON THE WAY to your location at ${_md.location || ''}. ` +
        `Estimated arrival: ${eta}. Please stay calm and keep your phone on. Stay safe!`;
}
function toggleFollowupMsg() {
    document.getElementById('followup-msg-area').style.display =
        document.getElementById('send-followup-chk').checked ? 'block' : 'none';
}
document.addEventListener('input', function(e) {
    if (e.target.id === 'inp-responder-name' || e.target.id === 'eta-custom') updateSmsPreview();
});
</script>
