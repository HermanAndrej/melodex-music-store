/**
 * User Model - Handles all user-related API calls
 */

/**
 * Fetches all users from the API
 * @returns {Promise<Array>} Array of user objects
 */
export async function fetchUsers() {
    try {
        const token = localStorage.getItem('token');
        if (!token) {
            throw new Error('No authentication token found');
        }

        const response = await fetch('/api/users', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Failed to fetch users');
        }

        return await response.json();
    } catch (error) {
        console.error('Error fetching users:', error);
        throw error;
    }
}

/**
 * Fetches a single user by ID
 * @param {number|string} userId - The ID of the user to fetch
 * @returns {Promise<Object>} User object
 */
export async function fetchUserById(userId) {
    try {
        const token = localStorage.getItem('token');
        if (!token) {
            throw new Error('No authentication token found');
        }

        const response = await fetch(`/api/users/${userId}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Failed to fetch user');
        }

        return await response.json();
    } catch (error) {
        console.error(`Error fetching user ${userId}:`, error);
        throw error;
    }
}

/**
 * Creates a new user
 * @param {Object} userData - The user data to create
 * @returns {Promise<Object>} The created user object
 */
export async function createUser(userData) {
    try {
        const token = localStorage.getItem('token');
        if (!token) {
            throw new Error('No authentication token found');
        }

        const response = await fetch('/api/users', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Failed to create user');
        }

        return await response.json();
    } catch (error) {
        console.error('Error creating user:', error);
        throw error;
    }
}

/**
 * Updates an existing user
 * @param {number|string} userId - The ID of the user to update
 * @param {Object} userData - The updated user data
 * @returns {Promise<Object>} The updated user object
 */
export async function updateUser(userId, userData) {
    try {
        const token = localStorage.getItem('token');
        if (!token) {
            throw new Error('No authentication token found');
        }

        const response = await fetch(`/api/users/${userId}`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Failed to update user');
        }

        return await response.json();
    } catch (error) {
        console.error(`Error updating user ${userId}:`, error);
        throw error;
    }
}

/**
 * Deletes a user
 * @param {number|string} userId - The ID of the user to delete
 * @returns {Promise<boolean>} True if deletion was successful
 */
export async function deleteUser(userId) {
    try {
        const token = localStorage.getItem('token');
        if (!token) {
            throw new Error('No authentication token found');
        }

        const response = await fetch(`/api/users/${userId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Failed to delete user');
        }

        return true;
    } catch (error) {
        console.error(`Error deleting user ${userId}:`, error);
        throw error;
    }
}
