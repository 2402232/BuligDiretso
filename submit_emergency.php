<?php
// submit_emergency.php - Handle emergency report submission

require_once __DIR__ . '/config/config.php';
require_once ROOT_PATH . 'services/SendGridService.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "index.php?action=login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "index.php?action=report-system");
    exit();
}

$user_id = $_SESSION['user_id'];
$emergency_type = $_POST['emergency_type'] ?? '';
$severity = $_POST['severity'] ?? 'moderate';
$description = $_POST['additional_details'] ?? '';
$location = trim($_POST['address'] ?? '');
$latitude = filter_var($_POST['latitude'] ?? null, FILTER_VALIDATE_FLOAT);
$longitude = filter_var($_POST['longitude'] ?? null, FILTER_VALIDATE_FLOAT);

$allowed_types = ['medical', 'accident', 'animal', 'disaster', 'fire', 'other'];
$allowed_severities = ['minor', 'moderate', 'high', 'critical'];
if (!in_array($emergency_type, $allowed_types, true) || !in_array($severity, $allowed_severities, true)) {
    $_SESSION['error'] = 'Please select a valid emergency type and priority.';
    header("Location: " . BASE_URL . "index.php?action=report-system");
    exit();
}

if ($latitude === false || $latitude < -90 || $latitude > 90) {
    $latitude = null;
}
if ($longitude === false || $longitude < -180 || $longitude > 180) {
    $longitude = null;
}
if ($location === '') {
    $location = ($latitude !== null && $longitude !== null)
        ? sprintf('GPS coordinates: %.7f, %.7f', $latitude, $longitude)
        : 'GPS location unavailable';
}
$location = substr($location, 0, 255);

// Generate report code
$report_code = 'ER-' . strtoupper(substr(md5(uniqid()), 0, 6));

try {
    $pdo = db();

    // Insert emergency report
    $stmt = $pdo->prepare("
        INSERT INTO emergency_reports
        (report_code, user_id, emergency_type, severity, status, description, location, latitude, longitude, created_at, updated_at)
        VALUES (?, ?, ?, ?, 'pending_verification', ?, ?, ?, ?, NOW(), NOW())
    ");

    $stmt->execute([
        $report_code,
        $user_id,
        $emergency_type,
        $severity,
        $description,
        $location,
        $latitude,
        $longitude
    ]);

    $report_id = $pdo->lastInsertId();

    $userStmt = $pdo->prepare('SELECT first_name, last_name, email FROM users WHERE id = ?');
    $userStmt->execute([$user_id]);
    $reporter = $userStmt->fetch(PDO::FETCH_ASSOC);
    $userEmailResult = ['success' => false];
    if ($reporter && filter_var($reporter['email'], FILTER_VALIDATE_EMAIL)) {
        $reporterName = trim($reporter['first_name'] . ' ' . $reporter['last_name']);
        $reportDetails = $description !== '' ? $description : 'No additional details provided.';
        $confirmation = "Hello {$reporterName},\n\n"
            . "Your emergency report has been received and is awaiting verification.\n"
            . "Reference: {$report_code}\nType: {$emergency_type}\nPriority: {$severity}\n"
            . "Location: {$location}\nDetails: {$reportDetails}";
        $userEmailResult = SendGridService::send(
            $reporter['email'],
            'Emergency report received — ' . $report_code,
            $confirmation
        );
    }

    $adminSummary = "A new emergency report has been submitted.\n"
        . "Reference: {$report_code}\nReporter: " . ($reporter['first_name'] ?? 'Unknown')
        . ' ' . ($reporter['last_name'] ?? '') . "\nType: {$emergency_type}\nPriority: {$severity}\n"
        . "Location: {$location}\nDetails: " . ($description !== '' ? $description : 'No additional details provided.');
    $adminEmailResult = SendGridService::send(
        SENDGRID_ADMIN_EMAIL,
        'New emergency report — ' . $report_code,
        $adminSummary
    );

    // Handle file uploads
    if (!empty($_FILES['file_uploads']['name'][0])) {
        $upload_dir = __DIR__ . '/uploads/emergency_reports/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        foreach ($_FILES['file_uploads']['tmp_name'] as $key => $tmp_name) {
            if (!empty($tmp_name)) {
                $file_name = $_FILES['file_uploads']['name'][$key];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov'];

                if (in_array($file_ext, $allowed_exts)) {
                    $new_file_name = $report_code . '_' . $key . '.' . $file_ext;
                    $file_path = $upload_dir . $new_file_name;

                    if (move_uploaded_file($tmp_name, $file_path)) {
                        // In a real app, you'd store file paths in a separate table
                        // For now, just log the upload
                        error_log("Uploaded file: " . $file_path);
                    }
                }
            }
        }
    }

    $_SESSION['success'] = "Emergency report submitted successfully! Report code: " . $report_code;
    if (!$userEmailResult['success'] || !$adminEmailResult['success']) {
        $_SESSION['success'] .= ' Report saved, but one or more notification emails could not be delivered. Please contact an administrator.';
    }
    header("Location: " . BASE_URL . "index.php?action=emergency-tracking");
    exit();

} catch (Exception $e) {
    error_log("Emergency submission error: " . $e->getMessage());
    $_SESSION['error'] = "Failed to submit emergency report. Please try again.";
    header("Location: " . BASE_URL . "index.php?action=report-system");
    exit();
}
?>