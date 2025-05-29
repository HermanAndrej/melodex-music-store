/**
 * User View - Handles the rendering of user-related UI components
 */

/**
 * Renders a list of users in a table
 * @param {Array} users - Array of user objects to display
 * @param {Function} onEdit - Callback for edit action
 * @param {Function} onDelete - Callback for delete action
 * @returns {string} HTML string of the users table
 */
export function renderUserList(users = [], onEdit = () => {}, onDelete = () => {}) {
    if (!users || users.length === 0) {
        return `
            <div class="alert alert-info">
                No users found. <a href="#users/create" class="btn btn-sm btn-primary">Create New User</a>
            </div>
        `;
    }

    const userRows = users.map(user => `
        <tr data-user-id="${user.id}">
            <td>${user.name || 'N/A'}</td>
            <td>${user.email || 'N/A'}</td>
            <td>${user.roles ? user.roles.join(', ') : 'user'}</td>
            <td>
                <button class="btn btn-sm btn-primary edit-user" data-id="${user.id}">
                    Edit
                </button>
                <button class="btn btn-sm btn-danger delete-user" data-id="${user.id}">
                    Delete
                </button>
            </td>
        </tr>
    `).join('');

    return `
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>User Management</h2>
            <a href="#users/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New User
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${userRows}
                </tbody>
            </table>
        </div>
    `;
}

/**
 * Renders a user form for creating/editing users
 * @param {Object} userData - Existing user data (for editing) or empty object (for creation)
 * @returns {string} HTML string of the user form
 */
export function renderUserForm(userData = {}) {
    const isEdit = !!userData.id;
    const title = isEdit ? `Edit User: ${userData.name || ''}` : 'Create New User';
    
    return `
        <div class="card">
            <div class="card-header">
                <h2 class="h4 mb-0">${title}</h2>
            </div>
            <div class="card-body">
                <form id="userForm">
                    <input type="hidden" id="userId" value="${userData.id || ''}">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" 
                               value="${userData.name || ''}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" 
                               value="${userData.email || ''}" required 
                               ${isEdit ? 'readonly' : ''}>
                    </div>
                    
                    ${!isEdit ? `
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" 
                                   ${!isEdit ? 'required' : ''}>
                        </div>
                    ` : ''}
                    
                    <div class="mb-3">
                        <label class="form-label">Roles</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="user" id="roleUser" 
                                   ${!userData.roles || userData.roles.includes('user') ? 'checked' : ''}>
                            <label class="form-check-label" for="roleUser">
                                Regular User
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="admin" id="roleAdmin"
                                   ${userData.roles && userData.roles.includes('admin') ? 'checked' : ''}>
                            <label class="form-check-label" for="roleAdmin">
                                Administrator
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="#users" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> ${isEdit ? 'Update' : 'Create'} User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
}

/**
 * Shows a confirmation dialog before deleting a user
 * @param {Object} user - The user to be deleted
 * @param {Function} onConfirm - Callback when user confirms deletion
 * @returns {string} HTML string of the confirmation dialog
 */
export function renderDeleteConfirmation(user, onConfirm) {
    return `
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h2 class="h5 mb-0">Confirm Deletion</h2>
            </div>
            <div class="card-body">
                <p>Are you sure you want to delete the user <strong>${user.name || user.email}</strong>?</p>
                <p class="text-muted">This action cannot be undone.</p>
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="#users" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button id="confirmDelete" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete User
                    </button>
                </div>
            </div>
        </div>
        
        <script>
            document.getElementById('confirmDelete').addEventListener('click', () => {
                ${onConfirm.toString().replace(/\n/g, '')}
            });
        </script>
    `;
}
