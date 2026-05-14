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
});
