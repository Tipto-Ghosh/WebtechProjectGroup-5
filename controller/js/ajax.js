// ajax.js — Leaderboard auto-refresh 30 sec

let timeLeft = 30;
const countdownEl = document.getElementById("refresh-countdown");
const bodyEl      = document.getElementById("leaderboard-body");

function fetchLeaderboard() {
    let xhttp = new XMLHttpRequest();

    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            let json = JSON.parse(this.responseText);

            if (!json.success || json.data.length === 0) {
                bodyEl.innerHTML = "<tr><td colspan='4'>No data yet.</td></tr>";
                return;
            }

            let rows = "";
            for (let i = 0; i < json.data.length; i++) {
                let row   = json.data[i];
                let rank  = i + 1;
                let color = rank == 1 ? "gold" : rank == 2 ? "silver" : rank == 3 ? "#cd7f32" : "";
                let style = color ? "style='color:" + color + ";font-weight:bold;'" : "";

                rows += "<tr>" +
                    "<td " + style + ">" + rank + "</td>" +
                    "<td>" + row.name + "</td>" +
                    "<td>" + row.total_score + "</td>" +
                    "<td>" + row.total_attempts + "</td>" +
                    "</tr>";
            }

            bodyEl.innerHTML = rows;
        }
    };

    xhttp.open("GET", "../controller/LeaderboardAjax.php", true);
    xhttp.send();
}

// Countdown for 30 seconds
setInterval(function () {
    timeLeft--;
    countdownEl.textContent = "Refreshing in " + timeLeft + "s";

    if (timeLeft <= 0) {
        timeLeft = 30;
        fetchLeaderboard();
    }
}, 1000);