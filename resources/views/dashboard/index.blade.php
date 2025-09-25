@extends('layouts.app')

@section('title', 'User Management Dashboard')

@section('page-title', 'User Management Dashboard')

@section('header-actions')
    <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" id="searchUsers" placeholder="Search users..." style="padding: 10px 15px 10px 40px; border: 2px solid #e1e8ed; border-radius: 25px; width: 100%; max-width: 300px; font-size: 14px;">
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus"></i>
        <span class="btn-text">Add User</span>
    </a>
@endsection

@section('additional-styles')
<style>
    .search-bar {
        position: relative;
        width: 100%;
        max-width: 300px;
    }

    .search-bar i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #7f8c8d;
        z-index: 10;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        text-align: center;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
    }

    .stat-icon {
        font-size: 2em;
        margin-bottom: 8px;
    }

    .stat-number {
        font-size: 1.5em;
        font-weight: bold;
        color: #2c3e50;
    }

    .stat-label {
        color: #7f8c8d;
        font-weight: 500;
        font-size: 0.9em;
    }

    .users-table-container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        overflow-x: auto;
    }

    .table-header {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e1e8ed;
    }

    .table-title {
        font-size: 1.2em;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    .filter-select {
        padding: 6px 10px;
        border: 2px solid #e1e8ed;
        border-radius: 6px;
        font-size: 13px;
        background: white;
        width: 100%;
        max-width: 150px;
    }

    .users-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .users-table th,
    .users-table td {
        padding: 10px 8px;
        text-align: left;
        border-bottom: 1px solid #e1e8ed;
        font-size: 13px;
        white-space: nowrap;
    }

    .users-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .users-table tr:hover {
        background: #f8f9fa;
    }

    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(145deg, #3498db, #2980b9);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        margin-right: 8px;
        font-size: 12px;
    }

    .user-info {
        display: flex;
        align-items: center;
    }

    .user-details {
        display: flex;
        flex-direction: column;
    }

    .user-name {
        font-weight: 600;
        color: #2c3e50;
        font-size: 13px;
    }

    .status-badge {
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.75em;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }

    .actions-cell {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .btn.btn-sm {
        padding: 6px 10px;
        font-size: 12px;
        touch-action: manipulation;
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 20px;
        gap: 8px;
        flex-wrap: wrap;
    }

    .pagination button {
        padding: 6px 10px;
        border: 1px solid #e1e8ed;
        background: white;
        cursor: pointer;
        border-radius: 5px;
        font-size: 12px;
        transition: all 0.3s ease;
    }

    .pagination button:hover {
        background: #f8f9fa;
    }

    .pagination button.active {
        background: #3498db;
        color: white;
        border-color: #3498db;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        overflow-y: auto;
        padding: 10px;
    }

    .modal-content {
        background: white;
        margin: 5% auto;
        padding: 15px;
        border-radius: 10px;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr 1fr;
        }

        .search-bar {
            max-width: 100%;
        }

        .btn-text {
            display: none;
        }

        .btn.btn-primary {
            padding: 8px;
            font-size: 14px;
        }

        .users-table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .users-table th,
        .users-table td {
            font-size: 12px;
        }

        .table-header {
            flex-direction: column;
            gap: 10px;
        }

        .table-filters {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-select {
            max-width: 100%;
        }

        .actions-cell {
            flex-direction: row;
            justify-content: flex-start;
            flex-wrap: wrap;
        }

        .modal-content {
            margin: 10% auto;
            padding: 10px;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .stat-card {
            padding: 10px;
        }

        .stat-icon {
            font-size: 1.5em;
        }

        .stat-number {
            font-size: 1.2em;
        }

        .stat-label {
            font-size: 0.8em;
        }

        .users-table th,
        .users-table td {
            padding: 6px 4px;
            font-size: 11px;
        }

        .user-avatar {
            width: 28px;
            height: 28px;
            font-size: 10px;
        }

        .user-name {
            font-size: 12px;
        }

        .btn.btn-sm {
            padding: 5px 8px;
            font-size: 11px;
        }
    }
</style>
@endsection

@section('content')
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="color: #27ae60;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-number" id="totalUsers">{{ $stats['total'] ?? 0 }}</div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="color: #3498db;">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-number" id="activeUsers">{{ $stats['active'] ?? 0 }}</div>
            <div class="stat-label">Active Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="color: #f39c12;">
                <i class="fas fa-user-clock"></i>
            </div>
            <div class="stat-number" id="pendingUsers">{{ $stats['pending'] ?? 0 }}</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="color: #e74c3c;">
                <i class="fas fa-user-times"></i>
            </div>
            <div class="stat-number" id="inactiveUsers">{{ $stats['inactive'] ?? 0 }}</div>
            <div class="stat-label">Inactive</div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="users-table-container">
        <div class="table-header">
            <div class="table-title">
                <i class="fas fa-table"></i>
                Users Directory
            </div>
            <div class="table-filters">
                <select class="filter-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

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
                            <button class="btn btn-sm btn-primary" onclick="viewUser('{{ $user->id }}')">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="editUser('{{ $user->id }}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="disableUser('{{ $user->id }}')">
                                <i class="fas fa-user-slash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No users found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- View User Modal -->
    <div id="viewUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #e1e8ed;">
                <h3 style="font-size: 1.2em; font-weight: 600; color: #2c3e50; margin: 0;">User Details</h3>
                <span class="close" onclick="closeModal('viewUserModal')" style="font-size: 24px; font-weight: bold; cursor: pointer; color: #aaa;">&times;</span>
            </div>
            <div id="userDetailsContent">
                <!-- User details will be populated here -->
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #e1e8ed;">
                <h3 style="font-size: 1.2em; font-weight: 600; color: #2c3e50; margin: 0;">Edit User</h3>
                <span class="close" onclick="closeModal('editUserModal')" style="font-size: 24px; font-weight: bold; cursor: pointer; color: #aaa;">&times;</span>
            </div>
            <div id="editUserContent">
                <!-- Edit form will be populated here -->
            </div>
        </div>
    </div>
@endsection

@section('additional-scripts')
<script>
    let currentPage = 1;
    let filters = {
        search: '',
        status: ''
    };

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        loadUsers();
        setupEventListeners();
    });

    function setupEventListeners() {
        // Search functionality
        document.getElementById('searchUsers').addEventListener('input', debounce(function(e) {
            filters.search = e.target.value;
            currentPage = 1;
            loadUsers();
        }, 300));

        // Filter functionality
        document.getElementById('statusFilter').addEventListener('change', function(e) {
            filters.status = e.target.value;
            currentPage = 1;
            loadUsers();
        });
    }

    function loadUsers() {
        showLoading();
        
        const params = new URLSearchParams({
            page: currentPage,
            ...filters
        });

        axios.get(`/api/users?${params}`)
            .then(response => {
                updateUsersTable(response.data.data);
                updateStats();
                hideLoading();
            })
            .catch(error => {
                console.error('Error loading users:', error);
                showAlert('error', 'Error', 'Failed to load users');
                hideLoading();
            });
    }

    function updateUsersTable(users) {
        const tbody = document.getElementById('usersTableBody');
        
        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">No users found</td></tr>';
            return;
        }

        tbody.innerHTML = users.map(user => `
            <tr>
                <td>
                    <div class="user-info">
                        <div class="user-avatar">${user.name.substring(0, 2).toUpperCase()}</div>
                        <div class="user-details">
                            <div class="user-name">${user.name}</div>
                        </div>
                    </div>
                </td>
                <td>${user.email}</td>
                <td>${user.login_id || 'N/A'}</td>
                <td>${user.employee_id}</td>
                <td>${new Date(user.created_at).toLocaleDateString()}</td>
                <td><span class="status-badge status-${user.status}">${user.status}</span></td>
                <td class="actions-cell">
                    <button class="btn btn-sm btn-primary" onclick="viewUser('${user.id}')">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editUser('${user.id}')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="disableUser('${user.id}')">
                        <i class="fas fa-user-slash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function updateStats() {
        axios.get('/api/dashboard/stats')
            .then(response => {
                const stats = response.data;
                document.getElementById('totalUsers').textContent = stats.total;
                document.getElementById('activeUsers').textContent = stats.active;
                document.getElementById('pendingUsers').textContent = stats.pending;
                document.getElementById('inactiveUsers').textContent = stats.inactive;
            })
            .catch(error => {
                console.error('Error loading stats:', error);
            });
    }

    function viewUser(userId) {
        showLoading();
        
        axios.get(`/api/users/${userId}`)
            .then(response => {
                const user = response.data;
                const content = `
                    <div style="display: grid; grid-template-columns: 1fr; gap: 10px; margin: 15px 0;">
                        <div><strong>Name:</strong> ${user.name}</div>
                        <div><strong>Email:</strong> ${user.email}</div>
                        <div><strong>Login ID:</strong> ${user.login_id || 'N/A'}</div>
                        <div><strong>Employee ID:</strong> ${user.employee_id}</div>
                        <div><strong>Created On:</strong> ${new Date(user.created_at).toLocaleDateString()}</div>
                        <div><strong>Status:</strong> <span class="status-badge status-${user.status}">${user.status}</span></div>
                    </div>
                `;
                document.getElementById('userDetailsContent').innerHTML = content;
                document.getElementById('viewUserModal').style.display = 'block';
                hideLoading();
            })
            .catch(error => {
                console.error('Error loading user:', error);
                showAlert('error', 'Error', 'Failed to load user details');
                hideLoading();
            });
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
                
                axios.delete(`/api/users/${userId}`)
                    .then(response => {
                        showAlert('success', 'Success', 'User has been disabled');
                        loadUsers();
                        hideLoading();
                    })
                    .catch(error => {
                        console.error('Error disabling user:', error);
                        showAlert('error', 'Error', 'Failed to disable user');
                        hideLoading();
                    });
            }
        );
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
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
</script>
@endsection