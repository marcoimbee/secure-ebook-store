function validateCreditCardNumber(target) {
    if (!target.value)
        target.setCustomValidity("Please enter your credit card number.");
    else if (!/^[0-9]{16}$/.test(target.value))
        target.setCustomValidity("The credit card number must be a 16 digit number.");
    else
        target.setCustomValidity("");

    target.reportValidity();
}

function validateCVV(target) {
    if (!target.value)
        target.setCustomValidity("Please enter your CVV.");
    else if (!/^[0-9]{3}$/.test(target.value))
        target.setCustomValidity("The CVV must be a 3 digit number.");
    else
        target.setCustomValidity("");
    
    target.reportValidity();
}

function validateExpirationDate(target) {
    if (!target.value)
        target.setCustomValidity("Please enter the expiration date of your card.");
    else if (!/^(0[1-9]|1[0-2])\/([0-9]{2})$/.test(target.value))
        target.setCustomValidity("Invalid format. Format must be MM/YY.");
    else
        target.setCustomValidity("");
    
    target.reportValidity();
}

function validateCardHolder(target) {
    if (!target.value)
        target.setCustomValidity("Please enter the card holder name.");
    else if (!/^[a-zA-Z\s'-]+$/.test(target.value))
        target.setCustomValidity("The card holder name must only contain alphabetic characters, apostrophes, and hyphens.");
    else
        target.setCustomValidity("");
    
    target.reportValidity();
}

function setupListeners() {
    const creditCardNumber = document.getElementById('creditCardNumber');
    const cvv = document.getElementById('cvv');
    const expirationDate = document.getElementById('expirationDate');
    const cardHolder = document.getElementById('cardHolder');

    creditCardNumber.addEventListener('input', function(event) {
        validateCreditCardNumber(event.target);
    });

    cvv.addEventListener('input', function(event) {
        validateCVV(event.target);
    });

    expirationDate.addEventListener('input', function(event) {
        validateExpirationDate(event.target);
    });

    cardHolder.addEventListener('input', function(event) {
        validateCardHolder(event.target);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    setupListeners();
});
