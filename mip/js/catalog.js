document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const query = searchInput.value.trim();
            if (query.length > 0 && query.length < 2) {
                e.preventDefault();
                alert('Введите минимум 2 символа для поиска');
                searchInput.focus();
            }
        });
    }
    
    document.querySelectorAll('.btn-order').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
});