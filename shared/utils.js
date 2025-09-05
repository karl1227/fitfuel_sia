// Utility functions for FitFuel website
class Utils {
    // DOM Utilities
    static $(selector) {
        return document.querySelector(selector);
    }

    static $$(selector) {
        return document.querySelectorAll(selector);
    }

    static createElement(tag, className = '', content = '') {
        const element = document.createElement(tag);
        if (className) element.className = className;
        if (content) element.innerHTML = content;
        return element;
    }

    // String Utilities
    static capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    static slugify(str) {
        return str.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    static truncate(str, length = 100) {
        if (str.length <= length) return str;
        return str.substring(0, length).trim() + '...';
    }

    // Number Utilities
    static formatPrice(price, currency = 'P') {
        return `${currency} ${parseFloat(price).toLocaleString()}`;
    }

    static formatNumber(num) {
        return parseFloat(num).toLocaleString();
    }

    // Validation Utilities
    static isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    static isValidPhone(phone) {
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        return phoneRegex.test(phone.replace(/\s/g, ''));
    }

    static isValidUrl(url) {
        try {
            new URL(url);
            return true;
        } catch {
            return false;
        }
    }

    // Local Storage Utilities
    static setStorage(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (error) {
            console.error('Error saving to localStorage:', error);
            return false;
        }
    }

    static getStorage(key, defaultValue = null) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : defaultValue;
        } catch (error) {
            console.error('Error reading from localStorage:', error);
            return defaultValue;
        }
    }

    static removeStorage(key) {
        try {
            localStorage.removeItem(key);
            return true;
        } catch (error) {
            console.error('Error removing from localStorage:', error);
            return false;
        }
    }

    // URL Utilities
    static getUrlParams() {
        const params = new URLSearchParams(window.location.search);
        const result = {};
        for (const [key, value] of params) {
            result[key] = value;
        }
        return result;
    }

    static setUrlParam(key, value) {
        const url = new URL(window.location);
        url.searchParams.set(key, value);
        window.history.pushState({}, '', url);
    }

    static removeUrlParam(key) {
        const url = new URL(window.location);
        url.searchParams.delete(key);
        window.history.pushState({}, '', url);
    }

    // Animation Utilities
    static fadeIn(element, duration = 300) {
        element.style.opacity = '0';
        element.style.display = 'block';
        
        let start = performance.now();
        
        function animate(currentTime) {
            const elapsed = currentTime - start;
            const progress = Math.min(elapsed / duration, 1);
            
            element.style.opacity = progress;
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        }
        
        requestAnimationFrame(animate);
    }

    static fadeOut(element, duration = 300) {
        let start = performance.now();
        const initialOpacity = parseFloat(getComputedStyle(element).opacity);
        
        function animate(currentTime) {
            const elapsed = currentTime - start;
            const progress = Math.min(elapsed / duration, 1);
            
            element.style.opacity = initialOpacity * (1 - progress);
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                element.style.display = 'none';
            }
        }
        
        requestAnimationFrame(animate);
    }

    // Notification Utilities
    static showNotification(message, type = 'info', duration = 3000) {
        const notification = this.createElement('div', 
            `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`
        );
        
        // Set type-specific styles
        switch (type) {
            case 'success':
                notification.classList.add('bg-emerald-600', 'text-white');
                break;
            case 'error':
                notification.classList.add('bg-red-500', 'text-white');
                break;
            case 'warning':
                notification.classList.add('bg-yellow-500', 'text-white');
                break;
            default:
                notification.classList.add('bg-blue-500', 'text-white');
        }
        
        notification.textContent = message;
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Animate out and remove
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, duration);
    }

    // Loading Utilities
    static showLoading(element, text = 'Loading...') {
        const loading = this.createElement('div', 
            'absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10'
        );
        loading.innerHTML = `
            <div class="text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600 mx-auto mb-2"></div>
                <p class="text-gray-600">${text}</p>
            </div>
        `;
        element.style.position = 'relative';
        element.appendChild(loading);
        return loading;
    }

    static hideLoading(element) {
        const loading = element.querySelector('.absolute.inset-0');
        if (loading) {
            loading.remove();
        }
    }

    // Debounce utility
    static debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Throttle utility
    static throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    // Image loading utility
    static loadImage(src, alt = '') {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = src;
            img.alt = alt;
        });
    }

    // Lazy loading utility
    static lazyLoadImages() {
        const images = this.$$('img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    }

    // Error handling utility
    static handleError(error, context = '') {
        console.error(`Error${context ? ` in ${context}` : ''}:`, error);
        
        // In production, you might want to send this to an error tracking service
        if (window.gtag) {
            window.gtag('event', 'exception', {
                description: error.message,
                fatal: false
            });
        }
    }

    // Performance utilities
    static measurePerformance(name, fn) {
        const start = performance.now();
        const result = fn();
        const end = performance.now();
        console.log(`${name} took ${end - start} milliseconds`);
        return result;
    }

    // Accessibility utilities
    static setAriaLabel(element, label) {
        element.setAttribute('aria-label', label);
    }

    static setAriaDescribedBy(element, id) {
        element.setAttribute('aria-describedby', id);
    }

    static announceToScreenReader(message) {
        const announcement = this.createElement('div', 
            'sr-only', message
        );
        announcement.setAttribute('aria-live', 'polite');
        document.body.appendChild(announcement);
        
        setTimeout(() => {
            if (announcement.parentNode) {
                announcement.parentNode.removeChild(announcement);
            }
        }, 1000);
    }
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Utils;
} else {
    window.Utils = Utils;
}
