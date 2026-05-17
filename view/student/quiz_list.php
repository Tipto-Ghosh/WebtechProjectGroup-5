<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Available Quizzes</title>
  <link rel="stylesheet" href="../style/quizList.css">
</head>
<body>
  <h2>Available Quizzes</h2>
  <p id="subh">Select a quiz to start. Attempted quizzes are shown with your score.</p>

  <div id="quiz-list">
      <p id="noQuizzes" name="noQuizzes"></p>
  </div>

  <button id="back" name="back" type="button" onclick="window.location.href='dashboard.php'">Back</button>
  <script src="../../controller/js/quizListView.js" defer></script>
</body>
</html>
