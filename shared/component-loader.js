// Component loader for managing component-based architecture
class ComponentLoader {
    constructor() {
        this.components = new Map();
        this.loadedComponents = new Set();
        this.init();
    }

    init() {
        // Auto-load components when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            this.loadAllComponents();
        });
    }

    // Register a component
    register(name, componentClass, options = {}) {
        this.components.set(name, {
            class: componentClass,
            options: options,
            instance: null
        });
    }

    // Load a specific component
    load(name) {
        if (this.loadedComponents.has(name)) {
            return this.components.get(name).instance;
        }

        const component = this.components.get(name);
        if (!component) {
            console.warn(`Component '${name}' not found`);
            return null;
        }

        try {
            const instance = new component.class(component.options);
            component.instance = instance;
            this.loadedComponents.add(name);
            
            console.log(`Component '${name}' loaded successfully`);
            return instance;
        } catch (error) {
            console.error(`Error loading component '${name}':`, error);
            return null;
        }
    }

    // Load all registered components
    loadAllComponents() {
        for (const [name, component] of this.components) {
            if (!this.loadedComponents.has(name)) {
                this.load(name);
            }
        }
    }

    // Unload a component
    unload(name) {
        const component = this.components.get(name);
        if (component && component.instance) {
            if (typeof component.instance.destroy === 'function') {
                component.instance.destroy();
            }
            component.instance = null;
            this.loadedComponents.delete(name);
            console.log(`Component '${name}' unloaded`);
        }
    }

    // Get a component instance
    get(name) {
        const component = this.components.get(name);
        return component ? component.instance : null;
    }

    // Check if a component is loaded
    isLoaded(name) {
        return this.loadedComponents.has(name);
    }

    // Get all loaded components
    getLoadedComponents() {
        return Array.from(this.loadedComponents);
    }
}

// Create global instance
window.ComponentLoader = new ComponentLoader();

// Auto-register common components when they're available
document.addEventListener('DOMContentLoaded', () => {
    // Register header component
    if (typeof HeaderComponent !== 'undefined') {
        ComponentLoader.register('header', HeaderComponent);
    }

    // Register footer component
    if (typeof FooterComponent !== 'undefined') {
        ComponentLoader.register('footer', FooterComponent);
    }

    // Register cart component
    if (typeof CartComponent !== 'undefined') {
        ComponentLoader.register('cart', CartComponent);
    }

    // Register search component
    if (typeof SearchComponent !== 'undefined') {
        ComponentLoader.register('search', SearchComponent);
    }
});

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ComponentLoader;
}
