<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Available Quizzes</title>
</head>
<body>
  <h2>Available Quizzes</h2>
  <p>Select a quiz to start. Attempted quizzes are shown with your score.</p>

  <form method="post" action="">
    <div id="quiz-list">
        <p id="noQuizzes" name="noQuizzes"></p>
    </div>

    <form method="POST" action="">
        <input type="hidden" name="goDashboard" value="1">
        <button id="back" name="back" type="submit">Back</button>
    </form>
  </form>
  <script src="../../controller/js/quizListView.js" defer></script>
</body>
</html>