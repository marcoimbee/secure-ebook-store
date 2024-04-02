document.addEventListener("DOMContentLoaded", function() {
    let bookIdInput = document.querySelector('input[name="bookId"]');
    let CSRFTokenInput = document.querySelector('input[name="HTTP_X_CSRF_TOKEN"]');
    let quantitySelect = document.getElementById('quantity');

    document.getElementById("addToCartButton").addEventListener("click", function() {
        let bookID = bookIdInput.value;
        let quantity = quantitySelect.value;
        console.log(CSRFTokenInput);
        let antiCSRFToken = CSRFTokenInput.value;
        addToCart(bookID, quantity, antiCSRFToken);
    });
});

function addToCart(bookID, quantity, antiCSRFToken) {
    //console.log(bookID, quantity, antiCSRFToken);
    fetch('/cart', {
        method: 'POST',
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
        console.log(response);
        if (!response.ok)
            updateUI(true);
        
        return response.json();
    })
    .then(data => {
        if (Object.keys(data).length === 0 && data.constructor === Object){
            updateUI(true);
        }

        //update UI
        updateUI(false);
    })
    .catch((error) => {
        //update UI
        console.log(error);
        updateUI(true);
    });
}

function updateUI(genericError) {
    console.log(genericError)
    let addToCartButton = document.getElementById('addToCartButton');
    addToCartButton.style.pointerEvents = 'none';

    let newDivRules = new Array();

    let bookContainer = document.getElementsByClassName('book-container');
    bookContainer = bookContainer[0];
    let newDiv = document.createElement('div');
    newDiv.setAttribute('id', 'dynamic');
    let newParagraph = document.createElement('p');

    if(!genericError) {
        newParagraph.textContent = 'Great choice! The book has been added to your cart.';
        newDivRules.push('@keyFrames fadeOut{from{opacity:1;} to{opacity:0;}}');
        newDivRules.push('#dynamic{text-align:center; color:green; font-size: medium; font-weight:300; animation: fadeOut 2s ease-out;}');
    } else {
        newParagraph.textContent = "An error occured, please try again later.";
        newDivRules.push('@keyFrames fadeOut{from{opacity:1;} to{opacity:0;}}');
        newDivRules.push('#dynamic{text-align:center; color:red; font-size: medium; font-weight:300; animation: fadeOut 2s ease-out;}');
    }

    newDiv.appendChild(newParagraph);
    bookContainer.append(newDiv);

    const styleSheet = document.styleSheets[1];     //obtaining a ref to the linked CSS sheet
    
    styleSheet.insertRule(newDivRules[0], styleSheet.cssRules.length);
    styleSheet.insertRule(newDivRules[1], styleSheet.cssRules.length);

    bookContainer.classList.add('animate');

    //remove the new div after 2 seconds
    setTimeout(() => {
        addToCartButton.style.pointerEvents = 'auto';
        newDiv.remove();
    }, 2000); 
}
