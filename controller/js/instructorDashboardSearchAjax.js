document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('quizSearch');
    const filterTags = document.querySelectorAll('.filter_group .tag');
    const tableRows = document.querySelectorAll('.table_row');
    const emptyState = document.querySelector('.empty_state');

    let activeStatus = 'all';   // 'all', 'draft', 'published'
    const rows = Array.from(tableRows);

    //Live search
    searchInput.addEventListener('input', applyFilters);

    //Status filter tags
    filterTags.forEach(tag => {
        tag.addEventListener('click', () => {
            filterTags.forEach(t => t.classList.remove('active'));
            tag.classList.add('active');
            activeStatus = tag.classList.contains('all') ? 'all' : tag.classList.contains('draft') ? 'draft' : 'published';
            applyFilters();
        });
    });

    // Filter logic 
    function applyFilters() {
        const searchTerm = searchInput.value.trim().toLowerCase();
        let visibleCount = 0;

        rows.forEach(row => {
            const title  = row.dataset.title;
            const status = row.dataset.status;
            const matchSearch  = searchTerm === '' || title.includes(searchTerm);
            const matchStatus  = activeStatus === 'all' || status === activeStatus;

            if(matchSearch && matchStatus){
                row.style.visibility = '';// visible
                row.style.display = '';// needed to override previous display as none
                visibleCount++;
            } else {
                row.style.visibility = 'collapse';  // hidden, no space taken
                row.style.display = 'none';      // ensure it collapsed
            }
        });

        // Reorder rows so that names starting with the search term appear first
        if (searchTerm !== '') {
            rows.filter(row => row.style.visibility !== 'collapse').sort((a, b) => {
                    const posA = a.dataset.title.indexOf(searchTerm);
                    const posB = b.dataset.title.indexOf(searchTerm);
                    if (posA === -1) return 1;
                    if (posB === -1) return -1;
                    return posA - posB;
                }).forEach(row => row.parentNode.appendChild(row));
        }

        // Toggle empty state message
        if (emptyState) {
            emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    // Initial call
    applyFilters();
});