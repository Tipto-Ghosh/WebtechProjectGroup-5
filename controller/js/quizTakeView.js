document.addEventListener("DOMContentLoaded", function() {
    var attemptId = document.getElementById("attempt_id").value;
    var form = document.getElementById("quiz-form");
    var back = document.getElementById("back");
    var submitBtn = document.getElementById("submit");
    var timerBox = document.getElementById("timer-wrapper");
    var timerText = document.getElementById("timer");
    var banner = document.getElementById("times-up-banner");
    var submitting = false;
    var timer = null;

    if (back != null) {
        back.type = "button";
        back.onclick = function() {
            window.location.href = "quiz_list.php";
        };
    }

    fetch("../../controller/quizTakeController.php?attempt=" + attemptId)
        .then(function(res) {
            if (res.status == 403) {
                window.location.href = "/WebtechProjectGroup-5/view/Results_Leaderboard/result.php?attempt_id=" + attemptId;
                return null;
            }
            return res.json();
        })
        .then(function(data) {
            if (data == null) {
                return;
            }

            document.querySelector("h2").innerHTML = data.quizTitle;
            document.getElementById("instructornameVal").innerHTML = data.instructor;
            document.getElementById("totalmarksVal").innerHTML = data.totalMarks;

            if (timerBox != null) {
                timerBox.setAttribute("data-time-limit", data.timeLimitMinutes);
                startTimer(data.timeLimitMinutes);
            }

            var box = document.querySelector(".question");
            box.innerHTML = "";

            for (var i = 0; i < data.questions.length; i++) {
                var q = data.questions[i];
                var html = "<h3>" + q.question_text + " (" + q.marks + " marks)</h3>";

                for (var j = 0; j < q.options.length; j++) {
                    var op = q.options[j];
                    html = html + "<label>";
                    html = html + "<input type='radio' name='q" + q.question_id + "' value='" + op.option_id + "'> ";
                    html = html + op.option_text;
                    html = html + "</label><br>";
                }

                box.innerHTML = box.innerHTML + html;
            }
        })
        .catch(function() {
            alert("Quiz loading failed");
        });

    function startTimer(minutes) {
        var total = parseInt(minutes) * 60;

        if (isNaN(total) || total <= 0) {
            total = 1800;
        }

        if (banner != null) {
            banner.style.display = "none";
        }

        printTime(total);

        timer = setInterval(function() {
            total = total - 1;
            printTime(total);

            if (total <= 0) {
                clearInterval(timer);

                if (banner != null) {
                    banner.innerHTML = "Time's up!";
                    banner.style.display = "block";
                }

                alert("Time's up!");

                setTimeout(function() {
                    if (submitBtn != null) {
                        submitBtn.click();
                    }
                }, 500);
            }
        }, 1000);
    }

    function printTime(seconds) {
        if (timerText == null) {
            return;
        }

        if (seconds < 0) {
            seconds = 0;
        }

        var m = Math.floor(seconds / 60);
        var s = seconds % 60;

        if (s < 10) {
            s = "0" + s;
        }

        timerText.innerHTML = m + ":" + s;
    }

    form.onsubmit = function(e) {
        e.preventDefault();

        if (submitting == true) {
            return false;
        }

        submitting = true;
        if (submitBtn != null) {
            submitBtn.disabled = true;
        }

        var answers = {};
        var checked = document.querySelectorAll(".question input[type=radio]:checked");

        for (var i = 0; i < checked.length; i++) {
            var qid = checked[i].name.replace("q", "");
            answers[qid] = checked[i].value;
        }

        var sendData = {
            attempt_id: attemptId,
            answers: answers
        };

        fetch("../../controller/quizTakeController.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(sendData)
        })
            .then(function(res) {
                return res.json();
            })
            .then(function(res) {
                window.location.href = res.redirect;
            })
            .catch(function() {
                alert("Submit failed");
                submitting = false;
                if (submitBtn != null) {
                    submitBtn.disabled = false;
                }
            });

        return false;
    };
});

