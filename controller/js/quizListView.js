document.addEventListener("DOMContentLoaded", function() {
    // Fetch quizzes
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                    let data = JSON.parse(xhr.responseText);

                    if (data.error) {
                        document.getElementById("noQuizzes").textContent = data.error;
                        return;
                    }

                    let quizContainer = document.getElementById("quiz-list");
                    quizContainer.innerHTML = "";

                    data.quizzes.forEach(quiz => {
                        let div = document.createElement("div");
                        div.className = "quiz-card";

                        div.innerHTML = `
                            <h4>Quiz ID: ${quiz.quiz_id}</h4>
                            <p>Title: ${quiz.quiz_title}</p>
                            <p>Description: ${quiz.description}</p>
                            <p>Instructor: ${quiz.instructor_name}</p>
                            <p>Total Marks: ${quiz.total_marks}</p>
                            <p>Status: ${quiz.attempted == 1 ? "Attempted" : "Not Attempted"}</p>
                            ${quiz.attempted == 1 
                                ? `
                                    <p>Score: ${quiz.score}</p>
                                    <p>Started At: ${quiz.started_at ?? "N/A"}</p>
                                    <p>Completed At: ${quiz.completed_at ?? "N/A"}</p>
                                `
                                : `
                                    <form method="POST" action="/WebtechProjectGroup-5/controller/quizViewController.php">
                                        <input type="hidden" name="quizId" value="${quiz.quiz_id}">
                                        <button type="submit" name="startQuiz">Start Quiz</button>
                                    </form>
                                `
                            }
                        `;
                        quizContainer.appendChild(div);
                    });

                    if (data.quizzes.length === 0) {
                        document.getElementById("noQuizzes").textContent = "No quizzes available.";
                    }
                } catch (err) {
                    console.error("JSON parse error:", err);
                    document.getElementById("noQuizzes").textContent = "Failed to load quizzes.";
                }    
        }
    };
    xhr.open("POST", "/WebtechProjectGroup-5/controller/quizViewController.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("fetchQuizzes=1");

    function startClicked(quizId) {
        let xhrStart = new XMLHttpRequest();

        xhrStart.onreadystatechange = function() {
            if (xhrStart.readyState === 4 && xhrStart.status === 200) {
                console.log(xhrStart.responseText);
            } 
            else
            {
                console.log(xhrStart.status);
            }
        };
        xhrStart.open("POST", "/WebtechProjectGroup-5/controller/quizViewController.php", true);
        xhrStart.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhrStart.send("startQuiz=1&quizId=" + encodeURIComponent(quizId));
    }
});
