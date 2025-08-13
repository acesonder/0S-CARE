<?php
/**
 * Medications API
 * 0S-CARE - Cancer Patient Care Management System
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Require user to be logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$userId = getUserId();

if ($method === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid security token']);
        exit;
    }
    
    $medicationId = (int)($_POST['medication_id'] ?? 0);
    $status = sanitizeInput($_POST['status'] ?? '');
    $notes = sanitizeInput($_POST['notes'] ?? '');
    
    // Validate input
    if (!$medicationId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Medication ID required']);
        exit;
    }
    
    if (!in_array($status, ['taken', 'skipped', 'late', 'prn'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }
    
    // Verify user owns this medication
    if (!verifyMedicationOwnership($medicationId, $userId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }
    
    // Log the medication
    if (logMedication($medicationId, $userId, $status, $notes)) {
        // Check for adherence patterns
        $adherenceInfo = checkMedicationAdherence($medicationId, $userId);
        
        echo json_encode([
            'success' => true,
            'message' => 'Medication logged successfully',
            'adherence' => $adherenceInfo
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to log medication']);
    }
    
} elseif ($method === 'GET') {
    // Get medications for user
    $medications = getUserMedications($userId);
    echo json_encode(['success' => true, 'medications' => $medications]);
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

/**
 * Verify medication ownership
 */
function verifyMedicationOwnership($medicationId, $userId) {
    $db = Database::getInstance()->getConnection();
    
    try {
        $stmt = $db->prepare("SELECT id FROM medications WHERE id = ? AND user_id = ?");
        $stmt->execute([$medicationId, $userId]);
        return $stmt->fetch() !== false;
        
    } catch (PDOException $e) {
        logError("Error verifying medication ownership: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Get user medications
 */
function getUserMedications($userId) {
    $db = Database::getInstance()->getConnection();
    
    try {
        $stmt = $db->prepare("
            SELECT m.*, 
                   COUNT(ml.id) as logs_today,
                   MAX(ml.taken_at) as last_taken
            FROM medications m
            LEFT JOIN medication_logs ml ON m.id = ml.medication_id 
                AND DATE(ml.taken_at) = CURDATE()
                AND ml.status = 'taken'
            WHERE m.user_id = ? AND m.is_active = 1
            GROUP BY m.id
            ORDER BY m.name ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        logError("Error fetching medications: " . $e->getMessage(), 'ERROR');
        return [];
    }
}

/**
 * Check medication adherence
 */
function checkMedicationAdherence($medicationId, $userId) {
    $db = Database::getInstance()->getConnection();
    
    try {
        // Get last 7 days of logs
        $stmt = $db->prepare("
            SELECT DATE(taken_at) as log_date, status
            FROM medication_logs
            WHERE medication_id = ? AND user_id = ?
            AND taken_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY taken_at DESC
        ");
        $stmt->execute([$medicationId, $userId]);
        $logs = $stmt->fetchAll();
        
        $totalDays = 7;
        $takenDays = 0;
        $missedDays = 0;
        
        foreach ($logs as $log) {
            if ($log['status'] === 'taken') {
                $takenDays++;
            } elseif ($log['status'] === 'skipped') {
                $missedDays++;
            }
        }
        
        $adherenceRate = $totalDays > 0 ? ($takenDays / $totalDays) * 100 : 0;
        
        return [
            'rate' => round($adherenceRate, 1),
            'taken_days' => $takenDays,
            'missed_days' => $missedDays,
            'total_days' => $totalDays
        ];
        
    } catch (PDOException $e) {
        logError("Error checking adherence: " . $e->getMessage(), 'ERROR');
        return ['rate' => 0, 'taken_days' => 0, 'missed_days' => 0, 'total_days' => 7];
    }
}
?>