<?php
session_start();

// ── Retrieve (and then clear) session messages ──
$emailErr      = $_SESSION['email_error']    ?? null;
$passwordErr   = $_SESSION['password_error'] ?? null;
$loginAlert    = $_SESSION['login_alert']    ?? null;

unset($_SESSION['email_error'], $_SESSION['password_error'], $_SESSION['login_alert']);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Sign In — QuizForge</title>
  <link rel="stylesheet" href="../style/login.css" />
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet"/>
</head>
<body>

  <div class="page-wrapper">

    <!-- ── Left brand panel ── -->
    <aside class="brand-panel">
      <div class="brand-panel__inner">
        <div class="brand-logo">
          <span class="brand-logo__icon">⬡</span>
          <span class="brand-logo__name">QuizForge</span>
        </div>
        <div class="brand-copy">
          <h2 class="brand-copy__headline">Good to<br>see you<br>again.</h2>
          <p class="brand-copy__sub">Pick up right where you left off — your quizzes, scores, and progress are waiting.</p>
        </div>
        <div class="stat-cards" aria-hidden="true">
          <div class="stat-card stat-card--a">
            <span class="stat-card__value">12k+</span>
            <span class="stat-card__label">Active learners</span>
          </div>
          <div class="stat-card stat-card--b">
            <span class="stat-card__value">340+</span>
            <span class="stat-card__label">Quiz categories</span>
          </div>
          <div class="stat-card stat-card--c">
            <span class="stat-card__value">98%</span>
            <span class="stat-card__label">Satisfaction rate</span>
          </div>
        </div>
        <div class="brand-deco" aria-hidden="true">
          <div class="deco-ring deco-ring--1"></div>
          <div class="deco-ring deco-ring--2"></div>
          <div class="deco-ring deco-ring--3"></div>
        </div>
      </div>
    </aside>

    <!-- ── Right form panel ── -->
    <main class="form-panel">
      <div class="form-panel__inner">
        <div class="mobile-logo">
          <span class="brand-logo__icon">⬡</span>
          <span class="brand-logo__name">QuizForge</span>
        </div>

        <header class="form-header">
          <h1 class="form-header__title">Welcome back</h1>
          <p class="form-header__sub">Don't have an account? <a href="registration.php" class="form-link">Create one</a></p>
        </header>

        <!-- ── Alert banners (shown only when appropriate) ── -->
        <?php if ($loginAlert === 'suspended'): ?>
        <div class="alert alert--suspended" role="alert" aria-live="assertive">
          <span class="alert__icon" aria-hidden="true">🚫</span>
          <div class="alert__body">
            <strong>Account suspended</strong>
            <p>Your account has been suspended. Please contact <a href="mailto:support@quizforge.io" class="alert__link">support@quizforge.io</a> if you think this is a mistake.</p>
          </div>
        </div>
        <?php elseif ($loginAlert === 'invalid'): ?>
        <div class="alert alert--error" role="alert" aria-live="assertive">
          <span class="alert__icon" aria-hidden="true">⚠</span>
          <div class="alert__body">
            <strong>Incorrect email or password</strong>
            <p>Please double-check your credentials and try again.</p>
          </div>
        </div>
        <?php endif; ?>

        <!-- ── Login form ── -->
        <form action="../../controller/loginValidation.php" method="POST" class="login-form" novalidate id="loginForm">
          <table class="form-table">
            <tbody>

              <!-- Email -->
              <tr class="form-row">
                <td class="form-label-cell">
                  <label for="email">Email</label>
                </td>
                <td class="form-input-cell">
                  <div class="input-wrap">
                    <span class="input-icon">
                      <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <rect x="2.5" y="5" width="15" height="10.5" rx="2" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M2.5 7.5l7.5 5 7.5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                      </svg>
                    </span>
                    <input
                      type="email"
                      id="email"
                      name="email"
                      class="form-input<?= $emailErr ? ' input--error' : '' ?>"
                      placeholder="you@example.com"
                      autocomplete="email"
                      value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                      required
                    />
                  </div>
                  <?php if ($emailErr): ?>
                  <span class="form-error"><?= htmlspecialchars($emailErr) ?></span>
                  <?php endif; ?>
                </td>
              </tr>

              <!-- Password -->
              <tr class="form-row">
                <td class="form-label-cell">
                  <label for="password">Password</label>
                </td>
                <td class="form-input-cell">
                  <div class="input-wrap">
                    <span class="input-icon">
                      <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <rect x="5" y="9" width="10" height="7.5" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M7 9V6.5a3 3 0 016 0V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <circle cx="10" cy="12.5" r="1" fill="currentColor"/>
                      </svg>
                    </span>
                    <input
                      type="password"
                      id="password"
                      name="password"
                      class="form-input<?= $passwordErr ? ' input--error' : '' ?>"
                      placeholder="Your password"
                      autocomplete="current-password"
                      required
                    />
                    <button type="button" class="toggle-pwd" id="togglePwd" aria-label="Show password" tabindex="-1">
                      <svg class="eye-icon eye-open" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M1 10s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6z" stroke="currentColor" stroke-width="1.5"/>
                        <circle cx="10" cy="10" r="2.5" stroke="currentColor" stroke-width="1.5"/>
                      </svg>
                      <svg class="eye-icon eye-closed hidden" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M3 3l14 14M8.5 8.7A2.5 2.5 0 0013 10M1 10s3.5-6 9-6c1.4 0 2.7.3 3.85.85M19 10s-1.2 2-3.15 3.85M5.5 5.7C3.2 7 1 10 1 10s3.5 6 9 6a8.7 8.7 0 004.5-1.2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                      </svg>
                    </button>
                  </div>
                  <?php if ($passwordErr): ?>
                  <span class="form-error"><?= htmlspecialchars($passwordErr) ?></span>
                  <?php endif; ?>
                </td>
              </tr>

              <!-- Options row -->
              <tr class="form-row form-row--options">
                <td class="form-label-cell"></td>
                <td class="form-input-cell">
                  <div class="form-options">
                    <label class="remember-label">
                      <input type="checkbox" name="remember" id="remember" />
                      <span>Remember me</span>
                    </label>
                    <a href="forgot_password.php" class="form-link forgot-link">Forgot password?</a>
                  </div>
                </td>
              </tr>

              <!-- Submit -->
              <tr class="form-row form-row--submit">
                <td colspan="2" class="form-submit-cell">
                  <button type="submit" class="submit-btn" id="submitBtn">
                    <span class="submit-btn__text">Sign In</span>
                    <span class="submit-btn__arrow" aria-hidden="true">→</span>
                  </button>
                </td>
              </tr>

            </tbody>
          </table>
        </form>

      </div>
    </main>

  </div>

  <!-- Password show/hide toggle only -->
  <script>
    (function() {
      const toggleBtn = document.getElementById('togglePwd');
      const pwdInput  = document.getElementById('password');
      if (!toggleBtn || !pwdInput) return;

      const eyeOpen   = toggleBtn.querySelector('.eye-open');
      const eyeClosed = toggleBtn.querySelector('.eye-closed');

      toggleBtn.addEventListener('click', () => {
        const show = pwdInput.type === 'password';
        pwdInput.type = show ? 'text' : 'password';
        eyeOpen.classList.toggle('hidden', show);
        eyeClosed.classList.toggle('hidden', !show);
        toggleBtn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      });
    })();
  </script>

</body>
</html>