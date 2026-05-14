<?php
include "../../controller/QuizBuilderController.php";

$controller = new QuizBuilderController();
$formError = "";

$submission = $controller->processQuizFormSubmission();
if (!empty($submission["redirect"])) {
	header("Location: " . $submission["redirect"]);
	exit;
}

if (($submission["message"] ?? "") !== "") {
	$formError = (string) $submission["message"];
}

$pageData = $controller->getQuizFormData();
$quiz = isset($pageData["quiz"]) && is_array($pageData["quiz"]) ? $pageData["quiz"] : array();
$pageTitle = isset($pageData["page_title"]) ? $pageData["page_title"] : "Create New Quiz";
$pageSubtitle = isset($pageData["page_subtitle"]) ? $pageData["page_subtitle"] : "Fill in the details - you'll add questions in the next step";
$mode = isset($pageData["mode"]) ? $pageData["mode"] : "create";
$primaryActionLabel = isset($pageData["primary_action_label"]) ? $pageData["primary_action_label"] : "Save & Add Questions";
$breadcrumbs = isset($pageData["breadcrumbs"]) && is_array($pageData["breadcrumbs"]) ? $pageData["breadcrumbs"] : array("My Quizzes", $pageTitle);

$instructor_name = isset($pageData["instructor_name"]) ? (string) $pageData["instructor_name"] : "Instructor";
$instructor_role = isset($pageData["instructor_role"]) ? (string) $pageData["instructor_role"] : "Instructor";

function getQuizStatusBadgeClass(string $status): string
{
	return $status === "published" ? "badge-published" : "badge-draft";
}

function getQuizStatusLabel(string $status): string
{
	return $status === "published" ? "Published" : "Draft";
}

function getQuizTotalMarksLabel(array $quiz): string
{
	$questionCount = (int) ($quiz["question_count"] ?? 0);
	$totalMarks = (int) ($quiz["total_marks"] ?? 0);

	if ($questionCount <= 0) {
		return "Auto-computed from questions";
	}

	return (string) $totalMarks;
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>QuizMaker</title>
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="../style/quiz_style_quiz_form_page.css">
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
						<a class="nav-item active" href="dashboard.php">My Quizzes <span class="nav_badge">7</span></a>
					<a class="nav-item" href="#">Analytics</a>
				</nav>

				<section class="sidebar_user" aria-label="Current user">
					<div class="avatar"><?php echo htmlspecialchars(strtoupper(substr($instructor_name, 0, 1))); ?></div>
					<div>
						<div class="user_name"><?php echo htmlspecialchars($instructor_name); ?></div>
						<div class="user_role"><?php echo htmlspecialchars($instructor_role); ?></div>
					</div>
				</section>
			</aside>
			<div class="main">
				<header>
					<div class="bread_crumb">
						<?php echo htmlspecialchars($breadcrumbs[0] ?? "My Quizzes"); ?> <span class="breadcrumb_separator">&gt;</span> <strong><?php echo htmlspecialchars($breadcrumbs[1] ?? $pageTitle); ?></strong>
					</div>
					<div class="header_actions">
						<a class="btn btn_secondary" href="dashboard.php">Cancel</a>
						<button class="btn btn_primary" type="submit" form="quiz_form"><?php echo htmlspecialchars($primaryActionLabel); ?></button>
					</div>
				</header>
				<main class="content quiz_form_page">
					<section class="page_head">
						<h1><?php echo htmlspecialchars($pageTitle); ?></h1>
						<div class="page_subtitle"><?php echo htmlspecialchars($pageSubtitle); ?></div>
					</section>
					<section class="info_banner" aria-label="Quiz builder note">
						<div class="info_banner_icon">i</div>
						<div>
							<div class="info_banner_title">After saving, you'll be redirected to the Question Builder to add MCQ questions.</div>
							<div class="info_banner_text">The quiz stays in draft until you add at least one question and publish it later.</div>
						</div>
					</section>
					<?php if ($formError !== ""): ?>
						<section class="info_banner" aria-label="Quiz save error">
							<div class="info_banner_icon">!</div>
							<div>
								<div class="info_banner_title">Could not save quiz</div>
								<div class="info_banner_text"><?php echo htmlspecialchars($formError); ?></div>
							</div>
						</section>
					<?php endif; ?>
					<section class="quiz_form_card">
						<div class="quiz_form_card_header">
							<div>
								<div class="quiz_form_card_title">Quiz Details</div>
								<div class="quiz_form_card_subtitle">Basic information and quiz configuration</div>
							</div>
							<span class="badge <?php echo getQuizStatusBadgeClass((string) ($quiz["status"] ?? "draft")); ?>"><?php echo htmlspecialchars(getQuizStatusLabel((string) ($quiz["status"] ?? "draft"))); ?></span>
						</div>
						<form id="quiz_form" class="quiz_form" method="post" action="#">
							<?php if ((int) ($quiz["id"] ?? 0) > 0): ?>
								<input type="hidden" name="quiz_id" value="<?php echo (int) $quiz["id"]; ?>">
							<?php endif; ?>
							<input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); ?>">
							<input type="hidden" name="action" value="<?php echo $mode === "edit" ? "update_quiz" : "create_quiz"; ?>">
							<?php
							$titlePlaceholder = $mode === "create" ? "Add a descriptive quiz title" : "Enter a descriptive quiz title";
							$descriptionPlaceholder = $mode === "create" ? "Add a short description for the quiz" : "Add a short description for the quiz";
							?>

							<div class="form_section_label">Basic Information</div>

							<div class="field_group field_group_full">
								<label for="quiz_title">Quiz Title <span class="required_mark">*</span></label>
								<input id="quiz_title" name="title" type="text" value="<?php echo htmlspecialchars((string) ($quiz["title"] ?? "")); ?>" placeholder="<?php echo htmlspecialchars($titlePlaceholder); ?>" required maxlength="200">
								<div class="field_hint">Choose a clear, descriptive title for students</div>
							</div>
							<div class="field_group field_group_full">
								<label for="quiz_description">Description</label>
								<textarea id="quiz_description" name="description" rows="4" placeholder="<?php echo htmlspecialchars($descriptionPlaceholder); ?>"><?php echo htmlspecialchars((string) ($quiz["description"] ?? "")); ?></textarea>
							</div>
							<div class="form_section_label">Configuration</div>
								<div class="form_grid">
									<div class="field_group">
										<label for="time_limit_minutes">Time Limit (minutes) <span class="required_mark">*</span></label>
										<input id="time_limit_minutes" name="time_limit_minutes" type="number" min="1" step="1" value="<?php echo (int) ($quiz["time_limit_minutes"] ?? 60); ?>" required>
										<div class="field_hint">Positive integer only</div>
									</div>

									<div class="field_group">
										<label for="total_marks">Total Marks</label>
										<input id="total_marks" type="text" value="<?php echo htmlspecialchars(getQuizTotalMarksLabel($quiz)); ?>" readonly>
										<div class="field_hint">Calculated automatically from questions</div>
									</div>

									<div class="field_group">
										<label for="quiz_status">Status</label>
										<input id="quiz_status" type="text" value="<?php echo htmlspecialchars(getQuizStatusLabel((string) ($quiz["status"] ?? "draft"))); ?>" readonly>
										<div class="field_hint">Publish after adding questions</div>
									</div>

									<div class="form_footer">
									<div class="form_note">
										<span class="form_note_icon">⌛</span>
										<span>Saving creates a draft - publish only when questions are ready</span>
									</div>
									<div class="form_actions">
										<a class="btn btn_secondary" href="dashboard.php">Discard</a>
										<button class="btn btn_primary" type="submit"><?php echo htmlspecialchars($primaryActionLabel); ?></button>
									</div>
								</div>
							</div>
						</form>
                        
					</section>
					<div class="page_footer_hint">Total Marks - read-only, auto-computed by recomputeMarks() · Status - read-only, changed via Publish toggle only</div>
				</main>

				<footer>
					Quiz form view built with a simple MVC structure, semantic HTML, and database-backed data.
				</footer>
			</div>

		</div>    
	</body>
</html>
