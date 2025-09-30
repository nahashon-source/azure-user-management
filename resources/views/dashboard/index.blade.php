<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Management Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: rgba(255, 255, 255, 0.95);
            --border-color: #e1e8ed;
            --text-muted: #7f8c8d;
            --hover-bg: #f8f9fa;
            --shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            --border-radius: 10px;
            --transition: all 0.3s ease;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            margin: 0;
            padding: 20px;
            background: #f4f6f9;
        }

        .header-controls {
            display: flex;
            gap: 15px;
            align-items: center;
            width: 100%;
            max-width: 500px;
            margin-bottom: 24px;
        }

        .search-bar {
            position: relative;
            flex: 1;
            min-width: 0;
        }

        .search-bar i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            z-index: 10;
            pointer-events: none;
        }

        .search-bar input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid var(--border-color);
            border-radius: 25px;
            font-size: 14px;
            background: white;
            transition: var(--transition);
            outline: none;
        }

        .search-bar input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--light-bg);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
            border: 1px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--success-color));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 2.5em;
            margin-bottom: 12px;
            opacity: 0.9;
        }

        .stat-number {
            font-size: 2em;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 4px;
            line-height: 1;
        }

        .stat-label {
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .users-table-container {
            background: var(--light-bg);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .table-header {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--border-color);
        }

        .table-title {
            font-size: 1.3em;
            font-weight: 600;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }

        .filter-select {
            padding: 10px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            background: white;
            min-width: 150px;
            transition: var(--transition);
            outline: none;
        }

        .filter-select:focus {
            border-color: var(--primary-color);
        }

        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
            background: white;
        }

        .users-table th,
        .users-table td {
            padding: 16px 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
            vertical-align: middle;
        }

        .users-table th {
            background: var(--hover-bg);
            font-weight: 600;
            color: var(--secondary-color);
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .users-table tbody tr {
            transition: var(--transition);
        }

        .users-table tbody tr:hover {
            background: var(--hover-bg);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), #2980b9);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 12px;
            font-size: 14px;
            flex-shrink: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            min-width: 200px;
        }

        .user-details {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .user-name {
            font-weight: 600;
            color: var(--secondary-color);
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            min-width: 70px;
            text-align: center;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .actions-cell {
            display: flex;
            gap: 6px;
            flex-wrap: nowrap;
            min-width: 140px;
        }

        .btn.btn-sm {
            padding: 8px 12px;
            font-size: 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            touch-action: manipulation;
        }

        .btn.btn-sm:hover {
            transform: translateY(-1px);
        }

        .btn.btn-sm:active {
            transform: translateY(0);
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            overflow-y: auto;
            padding: 20px;
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 24px;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--border-color);
        }

        .modal-header h3 {
            font-size: 1.4em;
            font-weight: 600;
            color: var(--secondary-color);
            margin: 0;
        }

        .close {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: var(--text-muted);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: var(--transition);
        }

        .close:hover {
            background: var(--hover-bg);
            color: var(--danger-color);
        }

        .user-details-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
            margin: 20px 0;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            padding: 12px;
            background: var(--hover-bg);
            border-radius: 6px;
            border-left: 4px solid var(--primary-color);
        }

        .detail-label {
            font-weight: 600;
            color: var(--text-muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .detail-value {
            font-size: 14px;
            color: var(--secondary-color);
            font-weight: 500;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 3em;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .table-actions {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 16px;
        }

        .btn.btn-primary {
            background: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }

        .btn.btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .users-table th,
            .users-table td {
                padding: 12px 8px;
                font-size: 13px;
            }
        }

        @media (max-width: 768px) {
            .header-controls {
                flex-direction: column;
                gap: 12px;
            }

            .search-bar {
                order: 2;
            }

            .btn.btn-primary {
                order: 1;
                align-self: stretch;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .stat-card {
                padding: 16px;
            }

            .stat-icon {
                font-size: 2em;
            }

            .stat-number {
                font-size: 1.6em;
            }

            .table-header {
                gap: 12px;
            }

            .table-filters {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-select {
                min-width: 100%;
            }

            .users-table th,
            .users-table td {
                padding: 10px 6px;
                font-size: 12px;
            }

            .user-avatar {
                width: 32px;
                height: 32px;
                font-size: 12px;
                margin-right: 8px;
            }

            .actions-cell {
                min-width: 120px;
            }

            .btn.btn-sm {
                min-width: 32px;
                height: 32px;
                padding: 6px 8px;
            }

            .modal-content {
                margin: 10px auto;
                padding: 16px;
            }

            .user-details-grid {
                gap: 12px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .stat-card {
                padding: 12px;
            }

            .stat-icon {
                font-size: 1.8em;
            }

            .stat-number {
                font-size: 1.4em;
            }

            .stat-label {
                font-size: 0.8em;
            }

            .users-table {
                min-width: 600px;
            }

            .users-table th,
            .users-table td {
                padding: 8px 4px;
                font-size: 11px;
            }

            .user-avatar {
                width: 28px;
                height: 28px;
                font-size: 10px;
                margin-right: 6px;
            }

            .user-info {
                min-width: 140px;
            }

            .actions-cell {
                min-width: 100px;
                gap: 4px;
            }

            .btn.btn-sm {
                min-width: 28px;
                height: 28px;
                padding: 4px 6px;
                font-size: 11px;
            }

            .modal-content {
                margin: 5px auto;
                padding: 12px;
                width: 95%;
            }
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --light-bg: rgba(44, 62, 80, 0.95);
                --border-color: #34495e;
                --text-muted: #bdc3c7;
                --hover-bg: #34495e;
                --secondary-color: #ecf0f1;
            }

            body {
                background: #1a252f;
            }

            .search-bar input,
            .filter-select,
            .users-table,
            .modal-content {
                background: #2c3e50;
                color: #ecf0f1;
                border-color: #34495e;
            }

            .users-table th {
                background: #34495e;
            }

            .detail-item {
                background: #34495e;
            }
        }

        @media (hover: none) and (pointer: coarse) {
            .btn.btn-sm {
                min-width: 44px;
                height: 44px;
                padding: 10px;
            }

            .close {
                width: 44px;
                height: 44px;
            }

            .stat-card:hover {
                transform: none;
            }

            .users-table tbody tr:hover {
                background: transparent;
            }
        }

        @media print {
            .header-controls,
            .actions-cell,
            .modal {
                display: none !important;
            }

            .users-table-container {
                box-shadow: none;
                border: 1px solid #ddd;
            }

            .stat-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }

        .btn:focus,
        .search-bar input:focus,
        .filter-select:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Header Controls -->
    <div class="header-controls">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" id="searchUsers" placeholder="Search users..." autocomplete="off">
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="color: var(--success-color);">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-number" id="totalUsers">{{ $stats['total'] ?? 0 }}</div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="color: var(--primary-color);">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-number" id="activeUsers">{{ $stats['active'] ?? 0 }}</div>
            <div class="stat-label">Active Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="color: var(--warning-color);">
                <i class="fas fa-user-clock"></i>
            </div>
            <div class="stat-number" id="pendingUsers">{{ $stats['pending'] ?? 0 }}</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="color: var(--danger-color);">
                <i class="fas fa-user-times"></i>
            </div>
            <div class="stat-number" id="inactiveUsers">{{ $stats['inactive'] ?? 0 }}</div>
            <div class="stat-label">Inactive</div>
        </div>
    </div>

    <!-- Add User Button -->
    <div class="table-actions">
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus"></i>
            <span class="btn-text">Add User</span>
        </a>
    </div>

    <!-- Users Table -->
    <div class="users-table-container">
       

        <div class="table-wrapper">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Login ID</th>
                        <th>Employee ID</th>
                        <th>Created On</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">{{ substr($user->name, 0, 2) }}</div>
                                    <div class="user-details">
                                        <div class="user-name">{{ $user->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->login_id ?? 'N/A' }}</td>
                            <td>{{ $user->employee_id }}</td>
                            <td>{{ $user->created_at->format('Y-m-d') }}</td>
                            <td>
                                <span class="status-badge status-{{ $user->status }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td class="actions-cell">
                                <button class="btn btn-sm btn-primary" onclick="viewUser('{{ $user->id }}')" 
                                        title="View User" aria-label="View user {{ $user->name }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="editUser('{{ $user->id }}')" 
                                        title="Edit User" aria-label="Edit user {{ $user->name }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="disableUser('{{ $user->id }}')" 
                                        title="Disable User" aria-label="Disable user {{ $user->name }}">
                                    <i class="fas fa-user-slash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                <i class="fas fa-users"></i>
                                <div>No users found</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- View User Modal -->
    <div id="viewUserModal" class="modal" role="dialog" aria-labelledby="viewUserModalTitle" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="viewUserModalTitle">User Details</h3>
                <button class="close" onclick="closeModal('viewUserModal')" aria-label="Close modal">&times;</button>
            </div>
            <div id="userDetailsContent">
                <!-- User details will be populated here -->
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal" role="dialog" aria-labelledby="editUserModalTitle" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="editUserModalTitle">Edit User</h3>
                <button class="close" onclick="closeModal('editUserModal')" aria-label="Close modal">&times;</button>
            </div>
            <div id="editUserContent">
                <!-- Edit form will be populated here -->
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let filters = {
            search: '',
            status: ''
        };

        document.addEventListener('DOMContentLoaded', function() {
            loadUsers();
            setupEventListeners();
            setupAccessibility();
        });

        function setupEventListeners() {
            const searchInput = document.getElementById('searchUsers');
            searchInput.addEventListener('input', debounce(function(e) {
                filters.search = e.target.value.trim();
                currentPage = 1;
                loadUsers();
            }, 300));

            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    e.target.value = '';
                    filters.search = '';
                    currentPage = 1;
                    loadUsers();
                }
            });

            document.getElementById('statusFilter').addEventListener('change', function(e) {
                filters.status = e.target.value;
                currentPage = 1;
                loadUsers();
            });

            window.addEventListener('click', function(e) {
                if (e.target.classList.contains('modal')) {
                    closeModal(e.target.id);
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const openModals = document.querySelectorAll('.modal[style*="block"]');
                    openModals.forEach(modal => {
                        closeModal(modal.id);
                    });
                }
            });
        }

        function setupAccessibility() {
            const liveRegion = document.createElement('div');
            liveRegion.setAttribute('aria-live', 'polite');
            liveRegion.setAttribute('aria-atomic', 'true');
            liveRegion.style.position = 'absolute';
            liveRegion.style.left = '-10000px';
            liveRegion.style.width = '1px';
            liveRegion.style.height = '1px';
            liveRegion.style.overflow = 'hidden';
            liveRegion.id = 'liveRegion';
            document.body.appendChild(liveRegion);
        }

        function announceToScreenReader(message) {
            const liveRegion = document.getElementById('liveRegion');
            if (liveRegion) {
                liveRegion.textContent = message;
            }
        }

        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
            document.body.style.overflow = '';
        }

        function loadUsers() {
            showLoading();
            
            const params = new URLSearchParams({
                page: currentPage,
                ...filters
            });

            setTimeout(() => {
                try {
                    updateStats();
                    hideLoading();
                    announceToScreenReader('Users table loaded successfully');
                } catch (error) {
                    handleError(error);
                }
            }, 500);
        }

        function updateUsersTable(users) {
            const tbody = document.getElementById('usersTableBody');
            
            if (!users || users.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="fas fa-users"></i>
                            <div>No users found</div>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = users.map(user => `
                <tr>
                    <td>
                        <div class="user-info">
                            <div class="user-avatar" title="${user.name}">${user.name.substring(0, 2).toUpperCase()}</div>
                            <div class="user-details">
                                <div class="user-name">${escapeHtml(user.name)}</div>
                            </div>
                        </div>
                    </td>
                    <td title="${user.email}">${escapeHtml(user.email)}</td>
                    <td>${escapeHtml(user.login_id || 'N/A')}</td>
                    <td>${escapeHtml(user.employee_id)}</td>
                    <td>${new Date(user.created_at).toLocaleDateString()}</td>
                    <td>
                        <span class="status-badge status-${user.status}" title="Status: ${user.status}">
                            ${user.status.charAt(0).toUpperCase() + user.status.slice(1)}
                        </span>
                    </td>
                    <td class="actions-cell">
                        <button class="btn btn-sm btn-primary" onclick="viewUser('${user.id}')" 
                                title="View User" aria-label="View user ${escapeHtml(user.name)}">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="editUser('${user.id}')" 
                                title="Edit User" aria-label="Edit user ${escapeHtml(user.name)}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="disableUser('${user.id}')" 
                                title="Disable User" aria-label="Disable user ${escapeHtml(user.name)}">
                            <i class="fas fa-user-slash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function updateStats() {
            setTimeout(() => {
                try {
                    console.log('Stats updated');
                } catch (error) {
                    handleError(error);
                }
            }, 200);
        }

        function viewUser(userId) {
            showLoading();
            
            setTimeout(() => {
                try {
                    const mockUser = {
                        id: userId,
                        name: 'John Doe',
                        email: 'john.doe@example.com',
                        login_id: 'jdoe',
                        employee_id: 'EMP001',
                        status: 'active',
                        created_at: new Date().toISOString(),
                        last_login: new Date().toISOString(),
                        department: 'IT',
                        role: 'Developer'
                    };

                    displayUserDetails(mockUser);
                    hideLoading();
                } catch (error) {
                    handleError(error);
                }
            }, 300);
        }

        function displayUserDetails(user) {
            const content = `
                <div class="user-details-grid">
                    <div class="detail-item">
                        <div class="detail-label">Name</div>
                        <div class="detail-value">${escapeHtml(user.name)}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value">${escapeHtml(user.email)}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Login ID</div>
                        <div class="detail-value">${escapeHtml(user.login_id || 'N/A')}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Employee ID</div>
                        <div class="detail-value">${escapeHtml(user.employee_id)}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Department</div>
                        <div class="detail-value">${escapeHtml(user.department || 'N/A')}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Role</div>
                        <div class="detail-value">${escapeHtml(user.role || 'N/A')}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Created On</div>
                        <div class="detail-value">${new Date(user.created_at).toLocaleDateString('en-US', { 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric' 
                        })}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Last Login</div>
                        <div class="detail-value">${user.last_login ? new Date(user.last_login).toLocaleDateString('en-US', { 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        }) : 'Never'}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <span class="status-badge status-${user.status}">
                                ${user.status.charAt(0).toUpperCase() + user.status.slice(1)}
                            </span>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('userDetailsContent').innerHTML = content;
            openModal('viewUserModal');
            announceToScreenReader(`User details loaded for ${user.name}`);
        }

        function editUser(userId) {
            window.location.href = `/users/${userId}/edit`;
        }

        function disableUser(userId) {
            confirmAction(
                'Disable User',
                'Are you sure you want to disable this user? This will revoke access to all systems.',
                function() {
                    showLoading();
                    
                    setTimeout(() => {
                        try {
                            showAlert('success', 'Success', 'User has been disabled');
                            loadUsers();
                            hideLoading();
                            announceToScreenReader('User has been disabled successfully');
                        } catch (error) {
                            handleError(error);
                        }
                    }, 500);
                }
            );
        }

        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'block';
                modal.setAttribute('aria-hidden', 'false');
                
                const firstFocusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
                if (firstFocusable) {
                    firstFocusable.focus();
                }
                
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                
                const searchInput = document.getElementById('searchUsers');
                if (searchInput) {
                    searchInput.focus();
                }
            }
        }

        function confirmAction(title, message, onConfirm) {
            if (window.confirm(`${title}\n\n${message}`)) {
                onConfirm();
            }
        }

        function showAlert(type, title, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 16px 20px;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                z-index: 10000;
                max-width: 300px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideInRight 0.3s ease-out;
            `;
            
            const colors = {
                success: '#27ae60',
                error: '#e74c3c',
                warning: '#f39c12',
                info: '#3498db'
            };
            alertDiv.style.backgroundColor = colors[type] || colors.info;
            
            alertDiv.innerHTML = `
                <div style="font-weight: 600; margin-bottom: 4px;">${escapeHtml(title)}</div>
                <div style="font-size: 14px; opacity: 0.9;">${escapeHtml(message)}</div>
            `;
            
            document.body.appendChild(alertDiv);
            
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideInRight {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
            `;
            document.head.appendChild(style);
            
            setTimeout(() => {
                alertDiv.style.animation = 'slideInRight 0.3s ease-in reverse';
                setTimeout(() => {
                    if (document.body.contains(alertDiv)) {
                        document.body.removeChild(alertDiv);
                    }
                    if (document.head.contains(style)) {
                        document.head.removeChild(style);
                    }
                }, 300);
            }, 5000);
        }

        function handleError(error) {
            console.error('Error:', error);
            hideLoading();
            showAlert('error', 'Error', error.message || 'An unexpected error occurred');
            announceToScreenReader('An error occurred. Please try again.');
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text || '').replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('SW registered: ', registration);
                    })
                    .catch(function(registrationError) {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }

        window.addEventListener('load', function() {
            if (window.performance && window.performance.timing) {
                const loadTime = window.performance.timing.loadEventEnd - window.performance.timing.navigationStart;
                console.log('Page load time:', loadTime + 'ms');
            }
        });

        window.addEventListener('online', function() {
            showAlert('success', 'Online', 'Internet connection restored');
            loadUsers();
        });

        window.addEventListener('offline', function() {
            showAlert('warning', 'Offline', 'No internet connection detected');
        });

        const createIntersectionObserver = () => {
            const options = {
                root: null,
                rootMargin: '50px',
                threshold: 0.1
            };

            return new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        console.log('Element is visible:', entry.target);
                    }
                });
            }, options);
        };

        window.UserDashboard = {
            loadUsers,
            viewUser,
            editUser,
            disableUser,
            updateStats
        };
    </script>
</body>
</html>