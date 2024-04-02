document.addEventListener('DOMContentLoaded', function() {
    const searchBarContainer = document.getElementById('searchBarContainer');
    const searchLink = document.getElementById('search'); 
    searchBarContainer.style.display = 'none';
    if (searchLink) {
        searchLink.addEventListener('click', function(event) {
            event.preventDefault();
            searchBarContainer.style.display = searchBarContainer.style.display === 'none' ? 'block' : 'none';
            if (searchBarContainer.style.display === 'block') {
                document.getElementById('searchInput').focus();
            }
        });
    }
});
