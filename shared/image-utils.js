// Image optimization utilities for FitFuel website
class ImageUtils {
    constructor() {
        this.config = window.CONFIG || {};
        this.init();
    }

    init() {
        this.setupLazyLoading();
        this.optimizeExistingImages();
    }

    setupLazyLoading() {
        // Add lazy loading to all images that don't have it
        const images = document.querySelectorAll('img:not([loading])');
        images.forEach(img => {
            img.setAttribute('loading', 'lazy');
        });

        // Set up intersection observer for lazy loading
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        this.loadImage(img);
                        observer.unobserve(img);
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.01
            });

            // Observe all images with data-src
            const lazyImages = document.querySelectorAll('img[data-src]');
            lazyImages.forEach(img => imageObserver.observe(img));
        }
    }

    loadImage(img) {
        if (img.dataset.src) {
            img.src = img.dataset.src;
            img.classList.remove('lazy');
        }
    }

    optimizeExistingImages() {
        // Add proper alt texts to images that don't have them
        const images = document.querySelectorAll('img:not([alt])');
        images.forEach(img => {
            const altText = this.generateAltText(img);
            img.setAttribute('alt', altText);
        });

        // Add loading states
        const imagesWithSrc = document.querySelectorAll('img[src]');
        imagesWithSrc.forEach(img => {
            this.addLoadingState(img);
        });
    }

    generateAltText(img) {
        // Try to extract alt text from various sources
        const src = img.src || img.dataset.src || '';
        const className = img.className || '';
        const parentText = img.parentElement?.textContent?.trim() || '';
        
        // Common patterns for fitness equipment images
        if (src.includes('dumbbell') || className.includes('dumbbell')) {
            return 'Adjustable dumbbells for strength training';
        }
        if (src.includes('kettlebell') || className.includes('kettlebell')) {
            return 'Kettlebell for functional fitness training';
        }
        if (src.includes('barbell') || className.includes('barbell')) {
            return 'Barbell for weightlifting exercises';
        }
        if (src.includes('protein') || className.includes('protein')) {
            return 'Protein supplement for muscle recovery';
        }
        if (src.includes('gloves') || className.includes('gloves')) {
            return 'Gym gloves for weightlifting protection';
        }
        if (src.includes('belt') || className.includes('belt')) {
            return 'Weightlifting belt for back support';
        }
        if (src.includes('banner') || className.includes('banner')) {
            return 'FitFuel promotional banner';
        }
        if (src.includes('logo') || className.includes('logo')) {
            return 'FitFuel logo';
        }
        
        // Fallback to generic description
        return 'Fitness equipment and supplements from FitFuel';
    }

    addLoadingState(img) {
        // Add loading placeholder
        const placeholder = document.createElement('div');
        placeholder.className = 'image-loading-placeholder bg-gray-200 animate-pulse';
        placeholder.style.width = img.offsetWidth + 'px';
        placeholder.style.height = img.offsetHeight + 'px';
        
        img.style.opacity = '0';
        img.parentNode.insertBefore(placeholder, img);
        
        img.onload = () => {
            img.style.opacity = '1';
            img.style.transition = 'opacity 0.3s ease';
            if (placeholder.parentNode) {
                placeholder.parentNode.removeChild(placeholder);
            }
        };
        
        img.onerror = () => {
            img.src = this.config.products?.defaultImage || '/placeholder.svg?height=250&width=300';
            if (placeholder.parentNode) {
                placeholder.parentNode.removeChild(placeholder);
            }
        };
    }

    // Create responsive image element
    createResponsiveImage(src, alt, options = {}) {
        const img = document.createElement('img');
        img.src = src;
        img.alt = alt || this.generateAltText(img);
        img.loading = 'lazy';
        img.className = options.className || '';
        
        // Add responsive attributes
        if (options.sizes) {
            img.sizes = options.sizes;
        }
        if (options.srcset) {
            img.srcset = options.srcset;
        }
        
        this.addLoadingState(img);
        return img;
    }

    // Generate responsive image sources
    generateResponsiveSources(baseSrc, widths = [320, 640, 1024, 1280]) {
        return widths.map(width => {
            const src = baseSrc.replace(/\.(jpg|jpeg|png|webp)$/i, `_${width}w.$1`);
            return `${src} ${width}w`;
        }).join(', ');
    }

    // Optimize image for web
    optimizeImageUrl(url, options = {}) {
        const params = new URLSearchParams();
        
        if (options.width) params.append('w', options.width);
        if (options.height) params.append('h', options.height);
        if (options.quality) params.append('q', options.quality);
        if (options.format) params.append('f', options.format);
        
        const queryString = params.toString();
        return queryString ? `${url}?${queryString}` : url;
    }

    // Preload critical images
    preloadImages(urls) {
        urls.forEach(url => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'image';
            link.href = url;
            document.head.appendChild(link);
        });
    }

    // Add image error handling
    addErrorHandling(img) {
        img.onerror = () => {
            img.src = this.config.products?.defaultImage || '/placeholder.svg?height=250&width=300';
            img.alt = 'Image not available';
            img.classList.add('image-error');
        };
    }

    // Get image dimensions
    getImageDimensions(src) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => {
                resolve({
                    width: img.naturalWidth,
                    height: img.naturalHeight,
                    aspectRatio: img.naturalWidth / img.naturalHeight
                });
            };
            img.onerror = reject;
            img.src = src;
        });
    }

    // Create image gallery
    createImageGallery(images, container) {
        const gallery = document.createElement('div');
        gallery.className = 'image-gallery grid grid-cols-2 md:grid-cols-3 gap-4';
        
        images.forEach((imageSrc, index) => {
            const img = this.createResponsiveImage(imageSrc, `Gallery image ${index + 1}`, {
                className: 'w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-80 transition-opacity'
            });
            
            img.addEventListener('click', () => {
                this.openLightbox(imageSrc, images, index);
            });
            
            gallery.appendChild(img);
        });
        
        container.appendChild(gallery);
    }

    // Open lightbox for image viewing
    openLightbox(src, images, currentIndex) {
        const lightbox = document.createElement('div');
        lightbox.className = 'fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50';
        lightbox.innerHTML = `
            <div class="relative max-w-4xl max-h-full p-4">
                <button class="absolute top-4 right-4 text-white text-2xl z-10" onclick="this.closest('.fixed').remove()">
                    <i class="fas fa-times"></i>
                </button>
                <img src="${src}" alt="Gallery image" class="max-w-full max-h-full object-contain">
                ${images.length > 1 ? `
                    <button class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white text-2xl" onclick="changeImage(-1)">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white text-2xl" onclick="changeImage(1)">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                ` : ''}
            </div>
        `;
        
        document.body.appendChild(lightbox);
        
        // Add navigation functions
        window.changeImage = (direction) => {
            const newIndex = (currentIndex + direction + images.length) % images.length;
            const img = lightbox.querySelector('img');
            img.src = images[newIndex];
            currentIndex = newIndex;
        };
        
        // Close on escape key
        const handleKeydown = (e) => {
            if (e.key === 'Escape') {
                lightbox.remove();
                document.removeEventListener('keydown', handleKeydown);
            }
        };
        document.addEventListener('keydown', handleKeydown);
        
        // Close on background click
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) {
                lightbox.remove();
                document.removeEventListener('keydown', handleKeydown);
            }
        });
    }
}

// Auto-initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ImageUtils();
});

// Export for external use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ImageUtils;
} else {
    window.ImageUtils = ImageUtils;
}
