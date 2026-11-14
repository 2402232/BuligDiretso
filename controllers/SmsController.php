<?php
// controllers/SmsController.php — Admin SMS actions for BuligDiretso

// SmsService is already loaded by config.php, but require_once is safe to repeat
require_once ROOT_PATH . 'services/SmsService.php';
require_once ROOT_PATH . 'services/SendGridService.php';

class SmsController
{
    // NOTE: No session_start() needed here.
    // config/config.php (loaded first by index.php) already calls session_start().
    // Adding a second session_start() would cause a headers-already-sent warning.

    /**
     * POST /index.php?action=sms-assign-responder
     * Called from the Assign Responder modal.
     * Sends user assignment update, responder alert, and optional follow-up SMS.
     */
    public function assignResponder(): void
    {
        $this->requireAdmin();

        $emergencyId    = trim($_POST['emergency_id']     ?? '');
        $userPhone      = trim($_POST['user_phone']       ?? '');
        $userName       = trim($_POST['user_name']        ?? '');
        $location       = trim($_POST['location']         ?? '');
        $emergencyType  = trim($_POST['emergency_type']   ?? 'Emergency');
        $responderName  = trim($_POST['responder_name']   ?? '');
        $responderPhone = trim($_POST['responder_phone']  ?? '');
        $etaRaw         = trim($_POST['eta_minutes']      ?? '10');
        $etaCustom      = trim($_POST['eta_custom']       ?? '');
        $sendFollowup   = !empty($_POST['send_followup']);
        $followupMsg    = trim($_POST['followup_message'] ?? '');

        if ($emergencyId === '' || $responderName === '') {
            $_SESSION['sms_error'] = 'Missing required assignment details.';
            $this->back();
            return;
        }

        try {
            $pdo = db();
            $reportStmt = $pdo->prepare(
                "SELECT er.id, er.report_code, er.emergency_type, er.severity, er.status, er.description, er.location,
                        u.first_name, u.last_name, u.email, u.phone
                 FROM emergency_reports er
                 JOIN users u ON u.id = er.user_id
                 WHERE er.report_code = ?
                 LIMIT 1"
            );
            $reportStmt->execute([$emergencyId]);
            $report = $reportStmt->fetch(PDO::FETCH_ASSOC);

            if (!$report || !in_array($report['status'], ['pending', 'pending_verification', 'dispatched'], true)) {
                $_SESSION['sms_error'] = 'This report could not be found or is no longer available for assignment.';
                $this->back();
                return;
            }

            $userName = trim($report['first_name'] . ' ' . $report['last_name']);
            $userPhone = $report['phone'];
            $userEmail = $report['email'];
            $location = $report['location'];
            $emergencyType = $report['emergency_type'] . ' emergency';

            $updateStmt = $pdo->prepare(
                "UPDATE emergency_reports
                 SET status = 'dispatched', updated_at = NOW()
                 WHERE id = ? AND status IN ('pending', 'pending_verification', 'dispatched')"
            );
            $updateStmt->execute([(int)$report['id']]);
        } catch (Exception $e) {
            error_log('[SmsController] Assignment database error: ' . $e->getMessage());
            $_SESSION['sms_error'] = 'The responder could not be assigned because the report could not be updated.';
            $this->back();
            return;
        }

        // Build ETA string
        if ($etaRaw === 'custom' && $etaCustom !== '') {
            $eta = $etaCustom . ' minutes';
        } elseif (is_numeric($etaRaw) && (int)$etaRaw >= 60) {
            $eta = '1 hour';
        } else {
            $eta = $etaRaw . ' minutes';
        }

        // SMS 1: Notify the user their responder is on the way
        $userMsg = "BuligDiretso: Hi {$userName}, your {$emergencyType} report (#{$emergencyId}) has been received. "
                 . "Responder {$responderName} is now ON THE WAY to your location at {$location}. "
                 . "Estimated arrival: {$eta}. Please stay calm and keep your phone on. Stay safe!";

        $userResult = SmsService::send(SmsService::formatPhone($userPhone), $userMsg);

        // SMS 2: Alert the responder with assignment details
        if ($responderPhone !== '') {
            $respMsg = "BuligDiretso ALERT: You have been assigned to {$emergencyType} #{$emergencyId} at {$location}. "
                     . "User: {$userName} ({$userPhone}). Please respond immediately.";
            $responderResult = SmsService::send(SmsService::formatPhone($responderPhone), $respMsg);
        } else {
            $responderResult = ['success' => false, 'message' => 'Responder phone is blank.'];
        }

        // SMS 3: Optional follow-up (clinic reminder for pregnancy/medical cases)
        if ($sendFollowup && $followupMsg !== '') {
            SmsService::send(SmsService::formatPhone($userPhone), $followupMsg);
        }

        $assignmentEmail = "Hello {$userName},\n\n"
            . "A responder has been assigned to your emergency report.\n"
            . "Reference: {$emergencyId}\nType: {$emergencyType}\nResponder: {$responderName}\n"
            . "Estimated arrival: {$eta}\nLocation: {$location}\n\nPlease keep your phone available.";
        $userEmailResult = SendGridService::send(
            $userEmail,
            'Responder assigned — ' . $emergencyId,
            $assignmentEmail
        );
        $adminEmailResult = SendGridService::send(
            SENDGRID_ADMIN_EMAIL,
            'Responder assigned — ' . $emergencyId,
            "Responder {$responderName} was assigned to report {$emergencyId}.\n"
                . "Reporter: {$userName}\nType: {$emergencyType}\nLocation: {$location}\nETA: {$eta}"
        );

        // Build feedback message — assignment succeeds regardless of SMS outcome
        $msg = "Responder {$responderName} assigned successfully for case #{$emergencyId}.";
        $msg .= $userResult['success']
            ? ' User SMS sent.'
            : " User SMS failed: {$userResult['message']}.";
        $msg .= $responderResult['success']
            ? ' Responder SMS sent.'
            : " Responder SMS failed: {$responderResult['message']}.";
        $msg .= $userEmailResult['success']
            ? ' User email sent.'
            : " User email failed: {$userEmailResult['message']}.";
        $msg .= $adminEmailResult['success']
            ? ' Admin email sent.'
            : " Admin email failed: {$adminEmailResult['message']}.";

        $_SESSION['sms_success'] = $msg;
        $this->back();
    }

    /**
     * Verify the logged-in user is an admin.
     * Redirects to login page instead of showing a raw 403 — keeps UX clean
     * and prevents the "Forbidden" dead-end the old version showed.
     *
     * Session key is 'user_role' — set by AuthController::setUserSession().
     */
    private function requireAdmin(): void
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . 'index.php?action=login');
            exit();
        }
    }

    /**
     * Redirect back to the referring page, or fall back to the users list.
     */
    private function back(): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . 'index.php?action=users');
        header('Location: ' . $ref);
        exit();
    }
}