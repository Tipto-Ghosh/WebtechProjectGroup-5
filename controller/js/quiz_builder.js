document.addEventListener("DOMContentLoaded", function () {
	var page = document.querySelector(".question_builder_page");
	if (!page) {
		return;
	}

	var endpoint = page.getAttribute("data-endpoint") || "../../controller/QuizBuilderController.php";
	var quizId = page.getAttribute("data-quiz-id") || "0";
	var quizStatusBadge = document.getElementById("quiz_status_badge");
	var quizMetaText = document.getElementById("quiz_meta_text");
	var quizTotalMarks = document.getElementById("quiz_total_marks");
	var questionCount = document.getElementById("question_count_value");
	var publishButton = document.getElementById("quiz_toggle_button");
	var questionForm = document.getElementById("question_form");
	var questionsList = document.getElementById("questions_list");

	function serialize(data) {
		var parts = [];
		Object.keys(data).forEach(function (key) {
			parts.push(encodeURIComponent(key) + "=" + encodeURIComponent(data[key] === null ? "" : data[key]));
		});
		return parts.join("&");
	}

	function request(method, data, callback) {
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function () {
			if (this.readyState === 4) {
				var response = {};
				try {
					response = JSON.parse(this.responseText || "{}");
				} catch (error) {
					response = { success: false, message: this.responseText || "Invalid response" };
				}

				if (this.status === 200 && response.success) {
					callback(null, response);
				} else {
					callback(response.message || "Request failed", response);
				}
			}
		};

		xhttp.open(method, endpoint, true);
		xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
		xhttp.send(serialize(data));
	}

	function updateQuizSummary(quiz) {
		if (!quiz) {
			return;
		}

		quizId = String(quiz.id || quizId || "0");
		page.setAttribute("data-quiz-id", quizId);
		page.setAttribute("data-quiz-status", quiz.status || "draft");

		if (quizStatusBadge) {
			quizStatusBadge.textContent = quiz.status === "published" ? "Published" : "Draft";
			quizStatusBadge.className = quiz.status === "published" ? "badge badge-published" : "badge badge-draft";
		}

		if (quizMetaText) {
			quizMetaText.textContent = (quiz.time_limit_minutes || 0) + " min · " + ((quiz.status || "draft").charAt(0).toUpperCase() + (quiz.status || "draft").slice(1)) + " · " + (quiz.question_count || 0) + " question" + ((quiz.question_count || 0) === 1 ? "" : "s");
		}

		if (quizTotalMarks) {
			quizTotalMarks.textContent = (quiz.total_marks || 0) + " total marks";
		}

		if (questionCount) {
			questionCount.textContent = (quiz.question_count || 0) + " questions";
		}

		if (publishButton) {
			publishButton.textContent = (quiz.status === "published") ? "Unpublish Quiz" : "Publish Quiz";
		}
	}

	function escapeHtml(value) {
		return String(value || "")
			.replace(/&/g, "&amp;")
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;")
			.replace(/\"/g, "&quot;")
			.replace(/'/g, "&#39;");
	}

	function optionLabelList() {
		return ["A", "B", "C", "D"];
	}

	function renderQuestionDisplay(card, questionData) {
		var optionsHtml = "";
		optionLabelList().forEach(function (label) {
			var optionText = questionData.options[label] || "";
			var isCorrect = label === questionData.correct_option;
			optionsHtml += '<span class="option_badge' + (isCorrect ? ' option_correct' : '') + '">' + label + ': ' + escapeHtml(optionText) + '</span>';
		});

		card.innerHTML = '' +
			'<div class="question_card_header">' +
				'<div class="question_number">' + escapeHtml(questionData.index) + '</div>' +
				'<div class="question_info">' +
					'<div class="question_text">' + escapeHtml(questionData.question_text) + '</div>' +
					'<div class="question_correct">Correct answer: <strong>' + escapeHtml(questionData.correct_option) + '</strong></div>' +
				'</div>' +
			'</div>' +
			'<div class="question_card_options">' +
				'<div class="options_label">Options:</div>' +
				'<div class="options_preview">' + optionsHtml + '</div>' +
			'</div>' +
			'<div class="question_card_footer">' +
				'<div class="card_marks">' + escapeHtml(questionData.marks) + ' pts</div>' +
				'<div class="card_actions">' +
					'<button class="btn_icon btn_edit" type="button" data-action="edit">✎ Edit</button>' +
					'<button class="btn_icon btn_delete" type="button" data-action="delete">🗑 Delete</button>' +
				'</div>' +
			'</div>';

		card.dataset.mode = "view";
	}

	function renderQuestionEdit(card, questionData) {
		var optionsHtml = "";
		optionLabelList().forEach(function (label) {
			var optionText = questionData.options[label] || "";
			optionsHtml += '' +
				'<div class="option_input_group">' +
					'<div class="option_label">' +
						'<input type="radio" name="edit_correct_option_' + questionData.id + '" value="' + label + '" ' + (label === questionData.correct_option ? 'checked' : '') + '>' +
						'<label>' + label + '</label>' +
					'</div>' +
					'<input class="option_input" type="text" data-option-key="' + label + '" value="' + escapeHtml(optionText) + '" required>' +
				'</div>';
		});

		card.innerHTML = '' +
			'<div class="question_card_header">' +
				'<div class="question_number">' + escapeHtml(questionData.index) + '</div>' +
				'<div class="question_info">' +
					'<label class="form_section_label">Question Text</label>' +
					'<textarea class="edit_question_text" rows="3" required>' + escapeHtml(questionData.question_text) + '</textarea>' +
					'<div class="form_row">' +
						'<div class="field_group">' +
							'<label class="form_section_label">Marks</label>' +
							'<input class="edit_question_marks" type="number" min="1" step="1" value="' + escapeHtml(questionData.marks) + '" required>' +
						'</div>' +
						'<div class="field_group">' +
							'<label class="form_section_label">Correct Answer</label>' +
							'<select class="edit_correct_option">' +
								'<option value="A"' + (questionData.correct_option === 'A' ? ' selected' : '') + '>Option A</option>' +
								'<option value="B"' + (questionData.correct_option === 'B' ? ' selected' : '') + '>Option B</option>' +
								'<option value="C"' + (questionData.correct_option === 'C' ? ' selected' : '') + '>Option C</option>' +
								'<option value="D"' + (questionData.correct_option === 'D' ? ' selected' : '') + '>Option D</option>' +
							'</select>' +
						'</div>' +
					'</div>' +
				'</div>' +
			'</div>' +
			'<div class="question_card_options">' +
				'<div class="options_label">Options:</div>' +
				'<div class="options_preview">' + optionsHtml + '</div>' +
			'</div>' +
			'<div class="question_card_footer">' +
				'<div class="card_marks">Editing</div>' +
				'<div class="card_actions">' +
					'<button class="btn_icon btn_edit" type="button" data-action="save">Save</button>' +
					'<button class="btn_icon btn_delete" type="button" data-action="cancel">Cancel</button>' +
				'</div>' +
			'</div>';

		card.dataset.mode = "edit";
	}

	function getCardData(card) {
		return {
			id: card.getAttribute("data-question-id"),
			index: card.getAttribute("data-question-index") || "",
			question_text: card.getAttribute("data-question-text") || "",
			marks: card.getAttribute("data-question-marks") || "1",
			correct_option: card.getAttribute("data-correct-option") || "A",
			options: {
				A: card.getAttribute("data-option-a") || "",
				B: card.getAttribute("data-option-b") || "",
				C: card.getAttribute("data-option-c") || "",
				D: card.getAttribute("data-option-d") || ""
			}
		};
	}

	function syncCardAttributes(card, questionData) {
		card.setAttribute("data-question-text", questionData.question_text);
		card.setAttribute("data-question-marks", questionData.marks);
		card.setAttribute("data-correct-option", questionData.correct_option);
		card.setAttribute("data-option-a", questionData.options.A || "");
		card.setAttribute("data-option-b", questionData.options.B || "");
		card.setAttribute("data-option-c", questionData.options.C || "");
		card.setAttribute("data-option-d", questionData.options.D || "");
	}

	function updateCardIndexing() {
		var cards = document.querySelectorAll(".question_card");
		cards.forEach(function (card, index) {
			card.setAttribute("data-question-index", String(index + 1));
			var number = card.querySelector(".question_number");
			if (number) {
				number.textContent = String(index + 1);
			}
		});
	}

	function removeCard(card) {
		if (card && card.parentNode) {
			card.parentNode.removeChild(card);
			updateCardIndexing();
		}
	}

	if (questionForm) {
		questionForm.addEventListener("submit", function (event) {
			event.preventDefault();

			if (!quizId || quizId === "0") {
				alert("Save the quiz first before adding questions.");
				return;
			}

			var payload = {
				action: "create_question",
				quiz_id: quizId,
				question_text: document.getElementById("question_text").value,
				marks: document.getElementById("marks").value,
				correct_option: document.getElementById("correct_answer").value,
				option_a: document.querySelector('[name="option_a"]').value,
				option_b: document.querySelector('[name="option_b"]').value,
				option_c: document.querySelector('[name="option_c"]').value,
				option_d: document.querySelector('[name="option_d"]').value
			};

			request("POST", payload, function (error, response) {
				if (error) {
					alert(error);
					return;
				}

				if (response.quiz) {
					updateQuizSummary(response.quiz);
				}

				window.location.reload();
			});
		});
	}

	if (publishButton) {
		publishButton.addEventListener("click", function () {
			if (!quizId || quizId === "0") {
				alert("Quiz must be saved before it can be published.");
				return;
			}

			request("POST", {
				action: "toggle_quiz",
				quiz_id: quizId
			}, function (error, response) {
				if (error) {
					alert(error);
					return;
				}

				if (response.quiz) {
					updateQuizSummary(response.quiz);
				}
			});
		});
	}

	if (questionsList) {
		questionsList.addEventListener("click", function (event) {
			var button = event.target.closest("button[data-action]");
			if (!button) {
				return;
			}

			var card = button.closest(".question_card");
			if (!card) {
				return;
			}

			var action = button.getAttribute("data-action");
			var cardData = getCardData(card);

			if (action === "edit") {
				renderQuestionEdit(card, cardData);
				return;
			}

			if (action === "cancel") {
				renderQuestionDisplay(card, cardData);
				return;
			}

			if (action === "save") {
				var editText = card.querySelector(".edit_question_text");
				var editMarks = card.querySelector(".edit_question_marks");
				var editCorrect = card.querySelector(".edit_correct_option");
				var optionInputs = card.querySelectorAll(".options_preview .option_input");
				var updatedOptions = { A: "", B: "", C: "", D: "" };

				optionInputs.forEach(function (input) {
					updatedOptions[input.getAttribute("data-option-key")] = input.value;
				});

				request("PATCH", {
					action: "update_question",
					quiz_id: quizId,
					question_id: cardData.id,
					question_text: editText ? editText.value : cardData.question_text,
					marks: editMarks ? editMarks.value : cardData.marks,
					correct_option: editCorrect ? editCorrect.value : cardData.correct_option,
					option_a: updatedOptions.A,
					option_b: updatedOptions.B,
					option_c: updatedOptions.C,
					option_d: updatedOptions.D
				}, function (error, response) {
					if (error) {
						alert(error);
						return;
					}

					var updated = response.question || {
						id: cardData.id,
						index: cardData.index,
						question_text: editText ? editText.value : cardData.question_text,
						marks: editMarks ? editMarks.value : cardData.marks,
						correct_option: editCorrect ? editCorrect.value : cardData.correct_option,
						options: updatedOptions
					};

					syncCardAttributes(card, updated);
					renderQuestionDisplay(card, updated);
					if (response.quiz) {
						updateQuizSummary(response.quiz);
					}
				});
				return;
			}

			if (action === "delete") {
				if (!window.confirm("Delete this question?")) {
					return;
				}

				request("DELETE", {
					action: "delete_question",
					quiz_id: quizId,
					question_id: cardData.id
				}, function (error, response) {
					if (error) {
						alert(error);
						return;
					}

					removeCard(card);
					if (response.quiz) {
						updateQuizSummary(response.quiz);
					}
				});
			}
		});
	}
});
