document.addEventListener("DOMContentLoaded", function() {
    const attemptId = document.getElementById("attempt_id").value;
    fetch(`../../controller/quizTakeController.php?attempt=${attemptId}`)
        .then(res => res.json())
        .then(data => {
            document.querySelector("h2").textContent = data.quizTitle;
            document.getElementById("instructornameVal").textContent = data.instructor;
            document.getElementById("totalmarksVal").textContent = data.totalMarks;

            let container = document.querySelector(".question");
            container.innerHTML = "";

            data.questions.forEach(question => {
                let html = `<h3>${question.question_text} (${question.marks} marks)</h3>`;

                question.options.forEach(opt => {
                    html += `<label>
                               <input type="radio" name="q${question.question_id}" value="${opt.option_id}">
                               ${opt.option_text}
                             </label><br>`;
                });

                container.innerHTML += html;
            });
        })
    .catch(err => alert("Error loading quiz:", err));

    

    const form = document.getElementById("quiz-form");
    if (!form) {
        console.error("quiz-form element not found!");
        return;
    }
    form.addEventListener("submit", function(e) {
        e.preventDefault();

        const answers = {};
        document.querySelectorAll("input[type=radio]:checked").forEach(input => {
            const qid = input.name.replace("q", "");
            answers[qid] = input.value;
        });

        const payload = {
            attempt_id: attemptId,
            answers: answers
        };

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "../../controller/quizTakeController.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    console.log("Submit response:", response);
                    //window.location.href = `/view/student/quiz_result.php`;
                } else {
                    console.error("Error submitting quiz:", xhr.responseText);
                    alert("Submission failed.");
                }
            }
        };

        xhr.send(JSON.stringify(payload));
    });

});

