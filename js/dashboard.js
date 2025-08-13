/**
 * Dashboard JavaScript
 * 0S-CARE - Cancer Patient Care Management System
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    initializeNavigation();
    initializeCheckinModal();
    initializeMedicationLogging();
    loadAccessibilityPreferences();
});

/**
 * Dashboard Initialization
 */
function initializeDashboard() {
    // Load initial dashboard data
    refreshDashboardData();
    
    // Set up periodic updates
    setInterval(refreshDashboardData, 300000); // 5 minutes
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Navigation System
 */
function initializeNavigation() {
    const navLinks = document.querySelectorAll('.sidebar .nav-link[data-section]');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Update active state
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            // Show section
            const section = this.getAttribute('data-section');
            showSection(section);
        });
    });
}

/**
 * Show Dashboard Section
 */
function showSection(sectionName) {
    const dashboardSection = document.getElementById('dashboard-section');
    const dynamicContent = document.getElementById('dynamic-content');
    
    if (sectionName === 'dashboard') {
        dashboardSection.style.display = 'block';
        dynamicContent.style.display = 'none';
        return;
    }
    
    dashboardSection.style.display = 'none';
    dynamicContent.style.display = 'block';
    
    // Load section content
    loadSectionContent(sectionName);
}

/**
 * Load Section Content Dynamically
 */
function loadSectionContent(section) {
    const dynamicContent = document.getElementById('dynamic-content');
    
    // Show loading spinner
    dynamicContent.innerHTML = `
        <div class="text-center py-5">
            <div class="custom-spinner"></div>
            <p class="mt-3 text-muted">Loading ${section}...</p>
        </div>
    `;
    
    // Simulate content loading based on section
    setTimeout(() => {
        switch (section) {
            case 'checkin':
                loadCheckinSection();
                break;
            case 'pain-map':
                loadPainMapSection();
                break;
            case 'medications':
                loadMedicationsSection();
                break;
            case 'comfort':
                loadComfortSection();
                break;
            case 'patients':
                loadPatientsSection();
                break;
            case 'tasks':
                loadTasksSection();
                break;
            case 'alerts':
                loadAlertsSection();
                break;
            case 'appointments':
                loadAppointmentsSection();
                break;
            case 'messages':
                loadMessagesSection();
                break;
            case 'reports':
                loadReportsSection();
                break;
            case 'resources':
                loadResourcesSection();
                break;
            default:
                dynamicContent.innerHTML = '<div class="alert alert-warning">Section not found.</div>';
        }
    }, 500);
}

/**
 * Daily Check-in Modal
 */
function initializeCheckinModal() {
    const checkinForm = document.getElementById('checkinForm');
    
    if (checkinForm) {
        checkinForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitCheckin();
        });
    }
    
    // Initialize mood options styling
    initializeMoodOptions();
    initializePainScale();
}

function openCheckinModal() {
    const checkinModal = new bootstrap.Modal(document.getElementById('checkinModal'));
    checkinModal.show();
}

function submitCheckin() {
    const form = document.getElementById('checkinForm');
    const formData = new FormData(form);
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Saving...';
    submitBtn.disabled = true;
    
    // Submit via fetch
    fetch('api/checkin.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Check-in completed successfully! 🎉', 'success');
            
            // Show encouragement message
            const hydrationGoal = parseInt(formData.get('hydration_cups') || 0);
            if (hydrationGoal >= 8) {
                showAlert('Great job meeting your hydration goal! 💧', 'info');
            }
            
            // Close modal and refresh dashboard
            bootstrap.Modal.getInstance(document.getElementById('checkinModal')).hide();
            refreshDashboardData();
            
        } else {
            showAlert(data.message || 'Error saving check-in', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Network error. Please try again.', 'danger');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

/**
 * Mood Options Styling
 */
function initializeMoodOptions() {
    const moodOptions = document.querySelectorAll('.mood-option');
    
    moodOptions.forEach(option => {
        const input = option.querySelector('input[type="radio"]');
        const emoji = option.querySelector('.mood-emoji');
        
        option.addEventListener('click', function() {
            moodOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
        });
        
        // Add CSS for mood options
        option.style.cssText = `
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem;
            border: 2px solid transparent;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 80px;
        `;
        
        emoji.style.cssText = `
            font-size: 2rem;
            margin-bottom: 0.5rem;
        `;
        
        input.style.display = 'none';
    });
    
    // Add hover and selected styles
    const style = document.createElement('style');
    style.textContent = `
        .mood-option:hover {
            border-color: var(--primary-color);
            background: rgba(108, 92, 231, 0.1);
        }
        .mood-option.selected {
            border-color: var(--primary-color);
            background: rgba(108, 92, 231, 0.2);
        }
        .pain-level {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .pain-level input {
            display: none;
        }
        .pain-level:has(input:checked) {
            background: var(--danger-color);
            color: white;
            border-color: var(--danger-color);
        }
    `;
    document.head.appendChild(style);
}

/**
 * Pain Scale Initialization
 */
function initializePainScale() {
    const painLevels = document.querySelectorAll('.pain-level');
    
    painLevels.forEach(level => {
        level.addEventListener('click', function() {
            painLevels.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

/**
 * Hydration Adjustment
 */
function adjustHydration(change) {
    const input = document.getElementById('hydration_cups');
    const currentValue = parseInt(input.value) || 0;
    const newValue = Math.max(0, Math.min(20, currentValue + change));
    input.value = newValue;
    
    // Add visual feedback
    if (newValue >= 8) {
        input.classList.add('is-valid');
        input.classList.remove('is-invalid');
    } else if (newValue < 4) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
    } else {
        input.classList.remove('is-valid', 'is-invalid');
    }
}

/**
 * Medication Logging
 */
function initializeMedicationLogging() {
    // Initialize medication buttons
    const medicationBtns = document.querySelectorAll('[onclick^="logMedication"]');
    
    medicationBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Extract medication ID and status from onclick attribute
            const onclick = this.getAttribute('onclick');
            const matches = onclick.match(/logMedication\((\d+),\s*'(\w+)'\)/);
            
            if (matches) {
                const medicationId = matches[1];
                const status = matches[2];
                logMedication(medicationId, status);
            }
        });
    });
}

function logMedication(medicationId, status) {
    const formData = new FormData();
    formData.append('medication_id', medicationId);
    formData.append('status', status);
    formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
    
    fetch('api/medications.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`Medication ${status} successfully logged`, 'success');
            refreshMedicationTiles();
        } else {
            showAlert(data.message || 'Error logging medication', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Network error. Please try again.', 'danger');
    });
}

function logMedicationQuick() {
    // Show quick medication logging modal
    showAlert('Opening quick medication logger...', 'info');
    // This would open a simplified medication logging interface
}

/**
 * Urgent Alert Function
 */
function sendUrgentAlert() {
    if (confirm('This will send an urgent alert to your caregiver. Continue?')) {
        fetch('api/urgent_alert.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                csrf_token: document.querySelector('input[name="csrf_token"]').value,
                message: 'Urgent assistance needed'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Urgent alert sent to your caregiver', 'warning');
            } else {
                showAlert('Error sending alert. Please call directly if urgent.', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Network error. Please call directly if urgent.', 'danger');
        });
    }
}

/**
 * Refresh Dashboard Data
 */
function refreshDashboardData() {
    // Refresh medication status
    refreshMedicationTiles();
    
    // Refresh check-in status
    fetch('?ajax=checkin_status')
        .then(response => response.json())
        .then(data => {
            updateCheckinStatus(data.completed);
        })
        .catch(error => console.error('Error refreshing checkin status:', error));
}

function refreshMedicationTiles() {
    fetch('?ajax=medication_status')
        .then(response => response.json())
        .then(medications => {
            medications.forEach(med => {
                updateMedicationTile(med.id, med.taken_today > 0);
            });
        })
        .catch(error => console.error('Error refreshing medications:', error));
}

function updateMedicationTile(medicationId, taken) {
    const tiles = document.querySelectorAll('.medication-tile');
    tiles.forEach(tile => {
        const buttons = tile.querySelectorAll('button[onclick*="' + medicationId + '"]');
        if (buttons.length > 0) {
            const ring = tile.querySelector('.adherence-ring');
            if (ring) {
                ring.style.setProperty('--percentage', taken ? 100 : 0);
                ring.textContent = taken ? '✓' : '○';
            }
        }
    });
}

function updateCheckinStatus(completed) {
    const statusElements = document.querySelectorAll('.care-item:has-text("Check-in Status")');
    statusElements.forEach(element => {
        const statusText = element.querySelector('strong + br + *');
        if (statusText) {
            if (completed) {
                statusText.innerHTML = '<i class="bi bi-check-circle text-success"></i> Completed';
            } else {
                statusText.innerHTML = '<i class="bi bi-clock text-warning"></i> Pending';
            }
        }
    });
}

/**
 * Section Content Loaders
 */
function loadCheckinSection() {
    document.getElementById('dynamic-content').innerHTML = `
        <div class="widget-card">
            <div class="card-header">
                <h5><i class="bi bi-journal-check me-2"></i>Daily Check-in History</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <canvas id="checkinChart" width="400" height="200"></canvas>
                    </div>
                    <div class="col-md-4">
                        <h6>This Week's Summary</h6>
                        <ul class="list-unstyled">
                            <li><strong>Check-ins completed:</strong> 6/7 days</li>
                            <li><strong>Average mood:</strong> 3.8/5</li>
                            <li><strong>Average pain:</strong> 4.2/10</li>
                            <li><strong>Hydration goal met:</strong> 4/7 days</li>
                        </ul>
                        <button class="btn btn-primary" onclick="openCheckinModal()">
                            <i class="bi bi-plus me-1"></i>New Check-in
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function loadPainMapSection() {
    document.getElementById('dynamic-content').innerHTML = `
        <div class="widget-card">
            <div class="card-header">
                <h5><i class="bi bi-person me-2"></i>Pain Pattern Map</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="pain-map-container">
                            <h6>Click on the body areas where you feel pain:</h6>
                            <svg class="body-diagram" viewBox="0 0 200 400" style="border: 1px solid #ddd; background: #f8f9fa;">
                                <!-- Simple body outline -->
                                <ellipse cx="100" cy="50" rx="30" ry="40" fill="none" stroke="#333" stroke-width="2"/>
                                <rect x="70" y="80" width="60" height="100" rx="10" fill="none" stroke="#333" stroke-width="2"/>
                                <rect x="60" y="100" width="25" height="60" rx="5" fill="none" stroke="#333" stroke-width="2"/>
                                <rect x="115" y="100" width="25" height="60" rx="5" fill="none" stroke="#333" stroke-width="2"/>
                                <rect x="80" y="180" width="20" height="80" rx="5" fill="none" stroke="#333" stroke-width="2"/>
                                <rect x="100" y="180" width="20" height="80" rx="5" fill="none" stroke="#333" stroke-width="2"/>
                                <text x="100" y="30" text-anchor="middle" font-size="12">Head</text>
                                <text x="100" y="130" text-anchor="middle" font-size="12">Torso</text>
                                <text x="100" y="300" text-anchor="middle" font-size="12">Legs</text>
                            </svg>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Pain Level Scale</h6>
                        <div class="pain-scale d-flex justify-content-between mb-4">
                            ${Array.from({length: 11}, (_, i) => `
                                <div class="pain-level" data-level="${i}" onclick="setPainLevel(${i})">
                                    <span>${i}</span>
                                </div>
                            `).join('')}
                        </div>
                        
                        <h6>Recent Pain Entries</h6>
                        <div class="list-group">
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Lower Back Pain</h6>
                                    <small>2 hours ago</small>
                                </div>
                                <p class="mb-1">Severity: 6/10</p>
                                <small>Duration: ~3 hours, Sharp, radiating</small>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Headache</h6>
                                    <small>Yesterday</small>
                                </div>
                                <p class="mb-1">Severity: 4/10</p>
                                <small>Duration: ~1 hour, Dull, throbbing</small>
                            </div>
                        </div>
                        
                        <button class="btn btn-primary mt-3" onclick="logNewPain()">
                            <i class="bi bi-plus me-1"></i>Log New Pain
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function loadComfortSection() {
    document.getElementById('dynamic-content').innerHTML = `
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="widget-card text-center">
                    <div class="card-body">
                        <i class="bi bi-music-note-beamed display-4 text-primary mb-3"></i>
                        <h5>Music Therapy</h5>
                        <p class="text-muted">Relaxing playlists to help you feel better</p>
                        <button class="btn btn-primary" onclick="startMusic()">
                            <i class="bi bi-play me-1"></i>Start Playlist
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="widget-card text-center">
                    <div class="card-body">
                        <i class="bi bi-lightbulb display-4 text-warning mb-3"></i>
                        <h5>Lighting Control</h5>
                        <p class="text-muted">Adjust room lighting for comfort</p>
                        <div class="btn-group">
                            <button class="btn btn-outline-primary" onclick="setLighting('dim')">Dim</button>
                            <button class="btn btn-outline-primary" onclick="setLighting('normal')">Normal</button>
                            <button class="btn btn-outline-primary" onclick="setLighting('bright')">Bright</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="widget-card text-center">
                    <div class="card-body">
                        <i class="bi bi-wind display-4 text-info mb-3"></i>
                        <h5>Breathing Exercise</h5>
                        <p class="text-muted">Guided breathing for relaxation</p>
                        <button class="btn btn-info" onclick="startBreathing()">
                            <i class="bi bi-play me-1"></i>Start Exercise
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="widget-card">
            <div class="card-header">
                <h5><i class="bi bi-heart me-2"></i>Daily Inspiration</h5>
            </div>
            <div class="card-body text-center">
                <blockquote class="blockquote">
                    <p class="mb-4">"Every day may not be good, but there's something good in every day."</p>
                    <footer class="blockquote-footer">Daily Inspiration</footer>
                </blockquote>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h6>Photo Memory</h6>
                        <div class="bg-light p-4 rounded">
                            <i class="bi bi-image display-4 text-muted"></i>
                            <p class="mt-2 text-muted">Today's memory photo will appear here</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Fun Fact of the Day</h6>
                        <div class="bg-primary text-white p-4 rounded">
                            <i class="bi bi-lightbulb-fill mb-2"></i>
                            <p class="mb-0">Did you know? Laughing for 15 minutes can burn as many calories as walking for 30 minutes!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Additional section loaders would go here...
function loadMedicationsSection() {
    showAlert('Loading medications section...', 'info');
}

function loadPatientsSection() {
    showAlert('Loading patients section...', 'info');
}

function loadTasksSection() {
    showAlert('Loading tasks section...', 'info');
}

function loadAlertsSection() {
    showAlert('Loading alerts section...', 'info');
}

function loadAppointmentsSection() {
    showAlert('Loading appointments section...', 'info');
}

function loadMessagesSection() {
    showAlert('Loading messages section...', 'info');
}

function loadReportsSection() {
    showAlert('Loading reports section...', 'info');
}

function loadResourcesSection() {
    document.getElementById('dynamic-content').innerHTML = `
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="widget-card">
                    <div class="card-header">
                        <h5><i class="bi bi-hospital me-2"></i>Local Healthcare</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action">
                                <strong>Melfort Hospital</strong><br>
                                <small>Emergency: (306) 752-8700</small>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <strong>Saskatchewan Cancer Agency</strong><br>
                                <small>Saskatoon: (306) 655-2662</small>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <strong>Melfort Pharmacy</strong><br>
                                <small>(306) 752-4430</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="widget-card">
                    <div class="card-header">
                        <h5><i class="bi bi-people me-2"></i>Support Groups</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action">
                                <strong>Cancer Support Group - Melfort</strong><br>
                                <small>Wednesdays 7:00 PM</small>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <strong>Online Cancer Community</strong><br>
                                <small>24/7 Support Chat</small>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <strong>Caregiver Support Network</strong><br>
                                <small>Monthly Meetings</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="widget-card">
            <div class="card-header">
                <h5><i class="bi bi-info-circle me-2"></i>Educational Resources</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6>Cancer Information</h6>
                        <ul class="list-unstyled">
                            <li><a href="#" class="text-decoration-none">Understanding Your Diagnosis</a></li>
                            <li><a href="#" class="text-decoration-none">Treatment Options</a></li>
                            <li><a href="#" class="text-decoration-none">Managing Side Effects</a></li>
                            <li><a href="#" class="text-decoration-none">Nutrition During Treatment</a></li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6>Practical Guides</h6>
                        <ul class="list-unstyled">
                            <li><a href="#" class="text-decoration-none">Financial Resources</a></li>
                            <li><a href="#" class="text-decoration-none">Transportation Options</a></li>
                            <li><a href="#" class="text-decoration-none">Insurance Navigation</a></li>
                            <li><a href="#" class="text-decoration-none">Legal Resources</a></li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6>Wellness</h6>
                        <ul class="list-unstyled">
                            <li><a href="#" class="text-decoration-none">Exercise Guidelines</a></li>
                            <li><a href="#" class="text-decoration-none">Mental Health Support</a></li>
                            <li><a href="#" class="text-decoration-none">Sleep Hygiene</a></li>
                            <li><a href="#" class="text-decoration-none">Stress Management</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Comfort Dashboard Functions
 */
function startMusic() {
    showAlert('Starting relaxing music playlist... 🎵', 'info');
    // This would integrate with music service
}

function setLighting(level) {
    showAlert(`Setting lighting to ${level} level... 💡`, 'info');
    // This would integrate with smart home systems
}

function startBreathing() {
    showAlert('Starting guided breathing exercise... 🧘‍♀️', 'info');
    // This would start a breathing exercise interface
}

/**
 * Pain Management Functions
 */
function setPainLevel(level) {
    const painLevels = document.querySelectorAll('.pain-level');
    painLevels.forEach(l => l.classList.remove('active'));
    
    const selectedLevel = document.querySelector(`[data-level="${level}"]`);
    if (selectedLevel) {
        selectedLevel.classList.add('active');
    }
    
    showAlert(`Pain level set to ${level}/10`, 'info');
}

function logNewPain() {
    showAlert('Opening pain logging form...', 'info');
    // This would open a detailed pain logging modal
}

/**
 * Mobile Responsiveness
 */
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('show');
}

// Add mobile menu toggle for smaller screens
if (window.innerWidth <= 768) {
    const navbarToggler = document.querySelector('.navbar-toggler');
    if (navbarToggler) {
        navbarToggler.addEventListener('click', toggleSidebar);
    }
}