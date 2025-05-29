/**
 * Authentication Service
 * Handles user authentication, token management and role-based access control
 */
export class AuthService {
    constructor() {
        // Use absolute URL for API requests that works with XAMPP
        this.baseUrl = '/melodex/backend';
        this.token = localStorage.getItem('token') || null;
        this.user = null;
        
        // Initialize user from token
        this.initialize();
    }
    
    /**
     * Initialize user from token
     * This helps ensure roles are properly loaded
     */
    initialize() {
        if (!this.token) return;
        
        try {
            // Decode JWT (split by dots, take the middle part (payload), and decode base64)
            const payload = JSON.parse(atob(this.token.split('.')[1]));
            
            // Look for user info in both root and user property (handle different JWT formats)
            this.user = {
                id: payload.user?.id || payload.id,
                email: payload.user?.email || payload.email,
                name: payload.user?.name || payload.name,
                roles: []
            };
            
            // Extract roles from different possible locations in the token
            if (payload.user?.roles) {
                this.user.roles = payload.user.roles;
            } else if (payload.roles) {
                this.user.roles = payload.roles;
            } else if (payload.role) {
                this.user.roles = [payload.role];
            }
            
            console.log('User initialized from token:', this.user);
        } catch (error) {
            console.error('Invalid token format', error);
            this.logout(); // Clear invalid token
        }
    }

    /**
     * Attempt user login
     * @param {string} email User email
     * @param {string} password User password
     * @returns {Promise} Promise resolving to login result
     */
    /**
     * Attempt user login
     * @param {string} email User email
     * @param {string} password User password
     * @returns {Promise<{success: boolean, user?: object, error?: string}>} Login result
     */
    async login(email, password) {
        try {
            // Basic validation
            if (!email || !password) {
                throw new Error('Email and password are required');
            }

            const response = await fetch(`${this.baseUrl}/auth/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email, password }),
                credentials: 'include' // Include cookies for session handling
            });

            const result = await response.json();
            
            // Check if the response indicates success
            if (!result.success) {
                throw new Error(result.message || 'Login failed');
            }
            
            // Extract token from response
            const token = result.token;
            
            if (!token) {
                throw new Error('No authentication token received');
            }
            
            // Store token in localStorage
            localStorage.setItem('token', token);
            this.token = token;
            
            // Set user info from response
            this.user = result.user || {};
            
            console.log('Login successful, user:', this.user);
            
            // Notify any listeners of auth state change
            this.notifyAuthStateChange();
            
            return { 
                success: true, 
                user: { ...this.user },
                message: result.message || 'Login successful'
            };
            
        } catch (error) {
            console.error('Login error:', error);
            // Sanitize error message before sending to UI
            const safeMessage = error.message.includes('NetworkError') ? 
                'Unable to connect to the server. Please check your connection.' : 
                error.message;
                
            return { 
                success: false, 
                error: safeMessage,
                errorCode: error.code || 'AUTH_ERROR'
            };
        }
    }

    /**
     * Register a new user
     * @param {Object} userData User registration data
     * @returns {Promise<{success: boolean, message?: string, error?: string}>} Registration result
     */
    async register(userData) {
        try {
            // Input validation
            const { name, email, password, confirmPassword } = userData;
            
            if (!name || !email || !password) {
                throw new Error('All fields are required');
            }
            
            if (password !== confirmPassword) {
                throw new Error('Passwords do not match');
            }
            
            if (password.length < 8) {
                throw new Error('Password must be at least 8 characters long');
            }
            
            if (!/\S+@\S+\.\S+/.test(email)) {
                throw new Error('Please enter a valid email address');
            }
            
            console.log('Attempting to register user:', { ...userData, password: '***' });
            
            const response = await fetch(`${this.baseUrl}/auth/register`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: userData.name,
                    email: userData.email,
                    password: userData.password
                    // Add other fields as needed
                })
            });
            
            const result = await response.json();
            
            // Check if the response indicates success
            if (!result.success) {
                throw new Error(result.message || 'Registration failed');
            }
            
            // If registration is successful, store the token and user data
            if (result.token) {
                localStorage.setItem('token', result.token);
                this.token = result.token;
                this.user = result.user || {};
            }
            
            return {
                success: true,
                user: this.user,
                message: result.message || 'Registration successful. You are now logged in.'
            };
            
        } catch (error) {
            console.error('Registration error:', error);
            return { 
                success: false, 
                error: error.message || 'Registration failed. Please try again.',
                errorCode: error.code || 'REGISTRATION_ERROR'
            };
        }
    }

    /**
     * Log out the current user
     * @param {boolean} redirect Whether to redirect to login page after logout
     * @returns {Promise<{success: boolean, message?: string}>} Logout result
     */
    async logout(redirect = true) {
        try {
            // Optionally call server-side logout if needed
            // await fetch(`${this.baseUrl}/auth/logout`, {
            //     method: 'POST',
            //     headers: this.getAuthHeaders()
            // });
            
            // Clear local auth state
            localStorage.removeItem('token');
            this.token = null;
            const wasLoggedIn = !!this.user;
            this.user = null;
            
            // Notify listeners of auth state change
            this.notifyAuthStateChange();
            
            // Redirect to login page if requested
            if (redirect) {
                // Save current path for post-login redirect
                const currentPath = window.location.hash.replace('#', '') || '/';
                if (currentPath !== '/login') {
                    sessionStorage.setItem('preLoginRoute', currentPath);
                }
                window.location.hash = '/login';
            }
            
            return { 
                success: true, 
                message: wasLoggedIn ? 'Successfully logged out' : 'No active session',
                wasLoggedIn
            };
            
        } catch (error) {
            console.error('Logout error:', error);
            return { 
                success: false, 
                error: 'Failed to log out. Please try again.'
            };
        }
    }

    /**
     * Check if the current JWT token is expired
     * @param {string} token JWT token to check
     * @returns {boolean} Whether the token is expired
     */
    isTokenExpired(token) {
        try {
            const payload = this.parseJwt(token);
            if (!payload.exp) return false; // No expiration set
            
            const currentTime = Math.floor(Date.now() / 1000);
            return payload.exp < currentTime;
            
        } catch (error) {
            console.error('Error checking token expiration:', error);
            return true; // If we can't parse, assume expired
        }
    }

    /**
     * Parse a JWT token
     * @param {string} token JWT token to parse
     * @returns {object} Decoded token payload
     */
    parseJwt(token) {
        try {
            const base64Url = token.split('.')[1];
            const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
            const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            }).join(''));
            
            return JSON.parse(jsonPayload);
        } catch (error) {
            console.error('Error parsing JWT:', error);
            return {};
        }
    }

    /**
     * Check if user is authenticated with a valid token
     * @returns {boolean} Authentication status
     */
    isAuthenticated() {
        if (!this.token) return false;
        
        // Check if token is expired
        if (this.isTokenExpired(this.token)) {
            console.log('Token expired, logging out...');
            this.logout(false);
            return false;
        }
        
        return true;
    }

    /**
     * Check if user has a specific role
     * @param {string} role Role to check
     * @returns {boolean} Whether user has the role
     */
    hasRole(role) {
        if (!this.user || !this.user.roles) {
            return false;
        }
        return this.user.roles.includes(role);
    }

    /**
     * Check if user is an admin
     * @returns {boolean} Admin status
     */
    isAdmin() {
        return this.hasRole('admin');
    }

    /**
     * Get authentication headers for API requests
     * @returns {Object} Headers object with Authorization
     */
    getAuthHeaders() {
        return {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${this.token}`
        };
    }

    // Array to hold auth state change listeners
    authListeners = [];

    /**
     * Add an auth state change listener
     * @param {Function} callback Function to call when auth state changes
     * @returns {Function} Unsubscribe function
     */
    onAuthStateChange(callback) {
        if (typeof callback !== 'function') {
            throw new Error('Callback must be a function');
        }
        
        this.authListeners.push(callback);
        
        // Return unsubscribe function
        return () => {
            this.authListeners = this.authListeners.filter(cb => cb !== callback);
        };
    }
    
    /**
     * Notify all auth state change listeners
     */
    notifyAuthStateChange() {
        const user = this.user ? { ...this.user } : null;
        this.authListeners.forEach(callback => {
            try {
                callback(user);
            } catch (error) {
                console.error('Error in auth state change listener:', error);
            }
        });
    }

    /**
     * Make authenticated API request with automatic token refresh
     * @param {string} endpoint API endpoint
     * @param {Object} options Fetch options
     * @returns {Promise<any>} API response data
     */
    async authFetch(endpoint, options = {}) {
        // Set default headers if not provided
        const headers = {
            'Content-Type': 'application/json',
            ...options.headers
        };
        
        // Add auth token if available
        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }
        
        // Prepare request options
        const requestOptions = {
            ...options,
            headers,
            credentials: 'include' // Include cookies for session handling
        };
        
        try {
            const response = await fetch(`${this.baseUrl}${endpoint}`, requestOptions);
            
            // Handle 401 Unauthorized (token expired or invalid)
            if (response.status === 401) {
                // Try to refresh token if this wasn't a refresh request
                if (!endpoint.includes('/auth/refresh')) {
                    const refreshSuccess = await this.refreshToken();
                    if (refreshSuccess) {
                        // Retry the original request with new token
                        return this.authFetch(endpoint, options);
                    }
                }
                // If refresh failed or this was a refresh request, force logout
                this.logout();
                throw new Error('Session expired. Please log in again.');
            }
            
            // Handle other error statuses
            if (!response.ok) {
                const errorData = await this.parseResponse(response);
                const error = new Error(errorData.message || 'Request failed');
                error.status = response.status;
                error.data = errorData;
                throw error;
            }
            
            return await this.parseResponse(response);
            
        } catch (error) {
            console.error('API request failed:', error);
            throw error;
        }
    }
    
    /**
     * Attempt to refresh the access token
     * @returns {Promise<boolean>} Whether the refresh was successful
     */
    async refreshToken() {
        try {
            const response = await fetch(`${this.baseUrl}/auth/refresh`, {
                method: 'POST',
                credentials: 'include', // Important for httpOnly cookies
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error('Failed to refresh token');
            }
            
            const result = await response.json();
            const newToken = result.token;
            
            if (!newToken) {
                throw new Error('No token in refresh response');
            }
            
            // Update token and user info
            this.token = newToken;
            localStorage.setItem('token', newToken);
            
            // Update user info from new token
            const payload = this.parseJwt(newToken);
            this.user = {
                id: payload.user?.id || payload.id,
                name: payload.user?.name || payload.name,
                email: payload.user?.email || payload.email,
                roles: Array.isArray(payload.roles) ? 
                    payload.roles : 
                    (payload.role ? [payload.role] : ['user'])
            };
            
            this.notifyAuthStateChange();
            return true;
            
        } catch (error) {
            console.error('Token refresh failed:', error);
            this.logout(false);
            return false;
        }
    }
    
    /**
     * Parse JSON response, handling empty responses
     */
    async parseResponse(response) {
        const text = await response.text();
        return text ? JSON.parse(text) : {};
    }

    /**
     * Make an authenticated HTTP request with proper headers
     * @param {string} url The URL to fetch
     * @param {Object} options Fetch options
     * @returns {Promise<Response>} The fetch response
     */
    async authenticatedFetch(url, options = {}) {
        const headers = {
            'Content-Type': 'application/json',
            ...options.headers,
            ...this.getAuthHeaders()
        };

        try {
            const response = await fetch(url, { ...options, headers });
            
            // Handle 401 Unauthorized (expired token)
            if (response.status === 401) {
                this.logout();
                throw new Error('Session expired. Please log in again.');
            }
            
            return response;
        } catch (error) {
            console.error('API request error:', error);
            throw error;
        }
    }
}

// Create and export a singleton instance
const authService = new AuthService();
export default authService;
