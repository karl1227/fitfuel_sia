// Cart functionality
class Cart {
    constructor() {
        this.items = this.loadCartFromStorage();
        this.shippingThreshold = 75;
        this.shippingCost = 9.99;
        this.taxRate = 0.08; // 8% tax rate
        this.promoCode = null;
        this.promoDiscount = 0;
        this.selectedItems = new Set(); // Track selected items
        
        this.init();
    }

    init() {
        // Select all items by default
        this.items.forEach(item => {
            const itemKey = `${item.id}-${item.size}`;
            this.selectedItems.add(itemKey);
        });
        
        this.renderCart();
        this.updateSummary();
        this.setupEventListeners();
    }

    // Load cart from localStorage
    loadCartFromStorage() {
        const savedCart = localStorage.getItem('fitfuel_cart');
        return savedCart ? JSON.parse(savedCart) : [];
    }

    // Save cart to localStorage
    saveCartToStorage() {
        localStorage.setItem('fitfuel_cart', JSON.stringify(this.items));
    }

    // Add item to cart
    addItem(product, size = 'M', quantity = 1) {
        const existingItem = this.items.find(item => 
            item.id === product.id && item.size === size
        );

        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            this.items.push({
                id: product.id,
                name: product.name,
                price: product.price,
                image: product.image,
                size: size,
                quantity: quantity,
                category: product.category
            });
        }

        this.saveCartToStorage();
        this.renderCart();
        this.updateSummary();
    }

    // Remove item from cart
    removeItem(itemId, size) {
        this.items = this.items.filter(item => 
            !(item.id === itemId && item.size === size)
        );
        this.saveCartToStorage();
        this.renderCart();
        this.updateSummary();
    }

    // Update item quantity
    updateQuantity(itemId, size, quantity) {
        const item = this.items.find(item => 
            item.id === itemId && item.size === size
        );

        if (item) {
            if (quantity <= 0) {
                this.removeItem(itemId, size);
            } else {
                item.quantity = quantity;
                this.saveCartToStorage();
                this.renderCart();
                this.updateSummary();
            }
        }
    }

    // Get cart total (only for selected items)
    getSubtotal() {
        return this.items.reduce((total, item) => {
            const itemKey = `${item.id}-${item.size}`;
            if (this.selectedItems.has(itemKey)) {
                return total + (item.price * item.quantity);
            }
            return total;
        }, 0);
    }

    // Get shipping cost
    getShippingCost() {
        const subtotal = this.getSubtotal();
        return subtotal >= this.shippingThreshold ? 0 : this.shippingCost;
    }

    // Get tax amount
    getTaxAmount() {
        const subtotal = this.getSubtotal();
        return subtotal * this.taxRate;
    }

    // Get total
    getTotal() {
        const subtotal = this.getSubtotal();
        const shipping = this.getShippingCost();
        const tax = this.getTaxAmount();
        const discount = this.promoDiscount;
        
        return subtotal + shipping + tax - discount;
    }

    // Apply promo code
    applyPromoCode(code) {
        const validCodes = {
            'FITFUEL20': 0.20, // 20% off
            'WELCOME10': 0.10, // 10% off
            'FREESHIP': 'freeship' // Free shipping
        };

        if (validCodes[code]) {
            this.promoCode = code;
            
            if (validCodes[code] === 'freeship') {
                this.promoDiscount = this.getShippingCost();
            } else {
                this.promoDiscount = this.getSubtotal() * validCodes[code];
            }
            
            this.updateSummary();
            return true;
        }
        
        return false;
    }

    // Render cart items
    renderCart() {
        const cartItemsContainer = document.getElementById('cart-items');
        const emptyCartContainer = document.getElementById('empty-cart');
        const itemCountElement = document.getElementById('item-count');

        if (this.items.length === 0) {
            cartItemsContainer.innerHTML = '';
            emptyCartContainer.classList.remove('hidden');
            itemCountElement.textContent = '0 items';
            return;
        }

        emptyCartContainer.classList.add('hidden');
        itemCountElement.textContent = `${this.items.length} item${this.items.length !== 1 ? 's' : ''}`;

        // Update select all checkbox state
        this.updateSelectAllCheckbox();

        cartItemsContainer.innerHTML = this.items.map(item => {
            const itemKey = `${item.id}-${item.size}`;
            const isSelected = this.selectedItems.has(itemKey);
            
            return `
            <div class="p-6 cart-item cart-item-enter ${isSelected ? 'bg-white' : 'bg-gray-50 opacity-75'}">
                <div class="flex items-center space-x-4 cart-item-details">
                    <!-- Checkbox -->
                    <div class="flex-shrink-0">
                        <input type="checkbox" 
                               class="w-5 h-5 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500 focus:ring-2" 
                               ${isSelected ? 'checked' : ''}
                               onchange="cart.toggleItemSelection(${item.id}, '${item.size}', this.checked)">
                    </div>
                    
                    <!-- Product Image -->
                    <div class="flex-shrink-0">
                        <img src="${item.image}" alt="${item.name}" class="w-24 h-24 object-cover rounded-lg product-image">
                    </div>

                    <!-- Product Details -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-medium text-gray-900 truncate">${item.name}</h3>
                        <p class="text-sm text-gray-500">Size: ${item.size}</p>
                        <p class="text-sm text-gray-500">Category: ${item.category}</p>
                        
                        <!-- Quantity Controls -->
                        <div class="flex items-center space-x-2 mt-2 quantity-controls">
                            <label class="text-sm text-gray-600">Quantity:</label>
                            <div class="flex items-center quantity-selector">
                                <button onclick="cart.updateQuantity(${item.id}, '${item.size}', ${item.quantity - 1})" 
                                        class="px-3 py-1 hover:bg-gray-100 transition-colors focus-ring">
                                    <i class="fas fa-minus text-xs"></i>
                                </button>
                                <span class="px-3 py-1 text-sm font-medium min-w-[2rem] text-center">${item.quantity}</span>
                                <button onclick="cart.updateQuantity(${item.id}, '${item.size}', ${item.quantity + 1})" 
                                        class="px-3 py-1 hover:bg-gray-100 transition-colors focus-ring">
                                    <i class="fas fa-plus text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Price and Remove -->
                    <div class="flex flex-col items-end space-y-2 cart-item-price">
                        <div class="text-right">
                            <p class="text-lg font-semibold text-gray-900">$${(item.price * item.quantity).toFixed(2)}</p>
                            <p class="text-sm text-gray-500">$${item.price} each</p>
                        </div>
                        
                        <button onclick="cart.removeItem(${item.id}, '${item.size}')" 
                                class="text-red-600 hover:text-red-800 text-sm font-medium transition-colors remove-btn focus-ring">
                            <i class="fas fa-trash mr-1"></i>
                            Remove
                        </button>
                    </div>
                </div>
            </div>
        `;
        }).join('');
    }

    // Update order summary
    updateSummary() {
        const subtotal = this.getSubtotal();
        const shipping = this.getShippingCost();
        const tax = this.getTaxAmount();
        const total = this.getTotal();

        document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
        document.getElementById('shipping').textContent = shipping === 0 ? 'Free' : `$${shipping.toFixed(2)}`;
        document.getElementById('tax').textContent = `$${tax.toFixed(2)}`;
        document.getElementById('total').textContent = `$${total.toFixed(2)}`;

        // Enable/disable checkout button
        const checkoutBtn = document.getElementById('checkout-btn');
        checkoutBtn.disabled = this.items.length === 0;
    }

    // Setup event listeners
    setupEventListeners() {
        // Promo code input
        const promoInput = document.getElementById('promo-code');
        promoInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.handlePromoCode();
            }
        });
    }

    // Handle promo code application
    handlePromoCode() {
        const promoInput = document.getElementById('promo-code');
        const messageDiv = document.getElementById('promo-message');
        const code = promoInput.value.trim().toUpperCase();

        if (!code) return;

        if (this.applyPromoCode(code)) {
            messageDiv.textContent = 'Promo code applied successfully!';
            messageDiv.className = 'mt-2 text-sm text-green-600';
            messageDiv.classList.remove('hidden');
            promoInput.value = '';
        } else {
            messageDiv.textContent = 'Invalid promo code. Please try again.';
            messageDiv.className = 'mt-2 text-sm text-red-600';
            messageDiv.classList.remove('hidden');
        }

        // Hide message after 3 seconds
        setTimeout(() => {
            messageDiv.classList.add('hidden');
        }, 3000);
    }

    // Toggle individual item selection
    toggleItemSelection(itemId, size, isSelected) {
        const itemKey = `${itemId}-${size}`;
        if (isSelected) {
            this.selectedItems.add(itemKey);
        } else {
            this.selectedItems.delete(itemKey);
        }
        this.updateSelectAllCheckbox();
        this.updateSummary();
    }

    // Toggle select all items
    toggleSelectAll(isSelected) {
        if (isSelected) {
            // Select all items
            this.items.forEach(item => {
                const itemKey = `${item.id}-${item.size}`;
                this.selectedItems.add(itemKey);
            });
        } else {
            // Deselect all items
            this.selectedItems.clear();
        }
        this.renderCart();
        this.updateSummary();
    }

    // Update select all checkbox state
    updateSelectAllCheckbox() {
        const selectAllCheckbox = document.getElementById('select-all');
        const removeSelectedBtn = document.getElementById('remove-selected');
        
        if (!selectAllCheckbox) return;

        const totalItems = this.items.length;
        const selectedCount = this.selectedItems.size;

        if (selectedCount === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
            if (removeSelectedBtn) removeSelectedBtn.classList.add('hidden');
        } else if (selectedCount === totalItems) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
            if (removeSelectedBtn) removeSelectedBtn.classList.remove('hidden');
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
            if (removeSelectedBtn) removeSelectedBtn.classList.remove('hidden');
        }
    }

    // Remove selected items
    removeSelectedItems() {
        if (this.selectedItems.size === 0) return;
        
        if (confirm(`Are you sure you want to remove ${this.selectedItems.size} selected item(s)?`)) {
            // Remove selected items from the cart
            this.items = this.items.filter(item => {
                const itemKey = `${item.id}-${item.size}`;
                return !this.selectedItems.has(itemKey);
            });
            
            // Clear selection
            this.selectedItems.clear();
            
            // Save and update
            this.saveCartToStorage();
            this.renderCart();
            this.updateSummary();
        }
    }

    // Checkout function (non-functional as requested)
    checkout() {
        const selectedCount = this.selectedItems.size;
        if (selectedCount === 0) {
            alert('Please select at least one item to checkout.');
            return;
        }
        alert(`Checkout functionality is not implemented yet. You have ${selectedCount} selected item(s). This is a demo cart page.`);
    }
}

// Sample products data (using existing products from shop.js)
const sampleProducts = [
    { id: 1, name: "Adjustable Dumbbells", price: 299, category: "dumbbells", image: "img/Featured/1.png" },
    { id: 2, name: "Whey Protein Powder", price: 49, category: "whey-protein", image: "img/Featured/2.png" },
    { id: 3, name: "Resistance Bands Set", price: 29, category: "resistance-bands", image: "img/Featured/3.png" },
    { id: 4, name: "Olympic Barbell", price: 199, category: "barbells", image: "img/Featured/4.png" },
    { id: 5, name: "Pre-Workout Energy", price: 34, category: "pre-workout", image: "img/Featured/1.png" },
    { id: 6, name: "Gym Gloves Pro", price: 24, category: "gym-gloves", image: "img/Featured/2.png" },
    { id: 7, name: "Kettlebell 20kg", price: 89, category: "kettlebells", image: "img/Featured/3.png" },
    { id: 8, name: "Yoga Mat Premium", price: 39, category: "yoga-mats", image: "img/Featured/4.png" },
    { id: 9, name: "BCAA Powder", price: 39, category: "bcaas", image: "img/Featured/1.png" },
    { id: 10, name: "Weight Lifting Belt", price: 59, category: "weight-belts", image: "img/Featured/2.png" }
];

// Global functions for HTML onclick handlers
function applyPromoCode() {
    cart.handlePromoCode();
}

function checkout() {
    cart.checkout();
}

function removeSelectedItems() {
    cart.removeSelectedItems();
}

// Initialize cart when page loads
let cart;
document.addEventListener('DOMContentLoaded', function() {
    cart = new Cart();
    
    // Add some sample items to cart if it's empty (for demo purposes)
    if (cart.items.length === 0) {
        // Add a few sample items to demonstrate the cart functionality
        cart.addItem(sampleProducts[0], 'M', 1);
        cart.addItem(sampleProducts[1], 'L', 2);
        cart.addItem(sampleProducts[2], 'One Size', 1);
    }
});
