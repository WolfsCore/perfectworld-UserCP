/**
 * UCP Main JavaScript
 * Includes reCAPTCHA v3, form validation, and UI interactions
 */

// UCP Configuration
const UCP = {
    config: {
        apiUrl: 'php/api.php',
        recaptchaSiteKey: '6LfYour_reCAPTCHA_Site_Key_Here',
        defaultLanguage: 'de',
        maxFileSize: 5 * 1024 * 1024, // 5MB
        allowedFileTypes: ['image/jpeg', 'image/png', 'image/gif'],
        sessionTimeout: 30 * 60 * 1000, // 30 minutes
        debounceDelay: 300,
        animationDuration: 300
    },
    
    // Current language
    currentLanguage: localStorage.getItem('ucp_language') || 'de',
    
    // Session data
    session: {
        user: null,
        lastActivity: Date.now(),
        timeout: null
    },
    
    // reCAPTCHA instance
    recaptcha: null,
    
    // Form validation rules
    validationRules: {
        username: {
            required: true,
            minLength: 3,
            maxLength: 20,
            pattern: /^[a-zA-Z0-9_]+$/,
            message: 'validation_username_format'
        },
        email: {
            required: true,
            pattern: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
            message: 'validation_email'
        },
        password: {
            required: true,
            minLength: 8,
            pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/,
            message: 'validation_password_strength'
        },
        passwordConfirm: {
            required: true,
            matchField: 'password',
            message: 'validation_passwords_match'
        }
    }
};

// Utility Functions
const Utils = {
    // Get translated text
    t: function(key) {
        return window.translations && window.translations[key] ? window.translations[key] : key;
    },
    
    // Debounce function
    debounce: function(func, delay) {
        let timeoutId;
        return function(...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    },
    
    // Sanitize HTML
    sanitizeHtml: function(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    },
    
    // Format date
    formatDate: function(date) {
        return new Date(date).toLocaleDateString(UCP.currentLanguage, {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    },
    
    // Format time
    formatTime: function(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const remainingSeconds = seconds % 60;
        
        if (hours > 0) {
            return `${hours}h ${minutes}m ${remainingSeconds}s`;
        } else if (minutes > 0) {
            return `${minutes}m ${remainingSeconds}s`;
        } else {
            return `${remainingSeconds}s`;
        }
    },
    
    // Generate random string
    generateRandomString: function(length) {
        const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = '';
        for (let i = 0; i < length; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    },
    
    // Check if element is in viewport
    isInViewport: function(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    },
    
    // Smooth scroll to element
    scrollToElement: function(element, offset = 0) {
        const targetPosition = element.offsetTop - offset;
        window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
        });
    }
};

// API Functions
const API = {
    // Make API request
    request: async function(endpoint, data = {}, method = 'POST') {
        try {
            const response = await fetch(`${UCP.config.apiUrl}/${endpoint}`, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: method === 'GET' ? null : JSON.stringify(data)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },
    
    // User registration
    register: async function(userData) {
        return await this.request('register', userData);
    },
    
    // User login
    login: async function(credentials) {
        return await this.request('login', credentials);
    },
    
    // User logout
    logout: async function() {
        return await this.request('logout', {});
    },
    
    // Get user profile
    getProfile: async function() {
        return await this.request('profile', {}, 'GET');
    },
    
    // Update user profile
    updateProfile: async function(profileData) {
        return await this.request('profile', profileData, 'PUT');
    },
    
    // Get characters
    getCharacters: async function() {
        return await this.request('characters', {}, 'GET');
    },
    
    // Create character
    createCharacter: async function(characterData) {
        return await this.request('characters', characterData);
    },
    
    // Delete character
    deleteCharacter: async function(characterId) {
        return await this.request('characters', { id: characterId }, 'DELETE');
    },
    
    // Verify email
    verifyEmail: async function(token) {
        return await this.request('verify-email', { token });
    },
    
    // Reset password
    resetPassword: async function(email) {
        return await this.request('reset-password', { email });
    },
    
    // Get server status
    getServerStatus: async function() {
        return await this.request('server-status', {}, 'GET');
    }
};

// reCAPTCHA Functions
const ReCaptcha = {
    // Initialize reCAPTCHA
    init: function() {
        if (typeof grecaptcha !== 'undefined') {
            grecaptcha.ready(() => {
                UCP.recaptcha = grecaptcha;
                console.log('reCAPTCHA initialized');
            });
        } else {
            console.warn('reCAPTCHA not loaded');
        }
    },
    
    // Execute reCAPTCHA
    execute: async function(action) {
        if (!UCP.recaptcha) {
            throw new Error('reCAPTCHA not initialized');
        }
        
        try {
            const token = await UCP.recaptcha.execute(UCP.config.recaptchaSiteKey, { action });
            return token;
        } catch (error) {
            console.error('reCAPTCHA error:', error);
            throw error;
        }
    },
    
    // Verify reCAPTCHA token
    verify: async function(token, action) {
        try {
            const response = await API.request('verify-recaptcha', {
                token: token,
                action: action
            });
            
            return response.success && response.score >= 0.5;
        } catch (error) {
            console.error('reCAPTCHA verification error:', error);
            return false;
        }
    }
};

// Form Validation
const Validation = {
    // Validate field
    validateField: function(field, value) {
        const rules = UCP.validationRules[field];
        if (!rules) return { valid: true };
        
        const errors = [];
        
        // Required check
        if (rules.required && (!value || value.trim() === '')) {
            errors.push(Utils.t('validation_required'));
        }
        
        // Skip other validations if value is empty and not required
        if (!value && !rules.required) {
            return { valid: true };
        }
        
        // Length checks
        if (rules.minLength && value.length < rules.minLength) {
            errors.push(Utils.t('validation_min_length').replace('%d', rules.minLength));
        }
        
        if (rules.maxLength && value.length > rules.maxLength) {
            errors.push(Utils.t('validation_max_length').replace('%d', rules.maxLength));
        }
        
        // Pattern check
        if (rules.pattern && !rules.pattern.test(value)) {
            errors.push(Utils.t(rules.message));
        }
        
        // Match field check
        if (rules.matchField) {
            const matchValue = document.querySelector(`[name="${rules.matchField}"]`);
            if (matchValue && value !== matchValue.value) {
                errors.push(Utils.t(rules.message));
            }
        }
        
        return {
            valid: errors.length === 0,
            errors: errors
        };
    },
    
    // Validate form
    validateForm: function(formData) {
        const errors = {};
        let isValid = true;
        
        for (const [field, value] of Object.entries(formData)) {
            const validation = this.validateField(field, value);
            if (!validation.valid) {
                errors[field] = validation.errors;
                isValid = false;
            }
        }
        
        return { valid: isValid, errors: errors };
    },
    
    // Show field error
    showFieldError: function(fieldName, errors) {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (!field) return;
        
        // Remove existing errors
        this.clearFieldError(fieldName);
        
        // Add error class
        field.classList.add('error');
        
        // Create error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'form-error';
        errorDiv.textContent = errors[0]; // Show first error
        
        // Insert after field
        field.parentNode.insertBefore(errorDiv, field.nextSibling);
    },
    
    // Clear field error
    clearFieldError: function(fieldName) {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (!field) return;
        
        field.classList.remove('error');
        
        const errorDiv = field.parentNode.querySelector('.form-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    },
    
    // Clear all errors
    clearAllErrors: function() {
        document.querySelectorAll('.form-error').forEach(error => error.remove());
        document.querySelectorAll('.error').forEach(field => field.classList.remove('error'));
    }
};

// Password Strength Checker
const PasswordStrength = {
    // Check password strength
    check: function(password) {
        let score = 0;
        let feedback = [];
        
        // Length check
        if (password.length >= 8) score += 1;
        else feedback.push('At least 8 characters');
        
        // Lowercase check
        if (/[a-z]/.test(password)) score += 1;
        else feedback.push('Include lowercase letters');
        
        // Uppercase check
        if (/[A-Z]/.test(password)) score += 1;
        else feedback.push('Include uppercase letters');
        
        // Number check
        if (/\d/.test(password)) score += 1;
        else feedback.push('Include numbers');
        
        // Special character check
        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) score += 1;
        else feedback.push('Include special characters');
        
        let strength = 'weak';
        if (score >= 4) strength = 'strong';
        else if (score >= 2) strength = 'medium';
        
        return { score, strength, feedback };
    },
    
    // Update password strength indicator
    updateIndicator: function(password, indicatorElement) {
        const strength = this.check(password);
        
        indicatorElement.className = `password-strength ${strength.strength}`;
        indicatorElement.textContent = Utils.t(`password_strength_${strength.strength}`);
    }
};

// UI Functions
const UI = {
    // Show notification
    showNotification: function(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${Utils.sanitizeHtml(message)}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove notification
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, duration);
    },
    
    // Show loading spinner
    showLoading: function(element) {
        const spinner = document.createElement('div');
        spinner.className = 'spinner';
        spinner.innerHTML = '<div class="spinner-inner"></div>';
        
        element.appendChild(spinner);
        element.classList.add('loading');
    },
    
    // Hide loading spinner
    hideLoading: function(element) {
        const spinner = element.querySelector('.spinner');
        if (spinner) {
            spinner.remove();
        }
        element.classList.remove('loading');
    },
    
    // Show modal
    showModal: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    },
    
    // Hide modal
    hideModal: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    },
    
    // Toggle element visibility
    toggle: function(element) {
        element.classList.toggle('hidden');
    },
    
    // Animate counter
    animateCounter: function(element, start, end, duration = 1000) {
        const startTime = performance.now();
        const difference = end - start;
        
        const updateCounter = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const currentCount = Math.floor(start + (difference * progress));
            element.textContent = currentCount.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            }
        };
        
        requestAnimationFrame(updateCounter);
    }
};

// Language Functions
const Language = {
    // Set language
    set: function(lang) {
        UCP.currentLanguage = lang;
        localStorage.setItem('ucp_language', lang);
        
        // Update page content
        this.updatePageContent();
        
        // Update URL
        const url = new URL(window.location);
        url.searchParams.set('lang', lang);
        window.history.replaceState({}, '', url);
    },
    
    // Load language file
    load: async function(lang) {
        try {
            const response = await fetch(`lang/${lang}.json`);
            const translations = await response.json();
            window.translations = translations;
            return translations;
        } catch (error) {
            console.error('Failed to load language file:', error);
            return {};
        }
    },
    
    // Update page content with translations
    updatePageContent: function() {
        document.querySelectorAll('[data-i18n]').forEach(element => {
            const key = element.getAttribute('data-i18n');
            const translation = Utils.t(key);
            
            if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                element.placeholder = translation;
            } else {
                element.textContent = translation;
            }
        });
    }
};

// Session Management
const Session = {
    // Start session
    start: function(userData) {
        UCP.session.user = userData;
        UCP.session.lastActivity = Date.now();
        
        // Set session timeout
        this.resetTimeout();
        
        // Store session data
        localStorage.setItem('ucp_session', JSON.stringify(UCP.session));
    },
    
    // End session
    end: function() {
        UCP.session.user = null;
        UCP.session.lastActivity = Date.now();
        
        // Clear timeout
        if (UCP.session.timeout) {
            clearTimeout(UCP.session.timeout);
        }
        
        // Remove session data
        localStorage.removeItem('ucp_session');
        
        // Redirect to login
        window.location.href = 'login.html';
    },
    
    // Reset session timeout
    resetTimeout: function() {
        if (UCP.session.timeout) {
            clearTimeout(UCP.session.timeout);
        }
        
        UCP.session.timeout = setTimeout(() => {
            UI.showNotification(Utils.t('session_expired'), 'warning');
            this.end();
        }, UCP.config.sessionTimeout);
    },
    
    // Check if user is logged in
    isLoggedIn: function() {
        return UCP.session.user !== null;
    },
    
    // Get current user
    getCurrentUser: function() {
        return UCP.session.user;
    },
    
    // Update last activity
    updateActivity: function() {
        UCP.session.lastActivity = Date.now();
        this.resetTimeout();
    }
};

// Form Handlers
const Forms = {
    // Handle registration form
    handleRegister: async function(formData) {
        try {
            // Validate form
            const validation = Validation.validateForm(formData);
            if (!validation.valid) {
                Object.entries(validation.errors).forEach(([field, errors]) => {
                    Validation.showFieldError(field, errors);
                });
                return;
            }
            
            // Execute reCAPTCHA
            const recaptchaToken = await ReCaptcha.execute('register');
            
            // Verify reCAPTCHA
            const recaptchaValid = await ReCaptcha.verify(recaptchaToken, 'register');
            if (!recaptchaValid) {
                UI.showNotification(Utils.t('validation_captcha'), 'error');
                return;
            }
            
            // Add reCAPTCHA token to form data
            formData.recaptcha_token = recaptchaToken;
            
            // Submit registration
            const response = await API.register(formData);
            
            if (response.success) {
                UI.showNotification(Utils.t('register_success'), 'success');
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 2000);
            } else {
                UI.showNotification(response.message || Utils.t('register_error'), 'error');
            }
        } catch (error) {
            console.error('Registration error:', error);
            UI.showNotification(Utils.t('register_error'), 'error');
        }
    },
    
    // Handle login form
    handleLogin: async function(formData) {
        try {
            // Execute reCAPTCHA
            const recaptchaToken = await ReCaptcha.execute('login');
            
            // Verify reCAPTCHA
            const recaptchaValid = await ReCaptcha.verify(recaptchaToken, 'login');
            if (!recaptchaValid) {
                UI.showNotification(Utils.t('validation_captcha'), 'error');
                return;
            }
            
            // Add reCAPTCHA token to form data
            formData.recaptcha_token = recaptchaToken;
            
            // Submit login
            const response = await API.login(formData);
            
            if (response.success) {
                Session.start(response.user);
                UI.showNotification(Utils.t('login_success'), 'success');
                setTimeout(() => {
                    window.location.href = 'dashboard.html';
                }, 1000);
            } else {
                UI.showNotification(response.message || Utils.t('login_error'), 'error');
            }
        } catch (error) {
            console.error('Login error:', error);
            UI.showNotification(Utils.t('login_error'), 'error');
        }
    }
};

// Event Listeners
const EventListeners = {
    // Initialize all event listeners
    init: function() {
        // Form submissions
        document.addEventListener('submit', this.handleFormSubmit);
        
        // Input validation
        document.addEventListener('input', this.handleInputChange);
        
        // Language selector
        document.addEventListener('click', this.handleLanguageSelector);
        
        // Modal controls
        document.addEventListener('click', this.handleModalControls);
        
        // Navigation
        document.addEventListener('click', this.handleNavigation);
        
        // User activity tracking
        document.addEventListener('mousemove', this.handleUserActivity);
        document.addEventListener('keydown', this.handleUserActivity);
    },
    
    // Handle form submission
    handleFormSubmit: function(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        // Clear previous errors
        Validation.clearAllErrors();
        
        // Show loading
        UI.showLoading(form);
        
        // Handle different form types
        if (form.id === 'register-form') {
            Forms.handleRegister(data).finally(() => UI.hideLoading(form));
        } else if (form.id === 'login-form') {
            Forms.handleLogin(data).finally(() => UI.hideLoading(form));
        }
    },
    
    // Handle input changes
    handleInputChange: Utils.debounce(function(event) {
        const input = event.target;
        const fieldName = input.name;
        const value = input.value;
        
        // Real-time validation
        if (UCP.validationRules[fieldName]) {
            const validation = Validation.validateField(fieldName, value);
            if (!validation.valid) {
                Validation.showFieldError(fieldName, validation.errors);
            } else {
                Validation.clearFieldError(fieldName);
            }
        }
        
        // Password strength indicator
        if (fieldName === 'password') {
            const indicator = document.querySelector('.password-strength-indicator');
            if (indicator) {
                PasswordStrength.updateIndicator(value, indicator);
            }
        }
    }, UCP.config.debounceDelay),
    
    // Handle language selector
    handleLanguageSelector: function(event) {
        if (event.target.matches('.language-selector-toggle')) {
            const dropdown = event.target.nextElementSibling;
            dropdown.classList.toggle('show');
        }
        
        if (event.target.matches('.language-option')) {
            const lang = event.target.dataset.lang;
            Language.set(lang);
            
            // Close dropdown
            const dropdown = event.target.closest('.language-dropdown');
            dropdown.classList.remove('show');
        }
    },
    
    // Handle modal controls
    handleModalControls: function(event) {
        if (event.target.matches('.modal-close') || event.target.matches('.modal-backdrop')) {
            const modal = event.target.closest('.modal');
            if (modal) {
                UI.hideModal(modal.id);
            }
        }
        
        if (event.target.matches('[data-modal]')) {
            const modalId = event.target.dataset.modal;
            UI.showModal(modalId);
        }
    },
    
    // Handle navigation
    handleNavigation: function(event) {
        if (event.target.matches('.nav-link')) {
            // Update active navigation
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            event.target.classList.add('active');
        }
    },
    
    // Handle user activity
    handleUserActivity: Utils.debounce(function() {
        if (Session.isLoggedIn()) {
            Session.updateActivity();
        }
    }, 1000)
};

// Initialize UCP
document.addEventListener('DOMContentLoaded', function() {
    // Initialize reCAPTCHA
    ReCaptcha.init();
    
    // Initialize event listeners
    EventListeners.init();
    
    // Load language
    const urlParams = new URLSearchParams(window.location.search);
    const lang = urlParams.get('lang') || UCP.currentLanguage;
    
    Language.load(lang).then(() => {
        Language.set(lang);
    });
    
    // Restore session
    const sessionData = localStorage.getItem('ucp_session');
    if (sessionData) {
        try {
            const session = JSON.parse(sessionData);
            if (session.user) {
                Session.start(session.user);
            }
        } catch (error) {
            console.error('Failed to restore session:', error);
            localStorage.removeItem('ucp_session');
        }
    }
    
    // Initialize animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);
    
    // Observe elements for animations
    document.querySelectorAll('.card, .stat-card, .character-card').forEach(element => {
        observer.observe(element);
    });
    
    console.log('UCP initialized successfully');
});

// Export UCP object for external use
window.UCP = UCP; 