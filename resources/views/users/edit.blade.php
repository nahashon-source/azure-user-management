<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>Edit User</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js" defer></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

    <!-- Styles -->
    <style>
        /* ---------- Theme Variables ---------- */
        :root {
            --primary-color: #0b66ff;
            --primary-contrast: #ffffff;
            --secondary-color: #0ea5a4;
            --bg: #e9edf2;          /* softer background */
            --surface: #ffffff;
            --muted: #6b7280;
            --text: #0f172a;
            --danger: #ef4444;
            --success: #10b981;
            --radius: 10px;
            --shadow-sm: 0 2px 8px rgba(15, 23, 42, 0.06);
            --shadow-md: 0 8px 30px rgba(15, 23, 42, 0.08);
            --focus-ring: 3px rgba(11, 102, 255, 0.15);
            --max-width: 1320px;
        }

        /* ---------- Reset + Defaults ---------- */
        * { box-sizing: border-box; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
        html, body { height: 100%; margin: 0; }
        body {
            font-family: "Inter", "Figtree", "Segoe UI", system-ui, -apple-system, "Helvetica Neue", Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 16px;
            line-height: 1.45;
        }

        /* ---------- Layout ---------- */
        .app-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ---------- Main Content ---------- */
        .main {
            padding: 28px;
            max-width: var(--max-width);
            margin: 0 auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .page-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text);
        }

        /* ---------- Buttons ---------- */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: transform .08s ease, box-shadow .12s ease;
        }
        .btn:focus-visible { outline: none; box-shadow: 0 0 0 4px var(--focus-ring); }
        .btn-primary { background: var(--primary-color); color: var(--primary-contrast); box-shadow: var(--shadow-sm); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .btn-secondary { background: var(--secondary-color); color: var(--primary-contrast); }
        .btn-ghost { background: transparent; color: var(--text); border: 1px solid rgba(15,23,42,0.06); }
        .btn-danger { background: var(--danger); color: var(--primary-contrast); }
        .btn-success { background: var(--success); color: var(--primary-contrast); }
        .btn-sm { padding: 6px 10px; font-size: 0.875rem; }

        /* ---------- Cards ---------- */
        .card-surface {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 18px;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(15,23,42,0.04);
        }

        /* ---------- Loading Spinner ---------- */
        .loading-spinner {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255,255,255,0.98);
            padding: 16px 18px;
            border-radius: 10px;
            box-shadow: var(--shadow-md);
            text-align: center;
            min-width: 160px;
        }
        .loading-spinner[aria-hidden="false"] { display: block; }
        .spinner {
            width: 36px; height: 36px;
            border-radius: 50%;
            border: 4px solid rgba(15,23,42,0.06);
            border-top-color: var(--primary-color);
            margin: 0 auto 10px;
            animation: spin 0.9s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ---------- Responsive ---------- */
        @media (max-width: 991px) {
            .main { padding: 18px; }
        }

        .sr-only {
            position: absolute; width: 1px; height: 1px;
            padding: 0; margin: -1px; overflow: hidden;
            clip: rect(0,0,0,0); white-space: nowrap; border: 0;
        }

        /* ---------- Edit Form Styles ---------- */
        .edit-form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .form-section {
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 2px solid #e1e8ed;
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .form-section-title {
            font-size: 1.3em;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .form-control {
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .form-control.is-invalid {
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }

        .form-control:read-only,
        .form-control[readonly] {
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
            border-color: #ced4da;
        }

        .form-control:read-only:focus,
        .form-control[readonly]:focus {
            border-color: #ced4da;
            box-shadow: none;
        }

        select.form-control[disabled] {
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
            border-color: #ced4da;
        }

        .invalid-feedback {
            color: #e74c3c;
            font-size: 0.875em;
            margin-top: 5px;
        }

        .current-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
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

        .required {
            color: #e74c3c;
        }

        #modulesTableContainer {
            display: block;
        }

        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-height: 200px;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #e1e8ed;
            border-radius: 8px;
        }

        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 16px;
            height: 16px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .edit-form-container {
                padding: 20px;
            }
        }

        /* Assignment Cards */
        .assignments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .assignment-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            padding: 16px;
            color: white;
            box-shadow: var(--shadow-sm);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .assignment-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .assignment-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            font-size: 1.1em;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-bottom: 8px;
        }

        .assignment-card-body {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .assignment-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9em;
            opacity: 0.95;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--muted);
        }

        .empty-state i {
            font-size: 3em;
            margin-bottom: 15px;
            opacity: 0.3;
        }

        .empty-state p {
            font-size: 1.1em;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="app-shell" role="application">
        <!-- Main -->
        <main class="main" role="main">
            <header class="page-header">
                <div class="page-title">
                    <i class="fas fa-folder-open" style="color:var(--primary-color);"></i>
                    <div>
                        <div>Edit User</div>
                    </div>
                </div>
                {{-- <div>
                    <button type="submit" form="editUserForm" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Update User
                    </button>
                </div> --}}
            </header>

            <section>
                <div class="edit-form-container">
                    <form id="editUserForm" action="{{ route('users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information Section -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fas fa-user"></i>
                                Basic Information
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name" class="form-label">
                                        Full Name <span class="required">*</span>
                                    </label>
                                    <input type="text" 
                                           id="name" 
                                           name="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $user->name) }}" 
                                           readonly>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="employee_id" class="form-label">
                                        Employee ID <span class="required">*</span>
                                    </label>
                                    <input type="text" 
                                           id="employee_id" 
                                           name="employee_id" 
                                           class="form-control @error('employee_id') is-invalid @enderror" 
                                           value="{{ old('employee_id', $user->employee_id) }}" 
                                           readonly>
                                    @error('employee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        Email Address <span class="required">*</span>
                                    </label>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email', $user->email) }}" 
                                           readonly>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="form-label">
                                        Phone Number
                                    </label>
                                    <input type="tel" 
                                           id="phone" 
                                           name="phone" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone', $user->phone) }}"
                                           readonly>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Location and Company Section -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fas fa-map-marker-alt"></i>
                                Location & Company
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="location" class="form-label">
                                        Location <span class="required">*</span>
                                    </label>
                                    <select id="location" 
                                            name="location" 
                                            class="form-control @error('location') is-invalid @enderror" 
                                            required>
                                        <option value="">Select Location</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->code }}" 
                                                    {{ (old('location', $user->location) == $location->code) ? 'selected' : '' }}>
                                                {{ $location->name }} ({{ $location->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    
                                    <small class="text-muted">Current value: {{ $user->location ?? 'null' }}</small>
                                </div>

                                <div class="form-group">
                                    <label for="company_id" class="form-label">
                                        Company <span class="required">*</span>
                                    </label>
                                    <select id="company_id" 
                                            name="company_id" 
                                            class="form-control @error('company_id') is-invalid @enderror" 
                                            required>
                                        <option value="">Select Company</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" 
                                                    {{ (old('company_id', $user->company_id) == $company->id) ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    
                                    <small class="text-muted">Current value: {{ $user->company_id ?? 'null' }}</small>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Current Status</label>
                                    <div class="current-status status-{{ $user->status }}">
                                        <i class="fas fa-circle"></i>
                                        {{ ucfirst($user->status) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Module Assignments Section -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fas fa-th-large"></i>
                                Module Assignments
                            </div>

                            <!-- Summary Cards View (Default) -->
                            <div id="assignmentsSummary">
                                @if($userModuleAssignments && $userModuleAssignments->count() > 0)
                                    <div class="assignments-grid">
                                        @foreach($userModuleAssignments as $assignment)
                                            <div class="assignment-card">
                                                <div class="assignment-card-header">
                                                    <i class="fas fa-cube"></i>
                                                    <strong>{{ $assignment['module_name'] }}</strong>
                                                </div>
                                                <div class="assignment-card-body">
                                                    <div class="assignment-detail">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        <span>{{ $assignment['location_name'] }}</span>
                                                    </div>
                                                    <div class="assignment-detail">
                                                        <i class="fas fa-user-tag"></i>
                                                        <span>{{ $assignment['role_name'] }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <p>No module assignments yet</p>
                                    </div>
                                @endif
                                
                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-primary" onclick="toggleEditMode()">
                                        <i class="fas fa-edit"></i> Edit Assignments
                                    </button>
                                </div>
                            </div>

                            <!-- Edit Form (Hidden by default) -->
                            <div id="assignmentsEditForm" style="display: none;">
                                <div class="alert alert-info mb-3">
                                    <i class="fas fa-info-circle"></i>
                                    Make your changes below and click "Update User" to save.
                                </div>
                                
                                <div class="mb-3">
                                    <button type="button" class="btn btn-sm btn-success" onclick="addModuleRow()">
                                        <i class="fas fa-plus"></i> Add New Assignment
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEditMode()">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </div>

                                <div id="modulesTableContainer" class="table-responsive">
                                    <table class="table table-bordered align-middle" id="modulesTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 25%">Office</th>
                                                <th style="width: 25%">Module</th>
                                                <th style="width: 25%">Role</th>
                                                <th style="width: 15%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($user->modules as $assignmentIndex => $assignment)
                                                <tr>
                                                    <td>
                                                        <div class="checkbox-group">
                                                            <label>
                                                                <input type="checkbox" name="modules[{{ $assignmentIndex }}][location][]" value="all" onchange="toggleAllCheckboxes(this, 'location', {{ $assignmentIndex }})">
                                                                All Locations
                                                            </label>
                                                            @foreach($locations as $location)
                                                                <label>
                                                                    <input type="checkbox" name="modules[{{ $assignmentIndex }}][location][]" value="{{ $location->code }}" {{ $assignment->pivot->location == $location->code ? 'checked' : '' }} onchange="validateCheckboxes(this, 'location', {{ $assignmentIndex }})">
                                                                    {{ $location->name }}
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="checkbox-group">
                                                            <label>
                                                                <input type="checkbox" name="modules[{{ $assignmentIndex }}][module_id][]" value="all" onchange="toggleAllCheckboxes(this, 'module_id', {{ $assignmentIndex }})">
                                                                All Modules
                                                            </label>
                                                            @foreach($modules as $module)
                                                                <label>
                                                                    <input type="checkbox" name="modules[{{ $assignmentIndex }}][module_id][]" value="{{ $module->id }}" {{ $assignment->id == $module->id ? 'checked' : '' }} onchange="validateCheckboxes(this, 'module_id', {{ $assignmentIndex }})">
                                                                    {{ $module->name }}
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="checkbox-group">
                                                            <label>
                                                                <input type="checkbox" name="modules[{{ $assignmentIndex }}][role_id][]" value="all" onchange="toggleAllCheckboxes(this, 'role_id', {{ $assignmentIndex }})">
                                                                All Roles
                                                            </label>
                                                            @foreach($roles as $role)
                                                                <label>
                                                                    <input type="checkbox" name="modules[{{ $assignmentIndex }}][role_id][]" value="{{ $role->id }}" {{ $assignment->pivot->role_id == $role->id ? 'checked' : '' }} onchange="validateCheckboxes(this, 'role_id', {{ $assignmentIndex }})">
                                                                    {{ $role->name }}
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <!-- No existing assignments -->
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-section">
                            <div style="display: flex; gap: 15px; justify-content: flex-end;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Update User
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="loading-spinner" aria-hidden="true">
        <div class="spinner"></div>
        <div id="loadingText">Processing…</div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js" defer></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Axios defaults
            if (window.axios) {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
                axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            }

            // Loading helpers
            window.showLoading = () => {
                const el = document.getElementById('loadingSpinner');
                el?.setAttribute('aria-hidden', 'false');
                el.style.display = 'block';
            };
            window.hideLoading = () => {
                const el = document.getElementById('loadingSpinner');
                el?.setAttribute('aria-hidden', 'true');
                el.style.display = 'none';
            };

            // SweetAlert helpers
            window.showAlert = (type, title, message) => {
                if (window.Swal) {
                    Swal.fire({ icon: type, title, text: message, toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                } else {
                    alert(title + "\n\n" + message);
                }
            };
            window.confirmAction = (title, text, callback) => {
                if (window.Swal) {
                    Swal.fire({
                        title, text, icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim(),
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, proceed!'
                    }).then(result => {
                        if (result.isConfirmed && typeof callback === 'function') callback();
                    });
                } else if (confirm(title + "\n\n" + text)) {
                    callback?.();
                }
            };

            // Laravel flash + validation messages
            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: `<ul style="text-align:left;">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>`
                });
            @endif
            @if (session('success'))
                showAlert('success', 'Success', '{{ session("success") }}');
            @endif
            @if (session('error'))
                showAlert('error', 'Error', '{{ session("error") }}');
            @endif
        });

        let rowIndex = {{ count($user->modules ?? []) }};

        function addModuleRow() {
            const tableBody = document.querySelector('#modulesTable tbody');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="modules[${rowIndex}][location][]" value="all" onchange="toggleAllCheckboxes(this, 'location', ${rowIndex})">
                            All Locations
                        </label>
                        @foreach($locations as $location)
                            <label>
                                <input type="checkbox" name="modules[${rowIndex}][location][]" value="{{ $location->code }}" onchange="validateCheckboxes(this, 'location', ${rowIndex})">
                                {{ $location->name }}
                            </label>
                        @endforeach
                    </div>
                </td>
                <td>
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="modules[${rowIndex}][module_id][]"
 value="all" onchange="toggleAllCheckboxes(this, 'module_id', ${rowIndex})">
                            All Modules
                        </label>
                        @foreach($modules as $module)
                            <label>
                                <input type="checkbox" name="modules[${rowIndex}][module_id][]"
 value="{{ $module->id }}" onchange="validateCheckboxes(this, 'module_id', ${rowIndex})">
                                {{ $module->name }}
                            </label>
                        @endforeach
                    </div>
                </td>
                <td>
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="modules[${rowIndex}][role_id][]" value="all" onchange="toggleAllCheckboxes(this, 'role_id', ${rowIndex})">
                            All Roles
                        </label>
                        @foreach($roles as $role)
                            <label>
                                <input type="checkbox" name="modules[${rowIndex}][role_id][]" value="{{ $role->id }}" onchange="validateCheckboxes(this, 'role_id', ${rowIndex})">
                                {{ $role->name }}
                            </label>
                        @endforeach
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tableBody.appendChild(newRow);
            rowIndex++;
        }

        function removeRow(button) {
            const row = button.closest('tr');
            row.remove();
        }

        function toggleAllCheckboxes(allCheckbox, field, index) {
            const checkboxes = allCheckbox.closest('.checkbox-group').querySelectorAll(`input[name^="modules[${index}][${field}][]"]:not([value="all"])`);
            checkboxes.forEach(checkbox => {
                checkbox.checked = allCheckbox.checked;
                checkbox.disabled = allCheckbox.checked;
            });
        }

        function validateCheckboxes(checkbox, field, index) {
            const allCheckbox = checkbox.closest('.checkbox-group').querySelector(`input[name="modules[${index}][${field}][]"][value="all"]`);
            const individualCheckboxes = checkbox.closest('.checkbox-group').querySelectorAll(`input[name^="modules[${index}][${field}][]"]:not([value="all"])`);
            if (checkbox.value !== 'all' && checkbox.checked) {
                allCheckbox.checked = false;
                individualCheckboxes.forEach(cb => cb.disabled = false);
            } else if (!checkbox.checked) {
                // Check if any individual checkboxes are still checked
                const anyChecked = Array.from(individualCheckboxes).some(cb => cb.checked);
                if (!anyChecked) {
                    allCheckbox.checked = false;
                    individualCheckboxes.forEach(cb => cb.disabled = false);
                }
            }
        }

        function toggleEditMode() {
            const summary = document.getElementById('assignmentsSummary');
            const editForm = document.getElementById('assignmentsEditForm');
            
            if (summary.style.display === 'none') {
                summary.style.display = 'block';
                editForm.style.display = 'none';
            } else {
                summary.style.display = 'none';
                editForm.style.display = 'block';
            }
        }

        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Validate that each row has at least one selection in each category
           const rows = document.querySelectorAll('#modulesTable tbody tr');
let isValid = true;
rows.forEach((row, index) => {
    ['location', 'module_id', 'role_id'].forEach(field => {
        const checkboxes = row.querySelectorAll(`input[name^="modules[${index}][${field}][]"]`);
                    const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
                    if (!anyChecked) {
                        isValid = false;
                        showAlert('error', 'Validation Error', `Please select at least one ${field.replace('_id', '')} in row ${index + 1}`);
                    }
                });
            });

            if (!isValid) {
                return;
            }

            showLoading();
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            
            axios.post(this.action, new FormData(this))
                .then(response => {
                    showAlert('success', 'Success', 'User updated successfully');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    hideLoading();
                    
                    // Switch back to summary view after successful save
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                })
                .catch(error => {
                    showAlert('error', 'Error', error.response?.data?.message || 'Failed to update user');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    hideLoading();
                });
        });
    </script>
</body>
</html>