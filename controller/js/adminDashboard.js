var apiUrl = '../../controller/adminDashboardController.php';
function getData() {
    fetch(apiUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function(response) {
        return response.json();
    }).then(function(data) {
        document.getElementById('count-students').innerText = data.total_students;
        document.getElementById('count-instructors').innerText = data.total_instructors;
        document.getElementById('count-quizzes').innerText = data.live_quizzes;
        document.getElementById('count-attempts').innerText = data.total_attempts;
        
        document.getElementById('count-active').innerText = data.active_accounts;
        document.getElementById('count-suspended').innerText = data.suspended_accounts;
        document.getElementById('count-admins').innerText = data.total_admins;
        document.getElementById('count-published').innerText = data.live_quizzes;
        document.getElementById('count-drafts').innerText = data.draft_quizzes;

        var pct = data.avg_score_percent;
        document.getElementById('avg-score').innerText = pct;
        document.getElementById('gauge-score').style.setProperty('--pct', pct);
        
        var circle = document.getElementById('gauge-circle');
        var circ = 314.15; 
        circle.style.strokeDasharray = circ;
        circle.style.strokeDashoffset = circ - (pct / 100) * circ;

        document.getElementById('score-high').innerText = data.score_high;
        document.getElementById('score-low').innerText = data.score_low;
        document.getElementById('score-median').innerText = data.score_median;

        var tbody = document.getElementById('tbody-recent-users');
        var tableHTML = "";

        if (data.recent_users.length === 0) {
            tableHTML = '<tr class="recent-table__empty"><td colspan="4">No users yet.</td></tr>';
        } else {
            for (var i = 0; i < data.recent_users.length; i++) {
                var u = data.recent_users[i];
                
                var date = new Date(u.created_at);
                var joined = date.toLocaleDateString(); 

                var statusClass = "";
                var statusText = "";
                if (u.is_active == true) {
                    statusClass = "status-dot--active";
                    statusText = "Active";
                } else {
                    statusClass = "status-dot--suspended";
                    statusText = "Suspended";
                }

                tableHTML += '<tr data-user-id="' + u.id + '">' +
                    '<td>' + u.name + '</td>' +
                    '<td><span class="role-badge role-badge--' + u.role + '">' + u.role + '</span></td>' +
                    '<td>' + joined + '</td>' +
                    '<td><span class="status-dot ' + statusClass + '">' + statusText + '</span></td>' +
                    '</tr>';
            }
        }
        tbody.innerHTML = tableHTML;

        var badge = document.getElementById('badge-suspended');
        if (data.suspended_accounts > 0) {
            badge.innerText = data.suspended_accounts;
            badge.style.display = 'inline';
        } else {
            badge.style.display = 'none';
        }
    })
    .catch(function(err) {
        console.log("Ajax for adminDashboard is failing."); 
    });
}

// refresh after 30 seconds
setInterval(function() {getData();}, 30000);
// Run it when page loads
window.onload = function() {getData();};