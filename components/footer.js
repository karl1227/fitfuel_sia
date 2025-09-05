// Shared footer component for FitFuel website
class FooterComponent {
    constructor() {
        this.config = window.CONFIG || {};
        this.init();
    }

    init() {
        this.createFooter();
        this.attachEventListeners();
    }

    createFooter() {
        const footerHTML = `
            <footer class="bg-slate-800 text-white py-12">
                <div class="container mx-auto px-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <!-- Company Info -->
                        <div>
                            <h3 class="font-heading text-2xl font-bold text-white mb-4">
                                ${this.config.site?.name || 'FitFuel'}
                            </h3>
                            <p class="text-slate-300 mb-4">
                                ${this.config.site?.tagline || 'Your ultimate destination for premium fitness equipment, supplements, and accessories.'}
                            </p>
                            <div class="flex space-x-4">
                                ${this.config.footer?.socialMedia?.map(social => 
                                    `<a href="${social.href}" 
                                       class="text-slate-300 hover:text-emerald-400 transition-colors" 
                                       title="${social.platform}"
                                       aria-label="${social.platform}">
                                        <i class="${social.icon} text-xl"></i>
                                    </a>`
                                ).join('') || ''}
                            </div>
                        </div>
                        
                        <!-- Quick Links -->
                        <div>
                            <h4 class="font-semibold text-lg mb-4">Quick Links</h4>
                            <ul class="space-y-2">
                                ${this.config.footer?.quickLinks?.map(link => 
                                    `<li>
                                        <a href="${link.href}" 
                                           class="text-slate-300 hover:text-emerald-400 transition-colors">
                                            ${link.text}
                                        </a>
                                    </li>`
                                ).join('') || ''}
                            </ul>
                        </div>
                        
                        <!-- Categories -->
                        <div>
                            <h4 class="font-semibold text-lg mb-4">Categories</h4>
                            <ul class="space-y-2">
                                ${this.config.footer?.categories?.map(category => 
                                    `<li>
                                        <a href="${category.href}" 
                                           class="text-slate-300 hover:text-emerald-400 transition-colors">
                                            ${category.text}
                                        </a>
                                    </li>`
                                ).join('') || ''}
                            </ul>
                        </div>
                        
                        <!-- Customer Service -->
                        <div>
                            <h4 class="font-semibold text-lg mb-4">Customer Service</h4>
                            <ul class="space-y-2">
                                ${this.config.footer?.customerService?.map(service => 
                                    `<li>
                                        <a href="${service.href}" 
                                           class="text-slate-300 hover:text-emerald-400 transition-colors">
                                            ${service.text}
                                        </a>
                                    </li>`
                                ).join('') || ''}
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Newsletter Section -->
                    <div class="border-t border-slate-700 mt-8 pt-8">
                        <div class="max-w-md mx-auto text-center">
                            <h4 class="font-semibold text-lg mb-4">Stay Updated</h4>
                            <p class="text-slate-300 mb-4">
                                Get the latest fitness tips, product updates, and exclusive offers
                            </p>
                            <div class="flex">
                                <input type="email" 
                                       placeholder="Enter your email" 
                                       class="flex-1 px-4 py-3 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-emerald-300 text-gray-900"
                                       id="newsletter-email" />
                                <button onclick="subscribeNewsletter()" 
                                        class="bg-slate-800 text-white px-6 py-3 rounded-r-lg hover:bg-slate-700 transition-colors">
                                    Subscribe
                                </button>
                            </div>
                            <div id="newsletter-message" class="mt-2 text-sm hidden"></div>
                        </div>
                    </div>
                    
                    <!-- Copyright -->
                    <div class="border-t border-slate-700 mt-8 pt-8 text-center">
                        <p class="text-slate-300">
                            &copy; ${new Date().getFullYear()} ${this.config.site?.name || 'FitFuel'}. All rights reserved. | 
                            <a href="#" class="hover:text-emerald-400 transition-colors">Privacy Policy</a> | 
                            <a href="#" class="hover:text-emerald-400 transition-colors">Terms of Service</a>
                        </p>
                    </div>
                </div>
            </footer>
        `;

        // Insert footer at the end of body
        document.body.insertAdjacentHTML('beforeend', footerHTML);
    }

    attachEventListeners() {
        // Newsletter subscription
        const newsletterEmail = document.getElementById('newsletter-email');
        if (newsletterEmail) {
            newsletterEmail.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.handleNewsletterSubscription();
                }
            });
        }
    }

    handleNewsletterSubscription() {
        try {
            const emailInput = document.getElementById('newsletter-email');
            const messageDiv = document.getElementById('newsletter-message');
            
            if (!emailInput) {
                console.warn('Newsletter email input not found');
                return;
            }
            
            const email = emailInput.value;
            
            if (!email || !this.isValidEmail(email)) {
                this.showNewsletterMessage('Please enter a valid email address.', 'error');
                return;
            }

            // Simulate newsletter subscription
            this.showNewsletterMessage('Thank you for subscribing!', 'success');
            emailInput.value = '';
            
            // In a real application, you would send this to your backend
            console.log('Newsletter subscription:', email);
        } catch (error) {
            console.warn('Error handling newsletter subscription:', error);
        }
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    showNewsletterMessage(message, type) {
        const messageDiv = document.getElementById('newsletter-message');
        if (messageDiv) {
            messageDiv.textContent = message;
            messageDiv.className = `mt-2 text-sm ${type === 'error' ? 'text-red-400' : 'text-emerald-400'}`;
            messageDiv.classList.remove('hidden');
            
            setTimeout(() => {
                messageDiv.classList.add('hidden');
            }, 3000);
        }
    }
}

// Global function for newsletter subscription
window.subscribeNewsletter = function() {
    const footer = new FooterComponent();
    footer.handleNewsletterSubscription();
};

// Auto-initialize when DOM is loaded (only once)
document.addEventListener('DOMContentLoaded', () => {
    if (!document.querySelector('footer.bg-slate-800')) {
        new FooterComponent();
    }
});

// Export for external use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FooterComponent;
} else {
    window.FooterComponent = FooterComponent;
}
