
// Sample products data
        const products = [
            { id: 1, name: "Adjustable Dumbbells", price: 299, category: "dumbbells", type: "bestseller", image: "adjustable dumbbells set", badge: "Best Seller" },
            { id: 2, name: "Whey Protein Powder", price: 49, category: "whey-protein", type: "bestseller", image: "whey protein container", badge: "Best Seller" },
            { id: 3, name: "Resistance Bands Set", price: 29, category: "resistance-bands", type: "newarrival", image: "resistance bands set", badge: "New" },
            { id: 4, name: "pic Barbell", price: 199, category: "barbells", type: "equipment", image: "pic barbell", badge: "" },
            { id: 5, name: "Pre-Workout Energy", price: 34, category: "pre-workout", type: "bestseller", image: "pre workout supplement", badge: "Popular" },
            { id: 6, name: "Gym Gloves Pro", price: 24, category: "gym-gloves", type: "accessories", image: "professional gym gloves", badge: "" },
            { id: 7, name: "Kettlebell 20kg", price: 89, category: "kettlebells", type: "equipment", image: "kettlebell weight", badge: "" },
            { id: 8, name: "Yoga Mat Premium", price: 39, category: "yoga-mats", type: "newarrival", image: "premium yoga mat", badge: "New" },
            { id: 9, name: "BCAA Powder", price: 39, category: "bcaas", type: "supplements", image: "bcaa supplement", badge: "" },
            { id: 10, name: "Weight Lifting Belt", price: 59, category: "weight-belts", type: "accessories", image: "leather lifting belt", badge: "" },
            { id: 11, name: "Creatine Monohydrate", price: 29, category: "creatine", type: "supplements", image: "creatine supplement", badge: "" },
            { id: 12, name: "Lifting Straps", price: 19, category: "lifting-straps", type: "accessories", image: "lifting straps", badge: "" },
            { id: 13, name: "Post-Workout Recovery", price: 44, category: "post-workout", type: "newarrival", image: "post workout supplement", badge: "New" },
            { id: 14, name: "Knee Wraps", price: 25, category: "knee-wraps", type: "accessories", image: "knee wraps", badge: "" },
            { id: 15, name: "Wrist Wraps", price: 22, category: "wrist-wraps", type: "accessories", image: "wrist wraps", badge: "" },
            { id: 16, name: "Protein Bundle Pack", price: 129, category: "bundle", type: "bundle", image: "protein bundle pack", badge: "Bundle Deal" },
            { id: 17, name: "Home Gym Starter Kit", price: 399, category: "bundle", type: "bundle", image: "home gym equipment bundle", badge: "Bundle Deal" },
            { id: 18, name: "Supplement Stack", price: 89, category: "bundle", type: "bundle", image: "supplement stack bundle", badge: "Bundle Deal" }
        ];

        let currentProducts = [...products];
        let currentPage = 1;
        const productsPerPage = 9;

        function renderProducts() {
            const grid = document.getElementById('products-grid');
            const startIndex = (currentPage - 1) * productsPerPage;
            const endIndex = startIndex + productsPerPage;
            const pageProducts = currentProducts.slice(startIndex, endIndex);

            grid.innerHTML = pageProducts.map(product => `
                <div class="product-card bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
                    <div class="relative">
                        <img src="/placeholder.svg?height=250&width=300" alt="${product.name}" class="w-full h-64 object-cover">
                        ${product.badge ? `<span class="absolute top-4 left-4 bg-emerald-500 text-white px-2 py-1 rounded text-sm font-semibold">${product.badge}</span>` : ''}
                    </div>
                    <div class="p-6">
                        <h3 class="font-semibold text-lg text-slate-800 mb-2">${product.name}</h3>
                        <p class="text-slate-600 mb-4">High-quality fitness product for your workout needs</p>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold text-emerald-600">$${product.price}</span>
                            <div class="flex space-x-2">
                                <button onclick="viewProduct('${product.id}')" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="addToCart('${product.id}')" class="bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');

            document.getElementById('product-count').textContent = currentProducts.length;
        }

        function toggleCategory(category) {
            const subcategories = document.getElementById(category + '-sub');
            subcategories.classList.toggle('expanded');
            
            const chevron = event.target.querySelector('.fa-chevron-down');
            if (chevron) {
                chevron.classList.toggle('fa-chevron-down');
                chevron.classList.toggle('fa-chevron-up');
            }
        }

        function filterProducts(filter) {
            // Remove active class from all categories
            document.querySelectorAll('.sidebar-category').forEach(cat => {
                cat.classList.remove('active');
            });
            
            // Add active class to clicked category
            event.target.classList.add('active');

            if (filter === 'all') {
                currentProducts = [...products];
            } else if (['bestseller', 'newarrival', 'bundle'].includes(filter)) {
                currentProducts = products.filter(product => product.type === filter);
            } else {
                currentProducts = products.filter(product => product.category === filter);
            }
            
            currentPage = 1;
            renderProducts();
        }

        function sortProducts(sortBy) {
            switch(sortBy) {
                case 'price-low':
                    currentProducts.sort((a, b) => a.price - b.price);
                    break;
                case 'price-high':
                    currentProducts.sort((a, b) => b.price - a.price);
                    break;
                case 'name':
                    currentProducts.sort((a, b) => a.name.localeCompare(b.name));
                    break;
                case 'newest':
                    currentProducts = currentProducts.filter(p => p.type === 'newarrival').concat(
                        currentProducts.filter(p => p.type !== 'newarrival')
                    );
                    break;
                default:
                    currentProducts = [...products];
            }
            renderProducts();
        }

        function toggleFilter() {
            const dropdown = document.getElementById('filter-dropdown');
            dropdown.classList.toggle('hidden');
        }

        function changePage(direction) {
            const totalPages = Math.ceil(currentProducts.length / productsPerPage);
            
            if (direction === 'prev' && currentPage > 1) {
                currentPage--;
            } else if (direction === 'next' && currentPage < totalPages) {
                currentPage++;
            }
            
            renderProducts();
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            renderProducts();
        });

        // Close filter dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const filterDropdown = document.getElementById('filter-dropdown');
            const filterButton = event.target.closest('button');
            
            if (!filterButton || !filterButton.onclick || filterButton.onclick.toString().indexOf('toggleFilter') === -1) {
                filterDropdown.classList.add('hidden');
            }
        });
        
        // Add global functions for product interactions
        window.viewProduct = function(productId) {
            window.location.href = `product-detail.html?id=${productId}`;
        };
        
        window.addToCart = function(productId) {
            const product = products.find(p => p.id == productId);
            if (product) {
                const cartProduct = {
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    image: product.image || '/placeholder.svg?height=250&width=300',
                    category: product.category,
                    size: 'One Size'
                };
                
                CartUtils.addToCart(cartProduct, 1);
                showNotification(`${product.name} added to cart!`, 'success');
            }
        };
        
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
            
            if (type === 'success') {
                notification.classList.add('bg-emerald-600', 'text-white');
            } else if (type === 'error') {
                notification.classList.add('bg-red-500', 'text-white');
            } else {
                notification.classList.add('bg-emerald-600', 'text-white');
            }
            
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
