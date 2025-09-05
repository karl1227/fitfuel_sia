// SEO utilities for FitFuel website
class SEO {
    constructor() {
        this.config = window.CONFIG || {};
        this.init();
    }

    init() {
        this.setDefaultMetaTags();
        this.setStructuredData();
    }

    setDefaultMetaTags() {
        // Set default meta tags if not already present
        this.setMetaTag('description', this.config.seo?.defaultDescription || 'Your ultimate destination for premium fitness equipment, supplements, and accessories.');
        this.setMetaTag('keywords', this.config.seo?.keywords?.join(', ') || 'fitness, gym equipment, supplements, workout, exercise, health, wellness');
        this.setMetaTag('author', this.config.site?.name || 'FitFuel');
        this.setMetaTag('viewport', 'width=device-width, initial-scale=1.0');
        
        // Open Graph tags
        this.setMetaTag('og:title', document.title);
        this.setMetaTag('og:description', this.config.seo?.defaultDescription || '');
        this.setMetaTag('og:type', 'website');
        this.setMetaTag('og:url', window.location.href);
        this.setMetaTag('og:image', this.config.seo?.ogImage || this.config.site?.logo || '');
        this.setMetaTag('og:site_name', this.config.site?.name || 'FitFuel');
        
        // Twitter Card tags
        this.setMetaTag('twitter:card', this.config.seo?.twitterCard || 'summary_large_image');
        this.setMetaTag('twitter:title', document.title);
        this.setMetaTag('twitter:description', this.config.seo?.defaultDescription || '');
        this.setMetaTag('twitter:image', this.config.seo?.ogImage || this.config.site?.logo || '');
        
        // Additional SEO tags
        this.setMetaTag('robots', 'index, follow');
        this.setMetaTag('language', 'en');
        this.setMetaTag('revisit-after', '7 days');
    }

    setMetaTag(name, content) {
        if (!content) return;
        
        let metaTag = document.querySelector(`meta[name="${name}"]`) || 
                     document.querySelector(`meta[property="${name}"]`);
        
        if (!metaTag) {
            metaTag = document.createElement('meta');
            if (name.startsWith('og:') || name.startsWith('twitter:')) {
                metaTag.setAttribute('property', name);
            } else {
                metaTag.setAttribute('name', name);
            }
            document.head.appendChild(metaTag);
        }
        
        metaTag.setAttribute('content', content);
    }

    setStructuredData() {
        const structuredData = {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": this.config.site?.name || "FitFuel",
            "description": this.config.site?.tagline || "",
            "url": this.config.site?.url || window.location.origin,
            "logo": this.config.site?.logo || "",
            "contactPoint": {
                "@type": "ContactPoint",
                "telephone": this.config.site?.phone || "",
                "contactType": "customer service",
                "email": this.config.site?.email || ""
            },
            "sameAs": this.config.footer?.socialMedia?.map(social => social.href) || []
        };

        // Add structured data script
        const script = document.createElement('script');
        script.type = 'application/ld+json';
        script.textContent = JSON.stringify(structuredData);
        document.head.appendChild(script);
    }

    // Update page-specific SEO
    updatePageSEO(pageData) {
        if (pageData.title) {
            document.title = `${pageData.title} - ${this.config.site?.name || 'FitFuel'}`;
            this.setMetaTag('og:title', document.title);
            this.setMetaTag('twitter:title', document.title);
        }

        if (pageData.description) {
            this.setMetaTag('description', pageData.description);
            this.setMetaTag('og:description', pageData.description);
            this.setMetaTag('twitter:description', pageData.description);
        }

        if (pageData.image) {
            this.setMetaTag('og:image', pageData.image);
            this.setMetaTag('twitter:image', pageData.image);
        }

        if (pageData.keywords) {
            this.setMetaTag('keywords', pageData.keywords.join(', '));
        }

        // Update canonical URL
        this.setCanonicalURL(pageData.canonical || window.location.href);
    }

    setCanonicalURL(url) {
        let canonical = document.querySelector('link[rel="canonical"]');
        if (!canonical) {
            canonical = document.createElement('link');
            canonical.setAttribute('rel', 'canonical');
            document.head.appendChild(canonical);
        }
        canonical.setAttribute('href', url);
    }

    // Generate breadcrumb structured data
    setBreadcrumbStructuredData(breadcrumbs) {
        const structuredData = {
            "@context": "https://schema.org",
            "@type": "BreadcrumbList",
            "itemListElement": breadcrumbs.map((crumb, index) => ({
                "@type": "ListItem",
                "position": index + 1,
                "name": crumb.name,
                "item": crumb.url
            }))
        };

        const script = document.createElement('script');
        script.type = 'application/ld+json';
        script.textContent = JSON.stringify(structuredData);
        document.head.appendChild(script);
    }

    // Generate product structured data
    setProductStructuredData(product) {
        const structuredData = {
            "@context": "https://schema.org",
            "@type": "Product",
            "name": product.name,
            "description": product.description || "",
            "image": product.image || "",
            "brand": {
                "@type": "Brand",
                "name": this.config.site?.name || "FitFuel"
            },
            "offers": {
                "@type": "Offer",
                "price": product.price || 0,
                "priceCurrency": this.config.products?.currency || "USD",
                "availability": "https://schema.org/InStock",
                "seller": {
                    "@type": "Organization",
                    "name": this.config.site?.name || "FitFuel"
                }
            }
        };

        if (product.rating) {
            structuredData.aggregateRating = {
                "@type": "AggregateRating",
                "ratingValue": product.rating,
                "reviewCount": product.reviewCount || 0
            };
        }

        const script = document.createElement('script');
        script.type = 'application/ld+json';
        script.textContent = JSON.stringify(structuredData);
        document.head.appendChild(script);
    }

    // Generate FAQ structured data
    setFAQStructuredData(faqs) {
        const structuredData = {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": faqs.map(faq => ({
                "@type": "Question",
                "name": faq.question,
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": faq.answer
                }
            }))
        };

        const script = document.createElement('script');
        script.type = 'application/ld+json';
        script.textContent = JSON.stringify(structuredData);
        document.head.appendChild(script);
    }

    // Update page title dynamically
    updateTitle(newTitle) {
        document.title = `${newTitle} - ${this.config.site?.name || 'FitFuel'}`;
        this.setMetaTag('og:title', document.title);
        this.setMetaTag('twitter:title', document.title);
    }

    // Track page views (for analytics)
    trackPageView(pageName, additionalData = {}) {
        if (typeof gtag !== 'undefined') {
            gtag('config', 'GA_MEASUREMENT_ID', {
                page_title: document.title,
                page_location: window.location.href,
                page_name: pageName,
                ...additionalData
            });
        }
    }
}

// Auto-initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new SEO();
});

// Export for external use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SEO;
} else {
    window.SEO = SEO;
}
