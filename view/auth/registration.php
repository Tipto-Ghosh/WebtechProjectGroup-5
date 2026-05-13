<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Retrieve errors and old input from session, then clear them
$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Register — QuizForge</title>
  <link rel="stylesheet" href="../style/registration.css" />
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet"/>
  <script src="../../controller/js/checkEmail.js"></script>
</head>
<body>

  <div class="page-wrapper">

    <!-- Left panel -->
    <aside class="brand-panel">
      <div class="brand-panel__inner">
        <div class="brand-logo">
          <span class="brand-logo__icon">⬡</span>
          <span class="brand-logo__name">QuizForge</span>
        </div>
        <div class="brand-copy">
          <h2 class="brand-copy__headline">Build.<br>Test.<br>Master.</h2>
          <p class="brand-copy__sub">The platform where knowledge gets forged under pressure.</p>
        </div>
        <ul class="brand-features">
          <li><span class="feat-icon">◈</span> Create unlimited quizzes</li>
          <li><span class="feat-icon">◈</span> Real-time leaderboards</li>
          <li><span class="feat-icon">◈</span> Detailed analytics</li>
          <li><span class="feat-icon">◈</span> Prepare yourself for the future</li>
        </ul>
        <div class="brand-deco" aria-hidden="true">
          <div class="deco-ring deco-ring--1"></div>
          <div class="deco-ring deco-ring--2"></div>
          <div class="deco-ring deco-ring--3"></div>
        </div>
      </div>
    </aside>

    <!-- Right panel - form -->
    <main class="form-panel">
      <div class="form-panel__inner">

        <header class="form-header">
          <h1 class="form-header__title">Create account</h1>
          <p class="form-header__sub">Already have one? <a href="../auth/login.php" class="form-link">Sign in</a></p>
        </header>

        <!-- Role selector -->
        <div class="role-selector" role="group" aria-labelledby="role-label">
          <p class="role-selector__label" id="role-label">I am joining as</p>
          <div class="role-cards">

            <label class="role-card" for="role-student">
              <input type="radio" id="role-student" name="role" value="student" <?= (($old_input['role'] ?? 'student') === 'student') ? 'checked' : '' ?> />
              <div class="role-card__body">
                <span class="role-card__icon">🎓</span>
                <span class="role-card__title">Student</span>
                <span class="role-card__desc">Take quizzes &amp; track progress</span>
              </div>
              <span class="role-card__check" aria-hidden="true">✓</span>
            </label>

            <label class="role-card" for="role-instructor">
              <input type="radio" id="role-instructor" name="role" value="instructor" <?= (($old_input['role'] ?? '') === 'instructor') ? 'checked' : '' ?> />
              <div class="role-card__body">
                <span class="role-card__icon">🖊</span>
                <span class="role-card__title">Instructor</span>
                <span class="role-card__desc">Create &amp; manage quizzes</span>
              </div>
              <span class="role-card__check" aria-hidden="true">✓</span>
            </label>

          </div>
        </div>

        <!-- Registration table-form -->
        <form action="../../controller/registrationValidation.php" method="POST" class="reg-form" id="regForm" novalidate>
          <input type="hidden" name="role" id="hidden-role" value="student" />

          <table class="form-table">
            <tbody>

              <tr class="form-row">
                <td class="form-label-cell">
                  <label for="full_name">Full Name</label>
                </td>
                <td class="form-input-cell">
                  <div class="input-wrap">
                    <span class="input-icon">
                      <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="10" cy="7" r="3.5" stroke="currentColor" stroke-width="1.5"/><path d="M3 17c0-3.314 3.134-6 7-6s7 2.686 7 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </span>
                    <input type="text" id="full_name" name="full_name" class="form-input" placeholder="e.g. Tipto Ghosh" value="<?= htmlspecialchars($old_input['full_name'] ?? '') ?>" required />
                  </div>
                  <span class="form-error" id="err-name"><?= htmlspecialchars($errors['full_name'] ?? '') ?></span>
                </td>
              </tr>

              <tr class="form-row">
                  <td class="form-label-cell">
                    <label for="email">Email</label>
                  </td>
                  <td class="form-input-cell">
                    <div class="input-wrap">
                      <span class="input-icon">
                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><rect x="2.5" y="5" width="15" height="10.5" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M2.5 7.5l7.5 5 7.5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                      </span>
                      <input type="email" id="email" name="email" class="form-input" placeholder="you@example.com"
                            value="<?= htmlspecialchars($old_input['email'] ?? '') ?>"
                            onkeyup="checkEmail()" required />
                    </div>
                    <span id="emailresponse" class="availability-msg"></span>
                    <span class="form-error" id="err-email"><?= htmlspecialchars($errors['email'] ?? '') ?></span>
                  </td>
              </tr>

              <tr class="form-row">
                <td class="form-label-cell">
                  <label for="password">Password</label>
                </td>
                <td class="form-input-cell">
                  <div class="input-wrap">
                    <span class="input-icon">
                      <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><rect x="5" y="9" width="10" height="7.5" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M7 9V6.5a3 3 0 016 0V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="10" cy="12.5" r="1" fill="currentColor"/></svg>
                    </span>
                    <input
                      type="password"
                      id="password"
                      name="password"
                      class="form-input"
                      placeholder="Minimum 8 characters"
                      autocomplete="new-password"
                      required
                      minlength="8"
                      value=""
                    />
                    <button type="button" class="toggle-pwd" id="togglePwd" aria-label="Show password" tabindex="-1">
                      <svg class="eye-icon eye-open" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M1 10s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6z" stroke="currentColor" stroke-width="1.5"/><circle cx="10" cy="10" r="2.5" stroke="currentColor" stroke-width="1.5"/></svg>
                      <svg class="eye-icon eye-closed hidden" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M3 3l14 14M8.5 8.7A2.5 2.5 0 0013 10M1 10s3.5-6 9-6c1.4 0 2.7.3 3.85.85M19 10s-1.2 2-3.15 3.85M5.5 5.7C3.2 7 1 10 1 10s3.5 6 9 6a8.7 8.7 0 004.5-1.2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                  </div>
                  <div class="pwd-strength" id="pwdStrength" aria-live="polite">
                    <div class="pwd-strength__bar">
                      <div class="pwd-strength__fill" id="pwdFill"></div>
                    </div>
                    <span class="pwd-strength__label" id="pwdLabel"></span>
                  </div>
                  <span class="form-error" id="err-password"><?= htmlspecialchars($errors['password'] ?? '') ?></span>
                </td>
              </tr>

              <tr class="form-row">
                <td class="form-label-cell">
                  <label for="confirm_password">Confirm</label>
                </td>
                <td class="form-input-cell">
                  <div class="input-wrap">
                    <span class="input-icon">
                      <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 10l3.5 3.5L15 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.5"/></svg>
                    </span>
                    <input
                      type="password"
                      id="confirm_password"
                      name="confirm_password"
                      class="form-input"
                      placeholder="Repeat your password"
                      autocomplete="new-password"
                      required
                      value=""
                    />
                  </div>
                  <span class="form-error" id="err-confirm"><?= htmlspecialchars($errors['confirm_password'] ?? '') ?></span>
                </td>
              </tr>

              <tr class="form-row form-row--submit">
                <td colspan="2" class="form-submit-cell">
                  <label class="terms-label">
                    <input type="checkbox" name="terms" id="terms" <?= isset($old_input['terms']) ? 'checked' : '' ?> required />
                    <span>I agree to the <a href="#" class="form-link">Terms of Service</a> and <a href="#" class="form-link">Privacy Policy</a></span>
                  </label>
                  <span class="form-error" id="err-terms"><?= htmlspecialchars($errors['terms'] ?? '') ?></span>
                  <button type="submit" class="submit-btn" id="submitBtn">
                    <span class="submit-btn__text">Create Account</span>
                    <span class="submit-btn__arrow" aria-hidden="true">→</span>
                  </button>
                </td>
              </tr>

            </tbody>
          </table>
        </form>

      </div>
    </main>

  </div><!-- /page-wrapper -->

</body>
</html>