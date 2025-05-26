// Form Validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    let isValid = true;
    const inputs = form.querySelectorAll('input[required]');

    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    });

    return isValid;
}

// Password Strength Checker
function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[!@#$%^&*(),.?":{}|<>]+/)) strength++;

    return strength;
}

// Show Password Strength Indicator
function updatePasswordStrength(passwordId, strengthId) {
    const password = document.getElementById(passwordId);
    const strengthIndicator = document.getElementById(strengthId);
    
    if (!password || !strengthIndicator) return;

    password.addEventListener('input', function() {
        const strength = checkPasswordStrength(this.value);
        let strengthText = '';
        let strengthClass = '';

        switch(strength) {
            case 0:
            case 1:
                strengthText = 'อ่อน';
                strengthClass = 'text-danger';
                break;
            case 2:
            case 3:
                strengthText = 'ปานกลาง';
                strengthClass = 'text-warning';
                break;
            case 4:
            case 5:
                strengthText = 'แข็งแรง';
                strengthClass = 'text-success';
                break;
        }

        strengthIndicator.textContent = `ความปลอดภัยรหัสผ่าน: ${strengthText}`;
        strengthIndicator.className = strengthClass;
    });
}

// Initialize Bootstrap Tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}); 