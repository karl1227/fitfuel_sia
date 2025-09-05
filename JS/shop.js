
        // Sample products data
        const products = [
            { id: 1, name: "Adjustable Dumbbells", price: 299, category: "dumbbells", type: "bestseller", image: "/placeholder.svg?height=250&width=300", badge: "Best Seller", stock: 10 },
            { id: 2, name: "Whey Protein Powder", price: 49, category: "whey-protein", type: "bestseller", image: "/placeholder.svg?height=250&width=300", badge: "Best Seller", stock: 15 },
            { id: 3, name: "Resistance Bands Set", price: 29, category: "resistance-bands", type: "newarrival", image: "/placeholder.svg?height=250&width=300", badge: "New", stock: 20 },
            { id: 4, name: "Olympic Barbell", price: 199, category: "barbells", type: "equipment", image: "/placeholder.svg?height=250&width=300", badge: "", stock: 8 },
            { id: 5, name: "Pre-Workout Energy", price: 34, category: "pre-workout", type: "bestseller", image: "/placeholder.svg?height=250&width=300", badge: "Popular", stock: 12 },
            { id: 6, name: "Gym Gloves Pro", price: 24, category: "gym-gloves", type: "accessories", image: "/placeholder.svg?height=250&width=300", badge: "", stock: 25 },
            { id: 7, name: "Kettlebell 20kg", price: 89, category: "kettlebells", type: "equipment", image: "/placeholder.svg?height=250&width=300", badge: "", stock: 6 },
            { id: 8, name: "Yoga Mat Premium", price: 39, category: "yoga-mats", type: "newarrival", image: "/placeholder.svg?height=250&width=300", badge: "New", stock: 18 },
            { id: 9, name: "BCAA Powder", price: 39, category: "bcaas", type: "supplements", image: "/placeholder.svg?height=250&width=300", badge: "", stock: 14 },
            { id: 10, name: "Weight Lifting Belt", price: 59, category: "weight-belts", type: "accessories", image: "/placeholder.svg?height=250&width=300", badge: "", stock: 9 },
            { id: 11, name: "Creatine Monohydrate", price: 29, category: "creatine", type: "supplements", image: "/placeholder.svg?height=250&width=300", badge: "", stock: 16 },
            { id: 12, name: "Lifting Straps", price: 19, category: "lifting-straps", type: "accessories", image: "/placeholder.svg?height=250&width=300", badge: "", stock: 22 },
            { id: 13, name: "Post-Workout Recovery", price: 44, category: "post-workout", type: "newarrival", image: "/placeholder.svg?height=250&width=300", badge: "New", stock: 11 },
            { id: 14, name: "Knee Wraps", price: 25, category: "knee-wraps", type: "accessories", image: "/placeholder.svg?height=250&width=300", badge: "", stock: 13 },
            { id: 15, name: "Wrist Wraps", price: 22, category: "wrist-wraps", type: "accessories", image: "/placeholder.svg?height=250&width=300", badge: "", stock: 17 },
            { id: 16, name: "Protein Bundle Pack", price: 129, category: "bundle", type: "bundle", image: "/placeholder.svg?height=250&width=300", badge: "Bundle Deal", stock: 5 },
            { id: 17, name: "Home Gym Starter Kit", price: 399, category: "bundle", type: "bundle", image: "/placeholder.svg?height=250&width=300", badge: "Bundle Deal", stock: 3 },
            { id: 18, name: "Supplement Stack", price: 89, category: "bundle", type: "bundle", image: "/placeholder.svg?height=250&width=300", badge: "Bundle Deal", stock: 7 }
        ];

        let currentProducts = [...products];
        let currentPage = 1;
        const productsPerPage = 9;

        function renderProducts() {
            const grid = document.getElementById('products-grid');
            const startIndex = (currentPage - 1) * productsPerPage;
            const endIndex = startIndex + productsPerPage;
            const pageProducts = currentProducts.slice(startIndex, endIndex);

            // Show skeletons briefly
            grid.innerHTML = Array.from({ length: 9 }).map(() => `
                <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200 p-0">
                    <div class="skeleton image w-full"></div>
                    <div class="p-6">
                        <div class="skeleton text-lg w-3/4"></div>
                        <div class="skeleton text w-full"></div>
                        <div class="skeleton text w-5/6"></div>
                    </div>
                </div>
            `).join('');

            setTimeout(() => {
                grid.innerHTML = pageProducts.map(product => `
                    <div class="product-card bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
                        <div class="relative">
                            <img src="/placeholder.svg?height=250&width=300" alt="${product.name}" class="w-full h-64 object-cover gallery-trigger" data-id="${product.id}">
                            ${product.badge ? `<span class="absolute top-4 left-4 bg-emerald-500 text-white px-2 py-1 rounded text-sm font-semibold">${product.badge}</span>` : ''}
                        </div>
                        <div class="p-6">
                            <h3 class="font-semibold text-lg text-slate-800 mb-2">${product.name}</h3>
                            <p class="text-slate-600 mb-4">High-quality fitness product for your workout needs</p>
                            <div class="flex items-center justify-between">
                                <span class="text-2xl font-bold text-emerald-600">$${product.price}</span>
                                <button onclick="addToCart(${product.id})" class="bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');

                // Attach gallery triggers
                document.querySelectorAll('.gallery-trigger').forEach(img => {
                    img.addEventListener('click', () => openGallery(parseInt(img.dataset.id, 10)));
                });
            }, 350);

            document.getElementById('product-count').textContent = currentProducts.length;
        }

        // Product Image Gallery
        const productImagesMap = new Map();
        products.forEach(p => {
            productImagesMap.set(p.id, [
                '/placeholder.svg?height=800&width=1000',
                '/placeholder.svg?height=800&width=1000&text=Alt+1',
                '/placeholder.svg?height=800&width=1000&text=Alt+2'
            ]);
        });

        function openGallery(productId) {
            const overlay = document.getElementById('gallery-overlay');
            const imageEl = document.getElementById('gallery-image');
            const thumbs = document.getElementById('gallery-thumbs');
            const images = productImagesMap.get(productId) || [];
            let currentIndex = 0;

            function renderThumbs() {
                thumbs.innerHTML = images.map((src, idx) => `
                    <img src="${src}" data-index="${idx}" class="${idx === currentIndex ? 'active' : ''}" alt="Thumbnail ${idx+1}">
                `).join('');
                thumbs.querySelectorAll('img').forEach(t => t.addEventListener('click', () => {
                    currentIndex = parseInt(t.dataset.index, 10);
                    updateImage();
                }));
            }

            function updateImage() {
                imageEl.src = images[currentIndex];
                thumbs.querySelectorAll('img').forEach((t, idx) => t.classList.toggle('active', idx === currentIndex));
            }

            document.getElementById('gallery-prev').onclick = () => {
                currentIndex = (currentIndex - 1 + images.length) % images.length;
                updateImage();
            };
            document.getElementById('gallery-next').onclick = () => {
                currentIndex = (currentIndex + 1) % images.length;
                updateImage();
            };
            document.getElementById('gallery-close').onclick = () => overlay.classList.remove('active');
            overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.classList.remove('active'); });

            renderThumbs();
            updateImage();
            overlay.classList.add('active');
        }

        function toggleCategory(category) {
            const subcategories = document.getElementById(category + '-sub');
            const categoryElement = event.target.closest('.filter-category');
            
            subcategories.classList.toggle('expanded');
            categoryElement.classList.toggle('expanded');
        }

        function filterProducts(filter) {
            // Handle checkbox filtering
            const checkboxes = document.querySelectorAll('.filter-checkbox');
            const activeFilters = [];
            
            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    activeFilters.push(checkbox.dataset.filter);
                }
            });

            // Apply filters
            if (activeFilters.length === 0) {
                currentProducts = [...products];
            } else {
                currentProducts = products.filter(product => {
                    return activeFilters.some(filter => {
                        // Handle different filter types
                        if (filter === 'sale' && product.badge) return true;
                        if (filter === 'men' && product.gender === 'men') return true;
                        if (filter === 'women' && product.gender === 'women') return true;
                        if (filter === 'unisex' && product.gender === 'unisex') return true;
                        if (filter === product.category) return true;
                        if (filter === product.brand) return true;
                        
                        // Price range filters
                        if (filter === 'price-under-25' && product.price < 25) return true;
                        if (filter === 'price-25-50' && product.price >= 25 && product.price <= 50) return true;
                        if (filter === 'price-50-100' && product.price >= 50 && product.price <= 100) return true;
                        if (filter === 'price-over-100' && product.price > 100) return true;
                        
                        return false;
                    });
                });
            }
            
            currentPage = 1;
            renderProducts();
        }

        // Add event listeners for checkboxes
        function initFilterListeners() {
            const checkboxes = document.querySelectorAll('.filter-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', filterProducts);
            });
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

        // Add to cart function
        function addToCart(productId) {
            const product = products.find(p => p.id === productId);
            if (product && window.cartUtils) {
                window.cartUtils.addToCart(product);
            }
        }

        // Scroll Trigger Functionality
        function initScrollTriggers() {
            const sidebar = document.querySelector('.fixed-sidebar');
            let lastScrollY = window.scrollY;
            let ticking = false;

            function updateSidebar() {
                const scrollY = window.scrollY;
                
                // Subtle effects for sticky sidebar
                if (scrollY > lastScrollY && scrollY > 100) {
                    sidebar.classList.add('scroll-hidden');
                } else {
                    sidebar.classList.remove('scroll-hidden');
                }
                
                // Add scroll indicator based on scroll position
                if (scrollY > 50) {
                    sidebar.classList.add('scroll-visible');
                } else {
                    sidebar.classList.remove('scroll-visible');
                }
                
                lastScrollY = scrollY;
                ticking = false;
            }

            function requestTick() {
                if (!ticking) {
                    requestAnimationFrame(updateSidebar);
                    ticking = true;
                }
            }

            // Throttled scroll event
            window.addEventListener('scroll', requestTick, { passive: true });

            // Sidebar hover effects
            sidebar.addEventListener('mouseenter', function() {
                this.classList.remove('scroll-hidden');
                this.classList.add('scroll-visible');
            });

            sidebar.addEventListener('mouseleave', function() {
                if (window.scrollY > 100) {
                    this.classList.add('scroll-hidden');
                }
            });
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            renderProducts();
            initScrollTriggers();
            initFilterListeners();
        });

        // Close filter dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const filterDropdown = document.getElementById('filter-dropdown');
            const filterButton = event.target.closest('button');
            
            if (!filterButton || !filterButton.onclick || filterButton.onclick.toString().indexOf('toggleFilter') === -1) {
                filterDropdown.classList.add('hidden');
            }
        });
