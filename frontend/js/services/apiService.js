/**
 * API Service
 * Handles all API requests to the backend
 */
import authService from './authService.js';

export class ApiService {
    constructor() {
        this.baseUrl = 'http://localhost/webapp/backend/api';
    }

    /**
     * Make GET request to API
     * @param {string} endpoint API endpoint
     * @param {boolean} requireAuth Whether request requires authentication
     * @returns {Promise} Promise resolving to response data
     */
    async get(endpoint, requireAuth = true) {
        try {
            const headers = requireAuth ? authService.getAuthHeaders() : { 'Content-Type': 'application/json' };
            const response = await fetch(`${this.baseUrl}${endpoint}`, { 
                method: 'GET',
                headers
            });
            
            if (response.status === 401 && requireAuth) {
                authService.logout();
                throw new Error('Your session has expired. Please log in again.');
            }
            
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || `API error: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API GET error:', error);
            throw error;
        }
    }

    /**
     * Make POST request to API
     * @param {string} endpoint API endpoint
     * @param {Object} data Request payload
     * @param {boolean} requireAuth Whether request requires authentication
     * @returns {Promise} Promise resolving to response data
     */
    async post(endpoint, data, requireAuth = true) {
        try {
            const headers = requireAuth ? authService.getAuthHeaders() : { 'Content-Type': 'application/json' };
            const response = await fetch(`${this.baseUrl}${endpoint}`, {
                method: 'POST',
                headers,
                body: JSON.stringify(data)
            });
            
            if (response.status === 401 && requireAuth) {
                authService.logout();
                throw new Error('Your session has expired. Please log in again.');
            }
            
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || `API error: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API POST error:', error);
            throw error;
        }
    }

    /**
     * Make PUT request to API
     * @param {string} endpoint API endpoint
     * @param {Object} data Request payload
     * @param {boolean} requireAuth Whether request requires authentication
     * @returns {Promise} Promise resolving to response data
     */
    async put(endpoint, data, requireAuth = true) {
        try {
            const headers = requireAuth ? authService.getAuthHeaders() : { 'Content-Type': 'application/json' };
            const response = await fetch(`${this.baseUrl}${endpoint}`, {
                method: 'PUT',
                headers,
                body: JSON.stringify(data)
            });
            
            if (response.status === 401 && requireAuth) {
                authService.logout();
                throw new Error('Your session has expired. Please log in again.');
            }
            
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || `API error: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API PUT error:', error);
            throw error;
        }
    }

    /**
     * Make DELETE request to API
     * @param {string} endpoint API endpoint
     * @param {boolean} requireAuth Whether request requires authentication
     * @returns {Promise} Promise resolving to response data
     */
    async delete(endpoint, requireAuth = true) {
        try {
            const headers = requireAuth ? authService.getAuthHeaders() : { 'Content-Type': 'application/json' };
            const response = await fetch(`${this.baseUrl}${endpoint}`, {
                method: 'DELETE',
                headers
            });
            
            if (response.status === 401 && requireAuth) {
                authService.logout();
                throw new Error('Your session has expired. Please log in again.');
            }
            
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || `API error: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API DELETE error:', error);
            throw error;
        }
    }
}

// Create and export a singleton instance
const apiService = new ApiService();
export default apiService;
