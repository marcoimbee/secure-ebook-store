document.addEventListener('DOMContentLoaded', function () {
    let checkoutButton = document.getElementById('checkoutButton');
    if (checkoutButton) {
        checkoutButton.addEventListener('click', function() {
            window.location.href = 'shipment';
        });
    }

    let totalPriceElement = document.getElementById('total-price');
    let totalPrice = parseFloat(totalPriceElement.textContent.trim().replace(/[^\d.]/g, '')); // Remove non-numeric characters
    
    if(totalPrice == 0)
        disableCheckoutButton();

    let removeButtons = document.getElementsByClassName("remove-from-cart-button");
    for (let i = 0; i < removeButtons.length; i++) {
        removeButtons[i].addEventListener("click", function(event) {
            let parentElement = event.target.closest('.product-info');

            if (parentElement) {
                parentElement.setAttribute('id', 'clickedElement');

                let bookID = parentElement.querySelector('#bookId').textContent;
                let quantity = parentElement.querySelector('.quantity').textContent.trim();

                let CSRFTokenInput = document.querySelector('input[name="HTTP_X_CSRF_TOKEN"]');
                let antiCSRFToken = CSRFTokenInput.value;

                removeFromCart(bookID, quantity, antiCSRFToken);
            }
        });
    }
});

function removeFromCart(bookID, quantity, antiCSRFToken) {
    fetch('/cart', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': antiCSRFToken
        },
        body: JSON.stringify({
            bookID: bookID,
            quantity: quantity
        }),
    })
    .then(response => {
        if (!response.ok){
            //console.log('Response error: ' + response);
            updateUI(true, null);
        }
        
        return response.json();
    })
    .then(data => {
        if (Object.keys(data).length === 0 && data.constructor === Object){         //last element in cart, the query returns NULL
            updateUI(false, 0);          //0$ total price after last elem has been removed
        }

        //update UI
        updateUI(false, data.newTotalPrice);
    })
    .catch((error) => {
        //console.error('Fetch error: ' + error);

        //update UI
        updateUI(true, null);
    });
}


function updateUI(genericError, newTotalPrice) {
    let clickedElement = document.getElementById('clickedElement');

    if(genericError){               //an error occured
        let newDivRules = new Array();

        let newDiv = document.createElement('div');
        newDiv.setAttribute('id', 'dynamic');
        let newParagraph = document.createElement('p');

        newParagraph.textContent = 'An error occured, please try again later.';
        newDivRules.push('@keyFrames fadeOut{from{opacity:1;} to{opacity:0;}}');
        newDivRules.push('#dynamic{text-align:center; color:red; font-size: medium; font-weight:300; animation: fadeOut 2s ease-out;}');

        newDiv.appendChild(newParagraph);
        clickedElement.append(newDiv);

        const styleSheet = document.styleSheets[1];     //obtaining a ref to the linked CSS sheet
    
        styleSheet.insertRule(newDivRules[0], styleSheet.cssRules.length);
        styleSheet.insertRule(newDivRules[1], styleSheet.cssRules.length);

        clickedElement.classList.add('animate');

        //remove the new div after 2 seconds
        setTimeout(() => {
            newDiv.remove();
        }, 2000);

        clickedElement.removeAttribute('id');
    }else{
        let priceParagraph = document.getElementById('total-price');
        if(newTotalPrice != 0){         //still elements in the cart            
            priceParagraph.innerHTML = '<strong>Total:</strong> ' + newTotalPrice;
        }else{     //set price to 0
            priceParagraph.innerHTML = '<strong>Total:</strong> 0';
            disableCheckoutButton();
        }

        clickedElement.remove();            //remove the book from the displayed list        
    }
}

function disableCheckoutButton(){
    checkoutButton = document.getElementsByClassName('checkout-button')[0].disabled = true;
}
