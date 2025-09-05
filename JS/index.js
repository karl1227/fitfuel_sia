
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

        const popularProducts = [
            // Page 1
            [
                { name: "Olympic Barbell Set", price: "$199", image: "olympic barbell with plates", badge: "Best Seller" },
                { name: "Pre-Workout Energy", price: "$34", image: "pre workout supplement container", badge: "Popular" },
                { name: "Gym Gloves Pro", price: "$24", image: "professional gym gloves", badge: "" },
                { name: "Kettlebell 20kg", price: "$89", image: "black kettlebell weight", badge: "New" }
            ],
            // Page 2
            [
                { name: "Protein Shaker", price: "$15", image: "protein shaker bottle", badge: "" },
                { name: "Foam Roller", price: "$45", image: "foam roller for recovery", badge: "Popular" },
                { name: "Weight Lifting Belt", price: "$59", image: "leather weight lifting belt", badge: "" },
                { name: "BCAA Powder", price: "$39", image: "bcaa supplement powder", badge: "Sale" }
            ],
            // Page 3
            [
                { name: "Pull-up Bar", price: "$79", image: "doorway pull up bar", badge: "Best Seller" },
                { name: "Creatine Monohydrate", price: "$29", image: "creatine supplement container", badge: "" },
                { name: "Gym Towel Set", price: "$19", image: "microfiber gym towels", badge: "" },
                { name: "Ab Wheel Roller", price: "$25", image: "ab wheel exercise equipment", badge: "Popular" }
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
                        <img src="/placeholder.svg?height=250&width=300" alt="${product.name}" class="w-full h-64 object-cover">
                        ${product.badge ? `<span class="absolute top-4 left-4 bg-emerald-500 text-white px-2 py-1 rounded text-sm font-semibold">${product.badge}</span>` : ''}
                    </div>
                    <div class="p-6">
                        <h3 class="font-semibold text-lg text-slate-800 mb-2">${product.name}</h3>
                        <p class="text-slate-600 mb-4">High-quality fitness product for your workout needs</p>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold text-emerald-600">${product.price}</span>
                            <button class="bg-black text-white px-4 py-2 rounded-lg hover:bg-black-200 transition-colors">
                                <i class="fas fa-cart-plus"></i>
                            </button>
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
