// Shared header component for FitFuel website
class HeaderComponent {
    constructor() {
        this.config = window.CONFIG || {};
        this.init();
    }

    init() {
        this.createHeader();
        this.attachEventListeners();
        this.updateCartCount();
    }

    createHeader() {
        const headerHTML = `
            <!-- First Navigation Bar -->
            <nav class="bg-white text-black py-2">
                <div class="container mx-auto px-4">
                    <div class="flex justify-end space-x-6 text-sm">
                        ${this.config.navigation?.topNav?.map(item => 
                            `<a href="${item.href}" class="hover:text-emerald-400 transition-colors">${item.text}</a>`
                        ).join('') || ''}
                    </div>
                </div>
            </nav>

            <!-- Second Navigation Bar -->
            <nav class="sticky-nav bg-black border-b border-white py-4">
                <div class="container mx-auto px-4">
                    <div class="flex items-center justify-between">
                        <!-- Logo -->
                        <div class="flex items-center">
                            <a href="index.html">
                                <img src="${this.config.site?.logo || 'img/LOGO-Fitfuel.png'}" 
                                     width="75" height="auto" alt="${this.config.site?.name || 'FitFuel'} Logo" />
                            </a>
                        </div>

                        <!-- Main Navigation -->
                        <div class="hidden md:flex space-x-8">
                            ${this.config.navigation?.mainNav?.map(item => `
                                <div class="dropdown">
                                    <a href="${item.href}" 
                                       class="font-medium text-white hover:text-emerald-600 transition-colors">
                                        ${item.text}
                                    </a>
                                    <div class="dropdown-menu">
                                        ${item.submenu?.map(subItem => 
                                            `<a href="${subItem.href}">${subItem.text}</a>`
                                        ).join('') || ''}
                                    </div>
                                </div>
                            `).join('') || ''}
                        </div>

                        <!-- Search and Icons -->
                        <div class="flex items-center space-x-4">
                            <!-- Search Bar -->
                            <div class="relative hidden md:block">
                                <input type="text" 
                                       placeholder="Search products..." 
                                       class="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                       id="search-input" />
                                <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                            </div>
                            
                            <!-- Icons -->
                            <button class="relative p-2 text-white hover:text-emerald-600 transition-colors" 
                                    title="Notifications">
                                <i class="fas fa-bell text-xl"></i>
                            </button>
                            
                            <a href="cart.html" 
                               class="relative p-2 text-white hover:text-emerald-600 transition-colors"
                               title="Shopping Cart">
                                <i class="fas fa-shopping-cart text-xl"></i>
                                <span id="cart-count" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
                            </a>
                            
                            <button class="p-2 text-white hover:text-emerald-600 transition-colors" 
                                    title="User Account">
                                <i class="fas fa-user text-xl"></i>
                            </button>

                            <!-- Mobile Menu Button -->
                            <button class="md:hidden p-2 text-white hover:text-emerald-600 transition-colors" 
                                    id="mobile-menu-btn"
                                    title="Menu">
                                <i class="fas fa-bars text-xl"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <div id="mobile-menu" class="hidden md:hidden bg-black border-t border-gray-700">
                    <div class="container mx-auto px-4 py-4">
                        <div class="space-y-4">
                            ${this.config.navigation?.mainNav?.map(item => `
                                <div class="mobile-nav-item">
                                    <a href="${item.href}" 
                                       class="block text-white hover:text-emerald-600 transition-colors font-medium">
                                        ${item.text}
                                    </a>
                                    <div class="ml-4 mt-2 space-y-2">
                                        ${item.submenu?.map(subItem => 
                                            `<a href="${subItem.href}" 
                                               class="block text-gray-300 hover:text-emerald-400 transition-colors text-sm">
                                                ${subItem.text}
                                            </a>`
                                        ).join('') || ''}
                                    </div>
                                </div>
                            `).join('') || ''}
                            
                            <!-- Mobile Search -->
                            <div class="pt-4 border-t border-gray-700">
                                <input type="text" 
                                       placeholder="Search products..." 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                       id="mobile-search-input" />
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        `;

        // Insert header at the beginning of body
        document.body.insertAdjacentHTML('afterbegin', headerHTML);
    }

    attachEventListeners() {
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
                const icon = mobileMenuBtn.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-bars');
                    icon.classList.toggle('fa-times');
                }
            });
        }

        // Search functionality
        const searchInputs = document.querySelectorAll('#search-input, #mobile-search-input');
        searchInputs.forEach(input => {
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.handleSearch(e.target.value);
                }
            });
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (mobileMenu && !mobileMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                mobileMenu.classList.add('hidden');
                const icon = mobileMenuBtn.querySelector('i');
                if (icon) {
                    icon.classList.add('fa-bars');
                    icon.classList.remove('fa-times');
                }
            }
        });

        // Update cart count when storage changes
        window.addEventListener('storage', (e) => {
            if (e.key === this.config.cart?.storageKey || e.key === 'fitfuel_cart') {
                this.updateCartCount();
            }
        });
    }

    handleSearch(query) {
        if (query.trim()) {
            // Redirect to shop page with search parameter
            window.location.href = `shop.html?search=${encodeURIComponent(query.trim())}`;
        }
    }

    updateCartCount() {
        // Wait for CartUtils to be available
        if (typeof CartUtils !== 'undefined') {
            try {
                const cartCount = CartUtils.getCartCount();
                const cartCountElement = document.getElementById('cart-count');
                
                if (cartCountElement) {
                    cartCountElement.textContent = cartCount;
                    cartCountElement.style.display = cartCount > 0 ? 'flex' : 'none';
                }
            } catch (error) {
                console.warn('Error updating cart count:', error);
            }
        } else {
            // Retry after a short delay if CartUtils is not yet loaded
            setTimeout(() => this.updateCartCount(), 100);
        }
    }

    // Method to update cart count from external calls
    static updateCartCount() {
        const header = new HeaderComponent();
        header.updateCartCount();
    }
}

// Auto-initialize when DOM is loaded (only once)
document.addEventListener('DOMContentLoaded', () => {
    if (!document.querySelector('.sticky-nav')) {
        new HeaderComponent();
    }
});

// Export for external use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HeaderComponent;
} else {
    window.HeaderComponent = HeaderComponent;
}
