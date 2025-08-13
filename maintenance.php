<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Maintenance - 0S-CARE</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .maintenance-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: none;
        }
        .card-header {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
            color: white;
            border-radius: 15px 15px 0 0 !important;
        }
        .credential-card {
            transition: transform 0.2s ease;
        }
        .credential-card:hover {
            transform: translateY(-2px);
        }
        .credential-card.active {
            border: 2px solid #007bff;
            background: rgba(0, 123, 255, 0.1);
        }
        .log-container {
            max-height: 400px;
            overflow-y: auto;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
        }
        .status-badge {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }
        .danger-zone {
            border: 2px solid #dc3545;
            border-radius: 10px;
            background: rgba(220, 53, 69, 0.1);
        }
    </style>
</head>
<body>
    <!-- Status Badge -->
    <div id="statusBadge" class="status-badge">
        <span class="badge bg-secondary">No Connection</span>
    </div>

    <div class="maintenance-container">
        <!-- Header -->
        <div class="text-center text-white mb-4">
            <h1><i class="bi bi-tools me-2"></i>0S-CARE Database Maintenance</h1>
            <p class="lead">Comprehensive database management and maintenance tools</p>
            <p><small><i class="bi bi-shield-exclamation me-1"></i>This tool requires NO LOGIN and has administrative access</small></p>
        </div>

        <!-- Database Credentials Selection -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-database me-2"></i>Database Connection Settings</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="credential-card card h-100" id="devCard" onclick="selectCredentials('development')">
                            <div class="card-body text-center">
                                <i class="bi bi-laptop display-4 text-info mb-3"></i>
                                <h5>Development Environment</h5>
                                <div class="credentials-info">
                                    <strong>Host:</strong> localhost<br>
                                    <strong>Database:</strong> os_care_dev<br>
                                    <strong>Username:</strong> root<br>
                                    <strong>Password:</strong> <em>(blank)</em>
                                </div>
                                <span class="badge bg-info mt-2">Default</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="credential-card card h-100" id="prodCard" onclick="selectCredentials('production')">
                            <div class="card-body text-center">
                                <i class="bi bi-cloud display-4 text-success mb-3"></i>
                                <h5>Production Environment</h5>
                                <div class="credentials-info">
                                    <strong>Host:</strong> localhost<br>
                                    <strong>Database:</strong> outsrglr_mom<br>
                                    <strong>Username:</strong> outsrglr_mom<br>
                                    <strong>Password:</strong> born#1852Niptuck
                                </div>
                                <span class="badge bg-success mt-2">Live</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <button class="btn btn-primary" onclick="testConnection()">
                        <i class="bi bi-plug me-1"></i>Test Connection
                    </button>
                    <button class="btn btn-outline-secondary" onclick="showConnectionDetails()">
                        <i class="bi bi-info-circle me-1"></i>Connection Details
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Database Operations -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Database Operations</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Schema Management</h6>
                            <button class="btn btn-success btn-sm me-2" onclick="runOperation('create_tables')">
                                <i class="bi bi-plus-circle me-1"></i>Create Tables
                            </button>
                            <button class="btn btn-info btn-sm me-2" onclick="runOperation('update_schema')">
                                <i class="bi bi-arrow-repeat me-1"></i>Update Schema
                            </button>
                            <button class="btn btn-warning btn-sm" onclick="runOperation('backup_db')">
                                <i class="bi bi-download me-1"></i>Backup Database
                            </button>
                        </div>
                        
                        <div class="mb-3">
                            <h6>Data Management</h6>
                            <button class="btn btn-primary btn-sm me-2" onclick="runOperation('import_sample')">
                                <i class="bi bi-upload me-1"></i>Import Sample Data
                            </button>
                            <button class="btn btn-secondary btn-sm me-2" onclick="runOperation('cleanup_logs')">
                                <i class="bi bi-trash me-1"></i>Cleanup Old Logs
                            </button>
                            <button class="btn btn-info btn-sm" onclick="runOperation('optimize_tables')">
                                <i class="bi bi-speedometer2 me-1"></i>Optimize Tables
                            </button>
                        </div>
                        
                        <div class="danger-zone p-3">
                            <h6 class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Danger Zone</h6>
                            <button class="btn btn-danger btn-sm me-2" onclick="confirmDangerousOperation('reset_all_data')">
                                <i class="bi bi-arrow-clockwise me-1"></i>Reset All Data
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="confirmDangerousOperation('drop_all_tables')">
                                <i class="bi bi-trash3 me-1"></i>Drop All Tables
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- User Management -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-people me-2"></i>User Management</h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-success btn-sm me-2" onclick="runOperation('create_admin')">
                            <i class="bi bi-person-plus me-1"></i>Create Admin User
                        </button>
                        <button class="btn btn-info btn-sm me-2" onclick="runOperation('list_users')">
                            <i class="bi bi-list me-1"></i>List All Users
                        </button>
                        <button class="btn btn-warning btn-sm" onclick="runOperation('reset_passwords')">
                            <i class="bi bi-key me-1"></i>Reset Demo Passwords
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- System Information -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>System Information</h5>
                    </div>
                    <div class="card-body">
                        <div id="systemInfo">
                            <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
                            <p><strong>Server Software:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
                            <p><strong>Current Time:</strong> <span id="currentTime"><?php echo date('Y-m-d H:i:s'); ?></span></p>
                            <p><strong>Selected Environment:</strong> <span id="selectedEnv">Development</span></p>
                            <p><strong>Database Status:</strong> <span id="dbStatus" class="badge bg-secondary">Not Connected</span></p>
                        </div>
                        
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshSystemInfo()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Refresh Info
                        </button>
                    </div>
                </div>
                
                <!-- Database Statistics -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Database Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div id="dbStats">
                            <div class="row text-center">
                                <div class="col-4">
                                    <h4 id="userCount" class="text-primary">-</h4>
                                    <small>Users</small>
                                </div>
                                <div class="col-4">
                                    <h4 id="checkinCount" class="text-success">-</h4>
                                    <small>Check-ins</small>
                                </div>
                                <div class="col-4">
                                    <h4 id="medCount" class="text-info">-</h4>
                                    <small>Medications</small>
                                </div>
                            </div>
                        </div>
                        
                        <button class="btn btn-outline-success btn-sm mt-3" onclick="loadDatabaseStats()">
                            <i class="bi bi-bar-chart me-1"></i>Load Statistics
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Operation Logs -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Operation Logs</h5>
                <div class="float-end">
                    <button class="btn btn-sm btn-outline-light" onclick="clearLogs()">
                        <i class="bi bi-trash me-1"></i>Clear Logs
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="operationLogs" class="log-container">
                    <p class="text-muted">No operations performed yet. Logs will appear here.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let currentEnvironment = 'development';
        let connectionStatus = false;
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            selectCredentials('development');
            updateTime();
            setInterval(updateTime, 1000);
        });
        
        function selectCredentials(env) {
            currentEnvironment = env;
            
            // Update visual selection
            document.querySelectorAll('.credential-card').forEach(card => {
                card.classList.remove('active');
            });
            
            if (env === 'development') {
                document.getElementById('devCard').classList.add('active');
            } else {
                document.getElementById('prodCard').classList.add('active');
            }
            
            document.getElementById('selectedEnv').textContent = env === 'development' ? 'Development' : 'Production';
            addLog(`Switched to ${env} environment`, 'info');
            
            // Reset connection status
            connectionStatus = false;
            updateConnectionStatus();
        }
        
        function testConnection() {
            addLog('Testing database connection...', 'info');
            
            // Simulate connection test
            setTimeout(() => {
                const success = Math.random() > 0.2; // 80% success rate for demo
                connectionStatus = success;
                
                if (success) {
                    addLog('✅ Database connection successful', 'success');
                    loadDatabaseStats();
                } else {
                    addLog('❌ Database connection failed', 'error');
                }
                
                updateConnectionStatus();
            }, 1500);
        }
        
        function updateConnectionStatus() {
            const statusBadge = document.getElementById('statusBadge');
            const dbStatus = document.getElementById('dbStatus');
            
            if (connectionStatus) {
                statusBadge.innerHTML = '<span class="badge bg-success">Connected</span>';
                dbStatus.className = 'badge bg-success';
                dbStatus.textContent = 'Connected';
            } else {
                statusBadge.innerHTML = '<span class="badge bg-danger">Disconnected</span>';
                dbStatus.className = 'badge bg-danger';
                dbStatus.textContent = 'Disconnected';
            }
        }
        
        function runOperation(operation) {
            if (!connectionStatus && !['create_tables', 'backup_db'].includes(operation)) {
                addLog('❌ No database connection. Please test connection first.', 'error');
                return;
            }
            
            addLog(`🚀 Starting operation: ${operation}`, 'info');
            
            // Simulate operation
            setTimeout(() => {
                const operations = {
                    'create_tables': 'Created all database tables successfully',
                    'update_schema': 'Schema updated to latest version',
                    'backup_db': 'Database backup created successfully',
                    'import_sample': 'Sample data imported successfully',
                    'cleanup_logs': 'Old log entries cleaned up',
                    'optimize_tables': 'Database tables optimized',
                    'create_admin': 'Admin user created: admin/admin123',
                    'list_users': 'Found 5 users: 2 clients, 2 caregivers, 1 admin',
                    'reset_passwords': 'Demo passwords reset to default values'
                };
                
                const message = operations[operation] || 'Operation completed';
                addLog(`✅ ${message}`, 'success');
                
                if (operation === 'create_tables' || operation === 'import_sample') {
                    loadDatabaseStats();
                }
            }, 2000);
        }
        
        function confirmDangerousOperation(operation) {
            const confirmations = {
                'reset_all_data': 'This will DELETE ALL USER DATA. Are you absolutely sure?',
                'drop_all_tables': 'This will DROP ALL TABLES. This action cannot be undone. Continue?'
            };
            
            if (confirm(confirmations[operation])) {
                if (confirm('Final confirmation: This is a DESTRUCTIVE operation!')) {
                    addLog(`⚠️ DANGER: Executing ${operation}`, 'warning');
                    
                    setTimeout(() => {
                        const messages = {
                            'reset_all_data': 'All user data has been reset',
                            'drop_all_tables': 'All tables have been dropped'
                        };
                        
                        addLog(`🔥 ${messages[operation]}`, 'error');
                        connectionStatus = false;
                        updateConnectionStatus();
                        loadDatabaseStats();
                    }, 3000);
                }
            }
        }
        
        function loadDatabaseStats() {
            if (!connectionStatus) {
                document.getElementById('userCount').textContent = '-';
                document.getElementById('checkinCount').textContent = '-';
                document.getElementById('medCount').textContent = '-';
                return;
            }
            
            // Simulate loading stats
            setTimeout(() => {
                document.getElementById('userCount').textContent = Math.floor(Math.random() * 50) + 10;
                document.getElementById('checkinCount').textContent = Math.floor(Math.random() * 500) + 100;
                document.getElementById('medCount').textContent = Math.floor(Math.random() * 100) + 20;
                addLog('📊 Database statistics updated', 'info');
            }, 1000);
        }
        
        function showConnectionDetails() {
            const env = currentEnvironment;
            const details = env === 'development' 
                ? 'Development: localhost/os_care_dev (root/blank)'
                : 'Production: localhost/outsrglr_mom (outsrglr_mom/born#1852Niptuck)';
            
            alert(`Current Connection:\n${details}\n\nStatus: ${connectionStatus ? 'Connected' : 'Disconnected'}`);
        }
        
        function refreshSystemInfo() {
            addLog('🔄 Refreshing system information', 'info');
            document.getElementById('currentTime').textContent = new Date().toLocaleString();
            loadDatabaseStats();
        }
        
        function updateTime() {
            document.getElementById('currentTime').textContent = new Date().toLocaleString();
        }
        
        function addLog(message, type = 'info') {
            const logsContainer = document.getElementById('operationLogs');
            const timestamp = new Date().toLocaleTimeString();
            
            const logEntry = document.createElement('div');
            logEntry.className = `mb-2 p-2 rounded ${getLogClass(type)}`;
            logEntry.innerHTML = `<small class="text-muted">[${timestamp}]</small> ${message}`;
            
            // Remove initial message if present
            if (logsContainer.querySelector('.text-muted')) {
                logsContainer.innerHTML = '';
            }
            
            logsContainer.appendChild(logEntry);
            logsContainer.scrollTop = logsContainer.scrollHeight;
        }
        
        function getLogClass(type) {
            const classes = {
                'info': 'bg-light border-start border-4 border-info',
                'success': 'bg-light border-start border-4 border-success',
                'warning': 'bg-warning bg-opacity-25 border-start border-4 border-warning',
                'error': 'bg-danger bg-opacity-25 border-start border-4 border-danger'
            };
            return classes[type] || classes['info'];
        }
        
        function clearLogs() {
            document.getElementById('operationLogs').innerHTML = '<p class="text-muted">Logs cleared.</p>';
        }
    </script>
</body>
</html>