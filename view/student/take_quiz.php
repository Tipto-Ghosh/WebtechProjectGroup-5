<?php
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Take Quiz</title>
  
  <link rel="stylesheet" href="../style/quizTake.css">
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

        <input type="hidden" id="attempt_id" value="<?php echo $_GET['attempt']; ?>">

        <div class="question">
            <p id=question></p>
            <p id=marks></p>
            <input type="radio">
        </div>

        <div id="times-up-banner" style="display:none;">Time's up!</div>
        
        <div class="buttons">
            <button id="back" name="back" onclick="viewQuizlist()">Back</button>
            <input type="submit" name="submit" id="submit">
        </div>

    </form>
    <script src="../../controller/js/quizTakeView.js" defer></script>
</body>
</html>