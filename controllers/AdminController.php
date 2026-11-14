<?php

class AdminController  {
    // Shared data for header and footer
    protected $navItems;
    protected $userMenuItems;
    protected $footerLinks;
    protected $footerResources;
    protected $footerHotline;

    /**
     * Constructor - Initialize shared data
     */
    public function __construct()
    {
        $this->initSharedData();
    }

    /**
     * Initialize shared header/footer data
     */
    private function initSharedData()
    {
        // Header navigation items
        $this->navItems = [
            ['action' => 'admin-dashboard','label' => 'Home'],
            ['action' => 'users', 'label' => 'Users Needing Help'],
            ['action' => 'responders', 'label' => 'Responders'],
            ['action' => 'admin-reports', 'label' => 'Reports'],
            ['action' => 'admin-chart-data', 'label' => 'Chart Data'],
            ['action' => 'admin-settings', 'label' => 'Settings'],
        ];

        // Footer quick links
        $this->footerLinks = [
            ['action' => 'admin-dashboard','label' => 'Home'],
            ['action' => 'users', 'label' => 'Users Needing Help'],
            ['action' => 'responders', 'label' => 'Responders'],
        ];

        // Footer resources links
        $this->footerResources = [
            ['label' => 'First Aid', 'href' => '#'],
            ['label' => 'Documentation', 'href' => '#'],
            ['label' => 'FAQ', 'href' => '#'],
            ['label' => 'Support', 'href' => '#'],
        ];

        // Footer social links
        $this->footerHotline = [
            ['label' => 'LDRRMO 0951 682 1504', 'href' => '#'],
            ['label' => 'MHO Isabela 0963 156 6032', 'href' => '#'],
            ['label' => 'ILASMDH 0947 415 4761', 'href' => '#'],
            ['label' => 'PNP Isabela 0999 659 0677', 'href' => '#'],
            ['label' => 'NOCECO 0998 570 2725', 'href' => '#'],
            ['label' => 'BFP (Bureau of Fire Protection) 0970 465 9383', 'href' => '#'],
        ];
    }

    /**
     * Get shared data for views
     */
    protected function getSharedData()
    {
        return [
            'navItems' => $this->navItems,
            'userMenuItems' => $this->userMenuItems,
            'footerLinks' => $this->footerLinks,
            'footerResources' => $this->footerResources,
            'footerHotline' => $this->footerHotline,
            'currentAction' => $_GET['action'] ?? 'dashboard',
        ];
    }

    /**
     * Get all emergency reports for admin
     */
    private function getAllEmergencies() {
        try {
            $pdo = db();
            $stmt = $pdo->prepare("
                SELECT er.*, u.first_name, u.last_name, u.phone
                FROM emergency_reports er
                JOIN users u ON er.user_id = u.id
                ORDER BY er.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching emergencies: " . $e->getMessage());
            return [];
        }
    }

    private function getEmergencyMapReports(): array
    {
        try {
            $stmt = db()->query(
                "SELECT er.report_code, er.emergency_type, er.severity, er.status, er.description,
                        er.location, er.latitude, er.longitude, er.created_at,
                        u.first_name, u.last_name
                 FROM emergency_reports er
                 JOIN users u ON u.id = er.user_id
                 WHERE er.latitude IS NOT NULL AND er.longitude IS NOT NULL
                   AND er.status <> 'fake'
                 ORDER BY er.created_at DESC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching emergency map reports: " . $e->getMessage());
            return [];
        }
    }

    public function adminDashboard() {
        $pageTitle = "Admin Dashboard - BuligDiretso";
        // Sample emergency data
        $emergencies = [
            ['id' => 'ER-A373K', 'status' => 'CRITICAL', 'badge' => 'Responding', 'name' => 'Carlos Mendoza', 'type' => 'Medical', 'time' => '2024-02-08 13:45', 'location' => 'Makati City', 'details' => ['Chest pain', 'Difficulty breathing', 'Patient conscious but in severe pain'], 'assign' => 'Assigned to', 'responder' => ': Kim Taehyung'],
            ['id' => 'ER-A373K', 'status' => 'MODERATE', 'badge' => 'Responding', 'name' => 'Carlos Mendoza', 'type' => 'Fire', 'time' => '2024-02-08 13:30', 'location' => 'Quezon City', 'details' => ['Kitchen fire', 'Smoke detected', 'Residents evacuating'], 'assign' => 'Assigned to', 'responder' => ': Janelle Ba-al'],
            ['id' => 'ER-A373K', 'status' => 'CRITICAL', 'badge' => 'Responding', 'name' => 'Carlos Mendoza', 'type' => 'Medical', 'time' => '2024-02-08 13:20', 'location' => 'Manila', 'details' => ['Car accident', 'Multiple injuries', 'Road blocked, require police assist'], 'assign' => 'Assigned to', 'responder' => ': Jeon Jungkook'],
            ['id' => 'ER-A373K', 'status' => 'RESOLVED', 'badge' => 'Pending', 'name' => 'Carlos Mendoza', 'type' => 'Medical', 'time' => '2024-02-08 13:10', 'location' => 'Pasig', 'details' => ['Resolved', 'Patient stabilized'], 'assign' => 'Assign Responders', 'responder' => ''    ]
        ];

        // Shared header/footer data
        extract($this->getSharedData());

        require_once VIEW_PATH . 'admin-dashboard.php';
    }

    public function usersNeedingHelp() {
        $pageTitle = "Users Needing Help - BuligDiretso";

        // Fetch emergency reports from database
        $emergencies = $this->getAllEmergencies();

        // Shared header/footer data
        extract($this->getSharedData());

        require_once VIEW_PATH . 'admin-users-needing-help.php';
    }
    public function responders() {
        $pageTitle = "Responders - BuligDiretso";
        extract($this->getSharedData());
        require_once VIEW_PATH . 'admin-responders.php';
    }

    public function adminReports() {
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            redirect('index.php?action=login');
        }

        $pageTitle = "Reports & Analytics - BuligDiretso";
        $emergencyMapReports = $this->getEmergencyMapReports();
        extract($this->getSharedData());
        require_once VIEW_PATH . 'admin-reports.php';
    }

    public function adminSettings() {
        $pageTitle = "System Settings - BuligDiretso";
        extract($this->getSharedData());
        require_once VIEW_PATH . 'admin-settings.php';
    }

    /**
     * Admin Chart Data editor page
     */
    public function adminChartData() {
        $pageTitle = "Chart Data Editor - BuligDiretso";
        require_once MODEL_PATH . 'chart_data.php';

        $grouped = ChartData::getDatasetsGrouped();

        // Attach points to each dataset
        foreach ($grouped as $chartKey => $datasets) {
            foreach ($datasets as &$ds) {
                $ds['points'] = ChartData::getPoints((int)$ds['id']);
            }
            $grouped[$chartKey] = $datasets;
        }

        extract($this->getSharedData());
        require_once VIEW_PATH . 'admin-chart-data.php';
    }

    /**
     * JSON API — returns Chart.js-ready data for a parent_chart key.
     * GET ?action=chart-data-json&chart=monthly_volume
     */
    public function chartDataJson() {
        header('Content-Type: application/json');
        require_once MODEL_PATH . 'chart_data.php';
        $chart = trim($_GET['chart'] ?? '');
        if (!$chart) {
            echo json_encode(['error' => 'Missing chart parameter']);
            exit();
        }
        echo json_encode(ChartData::getChartJs($chart));
        exit();
    }

    /**
     * AJAX — save (replace) all data points for a dataset.
     * POST: dataset_id, rows (JSON array of {label, value, point_color})
     */
    public function saveChartPoints() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        require_once MODEL_PATH . 'chart_data.php';

        $dataset_id = (int)($_POST['dataset_id'] ?? 0);
        $rows_json  = $_POST['rows'] ?? '[]';

        if (!$dataset_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid dataset_id']);
            exit();
        }

        $rows = json_decode($rows_json, true);
        if (!is_array($rows)) {
            echo json_encode(['success' => false, 'message' => 'Invalid rows JSON']);
            exit();
        }

        // Basic sanitisation
        $clean = [];
        foreach ($rows as $row) {
            $label = trim($row['label'] ?? '');
            if ($label === '') continue;
            $clean[] = [
                'label'       => $label,
                'value'       => (float)($row['value'] ?? 0),
                'point_color' => !empty($row['point_color']) ? $row['point_color'] : null,
            ];
        }

        $ok = ChartData::replacePoints($dataset_id, $clean);
        echo json_encode(['success' => $ok]);
        exit();
    }

    /**
     * AJAX — update dataset meta (label, color, chart_type).
     */
    public function saveDatasetMeta() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        require_once MODEL_PATH . 'chart_data.php';

        $id             = (int)($_POST['id'] ?? 0);
        $dataset_label  = trim($_POST['dataset_label'] ?? '');
        $color          = trim($_POST['color'] ?? '#E74C3C');
        $chart_type     = trim($_POST['chart_type'] ?? 'bar');

        if (!$id || !$dataset_label) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit();
        }

        $ok = ChartData::updateDataset($id, [
            'dataset_label' => $dataset_label,
            'color'         => $color,
            'chart_type'    => $chart_type,
        ]);
        echo json_encode(['success' => $ok]);
        exit();
    }

    /**
     * POST params: report_code, action ('verify' | 'fake')
     */
    public function verifyEmergency() {
        header('Content-Type: application/json');

        // Must be logged-in admin
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        $report_code = trim($_POST['report_code'] ?? '');
        $action      = trim($_POST['action'] ?? '');   // 'verify' or 'fake'

        if (!$report_code || !in_array($action, ['verify', 'fake'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit();
        }

        $new_status = ($action === 'verify') ? 'pending' : 'fake';

        try {
            $pdo  = db();
            $stmt = $pdo->prepare(
                "UPDATE emergency_reports SET status = ?, updated_at = NOW() WHERE report_code = ?"
            );
            $stmt->execute([$new_status, $report_code]);
            echo json_encode(['success' => true, 'status' => $new_status]);
        } catch (Exception $e) {
            error_log("Verify emergency error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
        exit();
    }

    /**
     * POST params: report_code
     */
    public function resolveEmergency(): void
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        $reportCode = trim($_POST['report_code'] ?? '');
        if ($reportCode === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing report code']);
            exit();
        }

        try {
            $pdo = db();
            $stmt = $pdo->prepare(
                "SELECT er.id, er.report_code, er.emergency_type, er.severity, er.description, er.location,
                        er.created_at, u.first_name, u.last_name, u.email
                 FROM emergency_reports er
                 JOIN users u ON u.id = er.user_id
                 WHERE er.report_code = ? AND er.status IN ('pending', 'dispatched', 'en_route', 'on_scene')
                 LIMIT 1"
            );
            $stmt->execute([$reportCode]);
            $report = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$report) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Report not found or already resolved']);
                exit();
            }

            $update = $pdo->prepare(
                "UPDATE emergency_reports SET status = 'resolved', updated_at = NOW()
                 WHERE id = ? AND status IN ('pending', 'dispatched', 'en_route', 'on_scene')"
            );
            $update->execute([(int)$report['id']]);
            if ($update->rowCount() !== 1) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Report status changed; refresh and try again']);
                exit();
            }

            require_once ROOT_PATH . 'services/SendGridService.php';
            $reporterName = trim($report['first_name'] . ' ' . $report['last_name']);
            $resolvedAt = date('Y-m-d H:i:s');
            $details = $report['description'] ?: 'No additional details provided.';
            $summary = "Hello {$reporterName},\n\n"
                . "Your emergency report has been resolved.\n"
                . "Reference: {$report['report_code']}\nType: {$report['emergency_type']}\n"
                . "Priority: {$report['severity']}\nLocation: {$report['location']}\n"
                . "Reported: {$report['created_at']}\nResolved: {$resolvedAt}\n"
                . "Details: {$details}";
            $userEmailResult = SendGridService::send(
                $report['email'],
                'Emergency report resolved — ' . $report['report_code'],
                $summary
            );
            $adminEmailResult = SendGridService::send(
                SENDGRID_ADMIN_EMAIL,
                'Emergency resolution summary — ' . $report['report_code'],
                "Emergency report resolution summary\n"
                    . "Reference: {$report['report_code']}\nReporter: {$reporterName}\n"
                    . "Type: {$report['emergency_type']}\nPriority: {$report['severity']}\n"
                    . "Location: {$report['location']}\nReported: {$report['created_at']}\n"
                    . "Resolved: {$resolvedAt}\nDetails: {$details}"
            );

            $emailStatus = ($userEmailResult['success'] && $adminEmailResult['success'])
                ? 'Resolution emails sent.'
                : 'Report resolved, but one or more resolution emails failed. Check server logs.';
            echo json_encode(['success' => true, 'message' => $emailStatus]);
        } catch (Exception $e) {
            error_log('[AdminController] Resolve emergency error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Could not resolve the report']);
        }
        exit();
    }

    /**
     * Download reports as PDF using FPDF
     */
    public function downloadReportsPdf() {
        require_once ROOT_PATH . 'services/ReportPdfService.php';
        ReportPdfService::outputDownload();
    }
}