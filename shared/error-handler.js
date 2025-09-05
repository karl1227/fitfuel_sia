// Error handling utilities for FitFuel website
class ErrorHandler {
    constructor() {
        this.config = window.CONFIG || {};
        this.errorLog = [];
        this.debugMode = false; // Set to true for debugging
        this.init();
    }

    init() {
        this.setupGlobalErrorHandling();
        this.setupUnhandledRejectionHandling();
        this.setupNetworkErrorHandling();
    }

    setupGlobalErrorHandling() {
        // Handle JavaScript errors
        window.addEventListener('error', (event) => {
            this.handleError({
                type: 'javascript',
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                error: event.error
            });
        });

        // Handle unhandled promise rejections
        window.addEventListener('unhandledrejection', (event) => {
            this.handleError({
                type: 'promise',
                message: event.reason?.message || 'Unhandled promise rejection',
                error: event.reason
            });
        });
    }

    setupUnhandledRejectionHandling() {
        // Handle fetch errors
        const originalFetch = window.fetch;
        window.fetch = async (...args) => {
            try {
                const response = await originalFetch(...args);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response;
            } catch (error) {
                this.handleError({
                    type: 'network',
                    message: `Fetch error: ${error.message}`,
                    url: args[0],
                    error: error
                });
                throw error;
            }
        };
    }

    setupNetworkErrorHandling() {
        // Handle online/offline status
        window.addEventListener('online', () => {
            this.showNotification('Connection restored', 'success');
        });

        window.addEventListener('offline', () => {
            this.showNotification('Connection lost. Some features may be unavailable.', 'warning');
        });
    }

    handleError(errorInfo) {
        // Log error
        this.logError(errorInfo);

        // Only show user-friendly message for critical errors
        if (this.shouldShowError(errorInfo)) {
            this.showUserFriendlyError(errorInfo);
        }

        // Report to analytics (if available)
        this.reportError(errorInfo);
    }

    shouldShowError(errorInfo) {
        // If debug mode is off, only show critical errors
        if (!this.debugMode) {
            // Don't show errors for:
            // - Script loading errors (external resources)
            // - Minor JavaScript errors that don't affect functionality
            // - Network errors for non-critical resources
            
            if (errorInfo.type === 'javascript') {
                // Don't show errors for external scripts or minor issues
                if (errorInfo.filename && (
                    errorInfo.filename.includes('google') ||
                    errorInfo.filename.includes('facebook') ||
                    errorInfo.filename.includes('analytics') ||
                    errorInfo.message.includes('Script error') ||
                    errorInfo.message.includes('ResizeObserver')
                )) {
                    return false;
                }
            }

            if (errorInfo.type === 'network') {
                // Don't show network errors for non-critical resources
                if (errorInfo.url && (
                    errorInfo.url.includes('analytics') ||
                    errorInfo.url.includes('facebook') ||
                    errorInfo.url.includes('google')
                )) {
                    return false;
                }
            }

            // Show errors for critical functionality only
            return errorInfo.type === 'cart' || 
                   errorInfo.type === 'product' || 
                   errorInfo.type === 'validation';
        }

        // In debug mode, show all errors
        return true;
    }

    logError(errorInfo) {
        const errorLog = {
            timestamp: new Date().toISOString(),
            userAgent: navigator.userAgent,
            url: window.location.href,
            ...errorInfo
        };

        this.errorLog.push(errorLog);
        console.error('Error logged:', errorLog);

        // Store in localStorage for debugging
        try {
            const existingLogs = JSON.parse(localStorage.getItem('fitfuel_error_logs') || '[]');
            existingLogs.push(errorLog);
            
            // Keep only last 50 errors
            if (existingLogs.length > 50) {
                existingLogs.splice(0, existingLogs.length - 50);
            }
            
            localStorage.setItem('fitfuel_error_logs', JSON.stringify(existingLogs));
        } catch (e) {
            console.warn('Could not save error to localStorage:', e);
        }
    }

    showUserFriendlyError(errorInfo) {
        let message = 'An unexpected error occurred. Please try again.';
        
        switch (errorInfo.type) {
            case 'network':
                message = 'Network error. Please check your connection and try again.';
                break;
            case 'javascript':
                message = 'Something went wrong. Please refresh the page and try again.';
                break;
            case 'promise':
                message = 'An operation failed. Please try again.';
                break;
            case 'validation':
                message = errorInfo.message || 'Please check your input and try again.';
                break;
            case 'cart':
                message = 'Cart operation failed. Please try again.';
                break;
            case 'product':
                message = 'Product information could not be loaded. Please try again.';
                break;
        }

        this.showNotification(message, 'error');
    }

    showNotification(message, type = 'error') {
        if (typeof Utils !== 'undefined' && Utils.showNotification) {
            Utils.showNotification(message, type);
        } else {
            // Fallback notification
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
                type === 'error' ? 'bg-red-500 text-white' : 
                type === 'success' ? 'bg-emerald-600 text-white' : 
                'bg-blue-500 text-white'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 5000);
        }
    }

    reportError(errorInfo) {
        // Report to Google Analytics if available
        if (typeof gtag !== 'undefined') {
            gtag('event', 'exception', {
                description: errorInfo.message,
                fatal: false
            });
        }

        // Report to other analytics services
        if (typeof window.analytics !== 'undefined') {
            window.analytics.track('Error Occurred', {
                errorType: errorInfo.type,
                errorMessage: errorInfo.message,
                url: window.location.href
            });
        }
    }

    // Handle specific error types
    handleCartError(error) {
        this.handleError({
            type: 'cart',
            message: error.message || 'Cart operation failed',
            error: error
        });
    }

    handleProductError(error) {
        this.handleError({
            type: 'product',
            message: error.message || 'Product operation failed',
            error: error
        });
    }

    handleValidationError(field, message) {
        this.handleError({
            type: 'validation',
            message: `${field}: ${message}`,
            field: field
        });
    }

    handleNetworkError(error, context = '') {
        this.handleError({
            type: 'network',
            message: `Network error${context ? ` in ${context}` : ''}: ${error.message}`,
            error: error
        });
    }

    // Retry mechanism
    async retryOperation(operation, maxRetries = 3, delay = 1000) {
        for (let i = 0; i < maxRetries; i++) {
            try {
                return await operation();
            } catch (error) {
                if (i === maxRetries - 1) {
                    throw error;
                }
                
                console.warn(`Operation failed, retrying in ${delay}ms... (${i + 1}/${maxRetries})`);
                await this.delay(delay);
                delay *= 2; // Exponential backoff
            }
        }
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // Get error logs for debugging
    getErrorLogs() {
        return this.errorLog;
    }

    // Clear error logs
    clearErrorLogs() {
        this.errorLog = [];
        localStorage.removeItem('fitfuel_error_logs');
    }

    // Check if there are recent errors
    hasRecentErrors(minutes = 5) {
        const recentTime = new Date(Date.now() - minutes * 60 * 1000);
        return this.errorLog.some(error => 
            new Date(error.timestamp) > recentTime
        );
    }

    // Handle API errors
    handleAPIError(response, context = '') {
        let message = 'API request failed';
        
        switch (response.status) {
            case 400:
                message = 'Invalid request. Please check your input.';
                break;
            case 401:
                message = 'Authentication required. Please log in.';
                break;
            case 403:
                message = 'Access denied. You do not have permission.';
                break;
            case 404:
                message = 'Resource not found.';
                break;
            case 429:
                message = 'Too many requests. Please wait and try again.';
                break;
            case 500:
                message = 'Server error. Please try again later.';
                break;
            case 503:
                message = 'Service unavailable. Please try again later.';
                break;
        }

        this.handleError({
            type: 'api',
            message: `${message}${context ? ` (${context})` : ''}`,
            status: response.status,
            statusText: response.statusText
        });
    }

    // Handle form validation errors
    handleFormValidationErrors(errors) {
        Object.keys(errors).forEach(field => {
            const fieldElement = document.querySelector(`[name="${field}"]`);
            if (fieldElement) {
                fieldElement.classList.add('border-red-500');
                
                // Show field-specific error message
                let errorElement = fieldElement.parentNode.querySelector('.error-message');
                if (!errorElement) {
                    errorElement = document.createElement('div');
                    errorElement.className = 'error-message text-red-500 text-sm mt-1';
                    fieldElement.parentNode.appendChild(errorElement);
                }
                errorElement.textContent = errors[field];
            }
        });
    }

    // Clear form validation errors
    clearFormValidationErrors(form) {
        const errorElements = form.querySelectorAll('.error-message');
        errorElements.forEach(element => element.remove());
        
        const fieldElements = form.querySelectorAll('.border-red-500');
        fieldElements.forEach(element => element.classList.remove('border-red-500'));
    }
}

// Create global instance only if it doesn't exist
if (!window.ErrorHandler) {
    window.ErrorHandler = new ErrorHandler();
}

// Export for external use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ErrorHandler;
}
