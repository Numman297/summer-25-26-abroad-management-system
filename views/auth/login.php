<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign in &mdash; <?= esc(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body class="auth-body">

<div class="auth-shell">
    <!-- Left panel -->
    <div class="auth-side">
        <div class="logo-big">&#127758;</div>
        <h1>Welcome</h1>
        <p>Abroad Management System &mdash; Gateway for Higher Education &amp; Global Study</p>
    </div>

    <!-- Right panel: the form -->
    <div class="auth-form-wrap">
        <div class="auth-card">
            <h2>Welcome back</h2>
            <p class="muted">Sign in to access your dashboard</p>

            <?php foreach (get_flash() as $flash): ?>
                <div class="alert alert-<?= esc($flash['type']) ?>"><?= esc($flash['message']) ?></div>
            <?php endforeach; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= esc($error) ?></div>
            <?php endif; ?>

            <!-- onsubmit runs the JavaScript validation in assets/js/app.js -->
            <form method="POST" action="index.php?page=login" class="form"
                  novalidate onsubmit="return validateForm(this);">
                <?php csrf_field(); ?>

                <div class="field">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" data-label="Username or Email"
                           value="<?= esc($prefill ?? '') ?>"
                           placeholder="username or email" required autofocus>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" data-label="Password"
                           placeholder="Your password" required>
                </div>

                <label class="checkbox">
                    <input type="checkbox" name="remember" <?= !empty($prefill) ? 'checked' : '' ?>>
                    <span>Remember my username on this device</span>
                </label>

                <button type="submit" class="btn btn-primary btn-block">Sign in</button>
            </form>

            <p class="auth-foot">
                Don't have an account? <a href="index.php?page=register">Create an account</a>
            </p>
        </div>
    </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
