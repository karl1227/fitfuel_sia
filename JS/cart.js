// Cart Page Functionality
class Cart {
    constructor() {
        this.cart = cartUtils.cart;
        this.init();
    }

    init() {
        this.renderCartItems();
        this.updateOrderSummary();
        this.bindEvents();
    }

    // Render cart items
    renderCartItems() {
        const cartItemsContainer = document.getElementById('cart-items');
        const emptyCart = document.getElementById('empty-cart');
        const itemCount = document.getElementById('item-count');
        
        if (this.cart.length === 0) {
            cartItemsContainer.innerHTML = '';
            emptyCart.classList.remove('hidden');
            itemCount.textContent = '0 items';
            return;
        }

        emptyCart.classList.add('hidden');
        itemCount.textContent = `${this.cart.length} item${this.cart.length !== 1 ? 's' : ''}`;

        cartItemsContainer.innerHTML = this.cart.map(item => `
            <div class="cart-item p-6 flex items-center space-x-4" data-id="${item.id}">
                <input type="checkbox" 
                       class="item-checkbox w-5 h-5 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500 focus:ring-2"
                       onchange="cart.toggleItemSelection(${item.id}, this.checked)">
                
                <div class="flex-shrink-0">
                    <img src="${item.image}" alt="${item.name}" class="w-20 h-20 object-cover rounded-lg product-image">
                </div>
                
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900 truncate">${item.name}</h3>
                    <div class="flex items-center space-x-4 mt-2">
                        <div class="quantity-selector flex items-center border border-gray-300 rounded-lg">
                            <button onclick="cart.updateItemQuantity(${item.id}, ${item.quantity - 1})" 
                                    class="px-3 py-1 hover:bg-gray-100 transition-colors">
                                <i class="fas fa-minus text-sm"></i>
                            </button>
                            <span class="px-4 py-1 text-sm font-medium">${item.quantity}</span>
                            <button onclick="cart.updateItemQuantity(${item.id}, ${item.quantity + 1})" 
                                    class="px-3 py-1 hover:bg-gray-100 transition-colors">
                                <i class="fas fa-plus text-sm"></i>
                            </button>
                        </div>
                        <span class="text-lg font-semibold text-gray-900">${cartUtils.formatCurrency(item.price * item.quantity)}</span>
                    </div>
                </div>
                
                <div class="flex-shrink-0">
                    <button onclick="cart.removeItem(${item.id})" 
                            class="text-red-600 hover:text-red-800 transition-colors remove-btn">
                        <i class="fas fa-trash text-lg"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }

    // Update order summary
    updateOrderSummary() {
        const subtotal = cartUtils.getCartTotal();
        const shipping = cartUtils.calculateShipping(subtotal);
        const tax = cartUtils.calculateTax(subtotal);
        const total = subtotal + shipping + tax;

        document.getElementById('subtotal').textContent = cartUtils.formatCurrency(subtotal);
        document.getElementById('shipping').textContent = cartUtils.formatCurrency(shipping);
        document.getElementById('tax').textContent = cartUtils.formatCurrency(tax);
        document.getElementById('total').textContent = cartUtils.formatCurrency(total);

        // Enable/disable checkout button
        const checkoutBtn = document.getElementById('checkout-btn');
        checkoutBtn.disabled = this.cart.length === 0;
    }

    // Bind events
    bindEvents() {
        // Select all checkbox
        const selectAllCheckbox = document.getElementById('select-all');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                this.toggleSelectAll(e.target.checked);
            });
        }

        // Remove selected button
        const removeSelectedBtn = document.getElementById('remove-selected');
        if (removeSelectedBtn) {
            removeSelectedBtn.addEventListener('click', () => {
                this.removeSelectedItems();
            });
        }
    }

    // Toggle select all items
    toggleSelectAll(checked) {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = checked;
        });
        this.updateRemoveSelectedButton();
    }

    // Toggle individual item selection
    toggleItemSelection(itemId, checked) {
        this.updateRemoveSelectedButton();
        this.updateSelectAllCheckbox();
    }

    // Update remove selected button visibility
    updateRemoveSelectedButton() {
        const selectedItems = document.querySelectorAll('.item-checkbox:checked');
        const removeSelectedBtn = document.getElementById('remove-selected');
        
        if (selectedItems.length > 0) {
            removeSelectedBtn.classList.remove('hidden');
        } else {
            removeSelectedBtn.classList.add('hidden');
        }
    }

    // Update select all checkbox state
    updateSelectAllCheckbox() {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
        const selectAllCheckbox = document.getElementById('select-all');
        
        if (checkedBoxes.length === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else if (checkedBoxes.length === checkboxes.length) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
        } else {
            selectAllCheckbox.indeterminate = true;
        }
    }

    // Update item quantity
    updateItemQuantity(itemId, newQuantity) {
        const item = this.cart.find(item => item.id === itemId);
        if (item && newQuantity > 0 && newQuantity <= item.maxQuantity) {
            cartUtils.updateQuantity(itemId, newQuantity);
            this.cart = cartUtils.cart;
            this.renderCartItems();
            this.updateOrderSummary();
        }
    }

    // Remove item from cart
    removeItem(itemId) {
        cartUtils.removeFromCart(itemId);
        this.cart = cartUtils.cart;
        this.renderCartItems();
        this.updateOrderSummary();
    }

    // Remove selected items
    removeSelectedItems() {
        const selectedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
        selectedCheckboxes.forEach(checkbox => {
            const cartItem = checkbox.closest('.cart-item');
            const itemId = parseInt(cartItem.dataset.id);
            this.removeItem(itemId);
        });
    }
}

// Promo code functionality
function applyPromoCode() {
    const promoCode = document.getElementById('promo-code').value.trim().toUpperCase();
    const promoMessage = document.getElementById('promo-message');
    
    // Reset message
    promoMessage.classList.add('hidden');
    promoMessage.classList.remove('message-success', 'message-error');
    
    if (!promoCode) {
        showPromoMessage('Please enter a promo code', 'error');
        return;
    }
    
    // Valid promo codes
    const validCodes = {
        'FITFUEL20': 0.20,  // 20% off
        'WELCOME10': 0.10,  // 10% off
        'SAVE15': 0.15      // 15% off
    };
    
    if (validCodes[promoCode]) {
        const discount = validCodes[promoCode];
        showPromoMessage(`${Math.round(discount * 100)}% discount applied!`, 'success');
        // Here you would apply the discount to the order
    } else {
        showPromoMessage('Invalid promo code', 'error');
    }
}

function showPromoMessage(message, type) {
    const promoMessage = document.getElementById('promo-message');
    promoMessage.textContent = message;
    promoMessage.classList.remove('hidden', 'message-success', 'message-error');
    promoMessage.classList.add(`message-${type}`);
}

// Checkout functionality
function checkout() {
    if (cartUtils.cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }
    
    // Here you would integrate with your payment system
    alert('Redirecting to checkout...');
    // window.location.href = 'checkout.html';
}

// Initialize cart when page loads
document.addEventListener('DOMContentLoaded', () => {
    window.cart = new Cart();
});
