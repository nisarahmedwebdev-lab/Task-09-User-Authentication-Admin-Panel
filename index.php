<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/session.php';

// Countries array
$countries = [
    'Afghanistan', 'Albania', 'Algeria', 'Argentina', 'Australia', 'Austria', 
    'Bangladesh', 'Belgium', 'Brazil', 'Canada', 'China', 'Denmark', 'Egypt',
    'Finland', 'France', 'Germany', 'Greece', 'India', 'Indonesia', 'Iran',
    'Iraq', 'Ireland', 'Italy', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya',
    'Kuwait', 'Lebanon', 'Malaysia', 'Mexico', 'Netherlands', 'New Zealand',
    'Nigeria', 'Norway', 'Pakistan', 'Philippines', 'Poland', 'Portugal',
    'Qatar', 'Romania', 'Russia', 'Saudi Arabia', 'Singapore', 'South Africa',
    'South Korea', 'Spain', 'Sweden', 'Switzerland', 'Taiwan', 'Thailand',
    'Turkey', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States',
    'Vietnam'
];

$currentUser = SessionManager::getCurrentUser();
$isAdmin = $currentUser && $currentUser['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php if (!$currentUser): ?>
        <!-- Auth Forms -->
        <div class="container">
            <div id="auth-container">
                <div class="auth-header">
                    <h1>Welcome</h1>
                    <p>Please login or register to continue</p>
                </div>
                
                <div class="auth-tabs">
                    <button class="tab-btn active" data-tab="login">Login</button>
                    <button class="tab-btn" data-tab="register">Register</button>
                </div>
                
                <!-- Login Form -->
                <div id="login-form" class="auth-form active">
                    <form id="loginForm" method="POST" action="ajax/login.php">
                        <?php echo CSRF::getTokenField(); ?>
                        <div class="form-group">
                            <label for="login_email">Email</label>
                            <input type="email" id="login_email" name="email" required>
                            <span class="error-message" id="login_email_error"></span>
                        </div>
                        <div class="form-group">
                            <label for="login_password">Password</label>
                            <input type="password" id="login_password" name="password" required>
                            <span class="error-message" id="login_password_error"></span>
                        </div>
                        <button type="submit" class="btn btn-primary">Login</button>
                        <div id="login_message"></div>
                    </form>
                </div>
                
                <!-- Register Form -->
                <div id="register-form" class="auth-form">
                    <form id="registerForm" method="POST" action="ajax/register.php" enctype="multipart/form-data">
                        <?php echo CSRF::getTokenField(); ?>
                        <div class="form-group">
                            <label for="reg_full_name">Full Name</label>
                            <input type="text" id="reg_full_name" name="full_name" required minlength="3">
                            <span class="error-message" id="reg_full_name_error"></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="reg_email">Email</label>
                            <input type="email" id="reg_email" name="email" required>
                            <span class="error-message" id="reg_email_error"></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="reg_password">Password</label>
                            <input type="password" id="reg_password" name="password" required>
                            <div class="password-strength">
                                <div class="strength-bar" id="strengthBar"></div>
                                <span class="strength-text" id="strengthText">Enter password</span>
                            </div>
                            <span class="error-message" id="reg_password_error"></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="reg_confirm_password">Confirm Password</label>
                            <input type="password" id="reg_confirm_password" name="confirm_password" required>
                            <span class="error-message" id="reg_confirm_password_error"></span>
                        </div>
                        
                        <div class="form-group">
                            <label>Gender</label>
                            <div class="radio-group">
                                <label><input type="radio" name="gender" value="male"> Male</label>
                                <label><input type="radio" name="gender" value="female"> Female</label>
                                <label><input type="radio" name="gender" value="other"> Other</label>
                            </div>
                            <span class="error-message" id="reg_gender_error"></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="reg_country">Country</label>
                            <select id="reg_country" name="country">
                                <option value="">Select Country</option>
                                <?php foreach ($countries as $country): ?>
                                    <option value="<?php echo htmlspecialchars($country); ?>">
                                        <?php echo htmlspecialchars($country); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="error-message" id="reg_country_error"></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="reg_profile_image">Profile Image</label>
                            <input type="file" id="reg_profile_image" name="profile_image" accept=".jpg,.jpeg,.png">
                            <span class="error-message" id="reg_profile_image_error"></span>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="reg_privacy" name="privacy_agreed" value="1">
                                I agree to the Privacy Policy
                            </label>
                            <span class="error-message" id="reg_privacy_error"></span>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Register</button>
                        <div id="register_message"></div>
                    </form>
                </div>
            </div>
        </div>
    <?php elseif ($isAdmin): ?>
        <!-- ============================================ -->
        <!-- ADMIN PANEL -->
        <!-- ============================================ -->
        <div class="admin-dashboard">
            <aside class="sidebar" id="sidebar">
                <div class="sidebar-brand">
                    <h2><i class="fas fa-tasks"></i> Task Manager</h2>
                </div>
                <div class="sidebar-user">
                    <img src="uploads/<?php echo htmlspecialchars($currentUser['profile_image']); ?>" alt="Profile" onerror="this.src='assets/images/default.png'">
                    <h4><?php echo htmlspecialchars($currentUser['full_name']); ?></h4>
                    <p><i class="fas fa-user-shield"></i> Administrator</p>
                </div>
                <ul class="sidebar-menu">
    <li class="menu-label">Main</li>
    <li><a href="#" class="active" data-section="dashboard"><i class="fas fa-th-large"></i> Dashboard</a></li>
    <li><a href="#" data-section="tasks"><i class="fas fa-tasks"></i> All Tasks</a></li>
    <li><a href="#" data-section="submissions"><i class="fas fa-upload"></i> Submissions</a></li>
    <li><a href="#" data-section="users"><i class="fas fa-users"></i> Users</a></li>
    <li class="menu-label">Settings</li>
    <li><a href="#" data-section="profile"><i class="fas fa-user-circle"></i> Profile</a></li>
    <li><a href="#" id="logoutBtnSidebar"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
</ul>
            </aside>

            <button class="mobile-toggle" id="mobileToggle"><i class="fas fa-bars"></i></button>

            <main class="main-content">
                <nav class="top-navbar">
                    <div class="page-title" id="pageTitle"><i class="fas fa-th-large"></i> Dashboard</div>
                    <div class="navbar-actions">
                        <span><i class="fas fa-clock"></i> <?php echo date('F d, Y H:i'); ?></span>
                        <button class="btn-logout" id="logoutBtnTop">Logout</button>
                    </div>
                </nav>

                <div id="dashboardContent">
                    <div class="stats-grid" id="statsGrid"></div>
                    <div class="task-header">
                        <h3><i class="fas fa-clock"></i> Recent Tasks</h3>
                        <button class="btn-add-task" id="addTaskBtn"><i class="fas fa-plus"></i> Add Task</button>
                    </div>
                    <div class="task-table-container">
                        <table class="task-table">
                            <thead><tr><th>Task</th><th>Description</th><th>Status</th><th>Priority</th><th>Due Date</th><th>Actions</th></tr></thead>
                            <tbody id="tasksTableBody"></tbody>
                        </table>
                    </div>
                </div>

                <div id="usersContent" style="display:none;">
                    <div class="task-header"><h3><i class="fas fa-users"></i> Users Management</h3></div>
                    <div class="task-table-container">
                        <table class="task-table">
                            <thead><tr><th>Profile</th><th>Name</th><th>Email</th><th>Gender</th><th>Country</th><th>Registered</th><th>Actions</th></tr></thead>
                            <tbody id="usersTableBody"></tbody>
                        </table>
                    </div>
                </div>

                <div id="profileContent" style="display:none;">
                    <div class="profile-card" style="max-width: 600px; margin: 0 auto;">
                        <div class="profile-image" style="text-align: center;">
                            <img src="uploads/<?php echo htmlspecialchars($currentUser['profile_image']); ?>" alt="Profile Image" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid #3498db;" onerror="this.src='assets/images/default.png'">
                        </div>
                        <div class="profile-info" style="text-align: center; margin-top: 20px;">
                            <h2><?php echo htmlspecialchars($currentUser['full_name']); ?></h2>
                            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($currentUser['email']); ?></p>
                            <p><i class="fas fa-venus-mars"></i> <?php echo ucfirst(htmlspecialchars($currentUser['gender'])); ?></p>
                            <p><i class="fas fa-globe"></i> <?php echo htmlspecialchars($currentUser['country']); ?></p>
                            <p><i class="fas fa-calendar"></i> Joined: <?php echo date('F d, Y', strtotime($currentUser['created_at'])); ?></p>
                            <p><i class="fas fa-user-shield"></i> Role: Administrator</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <!-- Admin Task Modal -->
        <div class="modal" id="taskModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="modalTitle">Add New Task</h3>
                    <button class="modal-close" id="modalClose">&times;</button>
                </div>
                <form id="taskForm">
                    <input type="hidden" id="taskId" name="task_id">
                    <?php echo CSRF::getTokenField(); ?>
                    <div class="form-group">
                        <label for="taskTitle">Task Title *</label>
                        <input type="text" id="taskTitle" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="taskDescription">Description</label>
                        <textarea id="taskDescription" name="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="taskStatus">Status</label>
                        <select id="taskStatus" name="status">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="taskPriority">Priority</label>
                        <select id="taskPriority" name="priority">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="taskDueDate">Due Date</label>
                        <input type="datetime-local" id="taskDueDate" name="due_date">
                    </div>
                    <div class="form-group">
                        <label for="taskAssignedTo">Assign To</label>
                        <select id="taskAssignedTo" name="assigned_to">
                            <option value="">-- Select User (Optional) --</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" id="modalCancel">Cancel</button>
                        <button type="submit" class="btn-submit" id="modalSubmit">Save Task</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Admin Panel - Submissions Section -->
<div id="submissionsContent" style="display:none;">
    <div class="task-header">
        <h3><i class="fas fa-upload"></i> User Submissions</h3>
        <div class="task-filters">
            <select id="adminSubmissionFilter" class="filter-select">
                <option value="all">All Submissions</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
            <input type="text" id="adminSubmissionSearch" placeholder="Search submissions..." class="filter-search">
        </div>
    </div>
    <div class="task-table-container">
        <table class="task-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Task</th>
                    <th>Submission Title</th>
                    <th>Description</th>
                    <th>File</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="adminSubmissionsTableBody">
                <!-- Submissions loaded via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- Admin Submission Review Modal -->
<div class="modal" id="reviewSubmissionModal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 id="reviewModalTitle">Review Submission</h3>
            <button class="modal-close" id="reviewModalClose">&times;</button>
        </div>
        <div id="reviewModalContent">
            <!-- Submission details loaded via AJAX -->
        </div>
    </div>
</div>

    <?php elseif (!$isAdmin): ?>
    <!-- ============================================ -->
    <!-- USER PANEL - UPDATED -->
    <!-- ============================================ -->
    <div class="user-dashboard">
        <!-- Sidebar -->
        <aside class="user-sidebar" id="userSidebar">
            <div class="sidebar-brand">
                <h2><i class="fas fa-tasks"></i> Task Manager</h2>
            </div>
            
            <div class="sidebar-user">
                <img src="uploads/<?php echo htmlspecialchars($currentUser['profile_image']); ?>" 
                     alt="Profile" 
                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['full_name']); ?>&background=3498db&color=fff&size=128'">
                <h4><?php echo htmlspecialchars($currentUser['full_name']); ?></h4>
                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($currentUser['email']); ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li class="menu-label">Main</li>
                <li>
                    <a href="#" class="active" data-section="tasks">
                        <i class="fas fa-tasks"></i> My Tasks
                    </a>
                </li>
                <li>
                    <a href="#" data-section="submissions">
                        <i class="fas fa-upload"></i> My Submissions
                    </a>
                </li>
                <li class="menu-label">Account</li>
                <li>
                    <a href="#" data-section="profile">
                        <i class="fas fa-user-circle"></i> Profile
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Mobile Toggle -->
        <button class="mobile-toggle" id="userMobileToggle">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Main Content -->
        <main class="user-main-content">
            <!-- Top Navbar with Profile and Logout -->
            <nav class="user-top-navbar">
                <div class="page-title" id="userPageTitle">
                    <i class="fas fa-tasks"></i> My Tasks
                </div>
                <div class="navbar-actions">
                    <div class="user-profile-nav">
                        <img src="uploads/<?php echo htmlspecialchars($currentUser['profile_image']); ?>" 
                             alt="Profile" 
                             class="nav-profile-img"
                             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['full_name']); ?>&background=3498db&color=fff&size=40'">
                        <span class="nav-user-name"><?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                    </div>
                    <button class="btn-logout" id="logoutBtnUser">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </nav>

            <!-- Stats Cards -->
            <div class="stats-grid" id="userStatsGrid">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-tasks"></i></div>
                    <div class="stat-number" id="totalTasks">0</div>
                    <div class="stat-label">Total Tasks</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
                    <div class="stat-number" id="pendingTasks">0</div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-spinner"></i></div>
                    <div class="stat-number" id="inProgressTasks">0</div>
                    <div class="stat-label">In Progress</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-number" id="completedTasks">0</div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red"><i class="fas fa-upload"></i></div>
                    <div class="stat-number" id="submittedTasks">0</div>
                    <div class="stat-label">Submitted</div>
                </div>
            </div>

            <!-- Tasks Content -->
            <div id="userTasksContent">
                <div class="task-section-header">
                    <h3><i class="fas fa-list"></i> My Tasks</h3>
                    <div class="task-filters">
                        <select id="userTaskFilter" class="filter-select">
                            <option value="all">All Tasks</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                        <input type="text" id="userTaskSearch" placeholder="Search tasks..." class="filter-search">
                    </div>
                </div>
                <div class="task-cards-grid" id="userTaskCards">
                    <!-- Task cards loaded via AJAX -->
                </div>
            </div>

            <!-- Submissions Content -->
            <div id="userSubmissionsContent" style="display:none;">
                <div class="task-section-header">
                    <h3><i class="fas fa-upload"></i> My Submissions</h3>
                </div>
                <div class="submissions-list" id="userSubmissionsList">
                    <!-- Submissions loaded via AJAX -->
                </div>
            </div>

            <!-- Profile Content -->
            <div id="userProfileContent" style="display:none;">
                <div class="profile-card-modern">
                    <div class="profile-header">
                        <img src="uploads/<?php echo htmlspecialchars($currentUser['profile_image']); ?>" 
                             alt="Profile" 
                             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['full_name']); ?>&background=3498db&color=fff&size=200'">
                        <h2><?php echo htmlspecialchars($currentUser['full_name']); ?></h2>
                        <p><i class="fas fa-user-tag"></i> User</p>
                    </div>
                    <div class="profile-details">
                        <div class="detail-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo htmlspecialchars($currentUser['email']); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-venus-mars"></i>
                            <span><?php echo ucfirst(htmlspecialchars($currentUser['gender'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-globe"></i>
                            <span><?php echo htmlspecialchars($currentUser['country']); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar"></i>
                            <span>Joined: <?php echo date('F d, Y', strtotime($currentUser['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Task Details Modal -->
    <div class="modal" id="taskDetailsModal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3 id="taskDetailsTitle">Task Details</h3>
                <button class="modal-close" id="taskDetailsClose">&times;</button>
            </div>
            <div id="taskDetailsContent">
                <!-- Task details loaded via AJAX -->
            </div>
        </div>
    </div>

    <!-- Submit Task Modal -->
    <div class="modal" id="submitTaskModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="submitModalTitle">Submit Task</h3>
                <button class="modal-close" id="submitModalClose">&times;</button>
            </div>
            <form id="submitTaskForm" enctype="multipart/form-data">
                <input type="hidden" id="submitTaskId" name="task_id">
                <?php echo CSRF::getTokenField(); ?>
                
                <div class="form-group">
                    <label for="submissionTitle">Submission Title *</label>
                    <input type="text" id="submissionTitle" name="title" required placeholder="Enter submission title">
                </div>
                
                <div class="form-group">
                    <label for="submissionDescription">Description *</label>
                    <textarea id="submissionDescription" name="description" required placeholder="Describe your work..." style="min-height: 100px;"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="submissionFile">Upload File (ZIP) *</label>
                    <input type="file" id="submissionFile" name="file" accept=".zip" required>
                    <small style="color: #666; display: block; margin-top: 5px;">Only ZIP files allowed. Max size: 10MB</small>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="submitModalCancel">Cancel</button>
                    <button type="submit" class="btn-submit" id="submitModalSubmit">Submit Task</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

    <script src="assets/js/app.js"></script>
</body>
</html>