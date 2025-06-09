// API Configuration
const API_BASE_URL = 'http://localhost:8000/api'; // Update this with your actual backend URL

// Authentication Functions
const auth = {
    async login(email, password) {
        try {
            const response = await fetch(`${API_BASE_URL}/auth/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ Email: email, Password: password })
            });
            const data = await response.json();
            console.log('Login response:', data);
            if (response.ok && data.success && data.data && data.data.token) {
                localStorage.setItem('token', data.data.token);
                localStorage.setItem('user', JSON.stringify(data.data.user));
                return true;
            }
            return false;
        } catch (error) {
            console.error('Login error:', error);
            return false;
        }
    },

    async register(userData) {
        try {
            const response = await fetch(`${API_BASE_URL}/auth/register`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(userData)
            });
            const data = await response.json();
            return data.success || false;
        } catch (error) {
            console.error('Registration error:', error);
            return false;
        }
    },

    logout() {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = 'login.html';
    },

    isAuthenticated() {
        return !!localStorage.getItem('token');
    },

    getCurrentUser() {
        const user = localStorage.getItem('user');
        return user ? JSON.parse(user) : null;
    },

    async updateProfile(userData) {
        try {
            const response = await fetch(`${API_BASE_URL}/users/profile`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: JSON.stringify(userData)
            });
            const data = await response.json();
            
            if (response.ok && data.success) {
                // Update local storage with new user data
                const currentUser = this.getCurrentUser();
                const updatedUser = { ...currentUser, ...userData };
                localStorage.setItem('user', JSON.stringify(updatedUser));
                return true;
            }
            return false;
        } catch (error) {
            console.error('Error updating profile:', error);
            return false;
        }
    },

    async getProfile() {
        try {
            const response = await fetch(`${API_BASE_URL}/users/profile`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            });
            const data = await response.json();
            if (response.ok && data.success) {
                return data.data;
            }
            return null;
        } catch (error) {
            console.error('Error fetching profile:', error);
            return null;
        }
    }
};

// Product Functions
const products = {
    async getAll() {
        const response = await fetch(`${API_BASE_URL}/products`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        const data = await response.json();
        console.log('Raw products response:', data); // Debug log
        return Array.isArray(data) ? data : (data.data || []);
    },

    async getById(id) {
        const response = await fetch(`${API_BASE_URL}/products/${id}`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        const data = await response.json();
        return data.data || data;
    },

    async getByCategory(categoryId) {
        const response = await fetch(`${API_BASE_URL}/products/category/${categoryId}`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        const data = await response.json();
        return Array.isArray(data) ? data : (data.data || []);
    },

    async search(query) {
        const response = await fetch(`${API_BASE_URL}/products/search?q=${encodeURIComponent(query)}`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        const data = await response.json();
        return Array.isArray(data) ? data : (data.data || []);
    }
};

// Cart Functions
const cart = {
    getCart() {
        return JSON.parse(localStorage.getItem('cart')) || [];
    },

    addToCart(product) {
        const cart = this.getCart();
        const existingItem = cart.find(item => item.id === product.id);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                price: product.price,
                quantity: 1,
                image: product.image
            });
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        this.updateCartUI();
    },

    removeFromCart(productId) {
        let cart = this.getCart();
        cart = cart.filter(item => item.id !== productId);
        localStorage.setItem('cart', JSON.stringify(cart));
        this.updateCartUI();
    },

    updateQuantity(productId, quantity) {
        const cart = this.getCart();
        const item = cart.find(item => item.id === productId);
        if (item) {
            item.quantity = quantity;
            localStorage.setItem('cart', JSON.stringify(cart));
            this.updateCartUI();
        }
    },

    clearCart() {
        localStorage.removeItem('cart');
        this.updateCartUI();
    },

    updateCartUI() {
        const cart = this.getCart();
        const cartCount = document.querySelector('.fa-cart-arrow-down + .badge');
        const cartTotal = document.getElementById('cart-total');
        const cartSubtotal = document.getElementById('cart-subtotal');
        const cartItems = document.getElementById('cart-items');
        const cartEmpty = document.getElementById('cart-empty');
        
        // Update cart count badge
        if (cartCount) {
            cartCount.textContent = cart.reduce((total, item) => total + item.quantity, 0);
        }
        
        // Calculate totals
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const shipping = 20.00; // Fixed shipping cost
        const total = subtotal + shipping;
        
        // Update totals in UI
        if (cartTotal) cartTotal.textContent = `$${total.toFixed(2)}`;
        if (cartSubtotal) cartSubtotal.textContent = `$${subtotal.toFixed(2)}`;
        
        // Update cart items display
        if (cartItems && cartEmpty) {
            if (cart.length === 0) {
                cartItems.style.display = 'none';
                cartEmpty.style.display = 'block';
            } else {
                cartItems.style.display = 'block';
                cartEmpty.style.display = 'none';
                
                cartItems.innerHTML = cart.map(item => `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <img src="${item.image}" class="img-fluid rounded" alt="${item.name}">
                                </div>
                                <div class="col-md-4">
                                    <h5 class="card-title">${item.name}</h5>
                                    <p class="card-text">$${item.price.toFixed(2)}</p>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <button class="btn btn-outline-secondary" type="button" onclick="api.cart.updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                                        <input type="text" class="form-control text-center" value="${item.quantity}" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="api.cart.updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <p class="card-text">$${(item.price * item.quantity).toFixed(2)}</p>
                                    <button class="btn btn-danger btn-sm" onclick="api.cart.removeFromCart(${item.id})">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        }

        // Update navigation badge
        if (window.nav) {
            window.nav.updateCartBadge();
        }
    }
};

// Orders Functions
const orders = {
    async getAll() {
        const response = await fetch(`${API_BASE_URL}/orders/all`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        const data = await response.json();
        return Array.isArray(data) ? data : (data.data || []);
    },

    async getById(id) {
        const response = await fetch(`${API_BASE_URL}/orders/${id}`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        const data = await response.json();
        return data.data || data;
    },

    async delete(id) {
        const response = await fetch(`${API_BASE_URL}/orders/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.error || 'Failed to delete order');
        }
        return data;
    },

    async createOrder(orderData) {
        const response = await fetch(`${API_BASE_URL}/orders`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify(orderData)
        });
        
        const data = await response.json();
        console.log('Order creation API response:', data);
        
        if (!response.ok) {
            throw new Error(data.error || 'Failed to create order');
        }
        
        return data;
    },

    async getUserOrders() {
        const response = await fetch(`${API_BASE_URL}/orders/user`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        const data = await response.json();
        return Array.isArray(data) ? data : (data.data || []);
    }
};

// Category Functions
const categories = {
    async getAll() {
        const response = await fetch(`${API_BASE_URL}/categories`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        const data = await response.json();
        console.log('Raw categories response:', data);
        return Array.isArray(data) ? data : (data.data || []);
    },

    async getById(id) {
        const response = await fetch(`${API_BASE_URL}/categories/${id}`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        const data = await response.json();
        return data.data || data;
    }
};

// Expose API functions to window
window.api = {
    auth,
    products,
    cart,
    orders,
    categories
}; 