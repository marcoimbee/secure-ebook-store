function validateAddress(target) {
    if (target.value.length > 60) {
      target.setCustomValidity("Address too long.");
    } else {
      target.setCustomValidity("");
    }
    target.reportValidity();
}

function validateHouseNumber(target) {
    if (target.value.length > 10 || !/^[a-zA-Z0-9]+$/.test(target.value)) {
      target.setCustomValidity("House number must be shorter than 10 and contain alphanumeric characters only.");
    } else {
      target.setCustomValidity("");
    }
    target.reportValidity();
}

function validateZipCode(target) {
    if (!/^[0-9]{5}$/.test(target.value)) {
      target.setCustomValidity("Zip Code must be a 5 digit number.");
    } else {
      target.setCustomValidity("");
    }
    target.reportValidity();
}

function validateCity(target) {
    if (target.value.length > 30 || !/^[a-zA-Z\s]+$/.test(target.value)) {
      target.setCustomValidity("City must be shorter than 30 and contain alphanumeric characters only.");
    } else {
      target.setCustomValidity("");
    }
    target.reportValidity();
}

function setupListeners() {
    const address = document.getElementById('address');
    const houseNumber = document.getElementById('houseNumber');
    const zipCode = document.getElementById('zipCode');
    const city = document.getElementById('city');

    address.addEventListener("input", function (event) {
    validateAddress(event.target); 
    });

    houseNumber.addEventListener('input', function(event) {
        validateHouseNumber(event.target);
    });

    zipCode.addEventListener('input', function(event) {
        validateZipCode(event.target);
    });

    city.addEventListener('input', function(event) {
        validateCity(event.target);
    });
}

document.addEventListener('DOMContentLoaded', function() {
  setupListeners();
});
