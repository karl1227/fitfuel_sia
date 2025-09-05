
        let currentSlideIndex = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const indicators = document.querySelectorAll('.carousel-indicator');
        
        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });
            
            indicators.forEach((indicator, i) => {
                if (i === index) {
                    indicator.classList.add('bg-emerald-600');
                    indicator.classList.remove('bg-white', 'bg-opacity-50');
                } else {
                    indicator.classList.remove('bg-emerald-600');
                    indicator.classList.add('bg-white', 'bg-opacity-50');
                }
            });
        }
        
        function nextSlide() {
            currentSlideIndex = (currentSlideIndex + 1) % slides.length;
            showSlide(currentSlideIndex);
        }
        
        function previousSlide() {
            currentSlideIndex = (currentSlideIndex - 1 + slides.length) % slides.length;
            showSlide(currentSlideIndex);
        }
        
        function currentSlide(index) {
            currentSlideIndex = index - 1;
            showSlide(currentSlideIndex);
        }
        
        // Auto-advance carousel
        setInterval(nextSlide, 5000);

        // Use real product data
        const popularProducts = [
            // Page 1
            [
                { id: "kettlebell-001", name: "Kettlebell", price: "P 1,200", image: "img/Featured/1.png", badge: "Best Seller" },
                { id: "dumbbell-001", name: "Adjustable Dumbbells", price: "P 1,950", image: "img/Featured/2.png", badge: "Best Seller" },
                { id: "barbell-pads-001", name: "Barbell Pads", price: "P 1,100", image: "img/Featured/3.png", badge: "" },
                { id: "weightlifting-belt-001", name: "Weightlifting Belt", price: "P 1,200", image: "img/Featured/4.png", badge: "Popular" }
            ],
            // Page 2
            [
                { id: "whey-protein-001", name: "Whey Protein Powder", price: "P 49", image: "img/Featured/1.png", badge: "Best Seller" },
                { id: "kettlebell-001", name: "Kettlebell", price: "P 1,200", image: "img/Featured/2.png", badge: "New" },
                { id: "dumbbell-001", name: "Adjustable Dumbbells", price: "P 1,950", image: "img/Featured/3.png", badge: "" },
                { id: "barbell-pads-001", name: "Barbell Pads", price: "P 1,100", image: "img/Featured/4.png", badge: "Popular" }
            ],
            // Page 3
            [
                { id: "weightlifting-belt-001", name: "Weightlifting Belt", price: "P 1,200", image: "img/Featured/1.png", badge: "Best Seller" },
                { id: "whey-protein-001", name: "Whey Protein Powder", price: "P 49", image: "img/Featured/2.png", badge: "" },
                { id: "kettlebell-001", name: "Kettlebell", price: "P 1,200", image: "img/Featured/3.png", badge: "" },
                { id: "dumbbell-001", name: "Adjustable Dumbbells", price: "P 1,950", image: "img/Featured/4.png", badge: "Popular" }
            ]
        ];

        let currentPage = 1;
        const totalPages = popularProducts.length;

        function renderPopularProducts(page) {
            const container = document.getElementById('popular-products');
            const products = popularProducts[page - 1];
            
            container.innerHTML = products.map(product => `
                <div class="product-card bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
                    <div class="relative">
                        <img src="${product.image}" alt="${product.name}" class="w-full h-64 object-cover">
                        ${product.badge ? `<span class="absolute top-4 left-4 bg-emerald-500 text-white px-2 py-1 rounded text-sm font-semibold">${product.badge}</span>` : ''}
                    </div>
                    <div class="p-6">
                        <h3 class="font-semibold text-lg text-slate-800 mb-2">${product.name}</h3>
                        <p class="text-slate-600 mb-4">High-quality fitness product for your workout needs</p>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold text-emerald-600">${product.price}</span>
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
        }

        function updatePagination() {
            document.querySelectorAll('.page-btn').forEach(btn => {
                btn.classList.toggle('active', parseInt(btn.dataset.page) === currentPage);
            });
            
            document.getElementById('prev-btn').disabled = currentPage === 1;
            document.getElementById('next-btn').disabled = currentPage === totalPages;
        }

        function goToPage(page) {
            currentPage = page;
            renderPopularProducts(currentPage);
            updatePagination();
        }

        function changePage(direction) {
            if (direction === 'prev' && currentPage > 1) {
                currentPage--;
            } else if (direction === 'next' && currentPage < totalPages) {
                currentPage++;
            }
            renderPopularProducts(currentPage);
            updatePagination();
        }

        // Initialize popular products
        document.addEventListener('DOMContentLoaded', function() {
            renderPopularProducts(1);
            updatePagination();
        });
        
        // Mobile menu toggle (if needed)
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        }
        
        // Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[placeholder="Search products..."]');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        // Implement search functionality
                        console.log('Searching for:', this.value);
                    }
                });
            }
        });
        
        // Add global functions for product interactions
        window.viewProduct = function(productId) {
            window.location.href = `product-detail.html?id=${productId}`;
        };
        
        window.addToCart = function(productId) {
            const product = getProductById(productId);
            if (product) {
                const cartProduct = {
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    image: product.image,
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
