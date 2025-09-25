@extends('layouts.app')

@section('title', 'Create New User')

@section('page-title', 'Azure User Management System')

@section('header-actions')
    <a href="{{ route('dashboard.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Back to Dashboard
    </a>
@endsection

@section('additional-styles')
<style>
    .main-content {
        display: grid;
        grid-template-columns: 400px 1fr;
        gap: 30px;
        align-items: start;
    }

    .procedures-panel {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .procedures-title {
        font-size: 1.3em;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .procedure-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        margin-bottom: 8px;
        border-radius: 10px;
        background: #f8f9fa;
        border-left: 4px solid #3498db;
        transition: all 0.3s ease;
    }

    .procedure-item:hover {
        background: #e3f2fd;
        transform: translateX(5px);
    }

    .procedure-text {
        font-weight: 500;
        color: #2c3e50;
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 15px;
        font-size: 0.8em;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-away {
        background: #fff3cd;
        color: #856404;
    }

    .status-mock {
        background: #d4edda;
        color: #155724;
    }

    .status-ready {
        background: #d1ecf1;
        color: #0c5460;
    }

    .status-processing {
        background: #f8d7da;
        color: #721c24;
    }

    .user-form-panel {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .form-section {
        margin-bottom: 30px;
    }

    .section-title {
        font-size: 1.4em;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .form-group input,
    .form-group select {
        padding: 12px 15px;
        border: 2px solid #e1e8ed;
        border-radius: 10px;
        font-size: 16px;
        transition: all 0.3s ease;
        background: white;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .form-group input.is-invalid,
    .form-group select.is-invalid {
        border-color: #e74c3c;
    }

    .invalid-feedback {
        color: #e74c3c;
        font-size: 0.875em;
        margin-top: 5px;
    }

    .modules-section {
        margin-top: 30px;
    }

    .modules-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .module-card {
        background: linear-gradient(145deg, #f0f2f5, #ffffff);
        border-radius: 12px;
        padding: 20px;
        border: 2px solid #e1e8ed;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .module-card:hover {
        border-color: #3498db;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .module-card.active {
        border-color: #27ae60;
        background: linear-gradient(145deg, #e8f5e8, #f0f8f0);
    }

    .module-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .module-checkbox {
        width: 18px;
        height: 18px;
    }

    .module-name {
        font-weight: 600;
        color: #2c3e50;
    }

    .role-dropdown {
        width: 100%;
        margin-top: 10px;
    }

    .permissions-display {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-top: 15px;
    }

    .permissions-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .permission-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .permission-tag {
        background: #3498db;
        color: white;
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.9em;
        font-weight: 500;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 30px;
    }

    @media (max-width: 768px) {
        .main-content {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .modules-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
    <div class="main-content">
        <!-- Procedures Panel -->
        <div class="procedures-panel">
            <div class="procedures-title">
                <i class="fas fa-list-check"></i>
                Procedures
            </div>
            
            <div class="procedure-item" id="step1">
                <span class="procedure-text">1) Office List</span>
                <span class="status-badge status-ready">Ready</span>
            </div>
            
            <div class="procedure-item" id="step2">
                <span class="procedure-text">2) Module List</span>
                <span class="status-badge status-ready">Ready</span>
            </div>
            
            <div class="procedure-item" id="step3">
                <span class="procedure-text">3) Role List based on Module</span>
                <span class="status-badge status-ready">Ready</span>
            </div>
            
            <div class="procedure-item" id="step4">
                <span class="procedure-text">4) Create User in Azure</span>
                <span class="status-badge status-mock">Mock</span>
            </div>
            
            <div class="procedure-item" id="step5">
                <span class="procedure-text">5) Mapping Module in Azure</span>
                <span class="status-badge status-mock">Mock</span>
            </div>
            
            <div class="procedure-item" id="step6">
                <span class="procedure-text">6) Creating in SCM/BIZ/FITGAP</span>
                <span class="status-badge status-away">Away</span>
            </div>
            
            <div class="procedure-item" id="step7">
                <span class="procedure-text">7) Mapping Module in SCM/BIZ/FITGAP</span>
                <span class="status-badge status-away">Away</span>
            </div>
            
            <div class="procedure-item" id="step8">
                <span class="procedure-text">8) Delete User from Azure</span>
                <span class="status-badge status-mock">Mock</span>
            </div>
            
            <div class="procedure-item" id="step9">
                <span class="procedure-text">9) Delete from SCM/BIZ/FITGAP</span>
                <span class="status-badge status-away">Away</span>
            </div>
        </div>

        <!-- User Form Panel -->
        <div class="user-form-panel">
            <form id="userForm" method="POST" action="{{ route('users.store') }}">
                @csrf
                
                <!-- Finding Server/Edit Server Section -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-server"></i>
                        User Information
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name"><i class="fas fa-user"></i> Name *</label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required 
                                   class="@error('name') is-invalid @enderror">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="employee_id"><i class="fas fa-id-badge"></i> Employee ID *</label>
                            <input type="text" 
                                   id="employee_id" 
                                   name="employee_id" 
                                   value="{{ old('employee_id') }}" 
                                   required
                                   class="@error('employee_id') is-invalid @enderror">
                            @error('employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email ID *</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required
                                   class="@error('email') is-invalid @enderror">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="phone"><i class="fas fa-phone"></i> Phone No *</label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone') }}" 
                                   required
                                   class="@error('phone') is-invalid @enderror">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="location"><i class="fas fa-map-marker-alt"></i> Location *</label>
                            <select id="location" 
                                    name="location" 
                                    required
                                    class="@error('location') is-invalid @enderror"
                                    onchange="loadCompaniesByLocation()">
                                <option value="">Select Location</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->code }}" 
                                            {{ old('location') == $location->code ? 'selected' : '' }}>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="company_id"><i class="fas fa-building"></i> Company *</label>
                            <select id="company_id" 
                                    name="company_id" 
                                    required
                                    class="@error('company_id') is-invalid @enderror">
                                <option value="">Select Company</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" 
                                            {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Module Selection Section -->
                <div class="modules-section">
                    <div class="section-title">
                        <i class="fas fa-puzzle-piece"></i>
                        Module & Role Assignment
                    </div>
                    
                    <div class="modules-grid">
                        @foreach($modules as $module)
                            <div class="module-card" onclick="toggleModule('{{ $module->code }}')">
                                <div class="module-header">
                                    <input type="checkbox" 
                                           class="module-checkbox" 
                                           id="{{ $module->code }}Module"
                                           name="modules[{{ $module->code }}][enabled]"
                                           value="1">
                                    <span class="module-name">{{ $module->name }}</span>
                                </div>
                                <select class="role-dropdown" 
                                        id="{{ $module->code }}Role" 
                                        name="modules[{{ $module->code }}][role_id]"
                                        disabled
                                        onchange="updatePermissions()">
                                    <option value="">Select Role</option>
                                    @foreach($module->roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="permissions-display">
                        <div class="permissions-title">
                            <i class="fas fa-shield-alt"></i>
                            Selected Permissions:
                        </div>
                        <div class="permission-tags" id="permissionTags">
                            <span class="permission-tag">No permissions selected</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="button" class="btn btn-secondary" onclick="cancelForm()">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('additional-scripts')
<script>
    // Module data for JavaScript access
    const moduleData = @json($modules->keyBy('code'));
    const rolePermissions = @json($rolePermissions ?? []);

    document.addEventListener('DOMContentLoaded', function() {
        updatePermissions();
    });

    function toggleModule(moduleName) {
        const checkbox = document.getElementById(moduleName + 'Module');
        const roleDropdown = document.getElementById(moduleName + 'Role');
        const moduleCard = checkbox.closest('.module-card');
        
        checkbox.checked = !checkbox.checked;
        
        if (checkbox.checked) {
            roleDropdown.disabled = false;
            moduleCard.classList.add('active');
            updateProcedureStatus('step3', 'processing');
        }
    }

    function updateProcedureStatus(stepId, status) {
        const step = document.getElementById(stepId);
        if (step) {
            const badge = step.querySelector('.status-badge');
            badge.className = `status-badge status-${status}`;
            
            switch(status) {
                case 'ready':
                    badge.textContent = 'Ready';
                    break;
                case 'processing':
                    badge.textContent = 'Processing';
                    break;
                case 'complete':
                    badge.textContent = 'Complete';
                    break;
                case 'error':
                    badge.textContent = 'Error';
                    break;
            }
        }
    }

    function loadCompaniesByLocation() {
        const locationSelect = document.getElementById('location');
        const companySelect = document.getElementById('company_id');
        const location = locationSelect.value;
        
        if (!location) {
            companySelect.innerHTML = '<option value="">Select Company</option>';
            return;
        }
        
        showLoading();
        updateProcedureStatus('step1', 'processing');
        
        axios.get(`/api/companies/${location}`)
            .then(response => {
                companySelect.innerHTML = '<option value="">Select Company</option>';
                response.data.forEach(company => {
                    companySelect.innerHTML += `<option value="${company.id}">${company.name}</option>`;
                });
                updateProcedureStatus('step1', 'complete');
                hideLoading();
            })
            .catch(error => {
                console.error('Error loading companies:', error);
                showAlert('error', 'Error', 'Failed to load companies');
                updateProcedureStatus('step1', 'error');
                hideLoading();
            });
    }

    function cancelForm() {
        confirmAction(
            'Cancel Form',
            'Are you sure you want to cancel? All unsaved changes will be lost.',
            function() {
                window.location.href = '{{ route("dashboard.index") }}';
            }
        );
    }

    // Form submission with Azure provisioning simulation
    document.getElementById('userForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Validate form
        if (!validateForm()) {
            return;
        }
        
        showLoading();
        
        // Simulate procedure steps
        simulateProvisioningProcess(formData);
    });

    function validateForm() {
        let isValid = true;
        const requiredFields = ['name', 'employee_id', 'email', 'phone', 'location', 'company_id'];
        
        requiredFields.forEach(field => {
            const element = document.getElementById(field);
            if (!element.value.trim()) {
                element.classList.add('is-invalid');
                isValid = false;
            } else {
                element.classList.remove('is-invalid');
            }
        });
        
        // Check if at least one module is selected
        const modules = ['office', 'scm', 'biz', 'fitgap'];
        const hasModules = modules.some(module => {
            const checkbox = document.getElementById(module + 'Module');
            return checkbox && checkbox.checked;
        });
        
        if (!hasModules) {
            showAlert('warning', 'Warning', 'Please select at least one module');
            isValid = false;
        }
        
        return isValid;
    }

    async function simulateProvisioningProcess(formData) {
        try {
            // Step 4: Create User in Azure
            updateProcedureStatus('step4', 'processing');
            await new Promise(resolve => setTimeout(resolve, 1000));
            updateProcedureStatus('step4', 'complete');
            
            // Step 5: Mapping Module in Azure
            updateProcedureStatus('step5', 'processing');
            await new Promise(resolve => setTimeout(resolve, 800));
            updateProcedureStatus('step5', 'complete');
            
            // Submit to Laravel backend
            const response = await axios.post('{{ route("users.store") }}', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
            
            if (response.data.success) {
                showAlert('success', 'Success', 'User created successfully!');
                setTimeout(() => {
                    window.location.href = '{{ route("dashboard.index") }}';
                }, 2000);
            } else {
                throw new Error(response.data.message || 'Failed to create user');
            }
            
        } catch (error) {
            console.error('Error creating user:', error);
            
            // Update failed steps
            updateProcedureStatus('step4', 'error');
            updateProcedureStatus('step5', 'error');
            
            let errorMessage = 'Failed to create user';
            if (error.response && error.response.data) {
                if (error.response.data.errors) {
                    // Handle validation errors
                    const errors = error.response.data.errors;
                    errorMessage = Object.values(errors).flat().join('<br>');
                    
                    // Highlight invalid fields
                    Object.keys(errors).forEach(field => {
                        const element = document.getElementById(field);
                        if (element) {
                            element.classList.add('is-invalid');
                        }
                    });
                } else if (error.response.data.message) {
                    errorMessage = error.response.data.message;
                }
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: errorMessage
            });
        } finally {
            hideLoading();
        }
    }

    // Add event listeners for role dropdowns
    @foreach($modules as $module)
        document.getElementById('{{ $module->code }}Role').addEventListener('change', updatePermissions);
    @endforeach
</script>
@endsection2', 'processing');
        } else {
            roleDropdown.disabled = true;
            roleDropdown.value = '';
            moduleCard.classList.remove('active');
        }
        
        updatePermissions();
    }

    function updatePermissions() {
        const permissionTags = document.getElementById('permissionTags');
        const modules = ['office', 'scm', 'biz', 'fitgap'];
        const permissions = [];
        
        modules.forEach(module => {
            const checkbox = document.getElementById(module + 'Module');
            const roleSelect = document.getElementById(module + 'Role');
            
            if (checkbox && roleSelect && checkbox.checked && roleSelect.value) {
                const roleId = roleSelect.value;
                const roleName = roleSelect.options[roleSelect.selectedIndex].text;
                permissions.push(`${module.toUpperCase()}: ${roleName}`);
            }
        });
        
        if (permissions.length === 0) {
            permissionTags.innerHTML = '<span class="permission-tag">No permissions selected</span>';
        } else {
            permissionTags.innerHTML = permissions.map(permission => 
                `<span class="permission-tag">${permission}</span>`
            ).join('');
            updateProcedureStatus('step