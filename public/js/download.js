document.addEventListener('DOMContentLoaded', function() {
    let downloadButtons = document.querySelectorAll('.download-ebook-button');
    downloadButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            let token = this.getAttribute('data-token');
            window.location.href = 'download?token=' + encodeURIComponent(token);
        });
    });
});
