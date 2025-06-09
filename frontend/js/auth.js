// Authentication Functions
const auth = {
    getCurrentUser() {
        try {
            const userStr = localStorage.getItem('user');
            return userStr ? JSON.parse(userStr) : null;
        } catch (error) {
            console.error('Error getting current user:', error);
            return null;
        }
    },

    isAuthenticated() {
        const token = localStorage.getItem('token');
        return !!token;
    },

    logout() {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = 'index.html';
    }
};

// Export auth object
window.auth = auth; 