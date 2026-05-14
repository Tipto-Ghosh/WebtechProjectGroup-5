<?php
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Take Quiz</title>
</head>
<body>
    
    <form id="quiz-form" method="post" action="">
        <h2>Quiz Title</h2>

        <div id="instructorname">
            <p>Instructor: <strong id="instructornameVal"></strong></p>
        </div>

        <div id="marks">
            <p>Total marks: <strong id="totalmarksVal"></strong></p>
        </div>

        <div id="timer-wrapper" data-time-limit="30">
            <p>Time Remaining: <span id="timer"></span></p>
        </div>

        <input type="hidden" name="attempt_id">

        <div class="question"></div>

        <div id="times-up-banner" style="display:none;">Time's up!</div>
        
        <div class="buttons">
            <button id="back" name="back" onclick="viewQuizlist()">Back</button>
            <input type="submit" name="submit" id="submit"></button>
        </div>

    </form>
</body>
</html>