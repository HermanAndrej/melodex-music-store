// Navbar Functions
const navbar = {
    updateNavbar() {
        const user = auth.getCurrentUser();
        const isAuthenticated = auth.isAuthenticated();
        const isAdmin = user && user.Role === 'admin';

        // Get all navbar elements
        const loginLink = document.getElementById('loginLink');
        const signupLink = document.getElementById('signupLink');
        const profileLink = document.getElementById('profileLink');
        const logoutLink = document.getElementById('logoutLink');
        const adminLink = document.getElementById('adminLink');

        if (isAuthenticated) {
            // Show authenticated user elements
            if (loginLink) loginLink.style.display = 'none';
            if (signupLink) signupLink.style.display = 'none';
            if (profileLink) profileLink.style.display = 'block';
            if (logoutLink) logoutLink.style.display = 'block';
            
            // Show admin link only for admin users
            if (adminLink) {
                adminLink.style.display = isAdmin ? 'block' : 'none';
            }
        } else {
            // Show unauthenticated user elements
            if (loginLink) loginLink.style.display = 'block';
            if (signupLink) signupLink.style.display = 'block';
            if (profileLink) profileLink.style.display = 'none';
            if (logoutLink) logoutLink.style.display = 'none';
            if (adminLink) adminLink.style.display = 'none';
        }
    }
};

// Update navbar when page loads
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', navbar.updateNavbar);
} else {
    navbar.updateNavbar();
} 