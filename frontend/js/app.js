// Import controllers and services
import authService from './services/authService.js';

// Simple hash-based router with authentication handling
class Router {
    constructor() {
        this.routes = {};
        this.currentRoute = '';
        this.appElement = document.getElementById('app');
        this.authRequiredRoutes = [];
        this.adminRequiredRoutes = [];
        
        // Bind methods
        this.navigate = this.navigate.bind(this);
        this.handleRoute = this.handleRoute.bind(this);
        
        // Initialize router
        window.addEventListener('hashchange', this.handleRoute);
        document.addEventListener('DOMContentLoaded', () => {
            // Parse the current URL and load the appropriate route
            this.handleRoute();
        });
    }
    
    /**
     * Add a new route
     * @param {string} path Route path
     * @param {Function} handler Route handler function
     * @param {Object} options Route options
     * @returns {Router} Router instance for chaining
     */
    addRoute(path, handler, options = {}) {
        this.routes[path] = handler;
        
        // Track routes that require authentication
        if (options.requireAuth) {
            this.authRequiredRoutes.push(path);
        }
        
        // Track routes that require admin role
        if (options.requireAdmin) {
            this.adminRequiredRoutes.push(path);
        }
        
        return this; // Allow chaining
    }
    
    /**
     * Navigate to a new route
     * @param {string} path Route path
     */
    navigate(path) {
        window.location.hash = path;
    }
    
    /**
     * Handle route changes with authentication checks
     */
    async handleRoute() {
        // Get the current hash, default to '/' if empty
        const hash = window.location.hash.substring(1) || '/';
        
        // Don't re-render if the route hasn't changed
        if (hash === this.currentRoute) return;
        
        this.currentRoute = hash;
        
        // Find matching route
        let matchedRoute = null;
        let matchedPath = null;
        let params = {};
        
        // Check for exact matches first
        if (this.routes[hash]) {
            matchedRoute = this.routes[hash];
            matchedPath = hash;
        } else {
            // Check for parameterized routes (e.g., /users/:id)
            for (const [route, handler] of Object.entries(this.routes)) {
                if (route.includes(':')) {
                    const routeParts = route.split('/');
                    const hashParts = hash.split('/');
                    
                    if (routeParts.length === hashParts.length) {
                        let isMatch = true;
                        const routeParams = {};
                        
                        for (let i = 0; i < routeParts.length; i++) {
                            if (routeParts[i].startsWith(':')) {
                                const paramName = routeParts[i].substring(1);
                                routeParams[paramName] = hashParts[i];
                            } else if (routeParts[i] !== hashParts[i]) {
                                isMatch = false;
                                break;
                            }
                        }
                        
                        if (isMatch) {
                            matchedRoute = handler;
                            matchedPath = route;
                            params = routeParams;
                            break;
                        }
                    }
                }
            }
        }
        
        // Check authentication requirements
        if (matchedRoute && matchedPath) {
            try {
                // Check if route requires authentication
                if (this.authRequiredRoutes.includes(matchedPath) && 
                    (!authService || typeof authService.isAuthenticated !== 'function' || !authService.isAuthenticated())) {
                    // Redirect to login
                    this.navigate('/login');
                    return;
                }
                
                // Check if route requires admin role
                if (this.adminRequiredRoutes.includes(matchedPath) && 
                    (!authService || typeof authService.isAdmin !== 'function' || !authService.isAdmin())) {
                    // Redirect to unauthorized or home
                    this.navigate('/');
                    document.getElementById('app').innerHTML = `
                        <div class="alert alert-danger">
                            <h4>Unauthorized Access</h4>
                            <p>You don't have permission to access this page.</p>
                        </div>
                    `;
                    return;
                }
            } catch (error) {
                console.error('Authentication check error:', error);
                // Continue with the route anyway
            }
            
            try {
                // Call the route's handler with parameters
                await matchedRoute(params);
            } catch (error) {
                console.error('Route handler error:', error);
                document.getElementById('app').innerHTML = `
                    <div class="alert alert-danger">
                        <h4>Error</h4>
                        <p>${error.message || 'An error occurred while loading this page.'}</p>
                    </div>
                `;
            }
        } else {
            // Handle 404
            document.getElementById('app').innerHTML = `
                <div class="container py-5">
                    <h1>404 - Page Not Found</h1>
                    <p>The requested page "${hash}" does not exist.</p>
                    <a href="#/" class="btn btn-primary">Go to Home</a>
                </div>
            `;
        }
    }
}

// Initialize router
const router = new Router();

/**
 * Initialize page-specific scripts and functionality
 * @param {string} pageName - The name of the current page
 */
function initPageScripts(pageName) {
    console.log(`Initializing scripts for ${pageName} page`);
    
    // Remove any active classes from navigation
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    
    // Add active class to current page nav link
    const activeNavLink = document.querySelector(`.nav-link[href="#/${pageName === 'home' ? '' : pageName}"]`);
    if (activeNavLink) {
        activeNavLink.classList.add('active');
    }
    
    // Initialize page-specific functionality
    switch(pageName) {
        case 'home':
            // Initialize home page specific elements (e.g., carousel)
            const carousel = document.querySelector('#template-mo-zay-hero-carousel');
            if (carousel) {
                // Any carousel initialization if needed
                console.log('Home page carousel initialized');
            }
            break;
            
        case 'login':
            // Initialize login form event listeners
            const loginForm = document.querySelector('#login-form');
            if (loginForm) {
                loginForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const email = document.querySelector('#login-email').value;
                    const password = document.querySelector('#login-password').value;
                    
                    try {
                        const result = await authService.login(email, password);
                        if (result.success) {
                            router.navigate('/');
                        }
                    } catch (error) {
                        console.error('Login error:', error);
                    }
                });
            }
            break;
            
        case 'register':
            // Initialize register form event listeners
            const registerForm = document.querySelector('#register-form');
            if (registerForm) {
                registerForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    // Get form values and register logic
                });
            }
            break;
            
        // Add cases for other pages as needed
    }
    
    // Update auth UI elements
    try {
        updateAuthUI();
    } catch (error) {
        console.error('Error updating auth UI:', error);
    }
}

// Function to load HTML views
async function loadView(viewPath) {
    try {
        const response = await fetch(viewPath);
        if (!response.ok) {
            throw new Error(`Failed to load view: ${viewPath}`);
        }
        const html = await response.text();
        return html;
    } catch (error) {
        console.error('Error loading view:', error);
        return `<div class="alert alert-danger">Error loading view: ${error.message}</div>`;
    }
}

// Add routes
router
    // Public routes
    .addRoute('/', async () => {
        const content = await loadView('./views/home.html');
        document.getElementById('app').innerHTML = content;
        // Update page title
        document.title = "Melodex - Home";
        // Initialize any page-specific scripts
        initPageScripts('home');
    })
    .addRoute('/about', async () => {
        const content = await loadView('./views/about.html');
        document.getElementById('app').innerHTML = content;
        document.title = "Melodex - About";
        initPageScripts('about');
    })
    .addRoute('/contact', async () => {
        const content = await loadView('./views/contact.html');
        document.getElementById('app').innerHTML = content;
        document.title = "Melodex - Contact";
        initPageScripts('contact');
    })
    .addRoute('/login', async () => {
        const content = await loadView('./views/login.html');
        document.getElementById('app').innerHTML = content;
        document.title = "Melodex - Login";
        initPageScripts('login');
    })
    .addRoute('/register', async () => {
        const content = await loadView('./views/register.html');
        document.getElementById('app').innerHTML = content;
        document.title = "Melodex - Register";
        initPageScripts('register');
    })
    
    // Auth required routes
    .addRoute('/profile', async () => {
        const content = await loadView('./views/profile.html');
        document.getElementById('app').innerHTML = content;
        document.title = "Melodex - Profile";
        initPageScripts('profile');
    }, { requireAuth: true })
    .addRoute('/shop', async () => {
        const content = await loadView('./views/shop.html');
        document.getElementById('app').innerHTML = content;
        document.title = "Melodex - Shop";
        initPageScripts('shop');
    }, { requireAuth: true })
    .addRoute('/shop-single', async () => {
        const content = await loadView('./views/shop-single.html');
        document.getElementById('app').innerHTML = content;
        document.title = "Melodex - Product Details";
        initPageScripts('shop-single');
    }, { requireAuth: true })
    .addRoute('/cart', async () => {
        const content = await loadView('./views/cart.html');
        document.getElementById('app').innerHTML = content;
        document.title = "Melodex - Cart";
        initPageScripts('cart');
    }, { requireAuth: true })
    .addRoute('/orders', async () => {
        document.getElementById('app').innerHTML = await loadView('./views/orders.html');
    }, { requireAuth: true })
    
    // Admin only routes
    .addRoute('/admin', async () => {
        document.getElementById('app').innerHTML = await loadView('./views/admin-dashboard.html');
    }, { requireAuth: true, requireAdmin: true })
    .addRoute('/admin/users', async () => {
        document.getElementById('app').innerHTML = await loadView('./views/admin-users.html');
    }, { requireAuth: true, requireAdmin: true })
    .addRoute('/admin/products', async () => {
        document.getElementById('app').innerHTML = await loadView('./views/admin-products.html');
    }, { requireAuth: true, requireAdmin: true })
    .addRoute('/admin/categories', async () => {
        document.getElementById('app').innerHTML = await loadView('./views/admin-categories.html');
    }, { requireAuth: true, requireAdmin: true })
    .addRoute('/admin/orders', async () => {
        document.getElementById('app').innerHTML = await loadView('./views/admin-orders.html');
    }, { requireAuth: true, requireAdmin: true })
    .addRoute('/admin/reviews', async () => {
        document.getElementById('app').innerHTML = await loadView('./views/admin-reviews.html');
    }, { requireAuth: true, requireAdmin: true });

// Export the router for use in other modules
export { router };

// Start the router
router.handleRoute();

// Make router available globally
window.router = router;

// Set up navigation event listeners
document.addEventListener('DOMContentLoaded', () => {
    // Attach click handlers to navigation links
    document.body.addEventListener('click', (e) => {
        // Find closest anchor tag
        const link = e.target.closest('a');
        
        // If it's an internal hash link, prevent default and use router
        if (link && link.getAttribute('href')?.startsWith('#/')) {
            e.preventDefault();
            const path = link.getAttribute('href').substring(1); // Remove the hash
            router.navigate(path);
        }
    });
    
    // Set up logout handler
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            import('./services/authService.js').then(({ default: authService }) => {
                authService.logout();
                router.navigate('/login');
            });
        });
    }
    
    // Update UI based on auth status
    import('./services/authService.js').then(({ default: authService }) => {
        updateAuthUI(authService);
    });
});

/**
 * Update UI elements based on authentication status
 */
function updateAuthUI(authService) {
    const isLoggedIn = authService.isAuthenticated();
    const isAdmin = authService.isAdmin();
    
    // Toggle auth-only elements
    document.querySelectorAll('[data-auth-required]').forEach(el => {
        el.style.display = isLoggedIn ? '' : 'none';
    });
    
    // Toggle guest-only elements
    document.querySelectorAll('[data-guest-only]').forEach(el => {
        el.style.display = isLoggedIn ? 'none' : '';
    });
    
    // Toggle admin-only elements
    document.querySelectorAll('[data-admin-required]').forEach(el => {
        el.style.display = isAdmin ? '' : 'none';
    });
}
