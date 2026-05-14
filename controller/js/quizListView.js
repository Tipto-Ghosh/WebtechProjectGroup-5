document.addEventListener("DOMContentLoaded", function() {
    const xhr = new XMLHttpRequest();

    xhr.open("POST", "/WebtechProjectGroup-5/controller/quizViewController.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    // handle response
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                const data = JSON.parse(xhr.responseText);

                if (data.error) {
                    document.getElementById("noQuizzes").textContent = data.error;
                    return;
                }

                const quizContainer = document.getElementById("quiz-list");
                quizContainer.innerHTML = "";

                data.quizzes.forEach(quiz => {
                    const div = document.createElement("div");
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
                                <p>Time Limit: ${quiz.time_limit_minutes} minutes</p>
                                <button class="start-quiz" data-id="${quiz.quiz_id}">Start Quiz</button>
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

    xhr.send("fetchQuizzes=1");
});
