document.addEventListener('DOMContentLoaded', function() {
    console.log('App.js loaded');

    // ========================================
    // COMMON FUNCTIONS
    // ========================================
    function showError(elementId, message) {
        const element = document.getElementById(elementId);
        if (element) element.textContent = message;
    }

    function clearErrors() {
        document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
    }

    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    function checkPasswordStrength(password) {
        let score = 0;
        if (password.length >= 8) score++;
        if (password.length >= 12) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        const strengths = [
            { className: '', text: 'Enter password' },
            { className: 'weak', text: 'Weak' },
            { className: 'weak', text: 'Weak' },
            { className: 'medium', text: 'Medium' },
            { className: 'strong', text: 'Strong' },
            { className: 'very-strong', text: 'Very Strong' }
        ];
        return strengths[Math.min(score, 5)];
    }

    function handleLogout(btn) {
        if (confirm('Are you sure you want to logout?')) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Logging out...';
            fetch('ajax/logout.php', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    btn.disabled = false;
                    btn.innerHTML = 'Logout';
                });
        }
    }

    // ========================================
    // AUTH FORMS
    // ========================================
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const tabBtns = document.querySelectorAll('.tab-btn');
    const loginFormDiv = document.getElementById('login-form');
    const registerFormDiv = document.getElementById('register-form');
    const passwordInput = document.getElementById('reg_password');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');

    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = checkPasswordStrength(password);
            strengthBar.className = 'strength-bar';
            if (password.length > 0) {
                strengthBar.classList.add(strength.className);
            }
            strengthText.textContent = strength.text;
        });
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tab = this.dataset.tab;
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            if (tab === 'login') {
                loginFormDiv.classList.add('active');
                registerFormDiv.classList.remove('active');
            } else {
                registerFormDiv.classList.add('active');
                loginFormDiv.classList.remove('active');
            }
            clearErrors();
        });
    });

    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearErrors();
            const formData = new FormData(this);
            let isValid = true;

            const fullName = formData.get('full_name');
            if (!fullName || fullName.length < 3) {
                showError('reg_full_name_error', 'Full name must be at least 3 characters');
                isValid = false;
            }
            const email = formData.get('email');
            if (!email || !validateEmail(email)) {
                showError('reg_email_error', 'Valid email is required');
                isValid = false;
            }
            const password = formData.get('password');
            const passwordStrength = checkPasswordStrength(password);
            if (password && passwordStrength.className === 'weak' && password.length > 0) {
                showError('reg_password_error', 'Password is too weak');
                isValid = false;
            }
            const confirmPassword = formData.get('confirm_password');
            if (password !== confirmPassword) {
                showError('reg_confirm_password_error', 'Passwords do not match');
                isValid = false;
            }
            const gender = formData.get('gender');
            if (!gender) {
                showError('reg_gender_error', 'Please select a gender');
                isValid = false;
            }
            const country = formData.get('country');
            if (!country) {
                showError('reg_country_error', 'Please select a country');
                isValid = false;
            }
            const privacy = formData.get('privacy_agreed');
            if (!privacy) {
                showError('reg_privacy_error', 'You must agree to the Privacy Policy');
                isValid = false;
            }
            const imageFile = document.getElementById('reg_profile_image').files[0];
            if (imageFile) {
                const allowedTypes = ['image/jpeg', 'image/png'];
                if (!allowedTypes.includes(imageFile.type)) {
                    showError('reg_profile_image_error', 'Only JPG and PNG images are allowed');
                    isValid = false;
                } else if (imageFile.size > 2 * 1024 * 1024) {
                    showError('reg_profile_image_error', 'Image size must be less than 2MB');
                    isValid = false;
                }
            }
            if (!isValid) return;

            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Registering...';

            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Register';
                if (data.success) {
                    const messageDiv = document.getElementById('register_message');
                    messageDiv.innerHTML = `<div class="success-message">${data.message}</div>`;
                    document.querySelector('.tab-btn[data-tab="login"]').click();
                    this.reset();
                    document.getElementById('login_email').value = email;
                    setTimeout(() => messageDiv.innerHTML = '', 5000);
                } else if (data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        const errorElement = document.getElementById(`reg_${key}_error`);
                        if (errorElement) errorElement.textContent = data.errors[key];
                    });
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Register';
                console.error('Error:', error);
            });
        });
    }

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearErrors();
            const formData = new FormData(this);
            let isValid = true;

            const email = formData.get('email');
            if (!email || !validateEmail(email)) {
                showError('login_email_error', 'Valid email is required');
                isValid = false;
            }
            const password = formData.get('password');
            if (!password) {
                showError('login_password_error', 'Password is required');
                isValid = false;
            }
            if (!isValid) return;

            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Logging in...';

            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Login';
                if (data.success) {
                    location.reload();
                } else if (data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        const errorElement = document.getElementById(`login_${key}_error`);
                        if (errorElement) errorElement.textContent = data.errors[key];
                    });
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Login';
                console.error('Error:', error);
            });
        });
    }

    // ========================================
    // ADMIN PANEL
    // ========================================
    const isAdminPage = document.querySelector('.admin-dashboard');
    if (isAdminPage) {
        console.log('✅ Admin panel detected');

        const sidebar = document.getElementById('sidebar');
        const mobileToggle = document.getElementById('mobileToggle');

        if (mobileToggle && sidebar) {
            mobileToggle.addEventListener('click', function(e) {
                e.preventDefault();
                sidebar.classList.toggle('active');
                const icon = this.querySelector('i');
                icon.className = sidebar.classList.contains('active') ? 'fas fa-times' : 'fas fa-bars';
            });
        }

        // Sidebar navigation
        // In the admin sidebar navigation
    document.querySelectorAll('.sidebar-menu a[data-section]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const section = this.dataset.section;
        document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
        this.classList.add('active');

        document.getElementById('dashboardContent').style.display = section === 'dashboard' ? 'block' : 'none';
        document.getElementById('usersContent').style.display = section === 'users' ? 'block' : 'none';
        document.getElementById('profileContent').style.display = section === 'profile' ? 'block' : 'none';
        document.getElementById('submissionsContent').style.display = section === 'submissions' ? 'block' : 'none';

        const titles = { 
            'dashboard': 'Dashboard', 
            'tasks': 'All Tasks', 
            'users': 'Users Management', 
            'profile': 'My Profile',
            'submissions': 'Submissions'
        };
        const icons = { 
            'dashboard': 'th-large', 
            'tasks': 'tasks', 
            'users': 'users', 
            'profile': 'user-circle',
            'submissions': 'upload'
        };
        document.getElementById('pageTitle').innerHTML = `<i class="fas fa-${icons[section] || 'th-large'}"></i> ${titles[section] || 'Dashboard'}`;

        if (section === 'dashboard' || section === 'tasks') loadAdminTasks();
        if (section === 'users' || section === 'dashboard') loadAdminUsers();
        if (section === 'submissions') loadAdminSubmissions();
        if (section === 'dashboard') loadAdminStats();

        if (window.innerWidth <= 768 && sidebar) {
            sidebar.classList.remove('active');
            const icon = mobileToggle?.querySelector('i');
            if (icon) icon.className = 'fas fa-bars';
        }
    });
});

        // Load initial admin data
        loadAdminStats();
        loadAdminTasks();
        loadAdminUsers();
        loadUsersList();

        // Add Task Button
        document.getElementById('addTaskBtn')?.addEventListener('click', function() {
            openAdminTaskModal();
        });

        // Modal close
        document.getElementById('modalClose')?.addEventListener('click', closeAdminModal);
        document.getElementById('modalCancel')?.addEventListener('click', closeAdminModal);
        document.getElementById('taskModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeAdminModal();
        });

        // Task form submit
        document.getElementById('taskForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            saveAdminTask();
        });

        // Logout
        document.getElementById('logoutBtnTop')?.addEventListener('click', function() { handleLogout(this); });
        document.getElementById('logoutBtnSidebar')?.addEventListener('click', function(e) { e.preventDefault(); handleLogout(this); });
    }

    // ========================================
    // ADMIN FUNCTIONS
    // ========================================
    function loadAdminStats() {
        fetch('ajax/get_stats.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const stats = data.stats;
                    document.getElementById('statsGrid').innerHTML = `
                        <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-tasks"></i></div><div class="stat-number">${stats.total_tasks}</div><div class="stat-label">Total Tasks</div></div>
                        <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-clock"></i></div><div class="stat-number">${stats.pending_tasks}</div><div class="stat-label">Pending</div></div>
                        <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-spinner"></i></div><div class="stat-number">${stats.in_progress_tasks}</div><div class="stat-label">In Progress</div></div>
                        <div class="stat-card"><div class="stat-icon green"><i class="fas fa-check-circle"></i></div><div class="stat-number">${stats.completed_tasks}</div><div class="stat-label">Completed</div></div>
                        <div class="stat-card"><div class="stat-icon red"><i class="fas fa-users"></i></div><div class="stat-number">${stats.total_users}</div><div class="stat-label">Total Users</div></div>
                    `;
                }
            })
            .catch(error => console.error('Error loading stats:', error));
    }

    function loadAdminTasks() {
        fetch('ajax/get_tasks.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('tasksTableBody');
                if (!tbody) return;
                tbody.innerHTML = '';
                if (data.success && data.tasks.length > 0) {
                    data.tasks.forEach(task => {
                        const statusBadge = { 'pending': 'badge-pending', 'in_progress': 'badge-in-progress', 'completed': 'badge-completed' }[task.status] || 'badge-pending';
                        const priorityBadge = { 'low': 'badge-low', 'medium': 'badge-medium', 'high': 'badge-high' }[task.priority] || 'badge-medium';
                        const dueDate = task.due_date ? new Date(task.due_date).toLocaleString() : 'No due date';
                        tbody.innerHTML += `
                            <tr>
                                <td class="task-title">${escapeHtml(task.title)}</td>
                                <td class="task-description">${escapeHtml(task.description || '')}</td>
                                <td><span class="badge ${statusBadge}">${task.status.replace('_', ' ').toUpperCase()}</span></td>
                                <td><span class="badge ${priorityBadge}">${task.priority.toUpperCase()}</span></td>
                                <td>${dueDate}</td>
                                <td>
                                    <div class="task-actions">
                                        <button class="btn-icon btn-edit" onclick="editAdminTask(${task.id})"><i class="fas fa-edit"></i></button>
                                        <button class="btn-icon btn-delete" onclick="deleteAdminTask(${task.id})"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 30px;">No tasks found</td></tr>';
                }
            })
            .catch(error => console.error('Error loading tasks:', error));
    }

    function loadAdminUsers() {
        fetch('ajax/get_users.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('usersTableBody');
                if (!tbody) return;
                tbody.innerHTML = '';
                if (data.success && data.users.length > 0) {
                    data.users.forEach(user => {
                        const imageUrl = user.profile_image ? `uploads/${user.profile_image}` : 'assets/images/default.png';
                        tbody.innerHTML += `
                            <tr>
                                <td><img src="${imageUrl}" alt="Profile" class="user-image-small" onerror="this.src='assets/images/default.png'"></td>
                                <td>${escapeHtml(user.full_name)}</td>
                                <td>${escapeHtml(user.email)}</td>
                                <td>${user.gender ? user.gender.charAt(0).toUpperCase() + user.gender.slice(1) : 'N/A'}</td>
                                <td>${escapeHtml(user.country || 'N/A')}</td>
                                <td>${formatDate(user.created_at)}</td>
                                <td><button class="btn btn-danger btn-sm delete-user" data-user-id="${user.id}"><i class="fas fa-trash"></i> Delete</button></td>
                            </tr>
                        `;
                    });
                    document.querySelectorAll('.delete-user').forEach(btn => {
                        btn.addEventListener('click', function() {
                            if (confirm('Are you sure you want to delete this user?')) {
                                deleteAdminUser(this.dataset.userId);
                            }
                        });
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 30px;">No users found</td></tr>';
                }
            })
            .catch(error => console.error('Error loading users:', error));
    }

    function loadUsersList() {
        fetch('ajax/get_users_list.php')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('taskAssignedTo');
                if (!select) return;
                select.innerHTML = '<option value="">-- Select User (Optional) --</option>';
                if (data.success) {
                    data.users.forEach(user => {
                        select.innerHTML += `<option value="${user.id}">${escapeHtml(user.full_name)} (${escapeHtml(user.email)})</option>`;
                    });
                }
            })
            .catch(error => console.error('Error loading users list:', error));
    }

    // ========================================
// ADMIN SUBMISSIONS FUNCTIONS
// ========================================

function loadAdminSubmissions(status = 'all', search = '') {
    console.log('Loading admin submissions...');
    const tbody = document.getElementById('adminSubmissionsTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 30px;"><i class="fas fa-spinner fa-spin"></i> Loading submissions...</td></tr>';
    
    let url = 'ajax/get_admin_submissions.php';
    const params = new URLSearchParams();
    if (status && status !== 'all') params.append('status', status);
    if (search) params.append('search', search);
    if (params.toString()) url += '?' + params.toString();
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            tbody.innerHTML = '';
            
            if (data.success && data.submissions && data.submissions.length > 0) {
                data.submissions.forEach(sub => {
                    const row = document.createElement('tr');
                    
                    const statusBadge = {
                        'pending': 'badge-pending',
                        'approved': 'badge-completed',
                        'rejected': 'badge-high'
                    }[sub.status] || 'badge-pending';
                    
                    const submittedDate = new Date(sub.submitted_at).toLocaleString();
                    
                    let actions = '';
                    if (sub.status === 'pending') {
                        actions = `
                            <button class="btn btn-success btn-sm review-btn" data-id="${sub.id}" style="padding: 4px 12px; background: #27ae60; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                <i class="fas fa-check"></i> Review
                            </button>
                        `;
                    } else {
                        actions = `
                            <span style="color: ${sub.status === 'approved' ? '#27ae60' : '#e74c3c'};">
                                ${sub.status === 'approved' ? '✅ Approved' : '❌ Rejected'}
                            </span>
                        `;
                    }
                    
                    row.innerHTML = `
                        <td>
                            <strong>${escapeHtml(sub.user_name)}</strong>
                            <br><small style="color: #666;">${escapeHtml(sub.user_email)}</small>
                        </td>
                        <td>${escapeHtml(sub.task_title)}</td>
                        <td><strong>${escapeHtml(sub.title)}</strong></td>
                        <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(sub.description || '')}</td>
                        <td>
                            <a href="${escapeHtml(sub.file_path)}" target="_blank" class="btn btn-sm" style="padding: 3px 10px; background: #3498db; color: white; border-radius: 4px; text-decoration: none; font-size: 11px;">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </td>
                        <td><span class="badge ${statusBadge}">${sub.status.toUpperCase()}</span></td>
                        <td>${submittedDate}</td>
                        <td>${actions}</td>
                    `;
                    tbody.appendChild(row);
                });
                
                // Add review button handlers
                document.querySelectorAll('.review-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const submissionId = this.dataset.id;
                        openReviewModal(submissionId);
                    });
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 30px;">No submissions found</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading submissions:', error);
            tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 30px; color: #e74c3c;">Error loading submissions</td></tr>';
        });
}

// ========================================
// REVIEW SUBMISSION MODAL
// ========================================

function openReviewModal(submissionId) {
    const modal = document.getElementById('reviewSubmissionModal');
    if (!modal) return;
    
    const content = document.getElementById('reviewModalContent');
    content.innerHTML = '<div style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Loading submission details...</div>';
    
    // Fetch submission details
    fetch(`ajax/get_submission.php?id=${submissionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const sub = data.submission;
                document.getElementById('reviewModalTitle').textContent = `Review Submission: ${escapeHtml(sub.title)}`;
                
                content.innerHTML = `
                    <div style="margin-bottom: 20px;">
                        <div class="detail-row">
                            <div class="detail-label">User</div>
                            <div class="detail-value"><strong>${escapeHtml(sub.user_name)}</strong> (${escapeHtml(sub.user_email)})</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Task</div>
                            <div class="detail-value">${escapeHtml(sub.task_title)}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Title</div>
                            <div class="detail-value"><strong>${escapeHtml(sub.title)}</strong></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Description</div>
                            <div class="detail-value">${escapeHtml(sub.description || 'No description')}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">File</div>
                            <div class="detail-value">
                                <a href="${escapeHtml(sub.file_path)}" target="_blank" style="color: #3498db;">
                                    <i class="fas fa-file-archive"></i> ${escapeHtml(sub.file_name)}
                                </a>
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Submitted</div>
                            <div class="detail-value">${new Date(sub.submitted_at).toLocaleString()}</div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ecf0f1;">
                        <form id="reviewForm">
                            <input type="hidden" name="submission_id" value="${sub.id}">
                            <?php echo CSRF::getTokenField(); ?>
                            
                            <div class="form-group">
                                <label for="admin_comment">Admin Comment</label>
                                <textarea id="admin_comment" name="admin_comment" placeholder="Add your feedback here..." style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; min-height: 80px;"></textarea>
                            </div>
                            
                            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                                <button type="button" class="btn-cancel" onclick="closeReviewModal()">Cancel</button>
                                <button type="button" class="btn-submit" onclick="reviewSubmission(${sub.id}, 'rejected')" style="background: #e74c3c;">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                                <button type="button" class="btn-submit" onclick="reviewSubmission(${sub.id}, 'approved')" style="background: #27ae60;">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            </div>
                        </form>
                    </div>
                `;
                
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            } else {
                content.innerHTML = `<div style="text-align: center; padding: 20px; color: #e74c3c;">${data.message || 'Error loading submission'}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div style="text-align: center; padding: 20px; color: #e74c3c;">Error loading submission details</div>';
        });
}

function closeReviewModal() {
    const modal = document.getElementById('reviewSubmissionModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

function reviewSubmission(submissionId, status) {
    if (!confirm(`Are you sure you want to ${status} this submission?`)) return;
    
    const formData = new FormData();
    formData.append('submission_id', submissionId);
    formData.append('status', status);
    formData.append('admin_comment', document.getElementById('admin_comment')?.value || '');
    
    const csrfToken = document.querySelector('#reviewForm input[name="csrf_token"]');
    if (csrfToken) formData.append('csrf_token', csrfToken.value);
    
    fetch('ajax/update_submission_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeReviewModal();
            loadAdminSubmissions();
            loadAdminStats();
            alert(`Submission ${status} successfully!`);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

// Make functions global
window.openReviewModal = openReviewModal;
window.closeReviewModal = closeReviewModal;
window.reviewSubmission = reviewSubmission;

// Admin Submission Filters
document.getElementById('adminSubmissionFilter')?.addEventListener('change', function() {
    loadAdminSubmissions(this.value, document.getElementById('adminSubmissionSearch')?.value || '');
});

document.getElementById('adminSubmissionSearch')?.addEventListener('input', function() {
    loadAdminSubmissions(document.getElementById('adminSubmissionFilter')?.value || 'all', this.value);
});

    function openAdminTaskModal(task = null) {
        const modal = document.getElementById('taskModal');
        if (!modal) return;
        if (task) {
            document.getElementById('modalTitle').textContent = 'Edit Task';
            document.getElementById('taskId').value = task.id;
            document.getElementById('taskTitle').value = task.title;
            document.getElementById('taskDescription').value = task.description || '';
            document.getElementById('taskStatus').value = task.status;
            document.getElementById('taskPriority').value = task.priority;
            document.getElementById('taskDueDate').value = task.due_date ? task.due_date.slice(0, 16) : '';
            document.getElementById('taskAssignedTo').value = task.assigned_to || '';
            document.getElementById('modalSubmit').textContent = 'Update Task';
        } else {
            document.getElementById('modalTitle').textContent = 'Add New Task';
            document.getElementById('taskId').value = '';
            document.getElementById('taskForm').reset();
            document.getElementById('taskStatus').value = 'pending';
            document.getElementById('taskPriority').value = 'medium';
            document.getElementById('modalSubmit').textContent = 'Save Task';
        }
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeAdminModal() {
        const modal = document.getElementById('taskModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function saveAdminTask() {
        const form = document.getElementById('taskForm');
        if (!form) return;
        const formData = new FormData(form);
        const taskId = document.getElementById('taskId').value;
        const url = taskId ? 'ajax/update_task.php' : 'ajax/create_task.php';
        const submitBtn = document.getElementById('modalSubmit');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.textContent = taskId ? 'Update Task' : 'Save Task';
            if (data.success) {
                closeAdminModal();
                loadAdminTasks();
                loadAdminStats();
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitBtn.textContent = taskId ? 'Update Task' : 'Save Task';
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }

    window.editAdminTask = function(taskId) {
        fetch(`ajax/get_task.php?id=${taskId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    openAdminTaskModal(data.task);
                } else {
                    alert('Error loading task: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
    };

    window.deleteAdminTask = function(taskId) {
        if (!confirm('Are you sure you want to delete this task?')) return;
        const formData = new FormData();
        formData.append('task_id', taskId);
        const csrfToken = document.querySelector('input[name="csrf_token"]');
        if (csrfToken) formData.append('csrf_token', csrfToken.value);
        fetch('ajax/delete_task.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadAdminTasks();
                loadAdminStats();
                alert('Task deleted successfully');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    };

    function deleteAdminUser(userId) {
        const formData = new FormData();
        formData.append('user_id', userId);
        const csrfToken = document.querySelector('input[name="csrf_token"]');
        if (csrfToken) formData.append('csrf_token', csrfToken.value);
        fetch('ajax/delete_user.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadAdminUsers();
                loadAdminStats();
                alert('User deleted successfully');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // ========================================
// USER PANEL - UPDATED
// ========================================
const isUserPage = document.querySelector('.user-dashboard');
if (isUserPage) {
    console.log('✅ User panel detected');

    const userSidebar = document.getElementById('userSidebar');
    const userMobileToggle = document.getElementById('userMobileToggle');

    if (userMobileToggle && userSidebar) {
        userMobileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            userSidebar.classList.toggle('active');
            const icon = this.querySelector('i');
            icon.className = userSidebar.classList.contains('active') ? 'fas fa-times' : 'fas fa-bars';
        });
    }

    // Sidebar navigation - Updated to remove dashboard
    document.querySelectorAll('.user-sidebar .sidebar-menu a[data-section]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.dataset.section;
            document.querySelectorAll('.user-sidebar .sidebar-menu a').forEach(a => a.classList.remove('active'));
            this.classList.add('active');

            // Show/hide content
            document.getElementById('userTasksContent').style.display = section === 'tasks' ? 'block' : 'none';
            document.getElementById('userSubmissionsContent').style.display = section === 'submissions' ? 'block' : 'none';
            document.getElementById('userProfileContent').style.display = section === 'profile' ? 'block' : 'none';

            // Update page title
            const titles = {
                'tasks': 'My Tasks',
                'submissions': 'My Submissions',
                'profile': 'My Profile'
            };
            const icons = {
                'tasks': 'tasks',
                'submissions': 'upload',
                'profile': 'user-circle'
            };
            document.getElementById('userPageTitle').innerHTML = `<i class="fas fa-${icons[section] || 'tasks'}"></i> ${titles[section] || 'My Tasks'}`;

            // Load data based on section
            if (section === 'tasks') loadUserTaskCards();
            if (section === 'submissions') loadUserSubmissions();

            // Close mobile sidebar
            if (window.innerWidth <= 768 && userSidebar) {
                userSidebar.classList.remove('active');
                const icon = userMobileToggle?.querySelector('i');
                if (icon) icon.className = 'fas fa-bars';
            }
        });
    });

    // Load initial user data
    loadUserStats();
    loadUserTaskCards();
    loadUserSubmissions();

    // Task filter
    document.getElementById('userTaskFilter')?.addEventListener('change', function() {
        loadUserTaskCards(this.value, document.getElementById('userTaskSearch')?.value || '');
    });
    document.getElementById('userTaskSearch')?.addEventListener('input', function() {
        loadUserTaskCards(document.getElementById('userTaskFilter')?.value || 'all', this.value);
    });

    // Task Details Modal
    document.getElementById('taskDetailsClose')?.addEventListener('click', closeTaskDetailsModal);
    document.getElementById('taskDetailsModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeTaskDetailsModal();
    });

    // Submit Modal
    document.getElementById('submitModalClose')?.addEventListener('click', closeSubmitModal);
    document.getElementById('submitModalCancel')?.addEventListener('click', closeSubmitModal);
    document.getElementById('submitTaskModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeSubmitModal();
    });

    document.getElementById('submitTaskForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        handleTaskSubmission(this);
    });

    // Logout
    document.getElementById('logoutBtnUser')?.addEventListener('click', function() { handleLogout(this); });
}

    // ========================================
    // USER FUNCTIONS
    // ========================================
    function loadUserStats() {
        fetch('ajax/get_user_stats.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const stats = data.stats;
                    document.getElementById('totalTasks').textContent = stats.total_tasks || 0;
                    document.getElementById('pendingTasks').textContent = stats.pending_tasks || 0;
                    document.getElementById('inProgressTasks').textContent = stats.in_progress_tasks || 0;
                    document.getElementById('completedTasks').textContent = stats.completed_tasks || 0;
                    document.getElementById('submittedTasks').textContent = stats.submitted_tasks || 0;
                }
            })
            .catch(error => console.error('Error loading stats:', error));
    }

    function loadUserTaskCards(status = 'all', search = '') {
        const container = document.getElementById('userTaskCards');
        if (!container) return;
        container.innerHTML = '<div style="text-align: center; padding: 40px; grid-column: 1/-1;"><i class="fas fa-spinner fa-spin"></i> Loading tasks...</div>';

        let url = 'ajax/get_user_tasks.php';
        const params = new URLSearchParams();
        if (status && status !== 'all') params.append('status', status);
        if (search) params.append('search', search);
        if (params.toString()) url += '?' + params.toString();

        fetch(url)
            .then(response => response.json())
            .then(data => {
                container.innerHTML = '';
                if (data.success && data.tasks && data.tasks.length > 0) {
                    data.tasks.forEach(task => {
                        const statusBadge = { 'pending': 'badge-pending', 'in_progress': 'badge-in-progress', 'completed': 'badge-completed' }[task.status] || 'badge-pending';
                        const priorityBadge = { 'low': 'badge-low', 'medium': 'badge-medium', 'high': 'badge-high' }[task.priority] || 'badge-medium';
                        const dueDate = task.due_date ? new Date(task.due_date).toLocaleString() : 'No due date';
                        const hasSubmitted = task.has_submitted > 0;
                        const assignmentText = task.assigned_to_name || 'Available';
                        const isCompleted = task.status === 'completed';

                        let actions = '';
                        if (isCompleted) {
                            actions = `<div class="btn btn-completed"><i class="fas fa-check-circle"></i> Completed</div>`;
                        } else if (hasSubmitted) {
                            actions = `<div class="btn btn-completed"><i class="fas fa-clock"></i> Submitted</div>`;
                        } else {
                            actions = `
                                <button class="btn btn-view" onclick="viewTaskDetails(${task.id})"><i class="fas fa-eye"></i> View</button>
                                <button class="btn btn-submit" onclick="openSubmitModal(${task.id}, '${escapeHtml(task.title)}')"><i class="fas fa-upload"></i> Submit</button>
                            `;
                        }

                        container.innerHTML += `
                            <div class="task-card priority-${task.priority} ${isCompleted ? 'status-completed' : ''}">
                                <div class="task-card-header">
                                    <div class="task-card-title">${escapeHtml(task.title)}</div>
                                    <span class="task-card-status badge ${statusBadge}">${task.status.replace('_', ' ').toUpperCase()}</span>
                                </div>
                                <div class="task-card-description">${escapeHtml(task.description || 'No description')}</div>
                                <div class="task-card-meta">
                                    <span><i class="fas fa-tag"></i> ${task.priority.toUpperCase()}</span>
                                    <span><i class="fas fa-calendar"></i> ${dueDate}</span>
                                    <span><i class="fas fa-user"></i> ${assignmentText}</span>
                                </div>
                                <div class="task-card-actions">${actions}</div>
                            </div>
                        `;
                    });
                } else {
                    container.innerHTML = '<div style="text-align: center; padding: 40px; color: #666; grid-column: 1/-1;">No tasks found. Tasks assigned to you will appear here.</div>';
                }
            })
            .catch(error => {
                console.error('Error loading tasks:', error);
                container.innerHTML = '<div style="text-align: center; padding: 40px; color: #e74c3c; grid-column: 1/-1;">Error loading tasks. Please refresh.</div>';
            });
    }

    function loadUserSubmissions() {
        const container = document.getElementById('userSubmissionsList');
        if (!container) return;
        container.innerHTML = '<div style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin"></i> Loading submissions...</div>';

        fetch('ajax/get_user_submissions.php')
            .then(response => response.json())
            .then(data => {
                container.innerHTML = '';
                if (data.success && data.submissions && data.submissions.length > 0) {
                    data.submissions.forEach(sub => {
                        const statusBadge = { 'pending': 'badge-pending', 'approved': 'badge-completed', 'rejected': 'badge-high' }[sub.status] || 'badge-pending';
                        container.innerHTML += `
                            <div class="submission-item">
                                <div class="submission-header">
                                    <div>
                                        <div class="submission-title">${escapeHtml(sub.title)}</div>
                                        <div class="submission-task"><i class="fas fa-tasks"></i> ${escapeHtml(sub.task_title)}</div>
                                    </div>
                                    <span class="badge ${statusBadge}">${sub.status.toUpperCase()}</span>
                                </div>
                                <div class="submission-description">${escapeHtml(sub.description || 'No description')}</div>
                                <div class="submission-meta">
                                    <span><i class="fas fa-file"></i> <a href="${escapeHtml(sub.file_path)}" target="_blank">${escapeHtml(sub.file_name)}</a></span>
                                    <span><i class="fas fa-calendar"></i> ${new Date(sub.submitted_at).toLocaleString()}</span>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    container.innerHTML = '<div style="text-align: center; padding: 40px; color: #666;">No submissions yet</div>';
                }
            })
            .catch(error => {
                console.error('Error loading submissions:', error);
                container.innerHTML = '<div style="text-align: center; padding: 40px; color: #e74c3c;">Error loading submissions</div>';
            });
    }

    // Task Details
   // ========================================
// VIEW TASK DETAILS - FIXED
// ========================================
window.viewTaskDetails = function(taskId) {
    console.log('Viewing task details for ID:', taskId);
    
    const modal = document.getElementById('taskDetailsModal');
    if (!modal) {
        console.error('Task details modal not found!');
        alert('Modal not found');
        return;
    }
    
    const content = document.getElementById('taskDetailsContent');
    if (!content) {
        console.error('Task details content not found!');
        alert('Content container not found');
        return;
    }
    
    // Show loading
    content.innerHTML = '<div style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin"></i> Loading task details...</div>';
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';

    // Fetch task details
    fetch(`ajax/get_task.php?id=${taskId}`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Task details response:', data);
            
            if (data.success && data.task) {
                const task = data.task;
                
                // Status badge
                const statusBadge = {
                    'pending': 'badge-pending',
                    'in_progress': 'badge-in-progress',
                    'completed': 'badge-completed'
                }[task.status] || 'badge-pending';
                
                // Priority badge
                const priorityBadge = {
                    'low': 'badge-low',
                    'medium': 'badge-medium',
                    'high': 'badge-high'
                }[task.priority] || 'badge-medium';
                
                // Set modal title
                document.getElementById('taskDetailsTitle').textContent = `Task: ${escapeHtml(task.title)}`;
                
                // Build details HTML
                let html = `
                    <div class="task-details-content">
                        <div class="detail-row">
                            <div class="detail-label">Title</div>
                            <div class="detail-value"><strong>${escapeHtml(task.title)}</strong></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Description</div>
                            <div class="detail-value">${escapeHtml(task.description || 'No description')}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Status</div>
                            <div class="detail-value"><span class="badge ${statusBadge}">${task.status.replace('_', ' ').toUpperCase()}</span></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Priority</div>
                            <div class="detail-value"><span class="badge ${priorityBadge}">${task.priority.toUpperCase()}</span></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Due Date</div>
                            <div class="detail-value">${task.due_date ? new Date(task.due_date).toLocaleString() : 'No due date'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Assigned To</div>
                            <div class="detail-value">${task.assigned_to_name || 'Available'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Created By</div>
                            <div class="detail-value">${task.created_by_name || 'Unknown'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Created At</div>
                            <div class="detail-value">${new Date(task.created_at).toLocaleString()}</div>
                        </div>
                `;
                
                // Add submit button if task is not completed
                if (task.status !== 'completed') {
                    html += `
                        <div style="margin-top: 20px; text-align: center; padding-top: 20px; border-top: 1px solid #ecf0f1;">
                            <button class="btn btn-submit" onclick="closeTaskDetailsModal(); openSubmitModal(${task.id}, '${escapeHtml(task.title)}')" style="padding: 10px 30px; background: #27ae60; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                                <i class="fas fa-upload"></i> Submit Task
                            </button>
                        </div>
                    `;
                }
                
                html += `</div>`;
                content.innerHTML = html;
                
            } else {
                content.innerHTML = `<div style="text-align: center; padding: 40px; color: #e74c3c;">
                    <i class="fas fa-exclamation-circle" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>Error Loading Task</h3>
                    <p>${data.message || 'Task not found or you do not have access.'}</p>
                    <button class="btn btn-primary" onclick="closeTaskDetailsModal()" style="margin-top: 15px; padding: 8px 25px; background: #3498db; color: white; border: none; border-radius: 6px; cursor: pointer;">Close</button>
                </div>`;
            }
        })
        .catch(error => {
            console.error('Error loading task details:', error);
            content.innerHTML = `<div style="text-align: center; padding: 40px; color: #e74c3c;">
                <i class="fas fa-exclamation-circle" style="font-size: 48px; margin-bottom: 15px;"></i>
                <h3>Error Loading Task</h3>
                <p>${error.message || 'An error occurred while loading task details.'}</p>
                <button class="btn btn-primary" onclick="closeTaskDetailsModal()" style="margin-top: 15px; padding: 8px 25px; background: #3498db; color: white; border: none; border-radius: 6px; cursor: pointer;">Close</button>
            </div>`;
        });
};

// Close task details modal
function closeTaskDetailsModal() {
    const modal = document.getElementById('taskDetailsModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Make sure close function is available globally
window.closeTaskDetailsModal = closeTaskDetailsModal;

    // Submit Task
    window.openSubmitModal = function(taskId, taskTitle) {
        const modal = document.getElementById('submitTaskModal');
        if (!modal) return;
        document.getElementById('submitModalTitle').textContent = `Submit Task: ${taskTitle}`;
        document.getElementById('submitTaskId').value = taskId;
        document.getElementById('submitTaskForm').reset();
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    function closeSubmitModal() {
        const modal = document.getElementById('submitTaskModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function handleTaskSubmission(form) {
        const formData = new FormData(form);
        const submitBtn = document.getElementById('submitModalSubmit');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        fetch('ajax/submit_task.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Task';
            if (data.success) {
                closeSubmitModal();
                loadUserTaskCards();
                loadUserSubmissions();
                loadUserStats();
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Task';
            console.error('Error submitting task:', error);
            alert('An error occurred. Please try again.');
        });
    }

    console.log('✅ All panels initialized successfully');
});