<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>0S-CARE - Secure Cancer Care Management</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="container-fluid h-100">
        <div class="row h-100">
            <!-- Left Panel - Welcome Content -->
            <div class="col-lg-6 welcome-panel d-flex align-items-center">
                <div class="welcome-content text-center">
                    <div class="logo-section mb-4">
                        <i class="bi bi-heart-pulse display-1 text-primary"></i>
                        <h1 class="display-4 fw-bold text-primary">0S-CARE</h1>
                        <p class="lead">Comprehensive Cancer Care Management</p>
                    </div>
                    
                    <div class="features-list">
                        <div class="feature-item mb-3">
                            <i class="bi bi-shield-check text-success me-2"></i>
                            <span>Secure & PHIPA/PIPEDA Compliant</span>
                        </div>
                        <div class="feature-item mb-3">
                            <i class="bi bi-calendar-heart text-info me-2"></i>
                            <span>Daily Check-ins & Medication Tracking</span>
                        </div>
                        <div class="feature-item mb-3">
                            <i class="bi bi-people-fill text-warning me-2"></i>
                            <span>Patient & Caregiver Coordination</span>
                        </div>
                        <div class="feature-item mb-3">
                            <i class="bi bi-graph-up text-danger me-2"></i>
                            <span>Health Analytics & Early Warnings</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Panel - Login Form -->
            <div class="col-lg-6 login-panel d-flex align-items-center">
                <div class="login-form-container w-100">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5">
                            <h2 class="text-center mb-4">Welcome Back</h2>
                            
                            <!-- Login Form -->
                            <form id="loginForm" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Remember me</label>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                                </button>
                            </form>
                            
                            <div class="text-center">
                                <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                                    Forgot Password?
                                </button>
                                <span class="mx-2">|</span>
                                <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#registerModal">
                                    Create Account
                                </button>
                            </div>
                            
                            <!-- Demo Credentials -->
                            <div class="demo-credentials mt-4 p-3 bg-light rounded">
                                <h6 class="text-muted">Demo Credentials:</h6>
                                <small class="text-muted">
                                    <strong>Patient:</strong> diana@example.com / password<br>
                                    <strong>Caregiver:</strong> chance@example.com / password
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModalLabel">Create Your Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="registerForm" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="register">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="reg_first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="reg_first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="reg_last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="reg_last_name" name="last_name" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="reg_username" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="reg_username" name="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="reg_email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="reg_email" name="email" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="reg_password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="reg_password" name="password" required>
                                <small class="text-muted">Min 8 chars, must include uppercase, lowercase, and number</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="reg_confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="reg_confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="reg_role" class="form-label">Your Role *</label>
                            <select class="form-select" id="reg_role" name="role" required>
                                <option value="">Select your role...</option>
                                <option value="client">Patient/Client</option>
                                <option value="caregiver">Caregiver/Family Member</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="reg_phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="reg_phone" name="phone">
                        </div>
                        
                        <div class="mb-3">
                            <label for="reg_dob" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="reg_dob" name="date_of_birth">
                        </div>
                        
                        <div class="mb-3">
                            <label for="reg_security_question" class="form-label">Security Question</label>
                            <select class="form-select" id="reg_security_question" name="security_question">
                                <option value="">Choose a security question...</option>
                                <option value="mother_maiden_name">What is your mother's maiden name?</option>
                                <option value="first_pet_name">What was the name of your first pet?</option>
                                <option value="childhood_city">What city were you born in?</option>
                                <option value="favorite_teacher">What was your favorite teacher's name?</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="reg_security_answer" class="form-label">Security Answer</label>
                            <input type="text" class="form-control" id="reg_security_answer" name="security_answer">
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="reg_terms" name="terms_agreed" required>
                            <label class="form-check-label" for="reg_terms">
                                I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and <a href="#" class="text-decoration-none">Privacy Policy</a> *
                            </label>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="reg_consent" name="consent_given" required>
                            <label class="form-check-label" for="reg_consent">
                                I consent to the collection and use of my health information as outlined in the Privacy Policy (PHIPA/PIPEDA compliant) *
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-person-plus me-2"></i>Create Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="forgotPasswordForm" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="forgot_password">
                        
                        <div class="mb-3">
                            <label for="forgot_email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="forgot_email" name="email" required>
                            <small class="text-muted">We'll send you a password reset link</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="forgot_security_answer" class="form-label">Security Answer</label>
                            <input type="text" class="form-control" id="forgot_security_answer" name="security_answer" placeholder="Answer to your security question">
                        </div>
                        
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-key me-2"></i>Reset Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="js/login.js"></script>

    <?php
    require_once 'config/database.php';
    require_once 'includes/functions.php';

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $error = 'Security token validation failed. Please try again.';
        } else {
            $action = $_POST['action'] ?? 'login';
            
            if ($action === 'login') {
                $email = sanitizeInput($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                
                if (authenticateUser($email, $password)) {
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Invalid email or password.';
                }
            } elseif ($action === 'register') {
                $userData = [
                    'username' => sanitizeInput($_POST['username'] ?? ''),
                    'email' => sanitizeInput($_POST['email'] ?? ''),
                    'password' => $_POST['password'] ?? '',
                    'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
                    'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
                    'role' => sanitizeInput($_POST['role'] ?? ''),
                    'phone' => sanitizeInput($_POST['phone'] ?? ''),
                    'date_of_birth' => $_POST['date_of_birth'] ?? null,
                    'security_question' => sanitizeInput($_POST['security_question'] ?? ''),
                    'security_answer' => sanitizeInput($_POST['security_answer'] ?? ''),
                    'consent_given' => isset($_POST['consent_given']) ? 1 : 0
                ];
                
                // Validate password confirmation
                if ($userData['password'] !== ($_POST['confirm_password'] ?? '')) {
                    $error = 'Passwords do not match.';
                } elseif (!validatePassword($userData['password'])) {
                    $error = 'Password must be at least 8 characters with uppercase, lowercase, and number.';
                } else {
                    $result = registerUser($userData);
                    if ($result['success']) {
                        // Auto-login after registration
                        if (authenticateUser($userData['email'], $userData['password'])) {
                            header('Location: index.php');
                            exit;
                        } else {
                            $success = 'Account created successfully. Please log in.';
                        }
                    } else {
                        $error = $result['message'];
                    }
                }
            } elseif ($action === 'forgot_password') {
                $success = 'If the email exists in our system, you will receive password reset instructions.';
            }
        }
    }

    // Display messages
    if (isset($error)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showAlert('<?php echo addslashes($error); ?>', 'danger');
            });
        </script>
    <?php endif;

    if (isset($success)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showAlert('<?php echo addslashes($success); ?>', 'success');
            });
        </script>
    <?php endif; ?>

</body>
</html>