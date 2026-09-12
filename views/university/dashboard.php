<?php
$pageTitle   = 'University Dashboard';
$pageHeading = 'University Representative Portal';
$pageSub     = '';
$isEditProg  = !empty($editingProg);
$isEditSch   = !empty($editingSch);
require __DIR__ . '/../partials/header.php';
?>

<!-- ============ LIVE STATISTICS PANEL ============ -->
<section class="stat-grid" id="statGrid">
    <div class="stat-card">
        <span class="stat-value" data-stat="programs"><?= (int)$stats['programs'] ?></span>
        <span class="stat-label">Programs Listed</span>
    </div>
    <div class="stat-card">
        <span class="stat-value" data-stat="applicants"><?= (int)$stats['applicants'] ?></span>
        <span class="stat-label">Total Applicants</span>
    </div>
    <div class="stat-card">
        <span class="stat-value" data-stat="pending"><?= (int)$stats['pending'] ?></span>
        <span class="stat-label">Pending Reviews</span>
    </div>
    <div class="stat-card">
        <span class="stat-value" data-stat="scholarships"><?= (int)$stats['scholarships'] ?></span>
        <span class="stat-label">Scholarship Schemes</span>
    </div>
</section>

<!-- ============ NAVIGATION TABS ============ -->
<div class="tab-nav">
    <button class="tab-btn active" onclick="showTab(event, 'tabPrograms')">Degree Programs</button>
    <button class="tab-btn" onclick="showTab(event, 'tabAdmissions')">Admissions Review (<?= count($applicants) ?>)</button>
    <button class="tab-btn" onclick="showTab(event, 'tabScholarships')">Scholarships &amp; Grants</button>
    <button class="tab-btn" onclick="showTab(event, 'tabRequirements')">Entry Requirements &amp; Visa</button>
    <button class="tab-btn" onclick="showTab(event, 'tabProfile')">Campus Profile</button>
</div>

<!-- ==================== TAB 1: DEGREE PROGRAMS ==================== -->
<div id="tabPrograms" class="tab-content" style="display: block;">
    <div class="card form-card">
        <h3 class="card-title"><?= $isEditProg ? 'Edit Degree Program #' . (int)$editingProg['id'] : 'Add a New Degree Program' ?></h3>
        <form method="POST" class="form" novalidate onsubmit="return validateForm(this);"
              action="index.php?page=university&amp;action=<?= $isEditProg ? 'update_program&amp;id=' . (int)$editingProg['id'] : 'add_program' ?>">
            <?php csrf_field(); ?>

            <div class="field-row">
                <div class="field">
                    <label for="prog_title">Program Title</label>
                    <input type="text" id="prog_title" name="title" data-label="Title" data-min="3"
                           value="<?= esc($editingProg['title'] ?? '') ?>" placeholder="e.g. MSc in Computer Science" required>
                </div>
                <div class="field">
                    <label for="prog_degree">Degree Level</label>
                    <select id="prog_degree" name="degree_level" data-label="Degree level" required>
                        <?php foreach (['Bachelor', 'Master', 'PhD', 'Diploma'] as $d): ?>
                            <option value="<?= $d ?>" <?= (($editingProg['degree_level'] ?? 'Master') === $d) ? 'selected' : '' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="prog_dept">Department / Faculty</label>
                    <input type="text" id="prog_dept" name="department" data-label="Department"
                           value="<?= esc($editingProg['department'] ?? '') ?>" placeholder="School of Engineering" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="prog_duration">Duration (Years)</label>
                    <input type="number" step="0.5" id="prog_duration" name="duration_years"
                           value="<?= esc($editingProg['duration_years'] ?? '2.0') ?>" required>
                </div>
                <div class="field">
                    <label for="prog_fee">Annual Tuition Fee ($)</label>
                    <input type="number" step="0.01" id="prog_fee" name="tuition_fee" data-label="Tuition Fee"
                           value="<?= esc($editingProg['tuition_fee'] ?? '') ?>" placeholder="28000.00" required>
                </div>
                <div class="field">
                    <label for="prog_intakes">Intake Semesters</label>
                    <input type="text" id="prog_intakes" name="intake_season"
                           value="<?= esc($editingProg['intake_season'] ?? 'Fall 2026, Spring 2027') ?>" required>
                </div>
            </div>

            <div class="field">
                <label for="prog_desc">Program Description &amp; Curriculum</label>
                <textarea id="prog_desc" name="description" rows="2" placeholder="Course overview and specializations..."><?= esc($editingProg['description'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <?php if ($isEditProg): ?>
                    <a href="index.php?page=university" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary">Add Program to Catalog</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title">Offered Degree Programs</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Program Title</th>
                        <th>Level &amp; Department</th>
                        <th>Duration</th>
                        <th>Tuition Fee</th>
                        <th>Intakes</th>
                        <th>Applicants</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($programs)): ?>
                        <tr><td colspan="7" class="text-center muted">No programs listed yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($programs as $p): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($p['title']) ?></strong>
                                    <p class="muted" style="font-size: 12px;"><?= esc(substr($p['description'] ?? '', 0, 70)) ?>...</p>
                                </td>
                                <td><span class="badge badge-info"><?= esc($p['degree_level']) ?></span><br><small class="muted"><?= esc($p['department']) ?></small></td>
                                <td><?= (float)$p['duration_years'] ?> Yrs</td>
                                <td><strong><?= money($p['tuition_fee']) ?></strong></td>
                                <td><small><?= esc($p['intake_season']) ?></small></td>
                                <td><span class="badge badge-primary"><?= (int)$p['total_applicants'] ?></span></td>
                                <td class="actions">
                                    <a href="index.php?page=university&amp;action=edit_program&amp;id=<?= (int)$p['id'] ?>" class="btn-sm btn-ghost">Edit</a>
                                    <a href="<?= csrf_url('index.php?page=university&amp;action=delete_program&amp;id=' . (int)$p['id']) ?>"
                                       class="btn-sm btn-danger" onclick="return confirm('Delete this program?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== TAB 2: ADMISSIONS REVIEW ==================== -->
<div id="tabAdmissions" class="tab-content" style="display: none;">
    <div class="card">
        <div class="card-toolbar">
            <div class="search-wrap">
                <span class="search-icon">&#128269;</span>
                <input type="text" id="admSearch" class="search-input" placeholder="Search applicant name, email, program...">
            </div>
            <select id="admIntakeFilter" class="filter-select" onchange="window.location.href='index.php?page=university&intake='+encodeURIComponent(this.value)+'#admissions'">
                <option value="">All intakes</option>
                <option value="Fall 2026"   <?= ($intakeFilter === 'Fall 2026') ? 'selected' : '' ?>>Fall 2026</option>
                <option value="Spring 2027" <?= ($intakeFilter === 'Spring 2027') ? 'selected' : '' ?>>Spring 2027</option>
            </select>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>App #</th>
                        <th>Applicant Student</th>
                        <th>Program</th>
                        <th>Intake</th>
                        <th>Academic History</th>
                        <th>Decision Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applicants)): ?>
                        <tr><td colspan="7" class="text-center muted">No admission applications received yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($applicants as $app): ?>
                            <tr>
                                <td><code>#UNI-<?= str_pad((int)$app['id'], 4, '0', STR_PAD_LEFT) ?></code></td>
                                <td>
                                    <strong><?= esc($app['student_name']) ?></strong><br>
                                    <small class="muted"><?= esc($app['student_email']) ?></small>
                                </td>
                                <td>
                                    <strong><?= esc($app['program_title']) ?></strong><br>
                                    <small class="muted"><?= money($app['tuition_fee']) ?> / yr</small>
                                </td>
                                <td><?= esc($app['intake']) ?></td>
                                <td style="max-width: 220px;"><small><?= esc($app['academic_summary'] ?? 'No records') ?></small></td>
                                <td>
                                    <?= status_badge($app['status']) ?>
                                </td>
                                <td>
                                    <button class="btn-sm btn-primary" onclick="openAdmissionModal(<?= (int)$app['id'] ?>, '<?= esc(addslashes($app['student_name'])) ?>', '<?= esc(addslashes($app['program_title'])) ?>', '<?= esc($app['status']) ?>', '<?= esc(addslashes($app['remarks'] ?? '')) ?>')">Review File</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== TAB 3: SCHOLARSHIPS ==================== -->
<div id="tabScholarships" class="tab-content" style="display: none;">
    <div class="card form-card">
        <h3 class="card-title"><?= $isEditSch ? 'Edit Scholarship Scheme #' . (int)$editingSch['id'] : 'Create a New Scholarship Scheme' ?></h3>
        <form method="POST" class="form" novalidate onsubmit="return validateForm(this);"
              action="index.php?page=university&amp;action=<?= $isEditSch ? 'update_scholarship&amp;id=' . (int)$editingSch['id'] : 'add_scholarship' ?>">
            <?php csrf_field(); ?>

            <div class="field-row">
                <div class="field">
                    <label for="sch_title">Scholarship Title</label>
                    <input type="text" id="sch_title" name="title" data-label="Title"
                           value="<?= esc($editingSch['title'] ?? '') ?>" placeholder="e.g. Global STEM Fellowship" required>
                </div>
                <div class="field">
                    <label for="sch_amount">Grant Amount ($)</label>
                    <input type="number" step="0.01" id="sch_amount" name="grant_amount" data-label="Amount"
                           value="<?= esc($editingSch['grant_amount'] ?? '') ?>" placeholder="15000.00" required>
                </div>
                <div class="field">
                    <label for="sch_coverage">Coverage Type</label>
                    <select id="sch_coverage" name="coverage_type">
                        <?php foreach (['Partial Tuition', 'Full Tuition', 'Living Stipend', 'One-time Grant'] as $c): ?>
                            <option value="<?= $c ?>" <?= (($editingSch['coverage_type'] ?? 'Partial Tuition') === $c) ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="sch_deadline">Application Deadline</label>
                    <input type="date" id="sch_deadline" name="deadline"
                           value="<?= esc($editingSch['deadline'] ?? date('Y-m-d', strtotime('+90 days'))) ?>" required>
                </div>
                <div class="field">
                    <label for="sch_max">Max Recipients</label>
                    <input type="number" id="sch_max" name="max_recipients"
                           value="<?= esc($editingSch['max_recipients'] ?? '5') ?>" required>
                </div>
            </div>

            <div class="field">
                <label for="sch_criteria">Eligibility Criteria</label>
                <textarea id="sch_criteria" name="eligibility_criteria" rows="2" placeholder="Minimum CGPA 3.60, IELTS 7.5 or GRE 320+" required><?= esc($editingSch['eligibility_criteria'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <?php if ($isEditSch): ?>
                    <a href="index.php?page=university#scholarships" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary">Create Scholarship</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title">Configured Scholarship Schemes</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Scheme Title</th>
                        <th>Grant Value</th>
                        <th>Coverage</th>
                        <th>Eligibility Criteria</th>
                        <th>Deadline</th>
                        <th>Awarded / Max</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scholarships as $sch): ?>
                        <tr>
                            <td><strong><?= esc($sch['title']) ?></strong></td>
                            <td><strong style="color:var(--success);"><?= money($sch['grant_amount']) ?></strong></td>
                            <td><span class="badge badge-info"><?= esc($sch['coverage_type']) ?></span></td>
                            <td style="max-width: 250px;"><small><?= esc($sch['eligibility_criteria']) ?></small></td>
                            <td><?= nice_date($sch['deadline']) ?></td>
                            <td><span class="badge badge-primary"><?= (int)$sch['awarded_count'] ?> / <?= (int)$sch['max_recipients'] ?></span></td>
                            <td class="actions">
                                <a href="index.php?page=university&amp;action=edit_scholarship&amp;id=<?= (int)$sch['id'] ?>" class="btn-sm btn-ghost">Edit</a>
                                <a href="<?= csrf_url('index.php?page=university&amp;action=delete_scholarship&amp;id=' . (int)$sch['id']) ?>"
                                   class="btn-sm btn-danger" onclick="return confirm('Delete scholarship?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Scholarship Applications -->
    <div class="card" style="margin-top: 20px;">
        <h3 class="card-title">Scholarship Applications Received</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Scholarship Scheme</th>
                        <th>Statement of Purpose</th>
                        <th>Status</th>
                        <th>Decision</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($schApps)): ?>
                        <tr><td colspan="5" class="text-center muted">No scholarship applications submitted yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($schApps as $sa): ?>
                            <tr>
                                <td><strong><?= esc($sa['student_name']) ?></strong><br><small class="muted"><?= esc($sa['student_email']) ?></small></td>
                                <td><?= esc($sa['scholarship_title']) ?><br><small style="color:var(--success); font-weight:600;"><?= money($sa['grant_amount']) ?></small></td>
                                <td style="max-width: 260px;"><small class="muted">"<?= esc($sa['statement']) ?>"</small></td>
                                <td><?= status_badge($sa['status']) ?></td>
                                <td>
                                    <form method="POST" action="index.php?page=university&amp;action=decide_scholarship" style="display: flex; gap: 4px;">
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="app_id" value="<?= (int)$sa['id'] ?>">
                                        <button type="submit" name="status" value="awarded" class="btn-sm btn-success">Award</button>
                                        <button type="submit" name="status" value="rejected" class="btn-sm btn-danger">Decline</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== TAB 4: REQUIREMENTS & VISA ==================== -->
<div id="tabRequirements" class="tab-content" style="display: none;">
    <div class="card form-card">
        <h3 class="card-title">Configure Program Entry Thresholds &amp; Visa Rules</h3>
        <form method="POST" action="index.php?page=university&amp;action=save_requirements" class="form" novalidate onsubmit="return validateForm(this);">
            <?php csrf_field(); ?>

            <div class="field">
                <label for="req_prog">Target Degree Program</label>
                <select id="req_prog" name="program_id" required>
                    <?php foreach ($programs as $p): ?>
                        <option value="<?= (int)$p['id'] ?>"><?= esc($p['title']) ?> (<?= esc($p['degree_level']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="req_cgpa">Minimum CGPA (out of 4.00)</label>
                    <input type="number" step="0.05" id="req_cgpa" name="min_cgpa" value="3.00" required>
                </div>
                <div class="field">
                    <label for="req_ielts">Minimum IELTS Band</label>
                    <input type="number" step="0.5" id="req_ielts" name="min_ielts" value="6.5" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="req_toefl">Minimum TOEFL iBT Score</label>
                    <input type="number" id="req_toefl" name="min_toefl" value="80" required>
                </div>
                <div class="field">
                    <label for="req_gre">Minimum GRE Score (0 if not required)</label>
                    <input type="number" id="req_gre" name="min_gre" value="300" required>
                </div>
            </div>

            <div class="field">
                <label for="req_docs">Required Checklist of Documents</label>
                <textarea id="req_docs" name="documents_required" rows="2" placeholder="Official Transcripts, SOP, 2 Letters of Recommendation, English Certificate">Official Transcripts, SOP, 2 Letters of Recommendation, English Certificate</textarea>
            </div>

            <div class="field">
                <label for="req_visa">Visa Solvency Guidelines &amp; Living Costs</label>
                <textarea id="req_visa" name="visa_guidelines" rows="2" placeholder="Proof of financial solvency covering 1st year tuition and living expenses...">Proof of financial solvency covering 1st year tuition and living expenses.</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Save Requirements</button>
        </form>
    </div>
</div>

<!-- ==================== TAB 5: CAMPUS PROFILE ==================== -->
<div id="tabProfile" class="tab-content" style="display: none;">
    <div class="card form-card">
        <h3 class="card-title">Campus Profile &amp; International Office</h3>
        <form method="POST" action="index.php?page=university&amp;action=update_profile" class="form" novalidate onsubmit="return validateForm(this);">
            <?php csrf_field(); ?>

            <div class="field">
                <label for="uni_name">University Full Name</label>
                <input type="text" id="uni_name" name="uni_name" value="<?= esc($uni['uni_name'] ?? '') ?>" required>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="uni_country">Country</label>
                    <input type="text" id="uni_country" name="country" value="<?= esc($uni['country'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="uni_city">City</label>
                    <input type="text" id="uni_city" name="city" value="<?= esc($uni['city'] ?? '') ?>" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="uni_ranking">Global Ranking (QS / THE)</label>
                    <input type="text" id="uni_ranking" name="ranking" value="<?= esc($uni['ranking'] ?? '') ?>" placeholder="e.g. #4 World QS">
                </div>
                <div class="field">
                    <label for="uni_website">Official Website URL</label>
                    <input type="url" id="uni_website" name="website" value="<?= esc($uni['website'] ?? '') ?>" placeholder="https://university.edu">
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="rep_name">Admissions Representative Name</label>
                    <input type="text" id="rep_name" name="name" value="<?= esc($me['name'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="rep_contact">Admissions Contact Number</label>
                    <input type="text" id="rep_contact" name="contact" value="<?= esc($me['contact'] ?? '') ?>" required>
                </div>
            </div>

            <div class="field">
                <label for="uni_bio">Campus Overview &amp; Research Facilities</label>
                <textarea id="uni_bio" name="bio" rows="3" placeholder="Describe campus facilities, faculty and research programs..."><?= esc($uni['bio'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Save Campus Profile</button>
        </form>
    </div>

    <!-- CHANGE PASSWORD CARD -->
    <div class="card form-card" style="margin-top: 24px;">
        <h3 class="card-title">Change Password</h3>
        <form method="POST" action="index.php?page=university&amp;action=change_password" class="form" novalidate onsubmit="return validateForm(this);">
            <?php csrf_field(); ?>

            <div class="field">
                <label for="up_curr_pass">Current Password</label>
                <input type="password" id="up_curr_pass" name="current_password" data-label="Current Password" placeholder="Enter current password" required>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="up_new_pass">New Password</label>
                    <input type="password" id="up_new_pass" name="new_password" data-label="New Password" data-min="6" placeholder="At least 6 characters" required>
                </div>
                <div class="field">
                    <label for="up_conf_pass">Confirm New Password</label>
                    <input type="password" id="up_conf_pass" name="confirm_password" data-label="Confirm Password" data-match="new_password" placeholder="Repeat new password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
    </div>
</div>

<!-- Modal: Review Admission & Issue Offer Letter -->
<div id="admissionModal" class="modal-overlay" style="display: none;">
    <div class="modal-card">
        <h3>Review Admission File</h3>
        <form method="POST" action="index.php?page=university&amp;action=admission_decision" class="form">
            <?php csrf_field(); ?>
            <input type="hidden" name="application_id" id="modalAdmAppId">

            <div class="field">
                <label>Student Applicant</label>
                <input type="text" id="modalAdmStudentName" readonly class="input-readonly">
            </div>

            <div class="field">
                <label>Program Applied</label>
                <input type="text" id="modalAdmProgramTitle" readonly class="input-readonly">
            </div>

            <div class="field">
                <label for="modalAdmStatus">Decision</label>
                <select id="modalAdmStatus" name="status" required>
                    <option value="under_review">Under Review</option>
                    <option value="offer_issued">Issue Official Offer Letter</option>
                    <option value="rejected">Reject Application</option>
                </select>
            </div>

            <div class="field">
                <label for="modalAdmRemarks">Admissions Remarks &amp; Conditions</label>
                <textarea id="modalAdmRemarks" name="remarks" rows="3" placeholder="Decision conditions or missing documents..." required></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-ghost" onclick="closeAdmissionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Admission Decision</button>
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

function openAdmissionModal(id, studentName, progTitle, currentStatus, remarks) {
    document.getElementById('modalAdmAppId').value = id;
    document.getElementById('modalAdmStudentName').value = studentName;
    document.getElementById('modalAdmProgramTitle').value = progTitle;
    document.getElementById('modalAdmStatus').value = currentStatus;
    document.getElementById('modalAdmRemarks').value = remarks;
    document.getElementById('admissionModal').style.display = 'flex';
}

function closeAdmissionModal() {
    document.getElementById('admissionModal').style.display = 'none';
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
