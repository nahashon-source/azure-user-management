@extends('layouts.app')

@section('title', 'Edit User')

@section('page-title', 'Edit User')

@section('header-actions')
    <button type="submit" form="editUserForm" class="btn btn-primary">
        <i class="fas fa-save"></i>
        Update User
    </button>
@endsection

@section('additional-styles')
<style>
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

    .section-title {
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

    /* Read-only styles - keeping for future use if needed */
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

    /* Hidden table initially */
    #modulesTableContainer {
        display: none;
    }

    #modulesTableContainer.show {
        display: block;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .edit-form-container {
            padding: 20px;
        }
    }
</style>
@endsection

@section('content')
    <div class="edit-form-container">
        <form id="editUserForm" action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Basic Information Section -->
            <div class="form-section">
                <div class="section-title">
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
                               required>
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
                               required>
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
                               required>
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
                               value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Location and Company Section -->
            <div class="form-section">
                <div class="section-title">
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
                        
                        <!-- Debug info - remove after fixing -->
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
                        
                        <!-- Debug info - remove after fixing -->
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
                <div class="section-title">
                    <i class="fas fa-th-large"></i>
                    Module Assignments
                    <button type="button" class="btn btn-sm btn-success ms-3" onclick="addModuleRow()">
                        <i class="fas fa-plus"></i> New
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
                            <!-- Table body will be populated by JavaScript when "New" is clicked -->
                        </tbody>
                    </table>
                    
                    <div style="margin-top: 15px;">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="hideModuleTable()">
                            <i class="fas fa-times"></i> Close
                        </button>
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
@endsection

@section('additional-scripts')
<script>
    let rowIndex = 0;

    function showModuleTable() {
        const tableContainer = document.getElementById('modulesTableContainer');
        tableContainer.classList.add('show');
        
        // Add a new row when "New" is clicked
        addModuleRow();
    }

    function hideModuleTable() {
        const tableContainer = document.getElementById('modulesTableContainer');
        tableContainer.classList.remove('show');
    }

    function addModuleRow() {
        // Show the table if it's not visible
        const tableContainer = document.getElementById('modulesTableContainer');
        if (!tableContainer.classList.contains('show')) {
            tableContainer.classList.add('show');
        }
        
        const tableBody = document.querySelector('#modulesTable tbody');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <select name="modules[${rowIndex}][location]" class="form-control">
                    <option value="">Select Office</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->code }}">{{ $location->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="modules[${rowIndex}][module_id]" class="form-control">
                    <option value="">Select Module</option>
                    @foreach($modules as $module)
                        <option value="{{ $module->id }}">{{ $module->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="modules[${rowIndex}][role_id]" class="form-control">
                    <option value="">Select Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
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
        
        // Don't hide the table when rows are removed - keep it visible for adding more modules
    }

    // Form submission loading state
    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }, 10000);
    });

    // Table is hidden by default - only shows when "New" button is clicked
</script>
@endsection