<?php
/**
 * Daily Check-in API
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$userId = getUserId();

// Prepare check-in data
$checkinData = [
    'mood' => (int)($_POST['mood'] ?? 3),
    'energy_level' => sanitizeInput($_POST['energy_level'] ?? 'okay'),
    'pain_level' => (int)($_POST['pain_level'] ?? 0),
    'pain_locations' => [],
    'appetite' => sanitizeInput($_POST['appetite'] ?? 'fair'),
    'hydration_cups' => (int)($_POST['hydration_cups'] ?? 0),
    'symptoms' => [],
    'activity_level' => sanitizeInput($_POST['activity_level'] ?? 'light'),
    'sleep_quality' => sanitizeInput($_POST['sleep_quality'] ?? null),
    'notes' => sanitizeInput($_POST['mood_notes'] ?? ''),
    'daily_highlight' => sanitizeInput($_POST['daily_highlight'] ?? ''),
    'anxiety_or_worry' => isset($_POST['anxiety_or_worry']) ? 1 : 0,
    'photos' => []
];

// Validate data
$errors = [];

if ($checkinData['mood'] < 1 || $checkinData['mood'] > 5) {
    $errors[] = 'Invalid mood value';
}

if ($checkinData['pain_level'] < 0 || $checkinData['pain_level'] > 10) {
    $errors[] = 'Invalid pain level';
}

if (!in_array($checkinData['energy_level'], ['high', 'okay', 'low', 'exhausted'])) {
    $errors[] = 'Invalid energy level';
}

if (!in_array($checkinData['appetite'], ['good', 'fair', 'poor'])) {
    $errors[] = 'Invalid appetite value';
}

if (!in_array($checkinData['activity_level'], ['none', 'light', 'moderate', 'active'])) {
    $errors[] = 'Invalid activity level';
}

if ($checkinData['hydration_cups'] < 0 || $checkinData['hydration_cups'] > 20) {
    $errors[] = 'Invalid hydration amount';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Validation errors: ' . implode(', ', $errors)]);
    exit;
}

// Save the check-in
if (saveDailyCheckin($userId, $checkinData)) {
    // Check for urgent conditions
    $urgentConditions = [];
    
    if ($checkinData['pain_level'] >= 8) {
        $urgentConditions[] = 'High pain level reported';
    }
    
    if ($checkinData['mood'] <= 2) {
        $urgentConditions[] = 'Low mood reported';
    }
    
    if ($checkinData['hydration_cups'] < 4) {
        $urgentConditions[] = 'Low hydration';
    }
    
    if ($checkinData['energy_level'] === 'exhausted') {
        $urgentConditions[] = 'Extreme fatigue reported';
    }
    
    // If urgent conditions detected, flag for caregiver review
    if (!empty($urgentConditions)) {
        // This would typically send notifications to caregivers
        logError("Urgent check-in conditions for user $userId: " . implode(', ', $urgentConditions), 'WARNING');
    }
    
    // Calculate streak and provide encouragement
    $streak = calculateCheckinStreak($userId);
    $encouragement = generateEncouragement($checkinData, $streak);
    
    echo json_encode([
        'success' => true,
        'message' => 'Check-in saved successfully',
        'streak' => $streak,
        'encouragement' => $encouragement,
        'urgent_conditions' => $urgentConditions
    ]);
    
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save check-in']);
}

/**
 * Calculate check-in streak
 */
function calculateCheckinStreak($userId) {
    $db = Database::getInstance()->getConnection();
    
    try {
        $stmt = $db->prepare("
            SELECT COUNT(*) as streak
            FROM daily_checkins 
            WHERE user_id = ? 
            AND checkin_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ORDER BY checkin_date DESC
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        
        return $result['streak'] ?? 0;
        
    } catch (PDOException $e) {
        logError("Error calculating streak: " . $e->getMessage(), 'ERROR');
        return 0;
    }
}

/**
 * Generate encouragement message
 */
function generateEncouragement($checkinData, $streak) {
    $messages = [];
    
    if ($streak >= 7) {
        $messages[] = "Amazing! You've been consistent with check-ins for $streak days! 🎉";
    } elseif ($streak >= 3) {
        $messages[] = "Great job keeping up with your check-ins! $streak days strong! 💪";
    }
    
    if ($checkinData['hydration_cups'] >= 8) {
        $messages[] = "Excellent hydration today! Your body will thank you! 💧";
    }
    
    if ($checkinData['mood'] >= 4) {
        $messages[] = "So glad to see you're feeling positive today! 😊";
    }
    
    if ($checkinData['activity_level'] === 'active' || $checkinData['activity_level'] === 'moderate') {
        $messages[] = "Way to stay active! Movement is healing! 🏃‍♀️";
    }
    
    if ($checkinData['pain_level'] <= 3) {
        $messages[] = "It's wonderful that your pain is manageable today! 🙏";
    }
    
    if (empty($messages)) {
        $messages[] = "Thank you for checking in. Every day is a step forward! ❤️";
    }
    
    return $messages;
}
?>