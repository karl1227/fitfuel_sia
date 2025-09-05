// Form validation utilities for FitFuel website
class FormValidator {
    constructor() {
        this.config = window.CONFIG || {};
        this.rules = this.getDefaultRules();
        this.init();
    }

    init() {
        this.setupFormValidation();
    }

    getDefaultRules() {
        return {
            required: {
                validate: (value) => value && value.trim().length > 0,
                message: 'This field is required'
            },
            email: {
                validate: (value) => {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return emailRegex.test(value);
                },
                message: 'Please enter a valid email address'
            },
            phone: {
                validate: (value) => {
                    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
                    return phoneRegex.test(value.replace(/\s/g, ''));
                },
                message: 'Please enter a valid phone number'
            },
            minLength: {
                validate: (value, min) => value && value.length >= min,
                message: (min) => `Must be at least ${min} characters long`
            },
            maxLength: {
                validate: (value, max) => !value || value.length <= max,
                message: (max) => `Must be no more than ${max} characters long`
            },
            numeric: {
                validate: (value) => !isNaN(value) && !isNaN(parseFloat(value)),
                message: 'Must be a valid number'
            },
            positive: {
                validate: (value) => parseFloat(value) > 0,
                message: 'Must be a positive number'
            },
            url: {
                validate: (value) => {
                    try {
                        new URL(value);
                        return true;
                    } catch {
                        return false;
                    }
                },
                message: 'Please enter a valid URL'
            },
            password: {
                validate: (value) => {
                    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
                    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/;
                    return passwordRegex.test(value);
                },
                message: 'Password must be at least 8 characters with uppercase, lowercase, and number'
            },
            confirmPassword: {
                validate: (value, originalValue) => value === originalValue,
                message: 'Passwords do not match'
            }
        };
    }

    setupFormValidation() {
        // Auto-validate forms with data-validate attribute
        document.addEventListener('DOMContentLoaded', () => {
            const forms = document.querySelectorAll('form[data-validate]');
            forms.forEach(form => this.setupForm(form));
        });
    }

    setupForm(form) {
        // Add validation to form inputs
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => this.setupInput(input));

        // Handle form submission
        form.addEventListener('submit', (e) => {
            if (!this.validateForm(form)) {
                e.preventDefault();
            }
        });

        // Real-time validation
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });

            input.addEventListener('input', () => {
                this.clearFieldError(input);
            });
        });
    }

    setupInput(input) {
        // Add validation attributes based on input type
        if (input.type === 'email') {
            input.setAttribute('data-rule', 'email');
        } else if (input.type === 'tel') {
            input.setAttribute('data-rule', 'phone');
        } else if (input.type === 'url') {
            input.setAttribute('data-rule', 'url');
        } else if (input.type === 'password') {
            input.setAttribute('data-rule', 'password');
        }

        // Add required validation if input is required
        if (input.hasAttribute('required')) {
            input.setAttribute('data-rule', 'required');
        }
    }

    validateForm(form) {
        const inputs = form.querySelectorAll('input, textarea, select');
        let isValid = true;

        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });

        return isValid;
    }

    validateField(field) {
        const rules = this.getFieldRules(field);
        let isValid = true;
        let errorMessage = '';

        for (const rule of rules) {
            if (!rule.validate(field.value, rule.param)) {
                isValid = false;
                errorMessage = typeof rule.message === 'function' 
                    ? rule.message(rule.param) 
                    : rule.message;
                break;
            }
        }

        if (!isValid) {
            this.showFieldError(field, errorMessage);
        } else {
            this.clearFieldError(field);
        }

        return isValid;
    }

    getFieldRules(field) {
        const rules = [];
        const ruleString = field.getAttribute('data-rule') || '';
        const ruleNames = ruleString.split('|').filter(rule => rule.trim());

        ruleNames.forEach(ruleName => {
            const [name, param] = ruleName.split(':');
            const rule = this.rules[name];
            
            if (rule) {
                rules.push({
                    ...rule,
                    param: param ? parseFloat(param) : undefined
                });
            }
        });

        // Add required rule if field is required
        if (field.hasAttribute('required') && !rules.some(rule => rule === this.rules.required)) {
            rules.unshift(this.rules.required);
        }

        return rules;
    }

    showFieldError(field, message) {
        this.clearFieldError(field);
        
        // Add error styling
        field.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
        field.classList.remove('border-gray-300', 'focus:border-emerald-500', 'focus:ring-emerald-500');

        // Create error message element
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error text-red-500 text-sm mt-1';
        errorElement.textContent = message;
        
        // Insert after field
        field.parentNode.insertBefore(errorElement, field.nextSibling);
    }

    clearFieldError(field) {
        // Remove error styling
        field.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
        field.classList.add('border-gray-300', 'focus:border-emerald-500', 'focus:ring-emerald-500');

        // Remove error message
        const errorElement = field.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
    }

    // Custom validation methods
    validateEmail(email) {
        return this.rules.email.validate(email);
    }

    validatePhone(phone) {
        return this.rules.phone.validate(phone);
    }

    validatePassword(password) {
        return this.rules.password.validate(password);
    }

    validateRequired(value) {
        return this.rules.required.validate(value);
    }

    // Newsletter subscription validation
    validateNewsletterEmail(email) {
        if (!this.validateEmail(email)) {
            return { isValid: false, message: 'Please enter a valid email address' };
        }
        return { isValid: true };
    }

    // Contact form validation
    validateContactForm(formData) {
        const errors = {};

        if (!this.validateRequired(formData.name)) {
            errors.name = 'Name is required';
        }

        if (!this.validateEmail(formData.email)) {
            errors.email = 'Please enter a valid email address';
        }

        if (!this.validateRequired(formData.message)) {
            errors.message = 'Message is required';
        }

        if (formData.message && formData.message.length < 10) {
            errors.message = 'Message must be at least 10 characters long';
        }

        return {
            isValid: Object.keys(errors).length === 0,
            errors: errors
        };
    }

    // Search form validation
    validateSearchForm(searchTerm) {
        if (!searchTerm || searchTerm.trim().length < 2) {
            return { isValid: false, message: 'Search term must be at least 2 characters long' };
        }
        return { isValid: true };
    }

    // Cart form validation
    validateCartForm(formData) {
        const errors = {};

        if (formData.quantity && (!this.rules.numeric.validate(formData.quantity) || formData.quantity < 1)) {
            errors.quantity = 'Quantity must be a positive number';
        }

        return {
            isValid: Object.keys(errors).length === 0,
            errors: errors
        };
    }

    // Show form-level error
    showFormError(form, message) {
        this.clearFormError(form);
        
        const errorElement = document.createElement('div');
        errorElement.className = 'form-error bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
        errorElement.textContent = message;
        
        form.insertBefore(errorElement, form.firstChild);
    }

    // Clear form-level error
    clearFormError(form) {
        const errorElement = form.querySelector('.form-error');
        if (errorElement) {
            errorElement.remove();
        }
    }

    // Show form success message
    showFormSuccess(form, message) {
        this.clearFormError(form);
        
        const successElement = document.createElement('div');
        successElement.className = 'form-success bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded mb-4';
        successElement.textContent = message;
        
        form.insertBefore(successElement, form.firstChild);
    }

    // Clear form success message
    clearFormSuccess(form) {
        const successElement = form.querySelector('.form-success');
        if (successElement) {
            successElement.remove();
        }
    }

    // Validate and submit form with error handling
    async submitForm(form, submitHandler) {
        if (!this.validateForm(form)) {
            return false;
        }

        try {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            await submitHandler(data);
            return true;
        } catch (error) {
            if (window.ErrorHandler) {
                window.ErrorHandler.handleFormValidationErrors(error.errors || {});
            }
            return false;
        }
    }

    // Add custom validation rule
    addRule(name, rule) {
        this.rules[name] = rule;
    }

    // Remove custom validation rule
    removeRule(name) {
        delete this.rules[name];
    }
}

// Create global instance
window.FormValidator = new FormValidator();

// Export for external use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FormValidator;
}
