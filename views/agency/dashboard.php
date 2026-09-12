<?php
$pageTitle   = 'Agency Dashboard';
$pageHeading = 'Consultancy Agency Portal';
$pageSub     = '';
$isEdit      = !empty($editing);
require __DIR__ . '/../partials/header.php';
?>

<!-- ============ LIVE STATISTICS PANEL ============ -->
<section class="stat-grid" id="statGrid">
    <div class="stat-card">
        <span class="stat-value" data-stat="packages"><?= (int)$stats['packages'] ?></span>
        <span class="stat-label">Service Packages</span>
    </div>
    <div class="stat-card">
        <span class="stat-value" data-stat="clients"><?= (int)$stats['clients'] ?></span>
        <span class="stat-label">Total Clients</span>
    </div>
    <div class="stat-card">
        <span class="stat-value" data-stat="visa_approved"><?= (int)$stats['visa_approved'] ?></span>
        <span class="stat-label">Visas Approved</span>
    </div>
    <div class="stat-card">
        <span class="stat-value" data-stat="in_progress"><?= (int)$stats['in_progress'] ?></span>
        <span class="stat-label">In Progress</span>
    </div>
    <div class="stat-card">
        <span class="stat-value" data-stat="success_rate"><?= (float)$stats['success_rate'] ?>%</span>
        <span class="stat-label">Success Rate</span>
    </div>
    <div class="stat-card">
        <span class="stat-value" data-stat="earnings"><?= money($stats['net_earnings']) ?></span>
        <span class="stat-label">Net Earnings (90%)</span>
    </div>
</section>

<!-- ============ NAVIGATION TABS ============ -->
<div class="tab-nav">
    <button class="tab-btn active" onclick="showTab(event, 'tabPackages')">Service Packages</button>
    <button class="tab-btn" onclick="showTab(event, 'tabApplications')">Application Tracker (<?= count($applications) ?>)</button>
    <button class="tab-btn" onclick="showTab(event, 'tabReports')">Performance Reports</button>
    <button class="tab-btn" onclick="showTab(event, 'tabProfile')">Agency Profile</button>
</div>

<!-- ==================== TAB 1: SERVICE PACKAGES ==================== -->
<div id="tabPackages" class="tab-content" style="display: block;">
    <div class="card form-card">
        <h3 class="card-title"><?= $isEdit ? 'Edit Service Package #' . (int)$editing['id'] : 'Publish a New Service Package' ?></h3>
        <form method="POST" class="form" novalidate onsubmit="return validateForm(this);"
              action="index.php?page=agency&amp;action=<?= $isEdit ? 'update_package&amp;id=' . (int)$editing['id'] : 'add_package' ?>">
            <?php csrf_field(); ?>

            <div class="field-row">
                <div class="field">
                    <label for="pkg_title">Package Title</label>
                    <input type="text" id="pkg_title" name="title" data-label="Package Title" data-min="4"
                           value="<?= esc($editing['title'] ?? '') ?>" placeholder="e.g. Full Study Abroad Guidance" required>
                </div>
                <div class="field">
                    <label for="pkg_price">Consultancy Fee ($)</label>
                    <input type="number" step="0.01" id="pkg_price" name="price" data-label="Fee"
                           value="<?= esc($editing['price'] ?? '') ?>" placeholder="450.00" required>
                </div>
                <div class="field">
                    <label for="pkg_duration">Duration (Weeks)</label>
                    <input type="number" id="pkg_duration" name="duration_weeks" data-label="Duration"
                           value="<?= esc($editing['duration_weeks'] ?? '4') ?>" required>
                </div>
            </div>

            <div class="field">
                <label for="pkg_desc">Package Overview &amp; Description</label>
                <textarea id="pkg_desc" name="description" rows="2" placeholder="Brief summary of consultancy support..." required><?= esc($editing['description'] ?? '') ?></textarea>
            </div>

            <div class="field">
                <label for="pkg_features">Features &amp; Deliverables <span class="label-hint">(comma-separated)</span></label>
                <textarea id="pkg_features" name="features_list" rows="2" placeholder="University Shortlisting, SOP Review, Mock Interviews, Visa Filing" required><?= esc($editing['features_list'] ?? '') ?></textarea>
            </div>

            <label class="checkbox">
                <input type="checkbox" name="is_active" value="1" <?= (!isset($editing['is_active']) || $editing['is_active']) ? 'checked' : '' ?>>
                <span>Active &amp; Visible in Student Directory</span>
            </label>

            <div class="form-actions">
                <?php if ($isEdit): ?>
                    <a href="index.php?page=agency" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary">Publish Package</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title">Offered Service Packages</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Package Title</th>
                        <th>Fee</th>
                        <th>Duration</th>
                        <th>Features</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($packages)): ?>
                        <tr><td colspan="6" class="text-center muted">No service packages created yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($packages as $pkg): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($pkg['title']) ?></strong>
                                    <p class="muted" style="font-size: 12px;"><?= esc($pkg['description']) ?></p>
                                </td>
                                <td><strong><?= money($pkg['price']) ?></strong></td>
                                <td><?= (int)$pkg['duration_weeks'] ?> Weeks</td>
                                <td style="max-width: 250px;"><small><?= esc($pkg['features_list']) ?></small></td>
                                <td><?= $pkg['is_active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-muted">Draft</span>' ?></td>
                                <td class="actions">
                                    <a href="index.php?page=agency&amp;action=edit_package&amp;id=<?= (int)$pkg['id'] ?>" class="btn-sm btn-ghost">Edit</a>
                                    <a href="<?= csrf_url('index.php?page=agency&amp;action=delete_package&amp;id=' . (int)$pkg['id']) ?>"
                                       class="btn-sm btn-danger" onclick="return confirm('Delete this package?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== TAB 2: APPLICATION TRACKER ==================== -->
<div id="tabApplications" class="tab-content" style="display: none;">
    <div class="card">
        <div class="card-toolbar">
            <div class="search-wrap">
                <span class="search-icon">&#128269;</span>
                <input type="text" id="appSearch" class="search-input" placeholder="Search student name, email, package...">
            </div>
            <select id="appStatusFilter" class="filter-select" onchange="window.location.href='index.php?page=agency&app_status='+this.value+'#applications'">
                <option value="">All stages</option>
                <option value="pending"          <?= ($statusFilter === 'pending') ? 'selected' : '' ?>>Pending</option>
                <option value="under_review"     <?= ($statusFilter === 'under_review') ? 'selected' : '' ?>>Under Review</option>
                <option value="offer_issued"     <?= ($statusFilter === 'offer_issued') ? 'selected' : '' ?>>Offer Issued</option>
                <option value="visa_in_progress" <?= ($statusFilter === 'visa_in_progress') ? 'selected' : '' ?>>Visa In Progress</option>
                <option value="visa_approved"    <?= ($statusFilter === 'visa_approved') ? 'selected' : '' ?>>Visa Approved</option>
                <option value="rejected"         <?= ($statusFilter === 'rejected') ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>

        <div class="table-wrap">
            <table class="data-table" id="appTable">
                <thead>
                    <tr>
                        <th>App #</th>
                        <th>Student Client</th>
                        <th>Service / Package</th>
                        <th>Intake</th>
                        <th>Current Milestone</th>
                        <th>Remarks / Notes</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)): ?>
                        <tr><td colspan="7" class="text-center muted">No student applications assigned yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><code>#AP-<?= str_pad((int)$app['id'], 4, '0', STR_PAD_LEFT) ?></code></td>
                                <td>
                                    <strong><?= esc($app['student_name']) ?></strong><br>
                                    <small class="muted"><?= esc($app['student_email']) ?> | <?= esc($app['student_contact']) ?></small>
                                </td>
                                <td><?= esc($app['package_name'] ?? ($app['uni_program_name'] ?? 'Direct Service')) ?></td>
                                <td><?= esc($app['intake']) ?></td>
                                <td><?= status_badge($app['status']) ?></td>
                                <td style="max-width: 220px;"><small class="muted"><?= esc($app['remarks'] ?? 'No remarks') ?></small></td>
                                <td>
                                    <button class="btn-sm btn-primary" onclick="openAppStageModal(<?= (int)$app['id'] ?>, '<?= esc(addslashes($app['student_name'])) ?>', '<?= esc($app['status']) ?>', '<?= esc(addslashes($app['remarks'] ?? '')) ?>')">Update Stage</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== TAB 3: PERFORMANCE REPORTS ==================== -->
<div id="tabReports" class="tab-content" style="display: none;">
    <div class="card">
        <h3 class="card-title">Automated Revenue &amp; Earnings Overview</h3>
        <p class="muted" style="margin-bottom: 16px;">Platform revenue model automatically credits 90% of all student package bookings directly to your agency, with 10% deducted for platform administration.</p>
        
        <div class="stat-grid" style="margin-bottom: 20px;">
            <div class="stat-card" style="background: #F8FAFC; border: 1px solid var(--border);">
                <span class="stat-value" style="color: var(--text);"><?= money($stats['gross_bookings']) ?></span>
                <span class="stat-label">Gross Package Sales (100%)</span>
            </div>
            <div class="stat-card" style="background: #FFFBEB; border: 1px solid #FDE68A;">
                <span class="stat-value" style="color: #B45309;">-<?= money($stats['platform_deductions']) ?></span>
                <span class="stat-label">Platform Admin Fee (10%)</span>
            </div>
            <div class="stat-card" style="background: #F0FDF4; border: 1px solid #BBF7D0;">
                <span class="stat-value" style="color: var(--success);">+<?= money($stats['net_earnings']) ?></span>
                <span class="stat-label">Your Net Credited Payout (90%)</span>
            </div>
        </div>

        <h4 style="margin: 20px 0 10px; font-size: 15px;">Recent Booking Payments</h4>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>TX #</th>
                        <th>Student Client</th>
                        <th>Booked Package</th>
                        <th>Gross Price</th>
                        <th>Admin Fee (10%)</th>
                        <th>Net Credited (90%)</th>
                        <th>Payment Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($agencyTransactions)): ?>
                        <tr><td colspan="7" class="text-center muted">No booking payments received yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($agencyTransactions as $atx): ?>
                            <tr>
                                <td><code>#TX-<?= str_pad((int)$atx['id'], 4, '0', STR_PAD_LEFT) ?></code></td>
                                <td>
                                    <strong><?= esc($atx['student_name']) ?></strong><br>
                                    <small class="muted"><?= esc($atx['student_email']) ?></small>
                                </td>
                                <td><?= esc($atx['package_title']) ?></td>
                                <td><?= money($atx['amount']) ?></td>
                                <td style="color: var(--error);">-<?= money($atx['platform_commission']) ?></td>
                                <td><strong style="color: var(--success);">+<?= money($atx['net_earned']) ?></strong></td>
                                <td><?= nice_date($atx['transaction_date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== TAB 4: AGENCY PROFILE ==================== -->
<div id="tabProfile" class="tab-content" style="display: none;">
    <div class="card form-card">
        <h3 class="card-title">Agency Business Profile</h3>
        <form method="POST" action="index.php?page=agency&amp;action=update_profile" class="form" novalidate onsubmit="return validateForm(this);">
            <?php csrf_field(); ?>

            <div class="field-row">
                <div class="field">
                    <label for="prof_company">Company / Agency Name</label>
                    <input type="text" id="prof_company" name="company_name" value="<?= esc($agency['company_name'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="prof_license">Trade License / Reg Number</label>
                    <input type="text" id="prof_license" name="license_no" value="<?= esc($agency['license_no'] ?? '') ?>" placeholder="BD-EDU-2026-XX">
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="prof_name">Authorized Representative Name</label>
                    <input type="text" id="prof_name" name="name" value="<?= esc($me['name'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="prof_contact">Contact Phone</label>
                    <input type="text" id="prof_contact" name="contact" value="<?= esc($me['contact'] ?? '') ?>" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="prof_web">Official Website URL</label>
                    <input type="url" id="prof_web" name="website" value="<?= esc($agency['website'] ?? '') ?>" placeholder="https://agency.com">
                </div>
                <div class="field">
                    <label for="prof_addr">Office Physical Address</label>
                    <input type="text" id="prof_addr" name="address" value="<?= esc($agency['address'] ?? '') ?>" placeholder="House #, Road #, City">
                </div>
            </div>

            <div class="field">
                <label for="prof_bio">Company Profile &amp; Accreditation Bio</label>
                <textarea id="prof_bio" name="bio" rows="3" placeholder="Describe agency services and background..."><?= esc($agency['bio'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Save Profile Details</button>
        </form>
    </div>

    <!-- CHANGE PASSWORD CARD -->
    <div class="card form-card" style="margin-top: 24px;">
        <h3 class="card-title">Change Password</h3>
        <form method="POST" action="index.php?page=agency&amp;action=change_password" class="form" novalidate onsubmit="return validateForm(this);">
            <?php csrf_field(); ?>

            <div class="field">
                <label for="ap_curr_pass">Current Password</label>
                <input type="password" id="ap_curr_pass" name="current_password" data-label="Current Password" placeholder="Enter current password" required>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="ap_new_pass">New Password</label>
                    <input type="password" id="ap_new_pass" name="new_password" data-label="New Password" data-min="6" placeholder="At least 6 characters" required>
                </div>
                <div class="field">
                    <label for="ap_conf_pass">Confirm New Password</label>
                    <input type="password" id="ap_conf_pass" name="confirm_password" data-label="Confirm Password" data-match="new_password" placeholder="Repeat new password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
    </div>
</div>

<!-- Modal: Update Application Stage -->
<div id="stageModal" class="modal-overlay" style="display: none;">
    <div class="modal-card">
        <h3>Update Student Application Stage</h3>
        <form method="POST" action="index.php?page=agency&amp;action=update_application" class="form">
            <?php csrf_field(); ?>
            <input type="hidden" name="application_id" id="modalAppId">

            <div class="field">
                <label>Student Client</label>
                <input type="text" id="modalStudentName" readonly class="input-readonly">
            </div>

            <div class="field">
                <label for="modalStageStatus">Pipeline Stage</label>
                <select id="modalStageStatus" name="status" required>
                    <option value="pending">Pending</option>
                    <option value="under_review">Under Review</option>
                    <option value="offer_issued">Offer Issued</option>
                    <option value="visa_in_progress">Visa In Progress</option>
                    <option value="visa_approved">Visa Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>

            <div class="field">
                <label for="modalRemarks">Progress Remarks &amp; Instructions</label>
                <textarea id="modalRemarks" name="remarks" rows="3" placeholder="Case notes or next document required..." required></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-ghost" onclick="closeAppStageModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Milestone</button>
            </div>
        </form>
    </div>
</div>

<script>
function showTab(evt, tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.getElementById(tabId).style.display = 'block';
    evt.currentTarget.classList.add('active');
}

function openAppStageModal(id, studentName, currentStatus, remarks) {
    document.getElementById('modalAppId').value = id;
    document.getElementById('modalStudentName').value = studentName;
    document.getElementById('modalStageStatus').value = currentStatus;
    document.getElementById('modalRemarks').value = remarks;
    document.getElementById('stageModal').style.display = 'flex';
}

function closeAppStageModal() {
    document.getElementById('stageModal').style.display = 'none';
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
