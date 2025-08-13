# 0S-CARE Healthcare Web Application

0S-CARE is a comprehensive healthcare web application designed for cancer patient care management. Built with PHP 8+, MySQL, Bootstrap 5, HTML, CSS, and JavaScript, it provides tools for patients, caregivers, and family members to track daily health metrics, medications, appointments, and care coordination.

**CRITICAL**: Always reference these instructions first and fallback to search or bash commands only when you encounter unexpected information that does not match the info here.

## Working Effectively

### Initial Environment Setup
**NEVER CANCEL these setup commands - they may take 15-30 minutes total. Set timeouts to 45+ minutes.**

```bash
# Check if packages are already installed (faster than reinstalling)
dpkg -l | grep -E "(apache2|mysql-server|php8.3)" | wc -l
```

```bash
# Install required packages only if needed
sudo apt-get update && sudo apt-get install -y apache2 mysql-server php8.3 php8.3-mysql php8.3-gd php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip libapache2-mod-php8.3
```
**Expected time: 10-15 minutes for fresh install, 2-3 minutes if already installed. NEVER CANCEL.**

```bash
# Start services (tested: completes in 30-60 seconds)
sudo service apache2 start
sudo service mysql start
```
**Expected time: 30-60 seconds. NEVER CANCEL.**

```bash
# Enable PHP module and restart Apache (tested: completes in 10-20 seconds)
sudo a2enmod php8.3
sudo service apache2 restart
```
**Expected time: 10-20 seconds. NEVER CANCEL.**

```bash
# Verify setup is working
sudo service apache2 status | head -1
sudo service mysql status | head -1
php --version | head -1
```

### Database Setup
**NEVER CANCEL database operations - they may take 5-10 minutes. Set timeout to 20+ minutes.**

```bash
# First, secure MySQL installation (interactive - follow prompts)
sudo mysql_secure_installation
```
**Expected time: 2-3 minutes of interaction. NEVER CANCEL.**

```bash
# Create development database - NOTE: This may fail if MySQL auth is not configured
# Alternative 1: Direct sudo access
sudo mysql -e "CREATE DATABASE IF NOT EXISTS outsrglr_mom; CREATE USER IF NOT EXISTS 'outsrglr_mom'@'localhost' IDENTIFIED BY 'born#1852Niptuck'; GRANT ALL PRIVILEGES ON outsrglr_mom.* TO 'outsrglr_mom'@'localhost'; FLUSH PRIVILEGES;"
```
**Expected time: 2-5 minutes. NEVER CANCEL.**

```bash
# Alternative 2: If sudo mysql fails, try setting up root password first
# This command will fail in some environments - document the error
sudo mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS outsrglr_mom;"
```
**Expected: May fail with "Access denied" - this is expected in some setups.**

```bash
# Import database schema (when database.sql exists)
mysql -u outsrglr_mom -pborn#1852Niptuck outsrglr_mom < database.sql
```
**Expected time: 1-3 minutes depending on schema size. NEVER CANCEL.**

```bash
# Test database connectivity after setup
mysql -u outsrglr_mom -pborn#1852Niptuck -e "SELECT VERSION(), NOW();"
```

### Project Setup
```bash
# Set proper directory permissions
chmod 755 logs/ uploads/ uploads/documents/
chown www-data:www-data logs/ uploads/ uploads/documents/
```

```bash
# Install Composer dependencies (when composer.json exists)
composer install --no-dev --optimize-autoloader
```
**Expected time: 2-5 minutes. NEVER CANCEL.**

```bash
# Install NPM dependencies for frontend tooling (when package.json exists)
npm install
```
**Expected time: 3-8 minutes. NEVER CANCEL.**

## Development Workflow

### Running the Application
```bash
# Method 1: Apache web server (recommended for production-like testing)
sudo service apache2 start
# Access at http://localhost/path-to-project
# Example: If project is in /var/www/html/0s-care, access http://localhost/0s-care
```

```bash
# Method 2: PHP built-in server (development only - tested and working)
php -S localhost:8000 -t .
# Access at http://localhost:8000
# Press Ctrl+C to stop the server
```
**Expected: Server starts immediately, returns 404 until index.php exists**

```bash
# Method 3: Using different port if 8000 is busy
php -S localhost:8080 -t .
```

```bash
# Test web server response
curl -I http://localhost:8000/ 2>/dev/null | head -1
# Expected: "HTTP/1.1 404 Not Found" until index.php exists
```

### Testing Database Connection
```bash
# Test database connectivity
php -r "try { \$pdo = new PDO('mysql:host=localhost;dbname=outsrglr_mom', 'outsrglr_mom', 'born#1852Niptuck'); echo 'Database connected successfully'; } catch(Exception \$e) { echo 'Connection failed: ' . \$e->getMessage(); }"
```

### Building Assets (when build process exists)
```bash
# Compile CSS/JS assets
npm run build
```
**Expected time: 2-5 minutes. NEVER CANCEL. Set timeout to 10+ minutes.**

```bash
# Development watch mode
npm run dev
```
**Runs continuously. Set timeout to 120+ minutes for long development sessions.**

## Validation

### Manual Testing Requirements
**ALWAYS perform these validation steps after making any changes:**

1. **Authentication Flow**: Test login, registration, password reset
   - Navigate to login.php
   - Create test account: `testuser@example.com` / `testpass123`
   - Verify automatic login after registration
   - Test forgot password functionality

2. **Patient Dashboard**: Verify core patient features
   - Log daily check-in (mood, pain, energy, symptoms)
   - Test medication logging and adherence tracking
   - Verify appointment calendar display
   - Test pain pattern body map functionality

3. **Caregiver Dashboard**: Verify caregiver features
   - Check patient alerts and notifications
   - Test task creation and assignment
   - Verify medication monitoring alerts
   - Test care note creation and viewing

4. **Database Operations**: Verify data persistence
   - Submit forms and verify data saves to database
   - Test data retrieval and display accuracy
   - Verify error handling for invalid data

5. **Security Features**: Test protection mechanisms
   - Verify CSRF token protection on forms
   - Test session timeout behavior
   - Verify password hashing (bcrypt)
   - Test access control between user roles

### Automated Testing
```bash
# Run PHPUnit tests (when tests exist)
./vendor/bin/phpunit tests/
```
**Expected time: 2-5 minutes. NEVER CANCEL. Set timeout to 10+ minutes.**

```bash
# Run JavaScript tests (when configured)
npm test
```
**Expected time: 1-3 minutes. NEVER CANCEL. Set timeout to 8+ minutes.**

### Code Quality Checks
```bash
# PHP syntax check
find . -name "*.php" -exec php -l {} \; | grep -v "No syntax errors"
```

```bash
# Run PHP linting (when configured)
./vendor/bin/phpcs --standard=PSR12 .
```

```bash
# Run JavaScript linting
npx eslint js/
```

**ALWAYS run these checks before committing changes or CI will fail.**

## Common Tasks

### Repository Structure
```
0S-CARE/
├── .github/
│   └── copilot-instructions.md    # This file
├── api/                           # API endpoints
│   ├── export.php                # Data export functionality
│   ├── medications.php           # Medication management API
│   └── tasks.php                 # Task management API
├── config/
│   └── database.php              # Database configuration
├── includes/
│   ├── functions.php             # Core application functions
│   └── error_page.php            # Error handling
├── logs/                         # Application logs (auto-generated)
├── uploads/                      # File uploads
│   └── documents/               # Patient documents
├── css/                          # Stylesheets
├── js/                           # JavaScript files
├── index.php                     # Main dashboard
├── login.php                     # Authentication
├── register.php                  # User registration
├── logout.php                    # Logout functionality
├── database.sql                  # Database schema
├── composer.json                 # PHP dependencies
├── package.json                  # Node.js dependencies
└── README.md                     # Project documentation
```

### Key Configuration Files

#### config/database.php (when it exists)
```php
// Development settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'outsrglr_mom');
define('DB_USER', 'outsrglr_mom');
define('DB_PASS', 'born#1852Niptuck');

// Production settings (use same for this project)
define('SESSION_LIFETIME', 3600); // 1 hour
define('MAX_FILE_SIZE', 10485760); // 10MB
define('LOG_LEVEL', 'INFO');
```

### Database Management
```bash
# Create database backup
mysqldump -u outsrglr_mom -p outsrglr_mom > backup_$(date +%Y%m%d).sql
```

```bash
# Reset database (development only)
mysql -u outsrglr_mom -p -e "DROP DATABASE IF EXISTS outsrglr_mom; CREATE DATABASE outsrglr_mom;"
mysql -u outsrglr_mom -p outsrglr_mom < database.sql
```

## Technology Stack Requirements

### PHP Requirements
- **PHP 8.0+** (tested working with 8.3.6)
- **Required extensions**: pdo, pdo_mysql, gd, mbstring, xml, curl, zip, json
- **Optional**: xdebug (development only)

```bash
# Check PHP version and verify it meets requirements
php --version | head -1
# Expected: PHP 8.3.6 or higher

# Verify required PHP modules are installed
php -m | grep -E "(pdo|mysql|gd|mbstring|xml|curl|zip|json)"
# Should show: gd, mysqli, mysqlnd, pdo_mysql, etc.

# Check specific critical modules
php -m | grep -c pdo_mysql
# Should return 1 (meaning found)
```

### Database Requirements
- **MySQL 5.7+** or **MySQL 8.0+** (tested with 8.0.42)
- **Minimum storage**: 100MB for development, 1GB+ for production
- **Permissions**: Full CRUD access to database

### Web Server Requirements
- **Apache 2.4+** with mod_php OR **Nginx** with PHP-FPM
- **Document root**: Point to project directory
- **URL rewriting**: For clean URLs (optional)

### Frontend Dependencies
- **Bootstrap 5.3+**: CSS framework
- **jQuery 3.6+**: JavaScript library
- **Chart.js**: For analytics dashboard
- **FontAwesome**: Icons

## Troubleshooting

### Common Issues

#### Database Connection Failures
```bash
# Check MySQL service status
sudo service mysql status | head -3
# Should show "Active: active (running)"

# If not running, start it
sudo service mysql start

# Test connection methods in order of likelihood to work:

# Method 1: Test with sudo (often works in development environments)
sudo mysql -e "SELECT VERSION();"

# Method 2: Test with user account (after database setup)
mysql -u outsrglr_mom -pborn#1852Niptuck -e "SELECT VERSION();"

# Method 3: Test root with empty password (some dev setups)
mysql -u root -e "SELECT VERSION();"

# Common error: "Access denied for user 'root'@'localhost'"
# Solution: Need to configure MySQL authentication first
```
**Note: Database authentication setup varies by environment. Expect authentication errors initially.**

#### Permission Errors
```bash
# Fix directory permissions
sudo chown -R www-data:www-data logs/ uploads/
sudo chmod -R 755 logs/ uploads/
```

#### Apache/PHP Issues
```bash
# Check Apache configuration
sudo apache2ctl configtest

# Check PHP modules
php -m | grep -E "(mysql|pdo|gd)"

# Restart services
sudo service apache2 restart
```

#### Session Problems
```bash
# Check session directory permissions
ls -ld /var/lib/php/sessions/
sudo chmod 733 /var/lib/php/sessions/
```

### Debug Mode
Enable debug mode by setting `LOG_LEVEL` to `'DEBUG'` in `config/database.php`. This will:
- Log all database queries
- Show detailed error messages
- Enable PHP error reporting

### Performance Monitoring
```bash
# Monitor Apache access logs
tail -f /var/log/apache2/access.log

# Monitor application logs
tail -f logs/app.log

# Monitor MySQL slow queries
sudo tail -f /var/log/mysql/slow.log
```

## Healthcare-Specific Validation

### PHIPA/PIPEDA Compliance Testing
- Verify data encryption at rest and in transit
- Test consent management workflows
- Validate audit logging for data access
- Verify user data export/deletion capabilities

### Medical Data Accuracy
- Test medication interaction checker
- Verify vitals tracking ranges and alerts
- Test symptom severity calculations
- Validate care plan adherence tracking

### Emergency Protocols
- Test urgent care alert notifications
- Verify emergency contact integration
- Test medication overdose prevention alerts
- Validate fall detection and reporting

## Current Status

**IMPORTANT**: This repository currently contains only specifications in README.md. The actual codebase needs to be implemented based on the comprehensive requirements documented there.

When the codebase exists:
- Follow all setup and testing procedures above
- Focus on healthcare data security and privacy
- Prioritize patient safety features and alerts
- Ensure cross-browser compatibility for accessibility

Until then:
- Commands referencing specific files (database.sql, composer.json, etc.) will fail
- Use this document as a blueprint for development setup
- Reference the README.md for complete feature specifications

## Validated Commands

**These commands have been tested and verified to work in the development environment:**

```bash
# Environment checks (all verified working)
php --version                                    # Returns: PHP 8.3.6
mysql --version                                  # Returns: mysql Ver 8.0.42
apache2 -v                                      # Returns: Apache/2.4.58
composer --version                              # Returns: Composer version 2.8.10
npm --version                                   # Returns: 10.8.2

# Service management (verified working)
sudo service apache2 start                     # Starts successfully
sudo service mysql start                       # Starts successfully
sudo service apache2 status | head -1          # Shows status
sudo service mysql status | head -1            # Shows status

# PHP development server (verified working)
php -S localhost:8000 -t .                     # Starts dev server
curl -I http://localhost:8000/ | head -1       # Returns HTTP/1.1 404 (expected)

# PHP module verification (verified working)
php -m | grep -E "(pdo|mysql|gd)"             # Shows required modules
php -m | grep -c pdo_mysql                     # Returns 1 (found)

# Database connectivity test (verified working - shows expected error)
php -r "try { \$pdo = new PDO('mysql:host=localhost;dbname=outsrglr_mom', 'outsrglr_mom', 'born#1852Niptuck'); echo 'Connected'; } catch(Exception \$e) { echo 'Failed: ' . \$e->getMessage(); }"
# Expected: "Failed: SQLSTATE[HY000] [1045] Access denied" until database is set up

# Directory operations (verified working)
mkdir -p logs uploads/documents                # Creates directories
chmod 755 logs uploads                         # Sets permissions
ls -ld logs uploads                            # Shows permissions

# PHP syntax checking (verified working)
find . -name "*.php" -exec php -l {} \;       # Checks PHP syntax (no files yet)
```

**Commands that will fail until codebase exists:**
- `composer install` (no composer.json)
- `npm install` (no package.json)
- `mysql ... < database.sql` (no database.sql)
- Database user creation (requires MySQL auth setup)

**Expected error messages to document:**
- "Access denied for user 'root'@'localhost'" - MySQL needs authentication setup
- "No such file or directory" - For missing project files
- "404 Not Found" - Web server running but no index.php yet