
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

        // Use dynamic data from PHP if available, otherwise fallback to static data
        const bestSellingProducts = window.bestSellingProductsData || [
            // Page 1 - Fallback data
            [
                { product_id: 1, name: "Olympic Barbell Set", price: "₱199", imagePath: "img/placeholder.svg", badge: "Best Seller" },
                { product_id: 2, name: "Pre-Workout Energy", price: "₱34", imagePath: "img/placeholder.svg", badge: "Popular" },
                { product_id: 3, name: "Gym Gloves Pro", price: "₱24", imagePath: "img/placeholder.svg", badge: "" },
                { product_id: 4, name: "Kettlebell 20kg", price: "₱89", imagePath: "img/placeholder.svg", badge: "New" }
            ],
            // Page 2 - Fallback data
            [
                { product_id: 5, name: "Protein Shaker", price: "₱15", imagePath: "img/placeholder.svg", badge: "" },
                { product_id: 6, name: "Foam Roller", price: "₱45", imagePath: "img/placeholder.svg", badge: "Popular" },
                { product_id: 7, name: "Weight Lifting Belt", price: "₱59", imagePath: "img/placeholder.svg", badge: "" },
                { product_id: 8, name: "BCAA Powder", price: "₱39", imagePath: "img/placeholder.svg", badge: "Sale" }
            ],
            // Page 3 - Fallback data
            [
                { product_id: 9, name: "Pull-up Bar", price: "₱79", imagePath: "img/placeholder.svg", badge: "Best Seller" },
                { product_id: 10, name: "Creatine Monohydrate", price: "₱29", imagePath: "img/placeholder.svg", badge: "" },
                { product_id: 11, name: "Gym Towel Set", price: "₱19", imagePath: "img/placeholder.svg", badge: "" },
                { product_id: 12, name: "Ab Wheel Roller", price: "₱25", imagePath: "img/placeholder.svg", badge: "Popular" }
            ]
        ];

        let currentPage = 1;
        const totalPages = bestSellingProducts.length;

        function renderBestSellingProducts(page) {
            const container = document.getElementById('best-selling-products');
            const products = bestSellingProducts[page - 1];
            
            if (!container) {
                console.error('Container element not found!');
                return;
            }
            
            if (!products || products.length === 0) {
                console.error('No products found for page:', page);
                return;
            }
            
            container.innerHTML = products.map(product => `
                <a href="shop.php" class="product-card bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200 hover:shadow-xl transition-shadow block">
                    <div class="relative">
                        <img src="${product.imagePath || 'img/placeholder.svg'}" alt="${product.name}" class="w-full h-64 object-cover">
                        ${product.badge ? `<span class="absolute top-4 left-4 bg-emerald-500 text-white px-2 py-1 rounded text-sm font-semibold">${product.badge}</span>` : ''}
                    </div>
                    <div class="p-6">
                        <h3 class="font-semibold text-lg text-slate-800 mb-2">${product.name}</h3>
                        <p class="text-slate-600 mb-4">High-quality fitness product for your workout needs</p>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold text-emerald-600">₱${product.price}</span>
                            <button onclick="event.preventDefault(); addToCart(${product.product_id});" class="bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </a>
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
            renderBestSellingProducts(currentPage);
            updatePagination();
        }

        function changePage(direction) {
            if (direction === 'prev' && currentPage > 1) {
                currentPage--;
            } else if (direction === 'next' && currentPage < totalPages) {
                currentPage++;
            }
            renderBestSellingProducts(currentPage);
            updatePagination();
        }

        // Initialize best selling products
        document.addEventListener('DOMContentLoaded', function() {
            renderBestSellingProducts(1);
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


// ===== PSGC address loader =====
const PSGC = "https://psgc.gitlab.io/api";

function fillSelect(id, items, placeholder) {
  const el = document.getElementById(id);
  if (!el) return;
  el.innerHTML = `<option value="">${placeholder}</option>`;
  (items || []).forEach(it => {
    const opt = document.createElement("option");
    opt.value = it.code;
    opt.textContent = it.name;
    el.appendChild(opt);
  });
  el.disabled = false;
}

document.addEventListener("DOMContentLoaded", async () => {
  const regionSel  = document.getElementById("region");
  const provSel    = document.getElementById("province");
  const citySel    = document.getElementById("city");
  const brgySel    = document.getElementById("barangay");
  if (!regionSel || !provSel || !citySel || !brgySel) {
    console.error("[PSGC] Missing selects (region/province/city/barangay).");
    return;
  }

  // initial disabled
  provSel.disabled = citySel.disabled = brgySel.disabled = true;

  try {
    const r = await fetch(`${PSGC}/regions/`);
    if (!r.ok) throw new Error("HTTP " + r.status);
    const regions = await r.json();
    fillSelect("region", regions, "Select Region");
  } catch (e) {
    console.error("[PSGC] Regions error:", e);
  }

  regionSel.addEventListener("change", async (e) => {
    const code = e.target.value;
    provSel.disabled = citySel.disabled = brgySel.disabled = true;
    provSel.innerHTML = `<option value="">Loading…</option>`;
    citySel.innerHTML = `<option value="">Select City/Municipality</option>`;
    brgySel.innerHTML = `<option value="">Select Barangay</option>`;
    if (!code) return;

    const res = await fetch(`${PSGC}/regions/${code}/provinces/`);
    const provs = await res.json();
    fillSelect("province", provs, "Select Province");
    citySel.disabled = brgySel.disabled = true;
  });

  provSel.addEventListener("change", async (e) => {
    const provCode = e.target.value;
    citySel.disabled = brgySel.disabled = true;
    citySel.innerHTML = `<option value="">Loading…</option>`;
    brgySel.innerHTML = `<option value="">Select Barangay</option>`;
    if (!provCode) return;

    const [cities, munis] = await Promise.all([
      fetch(`${PSGC}/provinces/${provCode}/cities/`).then(r => r.ok ? r.json() : [] ).catch(() => []),
      fetch(`${PSGC}/provinces/${provCode}/municipalities/`).then(r => r.ok ? r.json() : [] ).catch(() => []),
    ]);
    fillSelect("city", [...cities, ...munis], "Select City/Municipality");
  });

  citySel.addEventListener("change", async (e) => {
    const code = e.target.value;
    brgySel.disabled = true;
    brgySel.innerHTML = `<option value="">Loading…</option>`;
    if (!code) return;

    let brgys = [];
    try { brgys = await fetch(`${PSGC}/cities/${code}/barangays/`).then(r => r.json()); }
    catch { brgys = await fetch(`${PSGC}/municipalities/${code}/barangays/`).then(r => r.json()); }
    fillSelect("barangay", brgys, "Select Barangay");
  });
});

// --- Profile dropdown toggle ---
(function () {
    const btn = document.getElementById('profileBtn');
    const menu = document.getElementById('profileDropdown');
    if (!btn || !menu) return;
  
    const close = () => {
      menu.classList.add('hidden');
      btn.setAttribute('aria-expanded', 'false');
    };
    const open = () => {
      menu.classList.remove('hidden');
      btn.setAttribute('aria-expanded', 'true');
    };
  
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      if (menu.classList.contains('hidden')) open(); else close();
    });
  
    // Close on outside click
    document.addEventListener('click', (e) => {
      const container = document.getElementById('profileMenu');
      if (!container) return;
      if (!container.contains(e.target)) close();
    });
  
    // Close on Escape
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') close();
    });
  })();

  // Add to cart functionality
  function addToCart(productId) {
    fetch('add_to_cart.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ product_id: productId, quantity: 1 })
    })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        showNotification('Product added to cart!');
        updateCartCount();
      } else {
        if (d.message && d.message.includes('login')) {
          window.location.href = 'login.php';
        } else {
          showNotification('Error: ' + (d.message || 'Could not add to cart'), 'error');
        }
      }
    })
    .catch(() => showNotification('Network error adding to cart', 'error'));
  }

  function showNotification(message, type = 'success') {
    // Create notification element if it doesn't exist
    let n = document.getElementById('notification');
    if (!n) {
      n = document.createElement('div');
      n.id = 'notification';
      n.className = 'fixed top-20 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50 max-w-sm';
      n.innerHTML = '<div class="flex items-center"><i class="fas fa-check-circle mr-2"></i><span id="notification-message"></span></div>';
      document.body.appendChild(n);
    }
    
    const m = document.getElementById('notification-message');
    m.textContent = message;
    n.classList.remove('bg-emerald-500','bg-red-500');
    n.classList.add(type === 'error' ? 'bg-red-500' : 'bg-emerald-500');
    n.style.transform = 'translateX(0)';
    setTimeout(() => { n.style.transform = 'translateX(100%)'; }, 3000);
  }

  function updateCartCount() {
    fetch('get_cart_count.php')
      .then(r => r.json())
      .then(d => {
        if (!d.success) return;
        const cartIcon = document.querySelector('a[href="cart.php"]');
        let badge = cartIcon.querySelector('.cart-count-badge');
        if (d.count > 0) {
          if (!badge) {
            badge = document.createElement('span');
            badge.className = 'cart-count-badge bg-emerald-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center';
            cartIcon.appendChild(badge);
          }
          badge.textContent = d.count;
        } else if (badge) {
          badge.remove();
        }
      })
      .catch(() => {});
  }
  
