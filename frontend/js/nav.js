// Function to update cart badge
function updateCartBadge() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const cartCount = cart.reduce((total, item) => total + item.quantity, 0);
    const cartBadge = document.querySelector('.fa-cart-arrow-down + .badge');
    if (cartBadge) {
        cartBadge.textContent = cartCount || '';
        cartBadge.style.display = cartCount ? 'block' : 'none';
    }
}

// Initialize navigation badges
document.addEventListener('DOMContentLoaded', () => {
    updateCartBadge();
});

// Export functions for use in other files
window.nav = {
    updateCartBadge
}; 