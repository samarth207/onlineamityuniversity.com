// Form Validation and Country Codes
// =================================

// Popular Country Codes (Top 50 countries)
const countryCodes = [
    { code: '+91', country: 'IN', name: 'India' },
    { code: '+1', country: 'US', name: 'United States' },
    { code: '+1', country: 'CA', name: 'Canada' },
    { code: '+44', country: 'GB', name: 'United Kingdom' },
    { code: '+61', country: 'AU', name: 'Australia' },
    { code: '+971', country: 'AE', name: 'UAE' },
    { code: '+65', country: 'SG', name: 'Singapore' },
    { code: '+86', country: 'CN', name: 'China' },
    { code: '+81', country: 'JP', name: 'Japan' },
    { code: '+82', country: 'KR', name: 'South Korea' },
    { code: '+49', country: 'DE', name: 'Germany' },
    { code: '+33', country: 'FR', name: 'France' },
    { code: '+39', country: 'IT', name: 'Italy' },
    { code: '+34', country: 'ES', name: 'Spain' },
    { code: '+7', country: 'RU', name: 'Russia' },
    { code: '+55', country: 'BR', name: 'Brazil' },
    { code: '+52', country: 'MX', name: 'Mexico' },
    { code: '+27', country: 'ZA', name: 'South Africa' },
    { code: '+966', country: 'SA', name: 'Saudi Arabia' },
    { code: '+92', country: 'PK', name: 'Pakistan' },
    { code: '+880', country: 'BD', name: 'Bangladesh' },
    { code: '+94', country: 'LK', name: 'Sri Lanka' },
    { code: '+977', country: 'NP', name: 'Nepal' },
    { code: '+60', country: 'MY', name: 'Malaysia' },
    { code: '+62', country: 'ID', name: 'Indonesia' },
    { code: '+63', country: 'PH', name: 'Philippines' },
    { code: '+66', country: 'TH', name: 'Thailand' },
    { code: '+84', country: 'VN', name: 'Vietnam' },
    { code: '+64', country: 'NZ', name: 'New Zealand' },
    { code: '+974', country: 'QA', name: 'Qatar' },
    { code: '+968', country: 'OM', name: 'Oman' },
    { code: '+965', country: 'KW', name: 'Kuwait' },
    { code: '+973', country: 'BH', name: 'Bahrain' },
    { code: '+20', country: 'EG', name: 'Egypt' },
    { code: '+234', country: 'NG', name: 'Nigeria' },
    { code: '+254', country: 'KE', name: 'Kenya' },
    { code: '+90', country: 'TR', name: 'Turkey' },
    { code: '+98', country: 'IR', name: 'Iran' },
    { code: '+41', country: 'CH', name: 'Switzerland' },
    { code: '+31', country: 'NL', name: 'Netherlands' },
    { code: '+46', country: 'SE', name: 'Sweden' },
    { code: '+47', country: 'NO', name: 'Norway' },
    { code: '+45', country: 'DK', name: 'Denmark' },
    { code: '+48', country: 'PL', name: 'Poland' },
    { code: '+351', country: 'PT', name: 'Portugal' },
    { code: '+30', country: 'GR', name: 'Greece' },
    { code: '+353', country: 'IE', name: 'Ireland' },
    { code: '+358', country: 'FI', name: 'Finland' },
    { code: '+32', country: 'BE', name: 'Belgium' },
    { code: '+43', country: 'AT', name: 'Austria' }
];

// Populate country code dropdowns
function populateCountryDropdowns() {
    const selects = document.querySelectorAll('select[style*="width: 80px"], select[style*="width: 100px"]');
    selects.forEach(select => {
        // Clear existing options
        select.innerHTML = '';
        
        // Add country code options
        countryCodes.forEach(item => {
            const option = document.createElement('option');
            option.value = item.code;
            option.textContent = `${item.country} ${item.code}`;
            if (item.code === '+91') {
                option.selected = true;
            }
            select.appendChild(option);
        });
    });
}

// Validate phone number (10 digits)
function validatePhone(phone) {
    const phoneRegex = /^\d{10}$/;
    return phoneRegex.test(phone.replace(/\s/g, ''));
}

// Validate email
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Validate name (at least 2 characters, only letters and spaces)
function validateName(name) {
    const nameRegex = /^[a-zA-Z\s]{2,}$/;
    return nameRegex.test(name);
}

// Show error message
function showError(input, message) {
    let errorDiv = input.parentElement.querySelector('.error-message');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.color = '#ef4444';
        errorDiv.style.fontSize = '0.75rem';
        errorDiv.style.marginTop = '4px';
        input.parentElement.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
    input.style.borderColor = '#ef4444';
}

// Clear error message
function clearError(input) {
    const errorDiv = input.parentElement.querySelector('.error-message');
    if (errorDiv) {
        errorDiv.remove();
    }
    input.style.borderColor = '#d1d5db';
}

// Setup form validation
function setupFormValidation(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    // Get form elements
    const phoneInput = form.querySelector('input[type="tel"]');
    const nameInput = form.querySelector('input[type="text"]');
    const emailInput = form.querySelector('input[type="email"]');
    const checkbox = form.querySelector('input[type="checkbox"]');
    
    // Real-time validation
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            // Allow only numbers
            this.value = this.value.replace(/\D/g, '');
            // Limit to 10 digits
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
            if (this.value.length > 0) {
                if (validatePhone(this.value)) {
                    clearError(this);
                } else if (this.value.length === 10) {
                    showError(this, 'Please enter a valid 10-digit phone number');
                }
            } else {
                clearError(this);
            }
        });
    }
    
    if (nameInput) {
        nameInput.addEventListener('blur', function() {
            if (this.value.trim()) {
                if (validateName(this.value)) {
                    clearError(this);
                } else {
                    showError(this, 'Please enter a valid name (letters only, minimum 2 characters)');
                }
            }
        });
        
        nameInput.addEventListener('input', function() {
            clearError(this);
        });
    }
    
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            if (this.value.trim()) {
                if (validateEmail(this.value)) {
                    clearError(this);
                } else {
                    showError(this, 'Please enter a valid email address');
                }
            }
        });
        
        emailInput.addEventListener('input', function() {
            clearError(this);
        });
    }
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        let isValid = true;
        
        // Validate phone
        if (phoneInput && !validatePhone(phoneInput.value)) {
            showError(phoneInput, 'Please enter a valid 10-digit phone number');
            isValid = false;
        }
        
        // Validate name
        if (nameInput && !validateName(nameInput.value)) {
            showError(nameInput, 'Please enter a valid name (letters only, minimum 2 characters)');
            isValid = false;
        }
        
        // Validate email
        if (emailInput && !validateEmail(emailInput.value)) {
            showError(emailInput, 'Please enter a valid email address');
            isValid = false;
        }
        
        // Validate checkbox
        if (checkbox && !checkbox.checked) {
            if (window.NotificationSystem) {
                NotificationSystem.warning('Please accept the consent to proceed. We respect your privacy and will only use your information to provide you with program details.');
            } else {
                alert('Please accept the consent to proceed. We respect your privacy and will only use your information to provide you with program details.');
            }
            isValid = false;
        }
        
        if (isValid) {
            submitForm(form);
        }
    });
}

// Submit form
async function submitForm(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.textContent = 'Submitting...';
    submitBtn.disabled = true;
    
    try {
        const phoneInput = form.querySelector('input[type="tel"]');
        // Get name input - prefer named input, then writable text inputs
        let nameInput = form.querySelector('input[name="fullName"]') || 
                        form.querySelector('input[type="text"]:not([readonly])');
        const emailInput = form.querySelector('input[type="email"]');
        const countrySelect = form.querySelector('select');
        const checkbox = form.querySelector('input[type="checkbox"]');
        
        // Get course/program - check for course dropdown first, then hidden program field, then any select with course values, then page title
        let course = 'General';
        const courseSelect = form.querySelector('select[name="course"]');
        const selectedProgramInput = form.querySelector('input[name="program"]');
        
        if (courseSelect && courseSelect.value) {
            course = courseSelect.value;
        } else if (selectedProgramInput && selectedProgramInput.value) {
            course = selectedProgramInput.value;
        } else {
            // Try to find any select that has MBA/BBA/MCA options (course dropdown without name)
            const allSelects = form.querySelectorAll('select');
            for (let sel of allSelects) {
                const options = Array.from(sel.options).map(o => o.value);
                if (options.some(o => ['MBA', 'BBA', 'MCA', 'BCA'].includes(o))) {
                    course = sel.value || 'General';
                    break;
                }
            }
            // Fallback to page title
            if (course === 'General') {
                const pageTitle = document.title.split('|')[0].trim();
                if (pageTitle && pageTitle !== 'General') {
                    course = pageTitle;
                }
            }
        }
        
        const formData = {
            formType: form.id.replace('Form', '').replace('hero', '').replace('Apply', 'apply').replace('Enquire', 'enquire').replace('Brochure', 'brochure').toLowerCase() || 'apply',
            name: nameInput?.value || '',
            phone: phoneInput?.value || '',
            email: emailInput?.value || '',
            consent: checkbox?.checked || false,
            course: course
        };
        
        // Normalize formType to match database enum values
        if (formData.formType.includes('apply')) formData.formType = 'apply';
        else if (formData.formType.includes('enquire')) formData.formType = 'enquire';
        else if (formData.formType.includes('brochure') || formData.formType.includes('download')) formData.formType = 'brochure';
        else formData.formType = 'apply';
        
        console.log('Form data:', formData);
        
        // Make API call to submit form
        const response = await fetch('submit-form.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            if (window.NotificationSystem) {
                NotificationSystem.success('Thank you! Your application has been submitted successfully. Redirecting...');
            }
            sessionStorage.setItem('formSubmitted', 'true');
            setTimeout(() => {
                window.location.href = 'thank-you.html';
            }, 1500);
        } else {
            throw new Error(result.message || 'Server returned error');
        }
    } catch (error) {
        console.error('Form submission error:', error);
        if (window.NotificationSystem) {
            NotificationSystem.error('There was a connection error. Please check your internet connection and try again, or contact our admissions team at +91 8920785477.');
        } else {
            alert('There was a connection error. Please check your internet connection and try again, or contact our admissions team at +91 8920785477.');
        }
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Populate country dropdowns
    populateCountryDropdowns();
    
    // Setup validation for modal forms (hero forms are handled by script.js/mba.js with button state management)
    const formIds = ['applyNowForm', 'enquireNowForm', 'downloadBrochureForm', 'heroApplyForm'];
    formIds.forEach(formId => setupFormValidation(formId));
});
