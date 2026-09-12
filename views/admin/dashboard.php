<?php
$pageTitle   = 'Admin Dashboard';
$pageHeading = 'Administrator Portal';
$pageSub     = '';
$isEdit      = !empty($editing);
require __DIR__ . '/../partials/header.php';
?>

<!-- ============ LIVE STATISTICS PANEL ============ -->
<section class="stat-grid" id="statGrid">
    <div class="stat-card">
        <span class="stat-value" data-stat="students"><?= (int)$stats['students'] ?></span>
        <span class="stat-label">Students</span>
    </div>
    <div class="stat-card">
        <span class="stat-value" data-stat="agencies"><?= (int)$stats['agencies'] ?></span>
        <span class="stat-label">Agencies</span>
    </div>
    <div class="stat-card">
        <span class="stat-value" data-stat="universities"><?= (int)$stats['universities'] ?></span>
        <span class="stat-label">Universities</span>
    </div>
    <div class="stat-card">
        <span class="stat-value" data-stat="pending_verifications"><?= (int)$stats['pending_verifications'] ?></span>
        <span class="stat-label">Pending Verifications</span>
    </div>
    <div class="stat-card">
        <span class="stat-value" data-stat="gross_revenue"><?= money($stats['gross_revenue']) ?></span>
        <span class="stat-label">Gross Volume</span>
    </div>
    <div class="stat-card">
        <span class="stat-value" data-stat="commission_earnings"><?= money($stats['commission_earnings']) ?></span>
        <span class="stat-label">Platform Earnings</span>
    </div>
</section>

<div class="tab-nav">
    <button class="tab-btn active" onclick="showTab(event, 'tabUsers')">User Accounts</button>
    <button class="tab-btn" onclick="showTab(event, 'tabVerifications')">Verifications (<?= (int)$stats['pending_verifications'] ?>)</button>
    <button class="tab-btn" onclick="showTab(event, 'tabRevenue')">Revenue &amp; Commission</button>
    <button class="tab-btn" onclick="showTab(event, 'tabFeedback')">Student Feedback (<?= (int)$stats['feedbacks'] ?>)</button>
    <button class="tab-btn" onclick="showTab(event, 'tabLogs')">Activity Log</button>
    <button class="tab-btn" onclick="showTab(event, 'tabProfile')">My Profile</button>
</div>

<!-- ==================== TAB 1: USERS CRUD ==================== -->
<div id="tabUsers" class="tab-content" style="display: block;">
    <!-- CREATE / UPDATE FORM -->
    <div class="card form-card">
        <h3 class="card-title">
            <?= $isEdit ? 'Edit user account #' . (int)$editing['id'] : 'Create a new user account' ?>
        </h3>

        <form method="POST" class="form" novalidate onsubmit="return validateForm(this);"
              action="index.php?page=admin&amp;action=<?= $isEdit ? 'update_user&amp;id=' . (int)$editing['id'] : 'add_user' ?>">
            <?php csrf_field(); ?>

            <div class="field-row">
                <div class="field">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" data-label="Full name" data-min="3"
                           value="<?= esc($editing['name'] ?? '') ?>" placeholder="Full name" required>
                </div>
                <div class="field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" data-label="Username" data-min="3"
                           value="<?= esc($editing['username'] ?? '') ?>" placeholder="Username" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" data-label="Email"
                           value="<?= esc($editing['email'] ?? '') ?>" placeholder="name@example.com" required>
                </div>
                <div class="field">
                    <label for="contact">Contact Number</label>
                    <input type="text" id="contact" name="contact" data-label="Contact number" data-phone="1"
                           value="<?= esc($editing['contact'] ?? '') ?>" placeholder="+880 1XXXXXXXXX" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="role">Role</label>
                    <select id="role" name="role" data-label="Role" required>
                        <?php foreach (['admin', 'agency', 'university', 'student'] as $r): ?>
                            <option value="<?= $r ?>" <?= (($editing['role'] ?? 'student') === $r) ? 'selected' : '' ?>>
                                <?= esc(role_label($r)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($isEdit): ?>
                    <div class="field">
                        <label for="status">Account Status</label>
                        <select id="status" name="status" data-label="Status" required>
                            <option value="active"    <?= (($editing['status'] ?? '') === 'active')    ? 'selected' : '' ?>>Active</option>
                            <option value="suspended" <?= (($editing['status'] ?? '') === 'suspended') ? 'selected' : '' ?>>Suspended</option>
                        </select>
                    </div>
                <?php else: ?>
                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" data-label="Password" data-min="6"
                               placeholder="At least 6 characters" required>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($isEdit): ?>
                <div class="field">
                    <label for="password">New Password <span class="label-hint">(leave blank to keep current password)</span></label>
                    <input type="password" id="password" name="password" data-label="New password" data-min="6"
                           placeholder="Only fill this in to change the password">
                </div>
            <?php endif; ?>

            <div class="form-actions">
                <?php if ($isEdit): ?>
                    <a href="index.php?page=admin" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary">Create user</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- USERS TABLE -->
    <div class="card">
        <div class="card-toolbar">
            <div class="search-wrap">
                <span class="search-icon">&#128269;</span>
                <input type="text" id="userSearch" class="search-input"
                       placeholder="Search by name, username, email or phone...">
            </div>
            <select id="roleFilter" class="filter-select" onchange="filterUsersByRole(this.value)">
                <option value="">All roles</option>
                <option value="admin"      <?= $roleFilter === 'admin'      ? 'selected' : '' ?>>Administrators</option>
                <option value="agency"     <?= $roleFilter === 'agency'     ? 'selected' : '' ?>>Agencies</option>
                <option value="university" <?= $roleFilter === 'university' ? 'selected' : '' ?>>Universities</option>
                <option value="student"    <?= $roleFilter === 'student'    ? 'selected' : '' ?>>Students</option>
            </select>
            <span class="badge" id="userCount"><?= count($users) ?> accounts</span>
        </div>

        <div class="table-wrap">
            <table class="data-table" id="usersTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="9" class="text-center muted">No accounts found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= (int)$u['id'] ?></td>
                                <td><strong><?= esc($u['name']) ?></strong></td>
                                <td><code><?= esc($u['username']) ?></code></td>
                                <td><?= esc($u['email']) ?></td>
                                <td><?= esc($u['contact']) ?></td>
                                <td><span class="badge badge-info"><?= esc(role_label($u['role'])) ?></span></td>
                                <td><?= status_badge($u['status']) ?></td>
                                <td><?= nice_date($u['created_at']) ?></td>
                                <td class="actions">
                                    <a href="index.php?page=admin&amp;action=edit_user&amp;id=<?= (int)$u['id'] ?>" class="btn-sm btn-ghost">Edit</a>
                                    
                                    <?php if ($u['id'] !== (int)$me['id']): ?>
                                        <?php if ($u['status'] === 'active'): ?>
                                            <a href="<?= csrf_url('index.php?page=admin&amp;action=status&amp;id=' . (int)$u['id'] . '&amp;to=suspended') ?>"
                                               class="btn-sm btn-warn" onclick="return confirm('Suspend this account?');">Suspend</a>
                                        <?php else: ?>
                                            <a href="<?= csrf_url('index.php?page=admin&amp;action=status&amp;id=' . (int)$u['id'] . '&amp;to=active') ?>"
                                               class="btn-sm btn-success">Activate</a>
                                        <?php endif; ?>

                                        <a href="<?= csrf_url('index.php?page=admin&amp;action=delete_user&amp;id=' . (int)$u['id']) ?>"
                                           class="btn-sm btn-danger" onclick="return confirm('Permanently delete this user?');">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== TAB 2: VERIFICATIONS ==================== -->
<div id="tabVerifications" class="tab-content" style="display: none;">
    <div class="card">
        <h3 class="card-title">Agency Partner Verifications</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Contact Person</th>
                        <th>License No.</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Decision / Audit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agencies as $ag): ?>
                        <tr>
                            <td><strong><?= esc($ag['company_name']) ?></strong></td>
                            <td><?= esc($ag['name']) ?><br><small class="muted"><?= esc($ag['email']) ?></small></td>
                            <td><code><?= esc($ag['license_no'] ?? 'N/A') ?></code></td>
                            <td><?= esc($ag['address'] ?? 'Not provided') ?></td>
                            <td><?= status_badge($ag['verified_status']) ?></td>
                            <td>
                                <form method="POST" action="index.php?page=admin&amp;action=verify" style="display: flex; gap: 6px;">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="type" value="agency">
                                    <input type="hidden" name="id" value="<?= (int)$ag['id'] ?>">
                                    <input type="text" name="notes" placeholder="Notes" class="input-sm" value="<?= esc($ag['verification_notes'] ?? '') ?>">
                                    <button type="submit" name="status" value="approved" class="btn-sm btn-success">Approve</button>
                                    <button type="submit" name="status" value="rejected" class="btn-sm btn-danger">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h3 class="card-title">University Institutional Verifications</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>University Name</th>
                        <th>Location</th>
                        <th>Ranking</th>
                        <th>Representative</th>
                        <th>Status</th>
                        <th>Decision / Audit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($universities as $un): ?>
                        <tr>
                            <td><strong><?= esc($un['uni_name']) ?></strong></td>
                            <td><?= esc($un['city']) ?>, <?= esc($un['country']) ?></td>
                            <td><?= esc($un['ranking'] ?? 'Unranked') ?></td>
                            <td><?= esc($un['name']) ?><br><small class="muted"><?= esc($un['email']) ?></small></td>
                            <td><?= status_badge($un['verified_status']) ?></td>
                            <td>
                                <form method="POST" action="index.php?page=admin&amp;action=verify" style="display: flex; gap: 6px;">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="type" value="university">
                                    <input type="hidden" name="id" value="<?= (int)$un['id'] ?>">
                                    <input type="text" name="notes" placeholder="Notes" class="input-sm" value="<?= esc($un['verification_notes'] ?? '') ?>">
                                    <button type="submit" name="status" value="approved" class="btn-sm btn-success">Approve</button>
                                    <button type="submit" name="status" value="rejected" class="btn-sm btn-danger">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== TAB 3: REVENUE & COMMISSION ==================== -->
<div id="tabRevenue" class="tab-content" style="display: none;">
    <div class="card">
        <h3 class="card-title">Automated Revenue &amp; Commission Distribution</h3>
        <p class="muted" style="margin-bottom: 16px;">Platform revenue is automatically calculated upon student package bookings: 90% is credited directly to the consultancy agency and 10% platform commission is retained by the administrator.</p>

        <div class="stat-grid" style="margin-bottom: 24px;">
            <div class="stat-card" style="background: #F8FAFC; border: 1px solid var(--border);">
                <span class="stat-value" style="color: var(--text);"><?= money($stats['gross_revenue']) ?></span>
                <span class="stat-label">Gross Platform Volume (100%)</span>
            </div>
            <div class="stat-card" style="background: #F0FDF4; border: 1px solid #BBF7D0;">
                <span class="stat-value" style="color: var(--success);">+<?= money($stats['commission_earnings']) ?></span>
                <span class="stat-label">Admin Platform Profit (10%)</span>
            </div>
            <div class="stat-card" style="background: #EFF6FF; border: 1px solid #BFDBFE;">
                <span class="stat-value" style="color: var(--primary);">+<?= money($stats['gross_revenue'] - $stats['commission_earnings']) ?></span>
                <span class="stat-label">Total Disbursed to Agencies (90%)</span>
            </div>
            <div class="stat-card" style="background: #FAF5FF; border: 1px solid #E9D5FF;">
                <span class="stat-value" style="color: #7E22CE;"><?= count($transactions) ?></span>
                <span class="stat-label">Total Transactions</span>
            </div>
        </div>

        <h3 class="card-title" style="margin-top: 10px;">Platform Financial Ledger</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>TX #</th>
                        <th>Type / Details</th>
                        <th>Payer Account</th>
                        <th>Gross Paid (100%)</th>
                        <th>Agency Share (90%)</th>
                        <th>Admin Cut (10%)</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td><code>#TX-<?= str_pad((int)$tx['id'], 4, '0', STR_PAD_LEFT) ?></code></td>
                            <td>
                                <span class="badge badge-info"><?= esc(ucwords(str_replace('_', ' ', $tx['transaction_type']))) ?></span>
                                <?php if (!empty($tx['package_title'])): ?>
                                    <br><small class="muted"><?= esc($tx['package_title']) ?> (<?= esc($tx['agency_name'] ?? 'Agency') ?>)</small>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= esc($tx['user_name']) ?></strong><br><small class="muted"><?= esc($tx['user_email']) ?></small></td>
                            <td><strong><?= money($tx['amount']) ?></strong></td>
                            <td><strong style="color: var(--primary);"><?= money($tx['agency_share'] ?? ($tx['amount'] - $tx['platform_commission'])) ?></strong></td>
                            <td><strong style="color: var(--success);">+<?= money($tx['platform_commission']) ?></strong></td>
                            <td><?= esc($tx['payment_method']) ?></td>
                            <td><?= status_badge($tx['status']) ?></td>
                            <td><?= nice_date($tx['transaction_date']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== TAB 4: STUDENT FEEDBACK ==================== -->
<div id="tabFeedback" class="tab-content" style="display: none;">
    <div class="card">
        <h3 class="card-title">Student Reviews &amp; Dispute Resolution</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Category</th>
                        <th>Rating</th>
                        <th>Subject &amp; Message</th>
                        <th>Admin Response</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($feedbacks)): ?>
                        <tr><td colspan="7" class="text-center muted">No student feedback found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($feedbacks as $fb): ?>
                            <tr>
                                <td><strong><?= esc($fb['student_name']) ?></strong><br><small class="muted"><?= esc($fb['student_email']) ?></small></td>
                                <td><span class="badge badge-muted"><?= esc(ucfirst($fb['target_type'])) ?></span></td>
                                <td><strong style="color: var(--warn);">&#9733; <?= (int)$fb['rating'] ?>/5</strong></td>
                                <td style="max-width: 250px;">
                                    <strong><?= esc($fb['subject']) ?></strong>
                                    <p class="muted" style="font-size: 13px;"><?= esc($fb['message']) ?></p>
                                </td>
                                <td style="max-width: 220px;">
                                    <?php if (!empty($fb['admin_response'])): ?>
                                        <span style="color: var(--success); font-size: 13px;"><?= esc($fb['admin_response']) ?></span>
                                    <?php else: ?>
                                        <span class="muted" style="font-size: 12px;">Awaiting response</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= status_badge($fb['status']) ?></td>
                                <td>
                                    <button class="btn-sm btn-ghost" onclick="openReplyModal(<?= (int)$fb['id'] ?>, '<?= esc(addslashes($fb['subject'])) ?>', '<?= esc(addslashes($fb['admin_response'] ?? '')) ?>')">Reply</button>
                                    <a href="<?= csrf_url('index.php?page=admin&amp;action=feedback_delete&amp;id=' . (int)$fb['id']) ?>" class="btn-sm btn-danger" onclick="return confirm('Delete feedback?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== TAB 5: SYSTEM LOGS ==================== -->
<div id="tabLogs" class="tab-content" style="display: none;">
    <div class="card">
        <h3 class="card-title">System Activity Audit Log</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Action Logged</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td><?= (int)$l['id'] ?></td>
                            <td><strong><?= esc($l['user_name'] ?? 'System') ?></strong> (<code><?= esc($l['username'] ?? 'sys') ?></code>)</td>
                            <td><span class="badge badge-info"><?= esc(role_label($l['role'])) ?></span></td>
                            <td><?= esc($l['action']) ?></td>
                            <td><?= nice_date($l['created_at']) ?> <small class="muted"><?= date('H:i', strtotime($l['created_at'])) ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== TAB 6: ADMIN PROFILE & SECURITY ==================== -->
<div id="tabProfile" class="tab-content" style="display: none;">
    <div class="card form-card">
        <h3 class="card-title">Administrator Profile</h3>
        <div class="field-row">
            <div class="field">
                <label>Administrator Name</label>
                <input type="text" value="<?= esc($me['name'] ?? '') ?>" readonly class="input-readonly">
            </div>
            <div class="field">
                <label>Username</label>
                <input type="text" value="<?= esc($me['username'] ?? '') ?>" readonly class="input-readonly">
            </div>
        </div>
        <div class="field-row" style="margin-top: 14px;">
            <div class="field">
                <label>Email Address</label>
                <input type="text" value="<?= esc($me['email'] ?? '') ?>" readonly class="input-readonly">
            </div>
            <div class="field">
                <label>Contact Number</label>
                <input type="text" value="<?= esc($me['contact'] ?? '') ?>" readonly class="input-readonly">
            </div>
        </div>
    </div>

    <!-- CHANGE PASSWORD CARD -->
    <div class="card form-card" style="margin-top: 24px;">
        <h3 class="card-title">Change Password</h3>
        <form method="POST" action="index.php?page=admin&amp;action=change_password" class="form" novalidate onsubmit="return validateForm(this);">
            <?php csrf_field(); ?>

            <div class="field">
                <label for="adm_curr_pass">Current Password</label>
                <input type="password" id="adm_curr_pass" name="current_password" data-label="Current Password" placeholder="Enter current password" required>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="adm_new_pass">New Password</label>
                    <input type="password" id="adm_new_pass" name="new_password" data-label="New Password" data-min="6" placeholder="At least 6 characters" required>
                </div>
                <div class="field">
                    <label for="adm_conf_pass">Confirm New Password</label>
                    <input type="password" id="adm_conf_pass" name="confirm_password" data-label="Confirm Password" data-match="new_password" placeholder="Repeat new password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
    </div>
</div>

<!-- Modal: Reply to Feedback -->
<div id="replyModal" class="modal-overlay" style="display: none;">
    <div class="modal-card">
        <h3 id="replyModalTitle">Respond to Student Feedback</h3>
        <form method="POST" action="index.php?page=admin&amp;action=feedback_respond" class="form">
            <?php csrf_field(); ?>
            <input type="hidden" name="feedback_id" id="replyFeedbackId">

            <div class="field">
                <label>Feedback Subject</label>
                <input type="text" id="replySubject" readonly class="input-readonly">
            </div>

            <div class="field">
                <label for="replyResponse">Official Response</label>
                <textarea id="replyResponse" name="response" rows="4" placeholder="Type your response to the student..." required></textarea>
            </div>

            <div class="field">
                <label for="replyStatus">Resolution Status</label>
                <select id="replyStatus" name="status">
                    <option value="resolved">Resolved</option>
                    <option value="pending">Pending</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-ghost" onclick="closeReplyModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Response</button>
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

function filterUsersByRole(role) {
    window.location.href = 'index.php?page=admin&role=' + encodeURIComponent(role);
}

function openReplyModal(id, subject, existingResp) {
    document.getElementById('replyFeedbackId').value = id;
    document.getElementById('replySubject').value = subject;
    document.getElementById('replyResponse').value = existingResp;
    document.getElementById('replyModal').style.display = 'flex';
}

function closeReplyModal() {
    document.getElementById('replyModal').style.display = 'none';
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
