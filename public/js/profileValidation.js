function validateUsername(input) {
  const usernamePattern = /^[a-zA-Z0-9_]+$/;
  if (!usernamePattern.test(input.value)) {
    input.setCustomValidity("Username must be alphanumeric with underscores.");
  } else {
    input.setCustomValidity("");
  }
  input.reportValidity();
}

function resetPasswords() {
    newPasswordInput.setCustomValidity("");
    newPasswordInput.reportValidity();
}

function validateForm() {
    if (newPasswordInput.value && !isStrongPassword(newPasswordInput.value)) {
        newPasswordInput.setCustomValidity('Password must contain at least 8 characters, 1 digit, 1 capital letter and 1 special character.');
    } 

    newPasswordInput.reportValidity();
}

function setupListeners() {
    
    username = document.getElementById("username");
    newPasswordInput = document.getElementById('new-password');
    form = document.getElementById('custom-form');

    username.addEventListener("input", function (event) {
      validateUsername(event.target); 
    });

    newPasswordInput.addEventListener('input', function() {
        resetPasswords();
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

let username = null;
let newPasswordInput = null;
let form = null;

document.addEventListener('DOMContentLoaded', function() {
  setupListeners();
});
