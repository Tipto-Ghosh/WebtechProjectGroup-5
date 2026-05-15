document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("userSearch");
    const filterTabs = document.querySelectorAll(".filter-tab");
    const tableBody = document.getElementById("userTableBody");
    const userCountEl = document.getElementById("userCount");
    const emptyState = document.getElementById("emptyState");
    const toastContainer = document.getElementById("toastContainer");

    let allRows = Array.from(tableBody.querySelectorAll("tr[data-user-id]"));
    let activeRole = "all";
    let searchTerm = "";

    searchInput.addEventListener("input", () => {
        searchTerm = searchInput.value.trim().toLowerCase();
        applyFilters();
    });

    filterTabs.forEach(tab => {
        tab.addEventListener("click", () => {
            filterTabs.forEach(t => t.classList.remove("active"));
            tab.classList.add("active");
            activeRole = tab.dataset.role;
            applyFilters();
        });
    });

    function applyFilters() {
        let visible = 0;
        let matchingRows = [];

        // First pass: filter rows by role and search term
        allRows.forEach(row => {
            const rowName = row.dataset.name;            
            const rowRole = row.dataset.role;
            // Role filter
            const matchRole = activeRole === "all" || rowRole === activeRole;
            if(!matchRole){
                row.classList.add("hidden-row");
                return;
            }

            // Search filter
            const matchSearch = searchTerm === "" || rowName.includes(searchTerm);
            if (matchSearch) {
                row.classList.remove("hidden-row");
                matchingRows.push(row);
                visible++;
            } else {
                row.classList.add("hidden-row");
            }
        });
        if(searchTerm !== ""){
            matchingRows.sort((a, b) => {
                const posA = a.dataset.name.indexOf(searchTerm);
                const posB = b.dataset.name.indexOf(searchTerm);
                return posA - posB;   
            });
        }
        matchingRows.forEach(row => {tableBody.appendChild(row);});
        // Update the count badge
        if(userCountEl){
            userCountEl.innerHTML = `Showing <span>${visible}</span> user${visible !== 1 ? "s" : ""}`;
        }
        if(emptyState){
            emptyState.style.display = visible === 0 ? "block" : "none";
        }
    }
    tableBody.addEventListener("click", (e) => {
        const btn = e.target.closest(".btn-toggle");
        if(!btn){
            return;
        }

        const userId = btn.dataset.userId;
        const row = btn.closest("tr");
        if(btn.classList.contains("loading")){
            return;
        }
        btn.classList.add("loading");
        const formData = new FormData();
        formData.append("user_id", userId);
        fetch("adminManageUser.php", {
            method: "POST",
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            },
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error(`Server error: ${res.status}`);
            return res.json();
        })
        .then(data => {
            if (!data.success) throw new Error(data.message || "Toggle failed.");
            const isNowActive = data.new_state === 1;
            // Update status indicator
            const statusCell = row.querySelector(".status-indicator");
            if (statusCell) {
                statusCell.className = `status-indicator ${isNowActive ? "active-status" : "suspended-status"}`;
                statusCell.innerHTML = `
                    <span class="status-dot"></span>
                    ${isNowActive ? "Active" : "Suspended"}
                `;
            }
            // Update button
            btn.className  = `btn-toggle ${isNowActive ? "can-suspend" : "can-activate"}`;
            btn.innerHTML  = isNowActive ? "Suspend" : "Activate";
            btn.dataset.currentState = data.new_state;
            // Update row data attribute
            row.dataset.active = data.new_state;
            showToast(data.message, isNowActive ? "success" : "error");
        })
        .catch(err => {
            showToast(err.message, "error");
        })
        .finally(() => {
            btn.classList.remove("loading");
        });
    });

    function showToast(message, type = "success") {
        const toast = document.createElement("div");
        toast.className = `toast ${type}`;
        toast.textContent = message;
        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.classList.add("fade-out");
            toast.addEventListener("animationend", () => toast.remove());
        }, 3500);
    }

    // Initial filter to set count on page load
    applyFilters();
});