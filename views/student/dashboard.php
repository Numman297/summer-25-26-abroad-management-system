<?php
$pageTitle   = 'Student Dashboard';
$pageHeading = 'Student Study Abroad Portal';
$pageSub     = '';
$isEditEdu   = !empty($editingEdu);
require __DIR__ . '/../partials/header.php';
?>

<!-- ============ LIVE STATISTICS PANEL ============ -->
<section class="stat-grid" id="statGrid">
    <div class="stat-card">
        <span class="stat-value" data-stat="mock_tests"><?= (int)$stats['mock_tests'] ?></span>
        <span class="stat-label">Mock Tests Logged</span>
    </div>
    <div class="stat-card">
        <span class="stat-value" data-stat="best_score"><?= esc($stats['best_score']) ?></span>
        <span class="stat-label">Highest Score</span>
    </div>
    <div class="stat-card">
        <span class="stat-value" data-stat="applications"><?= (int)$stats['applications'] ?></span>
        <span class="stat-label">Active Applications</span>
    </div>
    <div class="stat-card">
        <span class="stat-value" data-stat="travel_posts"><?= (int)$stats['travel_posts'] ?></span>
        <span class="stat-label">Open Flight Buddies</span>
    </div>
</section>

<!-- ============ NAVIGATION TABS ============ -->
<div class="tab-nav">
    <button class="tab-btn active" onclick="showTab(event, 'tabExplore')">Explore Catalog</button>
    <button class="tab-btn" onclick="showTab(event, 'tabApplications')">Applications (<?= count($applications) ?>)</button>
    <button class="tab-btn" onclick="showTab(event, 'tabMockTests')">Mock Tests (<?= count($mockTests) ?>)</button>
    <button class="tab-btn" onclick="showTab(event, 'tabTravel')">Travel Buddies</button>
    <button class="tab-btn" onclick="showTab(event, 'tabEducation')">Education</button>
    <button class="tab-btn" onclick="showTab(event, 'tabFeedback')">Feedback</button>
    <button class="tab-btn" onclick="showTab(event, 'tabProfile')">Profile</button>
</div>

<!-- ==================== TAB 1: EXPLORE DIRECTORY ==================== -->
<div id="tabExplore" class="tab-content" style="display: block;">
    <div class="card">
        <div class="card-toolbar">
            <h3 class="card-title">Available University Programs</h3>
            <div class="search-wrap">
                <span class="search-icon">&#128269;</span>
                <input type="text" id="progSearch" class="search-input" placeholder="Search course, university, location...">
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table" id="exploreProgTable">
                <thead>
                    <tr>
                        <th>Program Title</th>
                        <th>University &amp; Location</th>
                        <th>Annual Tuition</th>
                        <th>Admission Cutoffs</th>
                        <th>Intakes</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($explorePrograms as $ep): ?>
                        <tr>
                            <td>
                                <strong><?= esc($ep['title']) ?></strong><br>
                                <span class="badge badge-info"><?= esc($ep['degree_level']) ?></span>
                            </td>
                            <td>
                                <strong><?= esc($ep['uni_name']) ?></strong><br>
                                <small class="muted"><?= esc($ep['city']) ?>, <?= esc($ep['country']) ?> (<?= esc($ep['ranking'] ?? 'Top Ranked') ?>)</small>
                            </td>
                            <td><strong><?= money($ep['tuition_fee']) ?></strong></td>
                            <td>
                                <small>
                                    <strong>CGPA:</strong> <?= number_format((float)$ep['min_cgpa'], 2) ?>+ |
                                    <strong>IELTS:</strong> <?= number_format((float)$ep['min_ielts'], 1) ?>+
                                </small>
                            </td>
                            <td><small><?= esc($ep['intake_season']) ?></small></td>
                            <td>
                                <button class="btn-sm btn-primary" onclick="openApplyProgModal(<?= (int)$ep['id'] ?>, '<?= esc(addslashes($ep['title'])) ?>', '<?= esc(addslashes($ep['uni_name'])) ?>')">Apply</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Agency Service Packages -->
    <div class="card" style="margin-top: 20px;">
        <h3 class="card-title">Consultancy Agency Packages</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Service Package</th>
                        <th>Consultancy Agency</th>
                        <th>Fee</th>
                        <th>Duration</th>
                        <th>Included Services</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($explorePackages as $epkg): ?>
                        <tr>
                            <td>
                                <strong><?= esc($epkg['title']) ?></strong>
                                <p class="muted" style="font-size:12px;"><?= esc($epkg['description']) ?></p>
                            </td>
                            <td>
                                <strong><?= esc($epkg['company_name']) ?></strong><br>
                                <small class="muted"><?= esc($epkg['address']) ?></small>
                            </td>
                            <td><strong><?= money($epkg['price']) ?></strong></td>
                            <td><?= (int)$epkg['duration_weeks'] ?> Weeks</td>
                            <td style="max-width:240px;"><small><?= esc($epkg['features_list']) ?></small></td>
                            <td>
                                <button class="btn-sm btn-success" onclick="openBookPkgModal(<?= (int)$epkg['id'] ?>, <?= (int)$epkg['agency_id'] ?>, '<?= esc(addslashes($epkg['title'])) ?>', <?= (float)$epkg['price'] ?>)">Book Package</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Scholarships -->
    <div class="card" style="margin-top: 20px;">
        <h3 class="card-title">Open University Scholarships</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Scholarship Title</th>
                        <th>University</th>
                        <th>Grant Value</th>
                        <th>Eligibility</th>
                        <th>Deadline</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($exploreScholarships as $esch): ?>
                        <tr>
                            <td><strong><?= esc($esch['title']) ?></strong></td>
                            <td><?= esc($esch['uni_name']) ?> (<?= esc($esch['country']) ?>)</td>
                            <td><strong style="color:var(--success);"><?= money($esch['grant_amount']) ?></strong></td>
                            <td style="max-width:250px;"><small><?= esc($esch['eligibility_criteria']) ?></small></td>
                            <td><?= nice_date($esch['deadline']) ?></td>
                            <td>
                                <button class="btn-sm btn-ghost" onclick="openApplySchModal(<?= (int)$esch['id'] ?>, '<?= esc(addslashes($esch['title'])) ?>')">Apply Grant</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== TAB 2: MY APPLICATIONS ==================== -->
<div id="tabApplications" class="tab-content" style="display: none;">
    <div class="card">
        <h3 class="card-title">My Application Progress</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>App #</th>
                        <th>Application Name</th>
                        <th>Institution / Agency</th>
                        <th>Intake</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Official Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)): ?>
                        <tr><td colspan="7" class="text-center muted">You have not submitted any applications yet. Explore programs above to get started!</td></tr>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><code>#AP-<?= str_pad((int)$app['id'], 4, '0', STR_PAD_LEFT) ?></code></td>
                                <td><strong><?= esc($app['program_title'] ?? $app['package_title']) ?></strong></td>
                                <td><?= esc($app['uni_name'] ?? $app['company_name']) ?></td>
                                <td><?= esc($app['intake']) ?></td>
                                <td><span class="badge badge-info"><?= esc(ucfirst($app['application_type'])) ?></span></td>
                                <td><?= status_badge($app['status']) ?></td>
                                <td>
                                    <small class="muted"><?= esc($app['remarks'] ?? 'Awaiting next milestone') ?></small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== TAB 3: MOCK TESTS ==================== -->
<div id="tabMockTests" class="tab-content" style="display: none;">
    <div class="card form-card">
        <h3 class="card-title">Log Exam Practice Score</h3>
        <form method="POST" action="index.php?page=student&amp;action=add_mock_test" class="form" novalidate onsubmit="return validateForm(this);">
            <?php csrf_field(); ?>

            <div class="field-row">
                <div class="field">
                    <label for="mock_type">Exam Type</label>
                    <select id="mock_type" name="test_type" onchange="updateMockLabels(this.value)">
                        <option value="IELTS">IELTS Academic (0 - 9.0)</option>
                        <option value="TOEFL">TOEFL iBT (0 - 120)</option>
                        <option value="GRE">GRE General (260 - 340)</option>
                        <option value="SAT">SAT</option>
                        <option value="Duolingo">Duolingo English Test</option>
                    </select>
                </div>
                <div class="field">
                    <label for="mock_date">Test Date</label>
                    <input type="date" id="mock_date" name="test_date" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label id="lbl_sec1" for="mock_r">Reading Score</label>
                    <input type="number" step="0.5" id="mock_r" name="reading_score" value="7.5" oninput="calcOverallScore()" required>
                </div>
                <div class="field">
                    <label id="lbl_sec2" for="mock_l">Listening Score</label>
                    <input type="number" step="0.5" id="mock_l" name="listening_score" value="8.0" oninput="calcOverallScore()" required>
                </div>
                <div class="field">
                    <label id="lbl_sec3" for="mock_w">Writing Score</label>
                    <input type="number" step="0.5" id="mock_w" name="writing_score" value="6.5" oninput="calcOverallScore()" required>
                </div>
                <div class="field">
                    <label id="lbl_sec4" for="mock_s">Speaking Score</label>
                    <input type="number" step="0.5" id="mock_s" name="speaking_score" value="7.0" oninput="calcOverallScore()" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label>Calculated Overall Band / Score</label>
                    <input type="text" id="mock_overall" readonly class="input-readonly" value="7.5">
                </div>
                <div class="field">
                    <label for="mock_notes">Practice Notes / Target Areas</label>
                    <input type="text" id="mock_notes" name="notes" placeholder="Focus on Writing Task 2 vocabulary">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save Exam Score</button>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title">Score History &amp; Progress</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Exam</th>
                        <th>Test Date</th>
                        <th>Reading</th>
                        <th>Listening</th>
                        <th>Writing</th>
                        <th>Speaking</th>
                        <th>Overall Score</th>
                        <th>Notes</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($mockTests)): ?>
                        <tr><td colspan="9" class="text-center muted">No practice test scores recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($mockTests as $mt): ?>
                            <tr>
                                <td><span class="badge badge-info"><?= esc($mt['test_type']) ?></span></td>
                                <td><?= nice_date($mt['test_date']) ?></td>
                                <td><?= (float)$mt['reading_score'] ?></td>
                                <td><?= (float)$mt['listening_score'] ?></td>
                                <td><?= (float)$mt['writing_score'] ?></td>
                                <td><?= (float)$mt['speaking_score'] ?></td>
                                <td><strong style="color:var(--primary); font-size:15px;"><?= (float)$mt['overall_score'] ?></strong></td>
                                <td style="max-width:200px;"><small class="muted"><?= esc($mt['notes'] ?? '-') ?></small></td>
                                <td>
                                    <a href="<?= csrf_url('index.php?page=student&amp;action=delete_mock_test&amp;id=' . (int)$mt['id']) ?>"
                                       class="btn-sm btn-danger" onclick="return confirm('Delete this test score?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== TAB 4: TRAVEL COMPANIONS ==================== -->
<div id="tabTravel" class="tab-content" style="display: none;">
    <div class="card form-card">
        <h3 class="card-title">Post Travel Itinerary to Find Flight Buddies</h3>
        <form method="POST" action="index.php?page=student&amp;action=add_travel_post" class="form" novalidate onsubmit="return validateForm(this);">
            <?php csrf_field(); ?>

            <div class="field-row">
                <div class="field">
                    <label for="dest_country">Destination Country</label>
                    <input type="text" id="dest_country" name="destination_country" placeholder="e.g. United States" required>
                </div>
                <div class="field">
                    <label for="dest_city">Destination City</label>
                    <input type="text" id="dest_city" name="destination_city" placeholder="e.g. Boston, MA" required>
                </div>
                <div class="field">
                    <label for="dest_uni">Target University (Optional)</label>
                    <input type="text" id="dest_uni" name="destination_uni" placeholder="e.g. Harvard University">
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="trav_date">Departure Date</label>
                    <input type="date" id="trav_date" name="travel_date" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                </div>
                <div class="field">
                    <label for="trav_airline">Airline</label>
                    <input type="text" id="trav_airline" name="airline" placeholder="e.g. Qatar Airways">
                </div>
                <div class="field">
                    <label for="trav_flight">Flight Number</label>
                    <input type="text" id="trav_flight" name="flight_no" placeholder="e.g. QR-639">
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="trav_contact">Public Contact Info (WhatsApp / Email)</label>
                    <input type="text" id="trav_contact" name="contact_info" value="<?= esc($me['contact'] ?? $me['email']) ?>" required>
                </div>
                <div class="field">
                    <label for="trav_notes">Travel Notes</label>
                    <input type="text" id="trav_notes" name="notes" placeholder="Looking for students traveling from Dhaka around mid-August">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Publish Flight Buddy Post</button>
        </form>
    </div>

    <!-- My Travel Requests -->
    <?php if (!empty($myPosts)): ?>
        <div class="card">
            <h3 class="card-title">My Active Travel Requests</h3>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Destination</th>
                            <th>Departure Date</th>
                            <th>Flight Details</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myPosts as $mp): ?>
                            <tr>
                                <td><strong><?= esc($mp['destination_city']) ?>, <?= esc($mp['destination_country']) ?></strong></td>
                                <td><?= nice_date($mp['travel_date']) ?></td>
                                <td><?= esc($mp['airline'] ?? 'TBD') ?> <?= esc($mp['flight_no'] ?? '') ?></td>
                                <td><?= status_badge($mp['status']) ?></td>
                                <td>
                                    <?php if ($mp['status'] === 'open'): ?>
                                        <a href="<?= csrf_url('index.php?page=student&amp;action=close_travel_post&amp;id=' . (int)$mp['id']) ?>" class="btn-sm btn-warn">Mark Closed</a>
                                    <?php endif; ?>
                                    <a href="<?= csrf_url('index.php?page=student&amp;action=delete_travel_post&amp;id=' . (int)$mp['id']) ?>" class="btn-sm btn-danger" onclick="return confirm('Delete post?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- All Travel Posts -->
    <div class="card" style="margin-top: 20px;">
        <h3 class="card-title">Find Travel Companions &amp; Flight Buddies</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fellow Student</th>
                        <th>Destination Country &amp; City</th>
                        <th>Departure Date</th>
                        <th>Flight Details</th>
                        <th>Notes</th>
                        <th>Contact / WhatsApp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($travelPosts)): ?>
                        <tr><td colspan="6" class="text-center muted">No open travel companion posts found. Post yours above!</td></tr>
                    <?php else: ?>
                        <?php foreach ($travelPosts as $tp): ?>
                            <tr>
                                <td><strong><?= esc($tp['student_name']) ?></strong></td>
                                <td>
                                    <span class="badge badge-info"><?= esc($tp['destination_country']) ?></span><br>
                                    <strong><?= esc($tp['destination_city']) ?></strong>
                                    <?php if (!empty($tp['destination_uni'])): ?><br><small class="muted"><?= esc($tp['destination_uni']) ?></small><?php endif; ?>
                                </td>
                                <td><?= nice_date($tp['travel_date']) ?></td>
                                <td><small><?= esc($tp['airline'] ?? 'TBD') ?> <?= esc($tp['flight_no'] ?? '') ?></small></td>
                                <td style="max-width:200px;"><small class="muted"><?= esc($tp['notes'] ?? '-') ?></small></td>
                                <td><strong style="color:var(--primary);"><?= esc($tp['contact_info']) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== TAB 5: ACADEMIC EDUCATION ==================== -->
<div id="tabEducation" class="tab-content" style="display: none;">
    <div class="card form-card">
        <h3 class="card-title"><?= $isEditEdu ? 'Edit Qualification #' . (int)$editingEdu['id'] : 'Add Educational Qualification' ?></h3>
        <form method="POST" class="form" novalidate onsubmit="return validateForm(this);"
              action="index.php?page=student&amp;action=<?= $isEditEdu ? 'update_education&amp;id=' . (int)$editingEdu['id'] : 'add_education' ?>">
            <?php csrf_field(); ?>

            <div class="field-row">
                <div class="field">
                    <label for="edu_degree">Degree Title</label>
                    <input type="text" id="edu_degree" name="degree_title" value="<?= esc($editingEdu['degree_title'] ?? '') ?>" placeholder="e.g. BSc in Computer Science" required>
                </div>
                <div class="field">
                    <label for="edu_inst">Institute Name</label>
                    <input type="text" id="edu_inst" name="institute_name" value="<?= esc($editingEdu['institute_name'] ?? '') ?>" placeholder="e.g. University Name or College" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="edu_board">Board / University</label>
                    <input type="text" id="edu_board" name="board_or_uni" value="<?= esc($editingEdu['board_or_uni'] ?? '') ?>" placeholder="e.g. Dhaka Board / UGC" required>
                </div>
                <div class="field">
                    <label for="edu_year">Passing Year</label>
                    <input type="number" id="edu_year" name="passing_year" value="<?= esc($editingEdu['passing_year'] ?? date('Y')) ?>" required>
                </div>
                <div class="field">
                    <label for="edu_result">Result / CGPA</label>
                    <input type="text" id="edu_result" name="result_cgpa" value="<?= esc($editingEdu['result_cgpa'] ?? '') ?>" placeholder="e.g. 3.75 / 4.00" required>
                </div>
            </div>

            <div class="form-actions">
                <?php if ($isEditEdu): ?>
                    <a href="index.php?page=student#education" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary">Add Qualification Record</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title">Educational History</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Degree Title</th>
                        <th>Institute</th>
                        <th>Board / University</th>
                        <th>Passing Year</th>
                        <th>Result / CGPA</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($education)): ?>
                        <tr><td colspan="6" class="text-center muted">No educational qualifications added yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($education as $edu): ?>
                            <tr>
                                <td><strong><?= esc($edu['degree_title']) ?></strong></td>
                                <td><?= esc($edu['institute_name']) ?></td>
                                <td><?= esc($edu['board_or_uni']) ?></td>
                                <td><?= (int)$edu['passing_year'] ?></td>
                                <td><span class="badge badge-success"><?= esc($edu['result_cgpa']) ?></span></td>
                                <td class="actions">
                                    <a href="index.php?page=student&amp;action=edit_education&amp;id=<?= (int)$edu['id'] ?>" class="btn-sm btn-ghost">Edit</a>
                                    <a href="<?= csrf_url('index.php?page=student&amp;action=delete_education&amp;id=' . (int)$edu['id']) ?>" class="btn-sm btn-danger" onclick="return confirm('Delete qualification?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== TAB 6: FEEDBACK & REVIEWS ==================== -->
<div id="tabFeedback" class="tab-content" style="display: none;">
    <div class="card form-card">
        <h3 class="card-title">Submit Review &amp; Feedback</h3>
        <form method="POST" action="index.php?page=student&amp;action=submit_feedback" class="form" novalidate onsubmit="return validateForm(this);">
            <?php csrf_field(); ?>

            <div class="field-row">
                <div class="field">
                    <label for="fb_target">Feedback Category</label>
                    <select id="fb_target" name="target_type">
                        <option value="platform">Platform General</option>
                        <option value="agency">Consultancy Agency Experience</option>
                        <option value="university">University Admissions Portal</option>
                    </select>
                </div>
                <div class="field">
                    <label for="fb_rating">Rating (1 to 5 Stars)</label>
                    <select id="fb_rating" name="rating">
                        <option value="5">&#9733;&#9733;&#9733;&#9733;&#9733; (5 - Excellent)</option>
                        <option value="4">&#9733;&#9733;&#9733;&#9733; (4 - Very Good)</option>
                        <option value="3">&#9733;&#9733;&#9733; (3 - Average)</option>
                        <option value="2">&#9733;&#9733; (2 - Poor)</option>
                        <option value="1">&#9733; (1 - Terrible)</option>
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="fb_subj">Subject</label>
                <input type="text" id="fb_subj" name="subject" placeholder="Summary of your feedback" required>
            </div>

            <div class="field">
                <label for="fb_msg">Detailed Message / Experience</label>
                <textarea id="fb_msg" name="message" rows="3" placeholder="Describe your experience or suggestions..." required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Submit Feedback</button>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title">My Submitted Reviews &amp; Admin Responses</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Rating</th>
                        <th>Subject &amp; Message</th>
                        <th>Date</th>
                        <th>Status &amp; Official Response</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($feedbacks)): ?>
                        <tr><td colspan="5" class="text-center muted">You have not submitted any feedback yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($feedbacks as $fb): ?>
                            <tr>
                                <td><span class="badge badge-info"><?= esc(ucfirst($fb['target_type'])) ?></span></td>
                                <td><strong style="color:var(--warn);">&#9733; <?= (int)$fb['rating'] ?>/5</strong></td>
                                <td style="max-width:250px;">
                                    <strong><?= esc($fb['subject']) ?></strong>
                                    <p class="muted" style="font-size:12px;"><?= esc($fb['message']) ?></p>
                                </td>
                                <td><?= nice_date($fb['created_at']) ?></td>
                                <td>
                                    <?= status_badge($fb['status']) ?>
                                    <?php if (!empty($fb['admin_response'])): ?>
                                        <br><span style="color:var(--success); font-size:12px; font-weight:600;">Response: <?= esc($fb['admin_response']) ?></span>
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

<!-- ==================== TAB 7: STUDENT PROFILE ==================== -->
<div id="tabProfile" class="tab-content" style="display: none;">
    <div class="card form-card">
        <h3 class="card-title">My Student Profile</h3>
        <form method="POST" action="index.php?page=student&amp;action=update_profile" class="form" novalidate onsubmit="return validateForm(this);">
            <?php csrf_field(); ?>

            <div class="field-row">
                <div class="field">
                    <label for="sp_name">Full Name</label>
                    <input type="text" id="sp_name" name="name" value="<?= esc($me['name'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="sp_contact">Contact Phone</label>
                    <input type="text" id="sp_contact" name="contact" value="<?= esc($me['contact'] ?? '') ?>" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="sp_passport">Passport Number</label>
                    <input type="text" id="sp_passport" name="passport_no" value="<?= esc($student['passport_no'] ?? '') ?>" placeholder="Passport #">
                </div>
                <div class="field">
                    <label for="sp_country">Target Country</label>
                    <input type="text" id="sp_country" name="target_country" value="<?= esc($student['target_country'] ?? 'United States') ?>" placeholder="e.g. USA, UK, Canada">
                </div>
                <div class="field">
                    <label for="sp_degree">Desired Degree Level</label>
                    <select id="sp_degree" name="target_degree">
                        <?php foreach (['Bachelor', 'Master', 'PhD', 'Diploma'] as $deg): ?>
                            <option value="<?= $deg ?>" <?= (($student['target_degree'] ?? 'Master') === $deg) ? 'selected' : '' ?>><?= $deg ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="sp_bio">Academic Interests &amp; Statement</label>
                <textarea id="sp_bio" name="bio" rows="3" placeholder="Describe your study goals and intended research area..."><?= esc($student['bio'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Save Profile</button>
        </form>
    </div>

    <!-- CHANGE PASSWORD CARD -->
    <div class="card form-card" style="margin-top: 24px;">
        <h3 class="card-title">Change Password</h3>
        <form method="POST" action="index.php?page=student&amp;action=change_password" class="form" novalidate onsubmit="return validateForm(this);">
            <?php csrf_field(); ?>

            <div class="field">
                <label for="sp_curr_pass">Current Password</label>
                <input type="password" id="sp_curr_pass" name="current_password" data-label="Current Password" placeholder="Enter current password" required>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="sp_new_pass">New Password</label>
                    <input type="password" id="sp_new_pass" name="new_password" data-label="New Password" data-min="6" placeholder="At least 6 characters" required>
                </div>
                <div class="field">
                    <label for="sp_conf_pass">Confirm New Password</label>
                    <input type="password" id="sp_conf_pass" name="confirm_password" data-label="Confirm Password" data-match="new_password" placeholder="Repeat new password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
    </div>
</div>

<!-- Modal: Apply to Program -->
<div id="applyProgModal" class="modal-overlay" style="display: none;">
    <div class="modal-card">
        <h3>Submit University Program Application</h3>
        <form method="POST" action="index.php?page=student&amp;action=apply_program" class="form">
            <?php csrf_field(); ?>
            <input type="hidden" name="program_id" id="modalApplyProgId">

            <div class="field">
                <label>Program Name</label>
                <input type="text" id="modalApplyProgTitle" readonly class="input-readonly">
            </div>

            <div class="field">
                <label>Target University</label>
                <input type="text" id="modalApplyUniName" readonly class="input-readonly">
            </div>

            <div class="field">
                <label for="modalApplyIntake">Intake Semester</label>
                <select id="modalApplyIntake" name="intake">
                    <option value="Fall 2026">Fall 2026</option>
                    <option value="Spring 2027">Spring 2027</option>
                </select>
            </div>

            <div class="field">
                <label for="modalApplyRemarks">Statement of Purpose / Notes</label>
                <textarea id="modalApplyRemarks" name="remarks" rows="3" placeholder="Brief statement to admissions committee..." required></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-ghost" onclick="closeApplyProgModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Application</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Book Agency Package -->
<div id="bookPkgModal" class="modal-overlay" style="display: none;">
    <div class="modal-card">
        <h3>Book Consultancy Package</h3>
        <form method="POST" action="index.php?page=student&amp;action=book_package" class="form">
            <?php csrf_field(); ?>
            <input type="hidden" name="package_id" id="modalBookPkgId">
            <input type="hidden" name="agency_id" id="modalBookAgencyId">
            <input type="hidden" name="package_price" id="modalBookPrice">

            <div class="field">
                <label>Package Title</label>
                <input type="text" id="modalBookTitle" readonly class="input-readonly">
            </div>

            <div class="field">
                <label>Total Fee</label>
                <input type="text" id="modalBookPriceDisplay" readonly class="input-readonly">
            </div>

            <div class="field">
                <label for="modalBookIntake">Target Intake</label>
                <select id="modalBookIntake" name="intake">
                    <option value="Fall 2026">Fall 2026</option>
                    <option value="Spring 2027">Spring 2027</option>
                </select>
            </div>

            <div class="field">
                <label for="modalBookRemarks">Special Requests / Target Countries</label>
                <textarea id="modalBookRemarks" name="remarks" rows="2" placeholder="Tell the consultant about your preferred countries..."></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-ghost" onclick="closeBookPkgModal()">Cancel</button>
                <button type="submit" class="btn btn-success">Confirm Booking</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Apply Scholarship -->
<div id="applySchModal" class="modal-overlay" style="display: none;">
    <div class="modal-card">
        <h3>Apply for Scholarship Scheme</h3>
        <form method="POST" action="index.php?page=student&amp;action=apply_scholarship" class="form">
            <?php csrf_field(); ?>
            <input type="hidden" name="scholarship_id" id="modalApplySchId">

            <div class="field">
                <label>Scholarship Scheme</label>
                <input type="text" id="modalApplySchTitle" readonly class="input-readonly">
            </div>

            <div class="field">
                <label for="modalApplySchStmt">Statement of Purpose / Need</label>
                <textarea id="modalApplySchStmt" name="statement" rows="4" placeholder="Explain your academic achievements, leadership, and why you deserve this award..." required></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-ghost" onclick="closeApplySchModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Scholarship Application</button>
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

function updateMockLabels(type) {
    if (type === 'IELTS') {
        document.getElementById('lbl_sec1').innerText = "Reading (0 - 9.0)";
        document.getElementById('lbl_sec2').innerText = "Listening (0 - 9.0)";
        document.getElementById('lbl_sec3').innerText = "Writing (0 - 9.0)";
        document.getElementById('lbl_sec4').innerText = "Speaking (0 - 9.0)";
    } else if (type === 'GRE') {
        document.getElementById('lbl_sec1').innerText = "Verbal (130 - 170)";
        document.getElementById('lbl_sec2').innerText = "Quant (130 - 170)";
        document.getElementById('lbl_sec3').innerText = "AWA (0 - 6.0)";
        document.getElementById('lbl_sec4').innerText = "Bonus (0)";
    } else {
        document.getElementById('lbl_sec1').innerText = "Section 1";
        document.getElementById('lbl_sec2').innerText = "Section 2";
        document.getElementById('lbl_sec3').innerText = "Section 3";
        document.getElementById('lbl_sec4').innerText = "Section 4";
    }
    calcOverallScore();
}

function calcOverallScore() {
    var type = document.getElementById('mock_type').value;
    var r = parseFloat(document.getElementById('mock_r').value) || 0;
    var l = parseFloat(document.getElementById('mock_l').value) || 0;
    var w = parseFloat(document.getElementById('mock_w').value) || 0;
    var s = parseFloat(document.getElementById('mock_s').value) || 0;

    var overall = 0;
    if (type === 'IELTS') {
        var avg = (r + l + w + s) / 4;
        overall = Math.round(avg * 2) / 2;
    } else if (type === 'GRE' || type === 'SAT') {
        overall = r + l + w + s;
    } else {
        overall = ((r + l + w + s) / 4).toFixed(1);
    }
    document.getElementById('mock_overall').value = overall;
}

function openApplyProgModal(id, title, uniName) {
    document.getElementById('modalApplyProgId').value = id;
    document.getElementById('modalApplyProgTitle').value = title;
    document.getElementById('modalApplyUniName').value = uniName;
    document.getElementById('applyProgModal').style.display = 'flex';
}
function closeApplyProgModal() { document.getElementById('applyProgModal').style.display = 'none'; }

function openBookPkgModal(id, agencyId, title, price) {
    document.getElementById('modalBookPkgId').value = id;
    document.getElementById('modalBookAgencyId').value = agencyId;
    document.getElementById('modalBookPrice').value = price;
    document.getElementById('modalBookTitle').value = title;
    document.getElementById('modalBookPriceDisplay').value = '$' + price.toFixed(2);
    document.getElementById('bookPkgModal').style.display = 'flex';
}
function closeBookPkgModal() { document.getElementById('bookPkgModal').style.display = 'none'; }

function openApplySchModal(id, title) {
    document.getElementById('modalApplySchId').value = id;
    document.getElementById('modalApplySchTitle').value = title;
    document.getElementById('applySchModal').style.display = 'flex';
}
function closeApplySchModal() { document.getElementById('applySchModal').style.display = 'none'; }

</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
