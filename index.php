<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Require user to be logged in
requireLogin();

$userId = getUserId();
$userRole = getUserRole();
$userName = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

// Get dashboard data based on user role
if ($userRole === 'client') {
    $dashboardData = getTodaysCareCard($userId);
} else {
    $dashboardData = getCaregiverDashboardData($userId);
}

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['ajax']) {
        case 'checkin_status':
            echo json_encode(['completed' => $dashboardData['checkin_completed'] ?? false]);
            break;
        case 'medication_status':
            echo json_encode($dashboardData['medications'] ?? []);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - 0S-CARE</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Accessibility Bar -->
    <div class="accessibility-bar d-none d-md-block">
        <small class="me-2">Accessibility:</small>
        <button type="button" class="btn btn-sm btn-outline-light" onclick="toggleHighContrast()" title="High Contrast">
            <i class="bi bi-circle-half"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-light" onclick="toggleLargeFont()" title="Large Font">
            <i class="bi bi-fonts"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-light" onclick="toggleDyslexiaFont()" title="Dyslexia-Friendly Font">
            <i class="bi bi-type"></i>
        </button>
    </div>

    <!-- Header -->
    <nav class="navbar navbar-expand-lg dashboard-header">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="#">
                <i class="bi bi-heart-pulse me-2"></i>0S-CARE
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <span class="navbar-text text-white">
                            Welcome, <strong><?php echo htmlspecialchars($userName); ?></strong> 
                            <span class="badge bg-primary"><?php echo ucfirst($userRole); ?></span>
                        </span>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-bell"></i>
                            <span class="badge bg-danger">3</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Medication due in 30 min</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-calendar text-info me-2"></i>Appointment tomorrow</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-chat text-primary me-2"></i>New message</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#dashboard" data-section="dashboard">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        
                        <?php if ($userRole === 'client'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#checkin" data-section="checkin">
                                <i class="bi bi-journal-check me-2"></i>Daily Check-in
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#pain-map" data-section="pain-map">
                                <i class="bi bi-person me-2"></i>Pain Map
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#medications" data-section="medications">
                                <i class="bi bi-capsule me-2"></i>Medications
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#comfort" data-section="comfort">
                                <i class="bi bi-heart me-2"></i>Comfort Dashboard
                            </a>
                        </li>
                        <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#patients" data-section="patients">
                                <i class="bi bi-people me-2"></i>Patients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#tasks" data-section="tasks">
                                <i class="bi bi-list-check me-2"></i>Tasks
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#alerts" data-section="alerts">
                                <i class="bi bi-exclamation-triangle me-2"></i>Alerts
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="#appointments" data-section="appointments">
                                <i class="bi bi-calendar-event me-2"></i>Appointments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#messages" data-section="messages">
                                <i class="bi bi-chat-dots me-2"></i>Messages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#reports" data-section="reports">
                                <i class="bi bi-graph-up me-2"></i>Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#resources" data-section="resources">
                                <i class="bi bi-info-circle me-2"></i>Resources
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
                <!-- Dashboard Section -->
                <div id="dashboard-section" class="content-section">
                    <?php if ($userRole === 'client'): ?>
                        <!-- Client Dashboard -->
                        <div class="care-card">
                            <h3><i class="bi bi-sun me-2"></i>Today's Care Card</h3>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="care-item">
                                        <strong>Weather</strong><br>
                                        <i class="bi bi-sun me-1"></i>Melfort, SK<br>
                                        <small>-5°C, Partly Cloudy</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="care-item">
                                        <strong>Next Appointment</strong><br>
                                        <?php if (!empty($dashboardData['appointments'])): ?>
                                            <?php $nextAppt = $dashboardData['appointments'][0]; ?>
                                            <?php echo htmlspecialchars($nextAppt['title']); ?><br>
                                            <small><?php echo formatDateTime($nextAppt['appointment_date']); ?></small>
                                        <?php else: ?>
                                            No upcoming appointments
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="care-item">
                                        <strong>Messages</strong><br>
                                        <?php echo $dashboardData['unread_messages'] ?? 0; ?> unread<br>
                                        <small>Latest: 2 hours ago</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="care-item">
                                        <strong>Check-in Status</strong><br>
                                        <?php if ($dashboardData['checkin_completed']): ?>
                                            <i class="bi bi-check-circle text-success"></i> Completed
                                        <?php else: ?>
                                            <i class="bi bi-clock text-warning"></i> Pending
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="quick-actions">
                            <a href="#" class="quick-action-btn" onclick="openCheckinModal()">
                                <i class="bi bi-journal-plus"></i>
                                <span>Log Something</span>
                            </a>
                            <a href="#" class="quick-action-btn" onclick="showSection('pain-map')">
                                <i class="bi bi-person"></i>
                                <span>Pain Map</span>
                            </a>
                            <a href="#" class="quick-action-btn" onclick="logMedicationQuick()">
                                <i class="bi bi-capsule"></i>
                                <span>Take Medication</span>
                            </a>
                            <a href="#" class="quick-action-btn" onclick="showSection('comfort')">
                                <i class="bi bi-heart"></i>
                                <span>Comfort Tools</span>
                            </a>
                            <a href="#" class="quick-action-btn" onclick="sendUrgentAlert()">
                                <i class="bi bi-exclamation-triangle"></i>
                                <span>Urgent Alert</span>
                            </a>
                        </div>

                        <!-- Today's Medications -->
                        <div class="widget-card">
                            <div class="card-header">
                                <h5><i class="bi bi-capsule me-2"></i>Today's Medications</h5>
                            </div>
                            <div class="card-body">
                                <div class="medication-grid">
                                    <?php if (!empty($dashboardData['medications'])): ?>
                                        <?php foreach ($dashboardData['medications'] as $med): ?>
                                        <div class="medication-tile">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($med['name']); ?></h6>
                                                    <p class="mb-1 text-muted"><?php echo htmlspecialchars($med['dosage']); ?></p>
                                                    <small class="text-muted"><?php echo htmlspecialchars($med['frequency']); ?></small>
                                                </div>
                                                <div class="adherence-ring" style="--percentage: <?php echo ($med['taken_today'] > 0) ? 100 : 0; ?>">
                                                    <?php echo ($med['taken_today'] > 0) ? '✓' : '○'; ?>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <button class="btn btn-sm btn-success me-1" onclick="logMedication(<?php echo $med['id']; ?>, 'taken')">
                                                    <i class="bi bi-check"></i> Taken
                                                </button>
                                                <button class="btn btn-sm btn-warning" onclick="logMedication(<?php echo $med['id']; ?>, 'skipped')">
                                                    <i class="bi bi-x"></i> Skip
                                                </button>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted">No medications scheduled for today.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Caregiver Dashboard -->
                        <h2><i class="bi bi-people me-2"></i>Caregiver Dashboard</h2>
                        
                        <!-- Urgent Alerts -->
                        <?php if (!empty($dashboardData['urgent_tasks']) || !empty($dashboardData['missed_medications'])): ?>
                        <div class="alert alert-warning">
                            <h5><i class="bi bi-exclamation-triangle me-2"></i>Urgent Attention Required</h5>
                            <?php if (!empty($dashboardData['urgent_tasks'])): ?>
                                <p><strong>Urgent Tasks:</strong> <?php echo count($dashboardData['urgent_tasks']); ?> tasks need immediate attention</p>
                            <?php endif; ?>
                            <?php if (!empty($dashboardData['missed_medications'])): ?>
                                <p><strong>Missed Medications:</strong> <?php echo count($dashboardData['missed_medications']); ?> medications missed today</p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Patients Overview -->
                        <div class="widget-card">
                            <div class="card-header">
                                <h5><i class="bi bi-people me-2"></i>Patients Under Care</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($dashboardData['patients'])): ?>
                                    <?php foreach ($dashboardData['patients'] as $patient): ?>
                                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></h6>
                                            <small class="text-muted">Access: <?php echo ucfirst($patient['access_level']); ?></small>
                                        </div>
                                        <div class="text-end">
                                            <?php if ($patient['checkin_date']): ?>
                                                <span class="badge bg-success">Checked in today</span><br>
                                                <small>Mood: <?php echo $patient['mood']; ?>/5, Pain: <?php echo $patient['pain_level']; ?>/10</small>
                                            <?php else: ?>
                                                <span class="badge bg-warning">No check-in today</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">No patients assigned.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Upcoming Appointments -->
                    <div class="widget-card">
                        <div class="card-header">
                            <h5><i class="bi bi-calendar-event me-2"></i>Upcoming Appointments</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($dashboardData['appointments'])): ?>
                                <?php foreach ($dashboardData['appointments'] as $appointment): ?>
                                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($appointment['title']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo formatDateTime($appointment['appointment_date']); ?>
                                            <?php if ($appointment['provider_name']): ?>
                                                - Dr. <?php echo htmlspecialchars($appointment['provider_name']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div>
                                        <?php if ($appointment['appointment_type'] === 'telehealth'): ?>
                                            <button class="btn btn-sm btn-primary">
                                                <i class="bi bi-camera-video me-1"></i>Join
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No upcoming appointments.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Other sections will be dynamically loaded here -->
                <div id="dynamic-content" style="display: none;"></div>
            </main>
        </div>
    </div>

    <!-- Daily Check-in Modal -->
    <div class="modal fade" id="checkinModal" tabindex="-1" aria-labelledby="checkinModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkinModalLabel">
                        <i class="bi bi-sun me-2"></i>Good <?php echo (date('H') < 12) ? 'Morning' : (date('H') < 18 ? 'Afternoon' : 'Evening'); ?>, <?php echo htmlspecialchars($_SESSION['first_name']); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Let's check in so we can keep your care plan up to date.</p>
                    <p><strong>Today:</strong> <?php echo date('l, F j, Y'); ?> | <strong>Weather:</strong> -5°C, Partly Cloudy in Melfort, SK</p>
                    
                    <form id="checkinForm" method="POST" action="api/checkin.php">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <!-- Step 1: Mood -->
                        <div class="mb-4">
                            <h6>How is your mood today?</h6>
                            <div class="d-flex gap-2 justify-content-center">
                                <label class="mood-option">
                                    <input type="radio" name="mood" value="1" required>
                                    <span class="mood-emoji">😢</span>
                                    <small>Very Sad</small>
                                </label>
                                <label class="mood-option">
                                    <input type="radio" name="mood" value="2" required>
                                    <span class="mood-emoji">🙁</span>
                                    <small>Sad</small>
                                </label>
                                <label class="mood-option">
                                    <input type="radio" name="mood" value="3" required>
                                    <span class="mood-emoji">😐</span>
                                    <small>Okay</small>
                                </label>
                                <label class="mood-option">
                                    <input type="radio" name="mood" value="4" required>
                                    <span class="mood-emoji">🙂</span>
                                    <small>Good</small>
                                </label>
                                <label class="mood-option">
                                    <input type="radio" name="mood" value="5" required>
                                    <span class="mood-emoji">😀</span>
                                    <small>Great</small>
                                </label>
                            </div>
                            <div class="mt-2">
                                <textarea class="form-control" name="mood_notes" placeholder="Want to say more about how you're feeling?"></textarea>
                            </div>
                        </div>
                        
                        <!-- Step 2: Pain Level -->
                        <div class="mb-4">
                            <h6>What is your pain level today? (0 = No pain, 10 = Worst pain)</h6>
                            <div class="pain-scale d-flex justify-content-between">
                                <?php for ($i = 0; $i <= 10; $i++): ?>
                                <label class="pain-level">
                                    <input type="radio" name="pain_level" value="<?php echo $i; ?>" required>
                                    <span><?php echo $i; ?></span>
                                </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <!-- Step 3: Energy Level -->
                        <div class="mb-4">
                            <h6>How is your energy level?</h6>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="energy_level" value="high" id="energy_high" required>
                                <label class="btn btn-outline-success" for="energy_high">High Energy</label>
                                
                                <input type="radio" class="btn-check" name="energy_level" value="okay" id="energy_okay" required>
                                <label class="btn btn-outline-info" for="energy_okay">Okay</label>
                                
                                <input type="radio" class="btn-check" name="energy_level" value="low" id="energy_low" required>
                                <label class="btn btn-outline-warning" for="energy_low">Low Energy</label>
                                
                                <input type="radio" class="btn-check" name="energy_level" value="exhausted" id="energy_exhausted" required>
                                <label class="btn btn-outline-danger" for="energy_exhausted">Exhausted</label>
                            </div>
                        </div>
                        
                        <!-- Step 4: Appetite & Hydration -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>How is your appetite?</h6>
                                <select class="form-select" name="appetite" required>
                                    <option value="">Select...</option>
                                    <option value="good">Good</option>
                                    <option value="fair">Fair</option>
                                    <option value="poor">Poor</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <h6>How many cups of water have you had today?</h6>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustHydration(-1)">-</button>
                                    <input type="number" class="form-control text-center" name="hydration_cups" id="hydration_cups" value="0" min="0" max="20">
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustHydration(1)">+</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Step 5: Activity Level -->
                        <div class="mb-4">
                            <h6>What was your activity level today?</h6>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="activity_level" value="none" id="activity_none" required>
                                <label class="btn btn-outline-secondary" for="activity_none">None</label>
                                
                                <input type="radio" class="btn-check" name="activity_level" value="light" id="activity_light" required>
                                <label class="btn btn-outline-info" for="activity_light">Light</label>
                                
                                <input type="radio" class="btn-check" name="activity_level" value="moderate" id="activity_moderate" required>
                                <label class="btn btn-outline-warning" for="activity_moderate">Moderate</label>
                                
                                <input type="radio" class="btn-check" name="activity_level" value="active" id="activity_active" required>
                                <label class="btn btn-outline-success" for="activity_active">Active</label>
                            </div>
                        </div>
                        
                        <!-- Step 6: Daily Highlight -->
                        <div class="mb-4">
                            <h6><?php echo (date('H') < 12) ? "What's one thing you're looking forward to today?" : "What was the best part of your day?"; ?></h6>
                            <textarea class="form-control" name="daily_highlight" placeholder="Share something positive..."></textarea>
                        </div>
                        
                        <!-- Submit -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Complete Check-in
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="js/login.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html>