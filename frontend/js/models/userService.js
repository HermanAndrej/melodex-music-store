/**
 * Service for handling user-related API calls
 */

const API_BASE_URL = '/api/users';

/**
 * Fetches all users from the API
 * @returns {Promise<Array>} Array of user objects
 */
export async function getUsers() {
    try {
        const response = await fetch(API_BASE_URL, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`Failed to fetch users: ${response.statusText}`);
        }

        return await response.json();
    } catch (error) {
        console.error('Error fetching users:', error);
        throw error;
    }
}

/**
 * Saves a user by either creating a new one or updating an existing one
 * @param {Object} userData - The user data to save
 * @returns {Promise<Object>} The saved user data
 */
export async function saveUser(userData) {
    const { id, ...data } = userData;
    const url = id ? `${API_BASE_URL}/${id}` : API_BASE_URL;
    const method = id ? 'PUT' : 'POST';

    try {
        // If this is an update, verify the user exists first
        if (id) {
            const exists = await userExists(id);
            if (!exists) {
                throw new Error('User not found');
            }
        }

        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Failed to save user');
        }

        return await response.json();
    } catch (error) {
        console.error('Error saving user:', error);
        throw error;
    }
}

/**
 * Checks if a user with the given ID exists
 * @param {string|number} userId - The ID of the user to check
 * @returns {Promise<boolean>} True if the user exists, false otherwise
 */
async function userExists(userId) {
    try {
        const response = await fetch(`${API_BASE_URL}/${userId}`, {
            method: 'HEAD',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        return response.ok;
    } catch (error) {
        console.error('Error checking if user exists:', error);
        return false;
    }
}

/**
 * Deletes a user
 * @param {string|number} userId - The ID of the user to delete
 * @returns {Promise<boolean>} True if deletion was successful
 */
export async function deleteUser(userId) {
    try {
        const response = await fetch(`${API_BASE_URL}/${userId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });

        if (!response.ok) {
            throw new Error('Failed to delete user');
        }

        return true;
    } catch (error) {
        console.error('Error deleting user:', error);
        throw error;
    }
}
