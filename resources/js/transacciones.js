document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const rows = document.querySelectorAll('#dataTable tr');

    searchInput.addEventListener('keyup', () => {
        const filter = searchInput.value.toUpperCase();

        rows.forEach(row => {
            const nameCell = row.cells[0].textContent.toUpperCase();
            const duiCell = row.cells[1].textContent.toUpperCase();

            if (nameCell.includes(filter) || duiCell.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
/*jj*/
