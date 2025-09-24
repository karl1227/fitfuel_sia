
        let currentSlideIndex = 0;
        let slides = [];
        let indicators = [];
        let carouselInterval;
        
        function showSlide(index) {
            slides.forEach((slide, i) => {
                const isActive = i === index;
                if (isActive) {
                    slide.classList.add('active');
                } else {
                    slide.classList.remove('active');
                }
            });

            indicators.forEach((indicator, i) => {
                if (i === index) {
                    indicator.classList.add('bg-emerald-600');
                    indicator.classList.remove('bg-white', 'bg-opacity-50');
                    indicator.setAttribute('aria-current', 'true');
                } else {
                    indicator.classList.remove('bg-emerald-600');
                    indicator.classList.add('bg-white', 'bg-opacity-50');
                    indicator.removeAttribute('aria-current');
                }
            });
        }
        
        function nextSlide() {
            if (slides.length < 2) return;
            currentSlideIndex = (currentSlideIndex + 1) % slides.length;
            showSlide(currentSlideIndex);
            resetCarouselInterval();
        }
        
        function previousSlide() {
            if (slides.length < 2) return;
            currentSlideIndex = (currentSlideIndex - 1 + slides.length) % slides.length;
            showSlide(currentSlideIndex);
            resetCarouselInterval();
        }
        
        function currentSlide(index) {
            if (slides.length === 0) return;
            currentSlideIndex = index - 1;
            showSlide(currentSlideIndex);
            resetCarouselInterval();
        }
        
        function resetCarouselInterval() {
            clearInterval(carouselInterval);
            if (slides.length > 1) {
                carouselInterval = setInterval(nextSlide, 5000);
            }
        }

        // Initialize hero carousel
        document.addEventListener('DOMContentLoaded', function() {
            // Capture nodes after DOM is ready
            slides = document.querySelectorAll('.carousel-slide');
            indicators = document.querySelectorAll('.carousel-indicator');
            if (slides.length > 0) {
                showSlide(0);
            }
            resetCarouselInterval();
            const carouselContainer = slides.length > 0 ? slides[0].closest('section') : null;
            if (carouselContainer && slides.length > 1) {
                carouselContainer.addEventListener('mouseenter', () => clearInterval(carouselInterval));
                carouselContainer.addEventListener('mouseleave', resetCarouselInterval);
            }

            // Robust event binding (IDs exist in markup)
            const prevBtn = document.getElementById('hero-prev');
            const nextBtn = document.getElementById('hero-next');
            const indicatorsWrap = document.getElementById('hero-indicators');
            if (prevBtn) prevBtn.addEventListener('click', previousSlide);
            if (nextBtn) nextBtn.addEventListener('click', nextSlide);
            if (indicatorsWrap) {
                indicatorsWrap.addEventListener('click', function(e) {
                    const btn = e.target.closest('.carousel-indicator');
                    if (!btn) return;
                    const idx = parseInt(btn.getAttribute('data-index'), 10);
                    if (!isNaN(idx)) {
                        currentSlideIndex = idx;
                        showSlide(currentSlideIndex);
                        resetCarouselInterval();
                    }
                });
            }

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (slides.length < 2) return;
                if (e.key === 'ArrowRight') nextSlide();
                if (e.key === 'ArrowLeft') previousSlide();
            });

            // Touch swipe support
            let touchStartX = 0;
            let touchEndX = 0;
            if (carouselContainer) {
                carouselContainer.addEventListener('touchstart', function(e) {
                    touchStartX = e.changedTouches[0].screenX;
                }, { passive: true });
                carouselContainer.addEventListener('touchend', function(e) {
                    touchEndX = e.changedTouches[0].screenX;
                    const diff = touchEndX - touchStartX;
                    if (Math.abs(diff) > 40) {
                        if (diff < 0) { nextSlide(); } else { previousSlide(); }
                    }
                }, { passive: true });
            }
        });

        // Expose controls for inline onclick handlers
        window.nextSlide = nextSlide;
        window.previousSlide = previousSlide;
        window.currentSlide = currentSlide;

        /* ---------------- BANNER CAROUSEL ---------------- */
        let bannerIndex = 0;
        const bannerSlides = document.querySelectorAll('.banner-slide');
        const bannerIndicators = document.querySelectorAll('.banner-indicator');

        function showBannerSlide(n) {
            bannerSlides.forEach((slide, i) => {
                slide.classList.remove('active');
                slide.style.display = 'none';
                if (bannerIndicators[i]) {
                    bannerIndicators[i].classList.remove('bg-emerald-600');
                    bannerIndicators[i].classList.add('bg-white', 'bg-opacity-50');
                }
            });
            if (bannerSlides[n]) {
                bannerSlides[n].classList.add('active');
                bannerSlides[n].style.display = 'block';
            }
            if (bannerIndicators[n]) {
                bannerIndicators[n].classList.add('bg-emerald-600');
                bannerIndicators[n].classList.remove('bg-white', 'bg-opacity-50');
            }
            bannerIndex = n;
        }

        function nextBannerSlide() {
            bannerIndex = (bannerIndex + 1) % bannerSlides.length;
            showBannerSlide(bannerIndex);
        }

        function prevBannerSlide() {
            bannerIndex = (bannerIndex - 1 + bannerSlides.length) % bannerSlides.length;
            showBannerSlide(bannerIndex);
        }

        function currentBannerSlide(n) {
            showBannerSlide(n);
        }

        // Initialize banner carousel
        document.addEventListener('DOMContentLoaded', function() {
            showBannerSlide(0);
            setInterval(nextBannerSlide, 5000);
        });

        const popularProducts = (window.popularProductsData && Array.isArray(window.popularProductsData) && window.popularProductsData.length)
            ? window.popularProductsData
            : [
                [
                    { name: "Barbell Set", price: 199, imagePath: "img/Featured/4.png", alt: "Olympic barbell with plates", badge: "Best Seller" },
                    { name: "Pre-Workout", price: 34, imagePath: "img/carousel/C3.png", alt: "Pre workout supplement", badge: "Popular" },
                    { name: "Gym Gloves Pro", price: 24, imagePath: "img/Featured/1.png", alt: "Professional gym gloves", badge: "" },
                    { name: "Kettlebell 20kg", price: 89, imagePath: "img/Featured/2.png", alt: "Kettlebell", badge: "New" }
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
                        <img src="${product.imagePath}" alt="${product.alt || product.name}" class="w-full h-64 object-cover">
                        ${product.badge ? `<span class="absolute top-4 left-4 bg-emerald-500 text-white px-2 py-1 rounded text-sm font-semibold">${product.badge}</span>` : ''}
                    </div>
                    <div class="p-6">
                        <h3 class="font-semibold text-lg text-slate-800 mb-2">${product.name}</h3>
                        <p class="text-slate-600 mb-4">High-quality fitness product for your workout needs</p>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold text-emerald-600">₱${typeof product.price === 'number' ? product.price.toFixed(2) : product.price}</span>
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
