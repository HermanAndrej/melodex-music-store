/**
 * User Controller - Handles user-related application logic
 * This controller manages user authentication, profile management, and admin user operations
 */

import User from '../models/User.js';
import authService from '../services/authService.js';
import apiService from '../services/apiService.js';

/**
 * Get current authenticated user
 * @returns {User|null} User object or null if not authenticated
 */
export function getCurrentUser() {
    if (!authService.isAuthenticated()) return null;
    
    const userData = authService.user;
    return userData ? new User(userData) : null;
}

/**
 * Handle user login
 * @param {string} email User email
 * @param {string} password User password
 * @returns {Promise<Object>} Login result
 */
export async function login(email, password) {
    try {
        return await authService.login(email, password);
    } catch (error) {
        console.error('Login error:', error);
        throw error;
    }
}

/**
 * Handle user registration
 * @param {Object} userData User registration data
 * @returns {Promise<Object>} Registration result
 */
export async function register(userData) {
    try {
        return await authService.register(userData);
    } catch (error) {
        console.error('Registration error:', error);
        throw error;
    }
}

/**
 * Handle user logout
 */
export function logout() {
    authService.logout();
}

/**
 * Get user profile
 * @returns {Promise<User>} User profile
 */
export async function getProfile() {
    try {
        if (!authService.isAuthenticated()) {
            throw new Error('User not authenticated');
        }
        
        const response = await apiService.get('/profile');
        return new User(response.user);
    } catch (error) {
        console.error('Error getting profile:', error);
        throw error;
    }
}

/**
 * Update user profile
 * @param {Object} profileData Updated profile data
 * @returns {Promise<User>} Updated user profile
 */
export async function updateProfile(profileData) {
    try {
        if (!authService.isAuthenticated()) {
            throw new Error('User not authenticated');
        }
        
        const response = await apiService.put('/profile', profileData);
        return new User(response.user);
    } catch (error) {
        console.error('Error updating profile:', error);
        throw error;
    }
}

/**
 * Change user password
 * @param {string} currentPassword Current password
 * @param {string} newPassword New password
 * @returns {Promise<Object>} Password change result
 */
export async function changePassword(currentPassword, newPassword) {
    try {
        if (!authService.isAuthenticated()) {
            throw new Error('User not authenticated');
        }
        
        return await apiService.post('/profile/password', {
            current_password: currentPassword,
            new_password: newPassword
        });
    } catch (error) {
        console.error('Error changing password:', error);
        throw error;
    }
}

// Admin functions

/**
 * Get all users (admin only)
 * @returns {Promise<Array<User>>} List of users
 */
export async function getAllUsers() {
    try {
        if (!authService.isAdmin()) {
            throw new Error('Admin privileges required');
        }
        
        const response = await apiService.get('/users');
        return response.users.map(userData => new User(userData));
    } catch (error) {
        console.error('Error getting users:', error);
        throw error;
    }
}

/**
 * Get user by ID (admin only)
 * @param {string|number} userId User ID
 * @returns {Promise<User>} User object
 */
export async function getUserById(userId) {
    try {
        if (!authService.isAdmin()) {
            throw new Error('Admin privileges required');
        }
        
        const response = await apiService.get(`/users/${userId}`);
        return new User(response.user);
    } catch (error) {
        console.error(`Error getting user ${userId}:`, error);
        throw error;
    }
}

/**
 * Create new user (admin only)
 * @param {Object} userData User data
 * @returns {Promise<User>} Created user
 */
export async function createUser(userData) {
    try {
        if (!authService.isAdmin()) {
            throw new Error('Admin privileges required');
        }
        
        const response = await apiService.post('/users', userData);
        return new User(response.user);
    } catch (error) {
        console.error('Error creating user:', error);
        throw error;
    }
}

/**
 * Update user (admin only)
 * @param {string|number} userId User ID
 * @param {Object} userData Updated user data
 * @returns {Promise<User>} Updated user
 */
export async function updateUser(userId, userData) {
    try {
        if (!authService.isAdmin()) {
            throw new Error('Admin privileges required');
        }
        
        const response = await apiService.put(`/users/${userId}`, userData);
        return new User(response.user);
    } catch (error) {
        console.error(`Error updating user ${userId}:`, error);
        throw error;
    }
}

/**
 * Delete user (admin only)
 * @param {string|number} userId User ID
 * @returns {Promise<Object>} Deletion result
 */
export async function deleteUser(userId) {
    try {
        if (!authService.isAdmin()) {
            throw new Error('Admin privileges required');
        }
        
        return await apiService.delete(`/users/${userId}`);
    } catch (error) {
        console.error(`Error deleting user ${userId}:`, error);
        throw error;
    }
}

/**
 * Check if the current user has admin privileges
 * @returns {boolean} Whether user is admin
 */
export function isAdmin() {
    return authService.isAdmin();
}

/**
 * Check if the user is authenticated
 * @returns {boolean} Authentication status
 */
export function isAuthenticated() {
    return authService.isAuthenticated();
}

// Export the controller functions
export default {
    getCurrentUser,
    login,
    register,
    logout,
    getProfile,
    updateProfile,
    changePassword,
    getAllUsers,
    getUserById,
    createUser,
    updateUser,
    deleteUser,
    isAdmin,
    isAuthenticated
};
