function validateUsername(input) {
  const usernamePattern = /^[a-zA-Z0-9_]+$/;
  if (!usernamePattern.test(input.value)) {
    input.setCustomValidity("Username must be alphanumeric with underscores.");
  } else {
    input.setCustomValidity("");
  }
  input.reportValidity();
}

function resetConfirmPasswords() {
    confirmPassword.setCustomValidity("");
    confirmPassword.reportValidity();
}

function resetPassword() {
    passwordInput.setCustomValidity("");
    passwordInput.reportValidity();
}

function validateForm() {
    if (password.value && !isStrongPassword(password.value)) {
        password.setCustomValidity('Password must contain at least 8 characters, 1 digit, 1 capital letter and 1 special character.');
    } 

    if ((confirmPassword.value && passwordInput.value) && (confirmPassword.value !== passwordInput.value)) {
        confirmPassword.setCustomValidity("Passwords do not match");
        passwordInput.setCustomValidity("");
    }

    if (!confirmPassword.value && !passwordInput.value) {
        confirmPassword.setCustomValidity("");
        passwordInput.setCustomValidity("");
    }

    passwordInput.reportValidity();
    confirmPassword.reportValidity();
}

function setupListeners() {
    
    username = document.getElementById("username");
    passwordInput = document.getElementById('password');
    confirmPassword = document.getElementById('confirm_password');
    form = document.getElementById('custom-form');

    username.addEventListener("input", function (event) {
      validateUsername(event.target); 
    });

    password.addEventListener('input', function() {
        resetPassword();
    });
    
    confirmPassword.addEventListener('input', function() {
        resetConfirmPasswords();
    });

    form.addEventListener('submit', function(event) {
        validateForm();
        if (!form.checkValidity()) {
            event.preventDefault(); 
        }
    });
}

function isStrongPassword(password) {
  if (password.length < 8) return false;
  if (!/[A-Z]/.test(password)) return false;
  if (!/[a-z]/.test(password)) return false;
  if (!/\d/.test(password)) return false;
  if (!/[^a-zA-Z0-9]/.test(password)) return false;
  return true;
}

let email = null;
let username = null;
let passwordInput = null;
let confirmPassword = null;
let form = null;

document.addEventListener('DOMContentLoaded', function() {
    setupListeners();
});
