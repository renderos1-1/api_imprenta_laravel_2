document.addEventListener('DOMContentLoaded', function() {
    // Add DUI format validation
    const duiInput = document.getElementById('searchDUI');
    if (duiInput) {
        duiInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
            if (value.length > 8) {
                value = value.substr(0, 8) + '-' + value.substr(8, 1);
            }
            e.target.value = value;
        });
    }

    // Handle form submission
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const dui = duiInput.value;
            if (dui && !/^\d{8}-\d$/.test(dui)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El formato del DUI debe ser: 00000000-0'
                });
            }
        });
    }
});
