// Handle login form submission
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    try {
        const result = await window.api.auth.login(email, password);
        if (result.success) {
            // Update navbar to show/hide admin panel link
            if (typeof window.updateNavbar === 'function') {
                window.updateNavbar();
            }
            window.location.href = 'index.html';
        } else {
            alert('Login failed: ' + result.message);
        }
    } catch (error) {
        console.error('Login error:', error);
        alert('An error occurred during login. Please try again.');
    }
}); 