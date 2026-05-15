document.addEventListener('DOMContentLoaded', () => {
    const searchInput   = document.getElementById('userSearch');
    const filterTabs    = document.querySelectorAll('.filter-tab');
    const tableBody     = document.getElementById('userTableBody');
    const userCountEl   = document.getElementById('userCount');
    const emptyState    = document.getElementById('emptyState');
    const toastContainer = document.getElementById('toastContainer');

    // All rows (NodeList converted to Array for easy manipulation)
    let allRows = Array.from(tableBody.querySelectorAll('tr[data-user-id]'));
    let activeRole   = 'all';   // 'all' | 'student' | 'instructor'
    let searchTerm   = '';

    /* ─────────────────────────────────────────
       LIVE SEARCH
    ───────────────────────────────────────── */
    searchInput.addEventListener('input', () => {
        searchTerm = searchInput.value.trim().toLowerCase();
        applyFilters();
    });

    /* ─────────────────────────────────────────
       ROLE FILTER TABS
    ───────────────────────────────────────── */
    filterTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            filterTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            activeRole = tab.dataset.role;   // 'all' | 'student' | 'instructor'
            applyFilters();
        });
    });

    /* ─────────────────────────────────────────
       FILTER ENGINE
       Runs search + role filter simultaneously
       and updates the visible row count.
    ───────────────────────────────────────── */
    function applyFilters() {
        let visible = 0;

        allRows.forEach(row => {
            const rowName = row.dataset.name.toLowerCase();
            const rowRole = row.dataset.role;

            const matchSearch = searchTerm === '' || rowName.includes(searchTerm);
            const matchRole   = activeRole === 'all' || rowRole === activeRole;

            if (matchSearch && matchRole) {
                row.classList.remove('hidden-row');
                visible++;
            } else {
                row.classList.add('hidden-row');
            }
        });

        // Update count badge
        if (userCountEl) {
            userCountEl.innerHTML = `Showing <span>${visible}</span> user${visible !== 1 ? 's' : ''}`;
        }

        // Show/hide empty state
        if (emptyState) {
            emptyState.style.display = visible === 0 ? 'block' : 'none';
        }
    }

    /* ─────────────────────────────────────────
       AJAX TOGGLE
    ───────────────────────────────────────── */
    tableBody.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-toggle');
        if (!btn) return;

        const userId   = btn.dataset.userId;
        const row      = btn.closest('tr');

        // Prevent double-click while request is in flight
        if (btn.classList.contains('loading')) return;

        btn.classList.add('loading');

        const formData = new FormData();
        formData.append('user_id', userId);

        fetch('/api/users/toggle', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`Server error: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Toggle failed.');
            }

            const newState      = data.new_state;         // 0 or 1
            const isNowActive   = newState === 1;

            // ── Update status indicator ──────────────────
            const statusCell = row.querySelector('.status-indicator');
            if (statusCell) {
                statusCell.className = `status-indicator ${isNowActive ? 'active-status' : 'suspended-status'}`;
                statusCell.innerHTML = `
                    <span class="status-dot"></span>
                    ${isNowActive ? 'Active' : 'Suspended'}
                `;
            }

            // ── Update button label & style ──────────────
            btn.className  = `btn-toggle ${isNowActive ? 'can-suspend' : 'can-activate'}`;
            btn.innerHTML  = isNowActive
                ? 'Suspend'
                : 'Activate';
            btn.dataset.currentState = newState;

            // ── Update row data attribute ────────────────
            row.dataset.active = newState;

            showToast(
                isNowActive
                    ? `${data.user_name} has been activated.`
                    : `${data.user_name} has been suspended.`,
                isNowActive ? 'success' : 'error'
            );
        })
        .catch(err => {
            showToast(`${err.message}`, 'error');
        })
        .finally(() => {
            btn.classList.remove('loading');
        });
    });

    /* ─────────────────────────────────────────
       TOAST SYSTEM
    ───────────────────────────────────────── */
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        toastContainer.appendChild(toast);

        // Auto-dismiss after 3.5 s
        setTimeout(() => {
            toast.classList.add('fade-out');
            toast.addEventListener('animationend', () => toast.remove());
        }, 3500);
    }

    // Run initial filter to set count on page load
    applyFilters();
});