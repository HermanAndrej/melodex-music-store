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
                                        <button class="btn btn-outline-secondary" type="button" onclick="cart.updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                                        <input type="text" class="form-control text-center" value="${item.quantity}" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="cart.updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <p class="card-text">$${(item.price * item.quantity).toFixed(2)}</p>
                                    <button class="btn btn-danger btn-sm" onclick="cart.removeFromCart(${item.id})">Remove</button>
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

// Export cart object immediately
window.cart = cart;

// Initialize cart UI when page loads
document.addEventListener('DOMContentLoaded', () => {
    console.log('Initializing cart UI...');
    cart.updateCartUI();
}); 