<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create account &mdash; <?= esc(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body class="auth-body">

<div class="auth-shell auth-shell-register">
    <!-- Left panel -->
    <div class="auth-side">
        <div class="logo-big">&#127758;</div>
        <h1>Create Account</h1>
        <p>Abroad Management System &mdash; Gateway for Higher Education &amp; Global Study</p>
    </div>

    <!-- Right panel: the form -->
    <div class="auth-form-wrap">
        <div class="auth-card auth-card-wide">
            <h2>Create an account</h2>
            <p class="muted">Fill in your information to get started</p>

            <?php foreach (get_flash() as $flash): ?>
                <div class="alert alert-<?= esc($flash['type']) ?>"><?= esc($flash['message']) ?></div>
            <?php endforeach; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= esc($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=register" class="form"
                  novalidate onsubmit="return validateForm(this);">
                <?php csrf_field(); ?>

                <div class="field">
                    <label for="role">Account Type</label>
                    <select id="role" name="role" data-label="Account Type" required onchange="toggleRegisterFields(this.value)">
                        <option value="student" <?= ($old['role'] ?? '') === 'student' ? 'selected' : '' ?>>Student</option>
                        <option value="agency" <?= ($old['role'] ?? '') === 'agency' ? 'selected' : '' ?>>Consultancy Agency</option>
                        <option value="university" <?= ($old['role'] ?? '') === 'university' ? 'selected' : '' ?>>University Representative</option>
                    </select>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" data-label="Full name" data-min="3"
                               value="<?= esc($old['name'] ?? '') ?>" placeholder="Your full name" required>
                    </div>
                    <div class="field">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" data-label="Username" data-min="3"
                               value="<?= esc($old['username'] ?? '') ?>" placeholder="Desired username" required>
                        <span id="usernameNote" class="field-note"></span>
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" data-label="Email"
                               value="<?= esc($old['email'] ?? '') ?>" placeholder="name@example.com" required>
                    </div>
                    <div class="field">
                        <label for="contact">Contact Number</label>
                        <input type="text" id="contact" name="contact" data-label="Contact number" data-phone="1"
                               value="<?= esc($old['contact'] ?? '') ?>" placeholder="+880 1XXXXXXXXX" required>
                    </div>
                </div>

                <!-- Role specific fields -->
                <div id="studentFields" style="display: block;">
                    <div class="field">
                        <label for="target_country">Target Study Country</label>
                        <input type="text" id="target_country" name="target_country" data-label="Target Country"
                               value="<?= esc($old['targetCountry'] ?? 'United States') ?>" placeholder="e.g. USA, UK, Canada">
                    </div>
                </div>

                <div id="agencyFields" style="display: none;">
                    <div class="field-row">
                        <div class="field">
                            <label for="company_name">Agency / Company Name</label>
                            <input type="text" id="company_name" name="company_name" data-label="Company name"
                                   value="<?= esc($old['companyName'] ?? '') ?>" placeholder="Global Pathway Education">
                        </div>
                        <div class="field">
                            <label for="license_no">Trade License Number</label>
                            <input type="text" id="license_no" name="license_no" data-label="License number"
                                   value="<?= esc($old['licenseNo'] ?? '') ?>" placeholder="BD-EDU-2026-XX">
                        </div>
                    </div>
                </div>

                <div id="universityFields" style="display: none;">
                    <div class="field">
                        <label for="uni_name">University Name</label>
                        <input type="text" id="uni_name" name="uni_name" data-label="University name"
                               value="<?= esc($old['uniName'] ?? '') ?>" placeholder="Harvard University">
                    </div>
                    <div class="field-row" style="margin-top: 10px;">
                        <div class="field">
                            <label for="country">Country</label>
                            <input type="text" id="country" name="country" data-label="Country"
                                   value="<?= esc($old['country'] ?? 'United States') ?>" placeholder="United States">
                        </div>
                        <div class="field">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" data-label="City"
                                   value="<?= esc($old['city'] ?? 'Cambridge, MA') ?>" placeholder="Cambridge, MA">
                        </div>
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" data-label="Password" data-min="6"
                               placeholder="At least 6 characters" required>
                    </div>
                    <div class="field">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" data-label="Confirm Password"
                               data-match="password" placeholder="Repeat password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Create account</button>
            </form>

            <p class="auth-foot">
                Already have an account? <a href="index.php?page=login">Sign in here</a>
            </p>
        </div>
    </div>
</div>

<script src="assets/js/app.js"></script>
<script>
function toggleRegisterFields(role) {
    var studentFields = document.getElementById('studentFields');
    var agencyFields = document.getElementById('agencyFields');
    var universityFields = document.getElementById('universityFields');

    if (studentFields) studentFields.style.display = (role === 'student') ? 'block' : 'none';
    if (agencyFields) agencyFields.style.display = (role === 'agency') ? 'block' : 'none';
    if (universityFields) universityFields.style.display = (role === 'university') ? 'block' : 'none';
}
toggleRegisterFields(document.getElementById('role').value);

// AJAX live username availability check
(function () {
    var input = document.getElementById('username');
    var note  = document.getElementById('usernameNote');
    var timer;

    if (!input || !note) return;

    input.addEventListener('input', function () {
        clearTimeout(timer);
        var value = input.value.trim();
        if (value === '') { note.textContent = ''; note.className = 'field-note'; return; }

        timer = setTimeout(function () {
            fetch('index.php?page=ajax&action=check_username&username=' + encodeURIComponent(value))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    note.textContent = data.message;
                    note.className = 'field-note ' + (data.ok ? 'note-ok' : 'note-bad');
                })
                .catch(function () { note.textContent = ''; });
        }, 300);
    });
})();
</script>
</body>
</html>
