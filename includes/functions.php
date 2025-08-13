<?php
/**
 * Core Functions
 * 0S-CARE - Cancer Patient Care Management System
 */

require_once __DIR__ . '/../config/database.php';

/**
 * User Authentication Functions
 */
function authenticateUser($email, $password) {
    $db = Database::getInstance()->getConnection();
    
    try {
        $stmt = $db->prepare("
            SELECT id, username, email, password_hash, role, first_name, last_name, account_status 
            FROM users 
            WHERE email = ? AND account_status = 'active'
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Update last login
            $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            
            logError("User login successful: " . $user['email'], 'INFO');
            return true;
        }
        
        logError("Failed login attempt for: " . $email, 'WARNING');
        return false;
        
    } catch (PDOException $e) {
        logError("Authentication error: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

function registerUser($userData) {
    $db = Database::getInstance()->getConnection();
    
    try {
        // Check if email or username already exists
        $checkStmt = $db->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $checkStmt->execute([$userData['email'], $userData['username']]);
        
        if ($checkStmt->fetch()) {
            return ['success' => false, 'message' => 'Email or username already exists'];
        }
        
        // Hash password
        $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password_hash, role, first_name, last_name, 
                             phone, security_question, security_answer, date_of_birth, consent_given) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $securityAnswerHash = $userData['security_answer'] ? password_hash($userData['security_answer'], PASSWORD_DEFAULT) : null;
        
        $stmt->execute([
            $userData['username'],
            $userData['email'],
            $passwordHash,
            $userData['role'],
            $userData['first_name'],
            $userData['last_name'],
            $userData['phone'] ?? null,
            $userData['security_question'] ?? null,
            $securityAnswerHash,
            $userData['date_of_birth'] ?? null,
            $userData['consent_given'] ?? 0
        ]);
        
        $userId = $db->lastInsertId();
        
        logError("New user registered: " . $userData['email'], 'INFO');
        
        return ['success' => true, 'user_id' => $userId, 'message' => 'Account created successfully'];
        
    } catch (PDOException $e) {
        logError("Registration error: " . $e->getMessage(), 'ERROR');
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Dashboard Data Functions
 */
function getTodaysCareCard($userId) {
    $db = Database::getInstance()->getConnection();
    
    try {
        $data = [];
        
        // Next appointments
        $stmt = $db->prepare("
            SELECT title, appointment_date, provider_name, appointment_type 
            FROM appointments 
            WHERE user_id = ? AND appointment_date >= NOW() 
            ORDER BY appointment_date ASC 
            LIMIT 3
        ");
        $stmt->execute([$userId]);
        $data['appointments'] = $stmt->fetchAll();
        
        // Unread messages
        $stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM messages 
            WHERE recipient_id = ? AND is_read = 0
        ");
        $stmt->execute([$userId]);
        $data['unread_messages'] = $stmt->fetch()['count'];
        
        // Today's medications
        $stmt = $db->prepare("
            SELECT m.id, m.name, m.dosage, m.frequency,
                   COUNT(ml.id) as taken_today
            FROM medications m
            LEFT JOIN medication_logs ml ON m.id = ml.medication_id 
                AND DATE(ml.taken_at) = CURDATE() 
                AND ml.status = 'taken'
            WHERE m.user_id = ? AND m.is_active = 1
            GROUP BY m.id
        ");
        $stmt->execute([$userId]);
        $data['medications'] = $stmt->fetchAll();
        
        // Today's checkin status
        $stmt = $db->prepare("
            SELECT id FROM daily_checkins 
            WHERE user_id = ? AND checkin_date = CURDATE()
        ");
        $stmt->execute([$userId]);
        $data['checkin_completed'] = $stmt->fetch() ? true : false;
        
        return $data;
        
    } catch (PDOException $e) {
        logError("Error fetching care card data: " . $e->getMessage(), 'ERROR');
        return [];
    }
}

function getCaregiverDashboardData($caregiverId) {
    $db = Database::getInstance()->getConnection();
    
    try {
        $data = [];
        
        // Get linked patients
        $stmt = $db->prepare("
            SELECT u.id, u.first_name, u.last_name, ur.access_level,
                   dc.checkin_date, dc.mood, dc.pain_level
            FROM user_relationships ur
            JOIN users u ON ur.client_id = u.id
            LEFT JOIN daily_checkins dc ON u.id = dc.user_id AND dc.checkin_date = CURDATE()
            WHERE ur.caregiver_id = ? AND ur.status = 'active'
        ");
        $stmt->execute([$caregiverId]);
        $data['patients'] = $stmt->fetchAll();
        
        // Get urgent tasks
        $stmt = $db->prepare("
            SELECT t.*, u.first_name, u.last_name 
            FROM tasks t
            JOIN users u ON t.user_id = u.id
            JOIN user_relationships ur ON u.id = ur.client_id
            WHERE ur.caregiver_id = ? AND t.priority = 'urgent' AND t.status != 'completed'
            ORDER BY t.due_date ASC
        ");
        $stmt->execute([$caregiverId]);
        $data['urgent_tasks'] = $stmt->fetchAll();
        
        // Get missed medication alerts
        $stmt = $db->prepare("
            SELECT m.name, m.frequency, u.first_name, u.last_name, m.user_id
            FROM medications m
            JOIN users u ON m.user_id = u.id
            JOIN user_relationships ur ON u.id = ur.client_id
            LEFT JOIN medication_logs ml ON m.id = ml.medication_id AND DATE(ml.taken_at) = CURDATE()
            WHERE ur.caregiver_id = ? AND m.is_active = 1 AND ml.id IS NULL
        ");
        $stmt->execute([$caregiverId]);
        $data['missed_medications'] = $stmt->fetchAll();
        
        return $data;
        
    } catch (PDOException $e) {
        logError("Error fetching caregiver dashboard data: " . $e->getMessage(), 'ERROR');
        return [];
    }
}

/**
 * Daily Check-in Functions
 */
function saveDailyCheckin($userId, $checkinData) {
    $db = Database::getInstance()->getConnection();
    
    try {
        $stmt = $db->prepare("
            INSERT INTO daily_checkins 
            (user_id, checkin_date, mood, energy_level, pain_level, pain_locations, 
             appetite, hydration_cups, symptoms, activity_level, sleep_quality, 
             notes, daily_highlight, anxiety_or_worry, photos)
            VALUES (?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            mood = VALUES(mood), energy_level = VALUES(energy_level), 
            pain_level = VALUES(pain_level), pain_locations = VALUES(pain_locations),
            appetite = VALUES(appetite), hydration_cups = VALUES(hydration_cups),
            symptoms = VALUES(symptoms), activity_level = VALUES(activity_level),
            sleep_quality = VALUES(sleep_quality), notes = VALUES(notes),
            daily_highlight = VALUES(daily_highlight), anxiety_or_worry = VALUES(anxiety_or_worry),
            photos = VALUES(photos), updated_at = CURRENT_TIMESTAMP
        ");
        
        $stmt->execute([
            $userId,
            $checkinData['mood'],
            $checkinData['energy_level'],
            $checkinData['pain_level'],
            json_encode($checkinData['pain_locations'] ?? []),
            $checkinData['appetite'],
            $checkinData['hydration_cups'],
            json_encode($checkinData['symptoms'] ?? []),
            $checkinData['activity_level'],
            $checkinData['sleep_quality'] ?? null,
            $checkinData['notes'] ?? null,
            $checkinData['daily_highlight'] ?? null,
            $checkinData['anxiety_or_worry'] ?? 0,
            json_encode($checkinData['photos'] ?? [])
        ]);
        
        logError("Daily checkin saved for user: " . $userId, 'INFO');
        return true;
        
    } catch (PDOException $e) {
        logError("Error saving daily checkin: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Medication Functions
 */
function logMedication($medicationId, $userId, $status, $notes = null) {
    $db = Database::getInstance()->getConnection();
    
    try {
        $stmt = $db->prepare("
            INSERT INTO medication_logs (medication_id, user_id, status, notes, logged_by)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$medicationId, $userId, $status, $notes, getUserId()]);
        
        logError("Medication logged: med_id=$medicationId, status=$status", 'INFO');
        return true;
        
    } catch (PDOException $e) {
        logError("Error logging medication: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Utility Functions
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M j, Y g:i A', strtotime($datetime));
}

function getTimeDifference($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    
    return formatDate($datetime);
}

/**
 * Security Functions
 */
function checkPermission($requiredRole) {
    $userRole = getUserRole();
    
    $roleHierarchy = [
        'client' => 1,
        'caregiver' => 2,
        'admin' => 3
    ];
    
    return isset($roleHierarchy[$userRole]) && 
           $roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole];
}

function hasAccessToPatient($caregiverId, $patientId) {
    $db = Database::getInstance()->getConnection();
    
    try {
        $stmt = $db->prepare("
            SELECT id FROM user_relationships 
            WHERE caregiver_id = ? AND client_id = ? AND status = 'active'
        ");
        $stmt->execute([$caregiverId, $patientId]);
        
        return $stmt->fetch() ? true : false;
        
    } catch (PDOException $e) {
        logError("Error checking patient access: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Validation Functions
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePassword($password) {
    return strlen($password) >= 8 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[a-z]/', $password) && 
           preg_match('/[0-9]/', $password);
}

function validatePhone($phone) {
    return preg_match('/^[\+]?[1-9][\d]{0,15}$/', $phone);
}
?>