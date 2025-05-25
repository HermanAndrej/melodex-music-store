/**
 * AdminPanel Component
 * Handles admin-specific functionality and UI
 */

export class AdminPanel {
    constructor(authService) {
        this.authService = authService;
        this.adminContainer = document.getElementById('admin-panel');
        this.initialize();
    }

    /**
     * Initialize the admin panel
     */
    initialize() {
        if (!this.authService.isAdmin()) {
            this.hideAdminPanel();
            return;
        }

        this.setupEventListeners();
        this.showAdminPanel();
        this.loadAdminData();
    }

    /**
     * Set up event listeners for admin actions
     */
    setupEventListeners() {
        // User management
        const manageUsersBtn = document.getElementById('manageUsers');
        if (manageUsersBtn) {
            manageUsersBtn.addEventListener('click', () => this.showUserManagement());
        }

        // Analytics
        const viewAnalyticsBtn = document.getElementById('viewAnalytics');
        if (viewAnalyticsBtn) {
            viewAnalyticsBtn.addEventListener('click', () => this.showAnalytics());
        }

        // System settings
        const systemSettingsBtn = document.getElementById('systemSettings');
        if (systemSettingsBtn) {
            systemSettingsBtn.addEventListener('click', () => this.showSystemSettings());
        }
    }

    /**
     * Show the admin panel
     */
    showAdminPanel() {
        if (this.adminContainer) {
            this.adminContainer.style.display = 'block';
        }
        
        // Show all admin-specific elements
        document.querySelectorAll('[data-role="admin"]').forEach(el => {
            el.classList.add('admin-visible');
        });
    }

    /**
     * Hide the admin panel
     */
    hideAdminPanel() {
        if (this.adminContainer) {
            this.adminContainer.style.display = 'none';
        }
        
        // Hide all admin-specific elements
        document.querySelectorAll('[data-role="admin"]').forEach(el => {
            el.classList.remove('admin-visible');
        });
    }

    /**
     * Load admin data
     */
    async loadAdminData() {
        try {
            // Load any initial admin data here
            const [users, stats] = await Promise.all([
                this.fetchUsers(),
                this.fetchAdminStats()
            ]);
            
            console.log('Admin data loaded:', { users, stats });
            this.updateAdminDashboard(users, stats);
            
        } catch (error) {
            console.error('Error loading admin data:', error);
            this.showError('Failed to load admin data');
        }
    }

    /**
     * Fetch user data
     */
    async fetchUsers() {
        try {
            const response = await this.authService.authFetch('/api/users');
            return response.data || [];
        } catch (error) {
            console.error('Error fetching users:', error);
            throw error;
        }
    }

    /**
     * Fetch admin statistics
     */
    async fetchAdminStats() {
        try {
            const response = await this.authService.authFetch('/api/admin/stats');
            return response.data || {};
        } catch (error) {
            console.error('Error fetching admin stats:', error);
            return {};
        }
    }

    /**
     * Update the admin dashboard with data
     */
    updateAdminDashboard(users = [], stats = {}) {
        // Update user count
        const userCountEl = document.getElementById('userCount');
        if (userCountEl) {
            userCountEl.textContent = stats.userCount || users.length;
        }

        // Update other stats as needed
        const statsContainer = document.getElementById('adminStats');
        if (statsContainer) {
            // Update stats display
        }
    }

    /**
     * Show user management interface
     */
    showUserManagement() {
        // Implement user management UI
        console.log('Showing user management');
        // This would typically render a user management interface
    }

    /**
     * Show analytics dashboard
     */
    showAnalytics() {
        // Implement analytics UI
        console.log('Showing analytics');
        // This would typically render analytics charts/graphs
    }

    /**
     * Show system settings
     */
    showSystemSettings() {
        // Implement system settings UI
        console.log('Showing system settings');
    }

    /**
     * Show error message
     */
    showError(message) {
        // Implement error display
        console.error('Admin Error:', message);
        // Could use a toast notification system here
        alert(`Admin Error: ${message}`);
    }
}

export default AdminPanel;
