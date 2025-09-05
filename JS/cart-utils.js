// Cart Utility Functions
class CartUtils {
    constructor() {
        this.cart = this.loadCart();
        this.updateCartDisplay();
    }

    // Load cart from localStorage
    loadCart() {
        const cart = localStorage.getItem('fitfuel_cart');
        return cart ? JSON.parse(cart) : [];
    }

    // Save cart to localStorage
    saveCart() {
        localStorage.setItem('fitfuel_cart', JSON.stringify(this.cart));
        this.updateCartDisplay();
    }

    // Add item to cart
    addToCart(product) {
        const existingItem = this.cart.find(item => item.id === product.id);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            this.cart.push({
                id: product.id,
                name: product.name,
                price: product.price,
                image: product.image,
                quantity: 1,
                maxQuantity: product.stock || 10
            });
        }
        
        this.saveCart();
        this.showAddToCartNotification(product.name);
    }

    // Remove item from cart
    removeFromCart(productId) {
        this.cart = this.cart.filter(item => item.id !== productId);
        this.saveCart();
    }

    // Update item quantity
    updateQuantity(productId, quantity) {
        const item = this.cart.find(item => item.id === productId);
        if (item) {
            if (quantity <= 0) {
                this.removeFromCart(productId);
            } else if (quantity <= item.maxQuantity) {
                item.quantity = quantity;
                this.saveCart();
            }
        }
    }

    // Get cart total
    getCartTotal() {
        return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    // Get cart item count
    getCartItemCount() {
        return this.cart.reduce((count, item) => count + item.quantity, 0);
    }

    // Clear cart
    clearCart() {
        this.cart = [];
        this.saveCart();
    }

    // Update cart display in navigation
    updateCartDisplay() {
        const cartCount = this.getCartItemCount();
        const cartIcons = document.querySelectorAll('.fa-shopping-cart');
        
        cartIcons.forEach(icon => {
            const parent = icon.parentElement;
            let badge = parent.querySelector('.cart-badge');
            
            if (cartCount > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'cart-badge absolute -top-1 -right-1 bg-emerald-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center';
                    parent.appendChild(badge);
                }
                badge.textContent = cartCount;
            } else if (badge) {
                badge.remove();
            }
        });
    }

    // Show add to cart notification
    showAddToCartNotification(productName) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-emerald-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300';
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>${productName} added to cart!</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    // Format currency
    formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }

    // Calculate shipping
    calculateShipping(subtotal) {
        if (subtotal >= 100) return 0; // Free shipping over $100
        if (subtotal >= 75) return 5;  // $5 shipping over $75
        return 10; // $10 standard shipping
    }

    // Calculate tax
    calculateTax(subtotal) {
        return subtotal * 0.08; // 8% tax rate
    }
}

// Initialize cart utils
const cartUtils = new CartUtils();

// Export for use in other files
window.cartUtils = cartUtils;
