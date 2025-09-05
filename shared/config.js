// Centralized configuration for FitFuel website
const CONFIG = {
    // Site Information
    site: {
        name: "FitFuel",
        tagline: "Your ultimate destination for premium fitness equipment, supplements, and accessories",
        logo: "img/LOGO-Fitfuel.png",
        favicon: "img/LOGO-Fitfuel.png",
        url: "https://fitfuel.com", // Update with actual domain
        email: "support@fitfuel.com",
        phone: "+1 (555) 123-4567"
    },

    // Navigation Configuration
    navigation: {
        topNav: [
            { text: "Review", href: "#", icon: "fas fa-star" },
            { text: "Help", href: "#", icon: "fas fa-question-circle" },
            { text: "Account", href: "#", icon: "fas fa-user" },
            { text: "Login", href: "#", icon: "fas fa-sign-in-alt" }
        ],
        mainNav: [
            {
                text: "Gym Accessories",
                href: "shop.html#accessories",
                icon: "fas fa-dumbbell",
                submenu: [
                    { text: "Lifting Straps", href: "shop.html#lifting-straps" },
                    { text: "Gym Gloves", href: "shop.html#gym-gloves" },
                    { text: "Weight Belts", href: "shop.html#weight-belts" },
                    { text: "Knee Wraps", href: "shop.html#knee-wraps" },
                    { text: "Wrist Wraps", href: "shop.html#wrist-wraps" },
                    { text: "Gym Bags", href: "shop.html#gym-bags" }
                ]
            },
            {
                text: "Gym Supplements",
                href: "shop.html#supplements",
                icon: "fas fa-pills",
                submenu: [
                    { text: "Whey Protein", href: "shop.html#whey-protein" },
                    { text: "Pre-Workout", href: "shop.html#pre-workout" },
                    { text: "Post-Workout", href: "shop.html#post-workout" },
                    { text: "Creatine", href: "shop.html#creatine" },
                    { text: "BCAAs", href: "shop.html#bcaas" },
                    { text: "Fat Burners", href: "shop.html#fat-burners" }
                ]
            },
            {
                text: "Gym Equipment",
                href: "shop.html#equipment",
                icon: "fas fa-weight-hanging",
                submenu: [
                    { text: "Dumbbells", href: "shop.html#dumbbells" },
                    { text: "Barbells", href: "shop.html#barbells" },
                    { text: "Resistance Bands", href: "shop.html#resistance-bands" },
                    { text: "Kettlebells", href: "product-detail.html" },
                    { text: "Yoga Mats", href: "shop.html#yoga-mats" },
                    { text: "Cardio Equipment", href: "shop.html#cardio-equipment" }
                ]
            }
        ]
    },

    // Footer Configuration
    footer: {
        quickLinks: [
            { text: "About Us", href: "#" },
            { text: "Contact", href: "#" },
            { text: "Blog", href: "#" },
            { text: "FAQs", href: "#" }
        ],
        categories: [
            { text: "Gym Equipment", href: "shop.html#equipment" },
            { text: "Supplements", href: "shop.html#supplements" },
            { text: "Accessories", href: "shop.html#accessories" },
            { text: "Apparel", href: "shop.html#apparel" }
        ],
        customerService: [
            { text: "Shipping Info", href: "#" },
            { text: "Returns", href: "#" },
            { text: "Size Guide", href: "#" },
            { text: "Track Order", href: "#" }
        ],
        socialMedia: [
            { platform: "Facebook", href: "#", icon: "fab fa-facebook" },
            { platform: "Instagram", href: "#", icon: "fab fa-instagram" },
            { platform: "Twitter", href: "#", icon: "fab fa-twitter" },
            { platform: "YouTube", href: "#", icon: "fab fa-youtube" }
        ]
    },

    // Product Configuration
    products: {
        itemsPerPage: 9,
        currency: "P",
        currencySymbol: "₱",
        defaultImage: "/placeholder.svg?height=250&width=300",
        categories: {
            "accessories": "Gym Accessories",
            "supplements": "Gym Supplements", 
            "equipment": "Gym Equipment"
        }
    },

    // Cart Configuration
    cart: {
        storageKey: "fitfuel_cart",
        freeShippingThreshold: 75,
        taxRate: 0.08, // 8% tax rate
        shippingRates: {
            standard: 0, // Free shipping over threshold
            express: 15,
            overnight: 25
        }
    },

    // UI Configuration
    ui: {
        theme: {
            primary: "#059669",
            secondary: "#10b981",
            accent: "#10b981",
            muted: "#f1f5f9",
            foreground: "#475569",
            border: "#e5e7eb"
        },
        animations: {
            duration: "0.3s",
            easing: "ease"
        },
        breakpoints: {
            sm: "640px",
            md: "768px",
            lg: "1024px",
            xl: "1280px"
        }
    },

    // SEO Configuration
    seo: {
        defaultTitle: "FitFuel - Premium Fitness Equipment & Supplements",
        defaultDescription: "Your ultimate destination for premium fitness equipment, supplements, and accessories. Fuel your fitness journey with quality products.",
        keywords: ["fitness", "gym equipment", "supplements", "workout", "exercise", "health", "wellness"],
        ogImage: "img/og-image.jpg",
        twitterCard: "summary_large_image"
    },

    // API Configuration (for future backend integration)
    api: {
        baseUrl: "/api", // Update with actual API URL
        endpoints: {
            products: "/products",
            categories: "/categories",
            cart: "/cart",
            orders: "/orders",
            users: "/users"
        },
        timeout: 10000
    }
};

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CONFIG;
} else {
    window.CONFIG = CONFIG;
}
