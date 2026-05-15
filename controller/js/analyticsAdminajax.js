document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("quizSearch");
    const instructorFilter = document.getElementById("instructorFilter");
    const statusTabs = document.querySelectorAll(".status-tab");
    const tableBody = document.getElementById("quizTableBody");
    const quizCount = document.getElementById("quizCount");
    const emptyState = document.getElementById("quizEmptyState");
    const detailPanel = document.getElementById("analyticsDetail");
    const toastContainer = document.getElementById("toastContainer");
    const sidebar = document.getElementById("sidebar");
    const mainContent = document.getElementById("main-content");
    const hamburger = document.getElementById("btn-hamburger");

    if (!tableBody || !detailPanel) {
        return;
    }

    const rows = Array.from(tableBody.querySelectorAll("tr[data-quiz-id]"));
    let activeStatus = "all";

    if (hamburger && sidebar && mainContent) {
        hamburger.addEventListener("click", () => {
            sidebar.classList.toggle("sidebar--collapsed");
            mainContent.classList.toggle("main--expanded");
        });
    }

    function escapeHtml(value) {
        return String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function applyFilters() {
        const term = searchInput ? searchInput.value.trim().toLowerCase() : "";
        const instructorId = instructorFilter ? instructorFilter.value : "all";
        let visible = 0;

        rows.forEach(row => {
            const matchesSearch = term === "" || (row.dataset.search || "").includes(term);
            const matchesInstructor = instructorId === "all" || row.dataset.instructorId === instructorId;
            const matchesStatus = activeStatus === "all" || row.dataset.status === activeStatus;
            const show = matchesSearch && matchesInstructor && matchesStatus;

            row.classList.toggle("hidden-row", !show);
            if (show) {
                visible++;
            }
        });

        if (quizCount) {
            quizCount.innerHTML = `Showing <span>${visible}</span> quiz${visible !== 1 ? "zes" : ""}`;
        }

        if (emptyState) {
            emptyState.classList.toggle("hidden", visible !== 0);
        }
    }

    function setActiveRow(quizId) {
        rows.forEach(row => {
            row.classList.toggle("active-row", row.dataset.quizId === String(quizId));
        });
    }

    function showToast(message, type = "success") {
        if (!toastContainer) {
            return;
        }

        const toast = document.createElement("div");
        toast.className = `toast ${type}`;
        toast.textContent = message;
        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.classList.add("fade-out");
            toast.addEventListener("transitionend", () => toast.remove(), { once: true });
            setTimeout(() => toast.remove(), 300);
        }, 3200);
    }

    function renderDetail(data) {
        const quiz = data.quiz;
        const summary = data.summary;
        const attempts = data.attempts || [];

        const rowsHtml = attempts.length
            ? attempts.map(attempt => `
                <tr>
                    <td>#${escapeHtml(attempt.row_number)}</td>
                    <td>
                        <div class="student-name">${escapeHtml(attempt.student_name)}</div>
                        <div class="student-email">${escapeHtml(attempt.student_email)}</div>
                    </td>
                    <td>${escapeHtml(attempt.score_display)}</td>
                    <td>${escapeHtml(attempt.percent_display)}</td>
                    <td>${escapeHtml(attempt.duration_display)}</td>
                    <td>${escapeHtml(attempt.completed_at_display)}</td>
                    <td><span class="result-badge ${escapeHtml(attempt.status_class)}">${escapeHtml(attempt.status_label)}</span></td>
                </tr>
            `).join("")
            : `<tr class="empty-row"><td colspan="7">No completed attempts found for this quiz.</td></tr>`;

        detailPanel.dataset.selectedQuiz = quiz.id;

        detailPanel.innerHTML = `
            <div class="detail-header">
                <div>
                    <p class="eyebrow">Selected Quiz</p>
                    <h2>${escapeHtml(quiz.title)}</h2>
                    <p>${escapeHtml(quiz.instructor_name)} &bull; ${escapeHtml(quiz.instructor_email)}</p>
                </div>
                <span class="quiz-status ${escapeHtml(quiz.status)}">${escapeHtml(quiz.status)}</span>
            </div>

            <div class="summary-strip">
                <div class="summary-cell">
                    <span>${escapeHtml(summary.attempt_count)}</span>
                    <p>Attempts</p>
                </div>
                <div class="summary-cell">
                    <span>${escapeHtml(summary.average)}</span>
                    <p>Average</p>
                </div>
                <div class="summary-cell">
                    <span>${escapeHtml(summary.highest)}</span>
                    <p>Highest</p>
                </div>
                <div class="summary-cell">
                    <span>${escapeHtml(summary.lowest)}</span>
                    <p>Lowest</p>
                </div>
                <div class="summary-cell">
                    <span>${escapeHtml(summary.pass_rate)}%</span>
                    <p>Pass Rate</p>
                </div>
            </div>

            <div class="attempt-table-wrap">
                <table class="attempt-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Score</th>
                            <th>Percent</th>
                            <th>Duration</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>${rowsHtml}</tbody>
                </table>
            </div>
        `;
    }

    function loadQuiz(quizId, button) {
        if (!quizId) {
            return;
        }

        if (button) {
            button.classList.add("loading");
            button.disabled = true;
        }

        const formData = new FormData();
        formData.append("action", "load_quiz");
        formData.append("quiz_id", quizId);

        fetch("analyticsAdmin.php", {
            method: "POST",
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            },
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Server error: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || "Could not load analytics.");
                }

                renderDetail(data);
                setActiveRow(quizId);

                const params = new URLSearchParams(window.location.search);
                params.set("quiz_id", quizId);
                window.history.replaceState({}, "", `analyticsAdmin.php?${params.toString()}`);
            })
            .catch(error => {
                showToast(error.message, "error");
            })
            .finally(() => {
                if (button) {
                    button.classList.remove("loading");
                    button.disabled = false;
                }
            });
    }

    if (searchInput) {
        searchInput.addEventListener("input", applyFilters);
    }

    if (instructorFilter) {
        instructorFilter.addEventListener("change", applyFilters);
    }

    statusTabs.forEach(tab => {
        tab.addEventListener("click", () => {
            statusTabs.forEach(item => item.classList.remove("active"));
            tab.classList.add("active");
            activeStatus = tab.dataset.status;
            applyFilters();
        });
    });

    tableBody.addEventListener("click", event => {
        const button = event.target.closest(".btn-view-analytics");
        if (!button) {
            return;
        }

        loadQuiz(button.dataset.quizId, button);
    });

    const selectedQuizId = detailPanel.dataset.selectedQuiz;
    if (selectedQuizId) {
        setActiveRow(selectedQuizId);
    }

    applyFilters();
});
