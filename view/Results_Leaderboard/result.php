<?php
require_once __DIR__ . "/../../controller/ResultController.php";
?>
<div class="container">
    <h2>My Quiz Results</h2>

    <?php if (empty($results)): ?>
        <p>You have not completed any quizzes yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Quiz Title</th>
                    <th>Score</th>
                    <th>Total Marks</th>
                    <th>Duration (minutes)</th>
                    <th>Completed At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($result["title"]); ?></td>
                        <td><?php echo htmlspecialchars($result["score"]); ?></td>
                        <td><?php echo htmlspecialchars($result["total_marks"]); ?></td>
                        <td><?php echo htmlspecialchars($result["duration"]); ?></td>
                        <td><?php echo htmlspecialchars(date("Y-m-d H:i", strtotime($result["completed_at"]))); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>

<?php

    session_abort();
    exit();
    ?>
    <div class="container"  
        <h2>Quiz Result Details</h2 
        <p><strong>Quiz Title:</strong> <?php echo htmlspecialchars($attempt["title"]); ?></p
        <p><strong>Score:</strong> <?php echo htmlspecialchars($attempt["score"]); ?> / <?php echo htmlspecialchars($attempt["total_marks"]); ?></p 
        <p><strong>Duration:</strong> <?php echo htmlspecialchars($attempt["duration"]); ?> minutes</p
        <p><strong>Completed At:</strong> <?php echo htmlspecialchars(date("Y-m-d H:i", strtotime($attempt["completed_at"]))); ?></p
        <h3>Answer Breakdown</h3 
        <table>
            <thead>
                <tr>
                    <th>Question</th>
                    <th>Your Answer</th>
                    <th>Correct Answer</th>
                    <th>Result</th>
                </tr>
            </thead>            
            <tbody>
                <?php foreach ($breakdown as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item["question_text"]); ?></td>
                        <td><?php echo htmlspecialchars($item["student_answer"] ?? "No answer"); ?></td>
                        <td><?php echo htmlspecialchars($item["correct_answer"]); ?></td>
                        <td>
                            <?php if ($item["is_correct"]): ?>
                                <span class="badge pass">Correct</span>
                            <?php else: ?>
                                <span class="badge fail">Wrong</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="<?php echo htmlspecialchars($close_button["href"]); ?>" class="close-button"><?php echo htmlspecialchars($close_button["text"]); ?></a
    </div>              
    <div    class="container"       
        <h2>Quiz Result Details</h2
        <p><strong>Quiz Title:</strong> <?php echo htmlspecialchars($attempt["title

"]); ?></p
        <p><strong>Score:</strong> <?php echo htmlspecialchars($attempt["score"]); ?> / <?php echo htmlspecialchars($attempt["total_marks"]); ?></p 
        <p><strong>Duration:</strong> <?php echo htmlspecialchars($attempt["duration"]); ?> minutes</p
        <p><strong>Completed At:</strong> <?php echo htmlspecialchars(date("Y-m-d H:i", strtotime($attempt["completed_at"]))); ?></p
        <h3>Answer Breakdown</h3 
        <table>
            <thead>
                <tr>
                    <th>Question</th>
                    <th>Your Answer</th>
                    <th>Correct Answer</th>
                    <th>Result</th>
                </tr>
            </thead>            
            <tbody>
                <?php foreach ($breakdown as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item["question_text"]); ?></td>
                        <td><?php echo htmlspecialchars($item["student_answer"] ?? "No answer"); ?></td>
                        <td><?php echo htmlspecialchars($item["correct_answer"]); ?></td>
                        <td>
                            <?php if ($item["is_correct"]): ?>
                                <span class="badge pass">Correct</span>
                            <?php else: ?>
                                <span class="badge fail">Wrong</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="<?php echo htmlspecialchars($close_button["href"]); ?>" class="close-button"><?php echo htmlspecialchars($close_button["text"]); ?></a
    </div>  
    <?php

    session_abort();
    exit();
    ?>
    <div class="container"  
        <h2>Quiz Result Details</h2 
        <p><strong>Quiz Title:</strong> <?php echo htmlspecialchars($attempt["title"]); ?></p
        <p><strong>Score:</strong> <?php echo htmlspecialchars($attempt["score"]); ?> / <?php echo htmlspecialchars($attempt["total_marks"]); ?></p 
        <p><strong>Duration:</strong> <?php echo htmlspecialchars($attempt["duration"]); ?> minutes</p
        <p><strong>Completed At:</strong> <?php echo htmlspecialchars(date("Y-m-d H:i", strtotime($attempt["completed_at"]))); ?></p
        <h3>Answer Breakdown</h3 
        <table>
            <thead>
                <tr>
                    <th>Question</th>
                    <th>Your Answer</th>
                    <th>Correct Answer</th>
                    <th>Result</th>
                </tr>
            </thead>            
            <tbody>
                <?php foreach ($breakdown as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item["question_text"]); ?></td>
                        <td><?php echo htmlspecialchars($item["student_answer"] ?? "No answer"); ?></td>
                        <td><?php echo htmlspecialchars($item["correct_answer"]); ?></td>
                        <td>
                            <?php if ($item["is_correct"]): ?>
                                <span class="badge pass">Correct</span>
                            <?php else: ?>
                                <span class="badge fail">Wrong</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="<?php echo htmlspecialchars($close_button["href"]); ?>" class="close-button"><?php echo htmlspecialchars($close_button["text"]); ?></a
    </div>              
    <div    class="container"       
        <h2>Quiz Result Details</h2
        <p><strong>Quiz Title:</strong> <?php echo htmlspecialchars($attempt["title

"]); ?></p
        <p><strong>Score:</strong> <?php echo htmlspecialchars($attempt["score"]); ?> / <?php echo htmlspecialchars($attempt["total_marks"]); ?></p 
        <p><strong>Duration:</strong> <?php echo htmlspecialchars($attempt["duration"]); ?> minutes</p
        <p><strong>Completed At:</strong> <?php echo htmlspecialchars(date("Y-m-d H:i", strtotime($attempt["completed_at"]))); ?></p
        <h3>Answer Breakdown</h3 
        <table>
            <thead>
                <tr>
                    <th>Question</th>
                    <th>Your Answer</th>
                    <th>Correct Answer</th>
                    <th>Result</th>
                </tr>
            </thead>            
            <tbody>
                <?php foreach ($breakdown as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item["question_text"]); ?></td>
                        <td><?php echo htmlspecialchars($item["student_answer"] ?? "No answer"); ?></td>
                        <td><?php echo htmlspecialchars($item["correct_answer"]); ?></td>
                        <td>
                            <?php if ($item["is_correct"]): ?>
                                <span class="badge pass">Correct</span>
                            <?php else: ?>
                                <span class="badge fail">Wrong</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="<?php echo htmlspecialchars($close_button["href"]); ?>" class="close-button"><?php echo htmlspecialchars($close_button["text"]); ?></a
    </div>  
    <?php

    session_abort();
    exit();
    ?>

    <date_interval_create_from_date_string("30 minutes");   
    <div class="container"  
        <h2>Quiz Result Details</h2 
        <p><strong>Quiz Title:</strong> <?php echo htmlspecialchars($attempt["title"]); ?></p
        <p><strong>Score:</strong> <?php echo htmlspecialchars($attempt["score"]); ?> / <?php echo htmlspecialchars($attempt["total_marks"]); ?></p 
        <p><strong>Duration:</strong> <?php echo htmlspecialchars($attempt["duration"]); ?> minutes</p
        <p><strong>Completed At:</strong> <?php echo htmlspecialchars(date("Y-m-d H:i", strtotime($attempt["completed_at"]))); ?></p
        <h3>Answer Breakdown</h3 
        <table>
            <thead>
                <tr>
                    <th>Question</th>
                    <th>Your Answer</th>
                    <th>Correct Answer</th>
                    <th>Result</th>
                </tr>
            </thead>            
            <tbody>
                <?php foreach ($breakdown as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item["question_text"]); ?></td>
                        <td><?php echo htmlspecialchars($item["student_answer"] ?? "No answer"); ?></td>
                        <td><?php echo htmlspecialchars($item["correct_answer"]); ?></td>
                        <td>
                            <?php if ($item["is_correct"]): ?>
                                <span class="badge pass">Correct</span>
                            <?php else: ?>
                                <span class="badge fail">Wrong</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="<?php echo htmlspecialchars($close_button["href"]); ?>" class="close-button"><?php echo htmlspecialchars($close_button["text"]); ?></a
    </div>              
    <div    class="container"       
        <h2>Quiz Result Details</h2
        <p><strong>Quiz Title:</strong> <?php echo htmlspecialchars($attempt["title"]); ?></p
        <p><strong>Score:</strong> <?php echo htmlspecialchars($attempt["score"]); ?> / <?php echo htmlspecialchars($attempt["total_marks"]); ?></p 
        <p><strong>Duration:</strong> <?php echo htmlspecialchars($attempt["duration"]); ?> minutes</p
        <p><strong>Completed At:</strong> <?php echo htmlspecialchars(date("Y-m-d H:i", strtotime($attempt["completed_at"]))); ?></p
        <h3>Answer Breakdown</h3 
        <table>
            <thead>
                <tr>
                    <th>Question</th>
                    <th>Your Answer</th>
                    <th>Correct Answer</th>
                    <th>Result</th>
                </tr>
            </thead>            
            <tbody>
                <?php foreach ($breakdown as $item): ?> 
                    <tr>
                        <td><?php echo htmlspecialchars($item["question_text"]); ?></td>
                        <td><?php echo htmlspecialchars($item["student_answer"] ?? "No answer"); ?></td>
                        <td><?php echo htmlspecialchars($item["correct_answer"]); ?></td>
                        <td>
                            <?php if ($item["is_correct"]): ?>
                                <span class="badge pass">Correct</span>
                            <?php else: ?>
                                <span class="badge fail">Wrong</span>
                            <?php endif; ?>         
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="<?php echo htmlspecialchars($close_button["href"]); ?>" class="close-button"><?php echo htmlspecialchars($close_button["text"]); ?></a
    </div>  
    <?php       
    session_abort();
    exit();
    ?>      
    <table class="result-table">
        <thead>
            <tr>
                <th>Question</th>
                <th>Your Answer</th>
                <th>Correct Answer</th>
                <th>Result</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($breakdown as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item["question_text"]); ?></td>
                    <td><?php echo htmlspecialchars($item["student_answer"] ?? "No answer"); ?></td>
                    <td><?php echo htmlspecialchars($item["correct_answer"]); ?></td>
                    <td>
                        <?php if ($item["is_correct"]): ?>
                            <span class="badge pass">Correct</span>
                        <?php else: ?>
                            <span class="badge fail">Wrong</span>               
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>        


        </tbody>    </table>
    <a href="<?php echo htmlspecialchars($close_button["href"]); ?>" class="close-button"><?php echo htmlspecialchars($close_button["text"]); ?></a
    <?php
    session_abort();
    exit();
    ?>  
    
