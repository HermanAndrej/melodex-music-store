/**
 * User Model
 * Represents a user entity in the application
 */
export class User {
    constructor(data = {}) {
        this.id = data.id || null;
        this.name = data.name || '';
        this.email = data.email || '';
        this.roles = data.roles || [];
        this.status = data.status || 'active';
        this.createdAt = data.created || data.createdAt || null;
    }

    /**
     * Check if user has a specific role
     * @param {string} role Role to check
     * @returns {boolean} Whether user has the role
     */
    hasRole(role) {
        return this.roles.includes(role);
    }

    /**
     * Check if user is an admin
     * @returns {boolean} Admin status
     */
    isAdmin() {
        return this.hasRole('admin');
    }

    /**
     * Format date for display
     * @returns {string} Formatted date
     */
    get formattedDate() {
        if (!this.createdAt) return 'N/A';
        
        const date = new Date(this.createdAt);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    /**
     * Get user's display name
     * @returns {string} User's name or email
     */
    get displayName() {
        return this.name || this.email.split('@')[0];
    }

    /**
     * Convert user to plain object
     * @returns {Object} Plain object representation
     */
    toJSON() {
        return {
            id: this.id,
            name: this.name,
            email: this.email,
            roles: this.roles,
            status: this.status,
            createdAt: this.createdAt
        };
    }

    /**
     * Create user from API data
     * @param {Object} data API data
     * @returns {User} User instance
     */
    static fromAPI(data) {
        return new User({
            id: data.id,
            name: data.name,
            email: data.email,
            roles: Array.isArray(data.roles) ? data.roles : [data.role],
            status: data.status,
            createdAt: data.created_at || data.createdAt
        });
    }
}

export default User;
