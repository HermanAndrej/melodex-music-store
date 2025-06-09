// Admin Panel Functions
const admin = {
    // Orders Management
    orders: {
        async loadOrders() {
            try {
                const response = await fetch(`${API_BASE_URL}/orders/all`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`
                    }
                });

                if (!response.ok) {
                    if (response.status === 403) {
                        alert('You do not have permission to view all orders');
                        return;
                    }
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                if (!result.success) {
                    throw new Error(result.error || 'Failed to load orders');
                }

                this.displayOrders(result.data || []);
            } catch (error) {
                console.error('Error loading orders:', error);
                alert('Error loading orders: ' + error.message);
            }
        },

        displayOrders(orders) {
            const tbody = document.getElementById('ordersTableBody');
            if (!tbody) return;

            tbody.innerHTML = orders.map(order => `
                <tr>
                    <td>${order.OrderID}</td>
                    <td>${order.Username || `User #${order.UserID}` || 'N/A'}</td>
                    <td>${this.formatDate(order.OrderDate)}</td>
                    <td>$${order.TotalAmount.toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-danger" onclick="admin.orders.deleteOrder(${order.OrderID})">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A';
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return 'N/A';
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                return date.toLocaleDateString(undefined, options);
            } catch (error) {
                console.error('Error formatting date:', error);
                return 'N/A';
            }
        },

        async deleteOrder(id) {
            if (confirm('Are you sure you want to delete this order?')) {
                try {
                    const response = await fetch(`${API_BASE_URL}/orders/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${localStorage.getItem('token')}`
                        }
                    });

                    if (response.ok) {
                        this.loadOrders();
                    } else {
                        alert('Failed to delete order');
                    }
                } catch (error) {
                    console.error('Error deleting order:', error);
                    alert('Error deleting order');
                }
            }
        }
    }
};

// Initialize admin panel when page loads
function initializeAdminPanel() {
    // Check if user is admin
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    if (user.Role !== 'admin') {
        window.location.href = 'index.html';
        return;
    }
    
    // Load initial data
    admin.orders.loadOrders();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAdminPanel);
} else {
    initializeAdminPanel();
}

// Export admin object and functions
window.admin = admin; 