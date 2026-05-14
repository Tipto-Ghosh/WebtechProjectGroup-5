<?php
include "../../controller/QuizBuilderController.php";

$controller = new QuizBuilderController();
$pageData = $controller->getQuestionBuilderData();
$quiz = isset($pageData["quiz"]) && is_array($pageData["quiz"]) ? $pageData["quiz"] : array();
$questions = isset($pageData["questions"]) && is_array($pageData["questions"]) ? $pageData["questions"] : array();
$pageTitle = isset($pageData["page_title"]) ? $pageData["page_title"] : "Question Builder";
$pageSubtitle = isset($pageData["page_subtitle"]) ? $pageData["page_subtitle"] : "Add, edit, and manage MCQ questions for your quiz";
$breadcrumbs = isset($pageData["breadcrumbs"]) && is_array($pageData["breadcrumbs"]) ? $pageData["breadcrumbs"] : array("My Quizzes", "Question Builder");

function getQuizMetaText(array $quiz): string
{
    $time = (int) ($quiz["time_limit_minutes"] ?? 0);
    $count = (int) ($quiz["question_count"] ?? 0);
    $status = $quiz["status"] ?? "draft";
    return $time . " min · " . ucfirst($status) . " · " . $count . " question" . ($count === 1 ? "" : "s");
}
?>
<!DOCTYPE html>
<html lang="eng">
    <head>
        <title>QuizMaker</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="../style/quiz_style_question_builder_page.css">
    </head>
    <body>
        <div class="app">
            <aside class="sidebar" aria-label="Sidebar navigation">
                <section class="sidebar-brand">
                    <div class="brand-title">QuizMaker</div>
                    <div class="brand-subtitle">Instructor Panel</div>
                </section>

                <nav class="nav_section" aria-label="Workspace">
                    <div class="nav-label">Workspace</div>
                    <a class="nav-item" href="#">Dashboard</a>
                    <a class="nav-item active" href="quiz_list.php">My Quizzes <span class="nav_badge">7</span></a>
                    <a class="nav-item" href="#">Analytics</a>
                </nav>

                <section class="sidebar_user" aria-label="Current user">
                    <div class="avatar">PR</div>
                    <div>
                        <div class="user_name">Prottoy Roy</div>
                        <div class="user_role">Instructor</div>
                    </div>
                </section>
            </aside>
            <div class="main">
                <header>
                    <div class="bread_crumb">
                        <?php echo htmlspecialchars($breadcrumbs[0] ?? "My Quizzes"); ?> <span class="breadcrumb_separator">&gt;</span> <strong><?php echo htmlspecialchars($breadcrumbs[1] ?? "Question Builder"); ?></strong>
                    </div>
                    <div class="header_actions">
                        <a class="btn btn_secondary" href="quiz_list.php">Back</a>
                        <button class="btn btn_primary" type="button"> Publish Quiz</button>
                    </div>
                </header>
                <main class="content question_builder_page">
                    <section class="page_head">
                        <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
                        <div class="page_subtitle"><?php echo htmlspecialchars($pageSubtitle); ?></div>
                    </section>

                    <section class="quiz_info_card">
                        <div class="quiz_info_header">
                            <div>
                                <div class="quiz_info_title"><?php echo htmlspecialchars($quiz["title"] ?? ""); ?></div>
                                <div class="quiz_info_meta"><?php echo htmlspecialchars(getQuizMetaText($quiz)); ?></div>
                            </div>
                            <div class="quiz_info_marks"><?php echo (int) ($quiz["total_marks"] ?? 0); ?> total marks</div>
                        </div>
                    </section>
                    <div class="builder_grid">
                        <section class="question_form_section">
                            <div class="section_header">
                                <h2>Add New Question</h2>
                                <span class="question_type_label">MCQ 4 options</span>
                            </div>

                            <form id="question_form" class="question_form" method="post" action="#">
                                <div class="field_group field_group_full">
                                    <label for="question_text">Question Text <span class="required_mark">*</span></label>
                                    <textarea id="question_text" name="question_text" rows="3" placeholder="Enter the question text" required></textarea>
                                </div>

                                <div class="form_row">
                                    <div class="field_group">
                                        <label for="marks">Marks <span class="required_mark">*</span></label>
                                        <input id="marks" name="marks" type="number" min="1" step="1" value="1" required>
                                    </div>

                                    <div class="field_group">
                                        <label for="correct_answer">Correct Answer <span class="required_mark">*</span></label>
                                        <select id="correct_answer" name="correct_answer" required>
                                            <option value="">Select correct option</option>
                                            <option value="A">Option A</option>
                                            <option value="B">Option B</option>
                                            <option value="C">Option C</option>
                                            <option value="D">Option D</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form_section_label">Options (Exactly 4 Required)</div>

                                <div class="options_grid">
                                    <div class="option_input_group">
                                        <div class="option_label">
                                            <input type="radio" name="option_select" value="A" id="opt_a">
                                            <label for="opt_a">A</label>
                                        </div>
                                        <input class="option_input" type="text" name="option_a" placeholder="Enter option A" required>
                                    </div>

                                    <div class="option_input_group">
                                        <div class="option_label">
                                            <input type="radio" name="option_select" value="B" id="opt_b">
                                            <label for="opt_b">B</label>
                                        </div>
                                        <input class="option_input" type="text" name="option_b" placeholder="Enter option B" required>
                                    </div>
                                    <div class="option_input_group">
                                        <div class="option_label">
                                            <input type="radio" name="option_select" value="C" id="opt_c">
                                            <label for="opt_c">C</label>
                                        </div>
                                        <input class="option_input" type="text" name="option_c" placeholder="Enter option C" required>
                                    </div>

                                    <div class="option_input_group">
                                        <div class="option_label">
                                            <input type="radio" name="option_select" value="D" id="opt_d">
                                            <label for="opt_d">D</label>
                                        </div>
                                        <input class="option_input" type="text" name="option_d" placeholder="Enter option D" required>
                                    </div>
                                </div>

                                 <button class="btn btn_primary btn_add_question" type="submit">+ Add Question to Quiz</button>
                            </form>

                        </section>
                    </div>
                </main>
            </div>        
        </div>
    </body>
</html>