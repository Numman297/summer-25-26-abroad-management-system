# Abroad Management System (PHP + MySQL, MVC)

A teaching project for a 4-role study abroad management system: **admin, agency, student, university**.
Written in plain PHP with procedural `mysqli` and prepared statements. No frameworks,
no Composer, no build step. Copy it into XAMPP and it runs.

---

## 1. Install (XAMPP)

1. Copy the `wbt_project` folder into `C:\xampp\htdocs\wbt\`
   so it becomes `htdocs/wbt/wbt_project/`.
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. Open `http://localhost/phpmyadmin` → **Import** → choose `database.sql` → **Go**.
4. Open `http://localhost/wbt/wbt_project/`.
5. Sign in as the default admin: **admin / password123**

The admin account is created automatically the first time a page loads
(see the bottom of `config/config.php`). Everyone else signs up on the register page.

If your MySQL uses a password, change `DB_PASS` in `config/config.php`.

---

## 2. Folder structure

```
wbt_project/
├── index.php                  Front controller: the ONLY entry point (router)
├── database.sql               Schema + sample seed data (16 relational tables)
├── README.md
│
├── config/
│   └── config.php             DB connection, session settings, app constants
│
├── helpers/
│   └── helpers.php            esc(), CSRF, login guards, flash alerts, formatters
│
├── models/                    M — every SQL query lives here (Prepared Statements)
│   ├── user_model.php         all 4 roles (users table), authentication & stats
│   ├── student_model.php      catalog, applications, mock tests, travel buddies
│   ├── agency_model.php       service packages, 5-stage pipeline, earnings
│   ├── university_model.php   degree programs, intake admissions, scholarships
│   ├── revenue_model.php      automated 90/10 financial ledger & transactions
│   ├── feedback_model.php     student agency reviews & ratings
│   └── log_model.php          system-wide security audit logs
│
├── controllers/               C — request handling, validation, decisions
│   ├── auth_controller.php    login / register / logout
│   ├── admin_controller.php   user management, status control, revenue ledger
│   ├── student_controller.php catalog browsing, applications, mock tests, bookings
│   ├── agency_controller.php  package CRUD, pipeline stage updates, earnings
│   ├── university_controller.php degree programs, applicant decisions, scholarships
│   └── ajax_controller.php    all JSON endpoints (live stats & username checker)
│
├── views/                     V — HTML only
│   ├── partials/              header.php, footer.php (shared layout)
│   ├── auth/                  login.php, register.php
│   ├── admin/dashboard.php
│   ├── student/dashboard.php
│   ├── agency/dashboard.php
│   └── university/dashboard.php
│
└── assets/
    ├── css/style.css          responsive stylesheet & design system
    └── js/app.js              validation, escaping, AJAX search & dynamic tabs
```

**The MVC rule used throughout:** a view never runs a query, and a model never
prints HTML. The controller sits in the middle: it reads `$_POST`, validates,
calls the model, then `require`s the view.

---

## 3. How the router works

Every URL looks like this:

```
index.php?page=<dashboard>&action=<what to do>&id=<row id>
```

| URL | What happens |
| --- | --- |
| `index.php?page=login` | Login page |
| `index.php?page=register` | Signup page with dynamic role-specific fields |
| `index.php?page=admin` | Admin control center |
| `index.php?page=student&tab=explore` | Student course and package catalog |
| `index.php?page=agency&tab=packages` | Agency packages & services management |
| `index.php?page=university&tab=admissions` | University applicant review portal |
| `index.php?page=ajax&action=check_username&username=rahim` | Live JSON availability check |
| `index.php?page=logout` | Sign out |

`index.php` loads config → helpers → models → controllers, checks the session
timeout, then sends the request to one controller. `require_role('admin')` blocks
anyone who is not an admin before the controller even starts.

---

## 4. The four roles

Each role features a comprehensive dashboard with real-time stat cards, searchable data tables,
action forms, and domain-specific capabilities.

| Role | Manages (CRUD) | Feature 1 | Feature 2 | Feature 3 |
| --- | --- | --- | --- | --- |
| **Admin** | User accounts (all roles) | Suspend / activate accounts, reset passwords | Automated 10% platform revenue ledger | System-wide activity log with search |
| **Student** | Applications & Bookings | Explore catalog with 1-click university & package apply | IELTS / TOEFL mock test band calculator | Travel buddies community finder |
| **Agency** | Service packages | 5-stage student pipeline tracking (Counseling to Visa) | Agency 90% revenue earnings overview | Client review and feedback monitoring |
| **University** | Degree programs | Intake admissions review (Accept / Reviewing / Reject) | Scholarship creation & criteria setup | Campus intake requirements manager |

### How the roles connect

- A **student** explores the university degree catalog or consultancy packages and submits an application/booking.
- The **agency** sees the student in their 5-stage pipeline tracker, moves them from Counseling to Visa approval, and receives their 90% revenue cut.
- The **university** representative reviews the student's admission application and issues an admission decision (Accepted, Reviewing, or Rejected).
- The **admin** oversees all registered users, tracks the 10% platform commission ledger, and monitors system-wide security audit logs.

---

## 5. Requirement checklist

| Requirement | Where to look |
| --- | --- |
| **MVC** | `models/`, `controllers/`, `views/`, routed by `index.php` |
| **DB (MySQLi procedural)** | every function in `models/` uses `mysqli_prepare` & bound params |
| **Auth (session + cookie)** | `controllers/auth_controller.php`, `helpers/helpers.php` |
| **PHP validation** | the `if / elseif` chain at the top of every controller action |
| **JS validation** | `validateForm()` in `assets/js/app.js`, called by `onsubmit` |
| **AJAX / JSON** | `controllers/ajax_controller.php` (live stats & username check) |
| **UI (HTML/CSS)** | `views/`, `assets/css/style.css` |
| **Basic web security** | see section 6 |
| **Feature completeness** | Full CRUD + search + 3 distinct features per role across 16 tables |

---

## 6. Security, and why each piece is there

| Attack | Defence | File |
| --- | --- | --- |
| SQL injection | Prepared statements everywhere — user text is never glued into SQL | all `models/` |
| Stolen passwords | `password_hash()` on save, `password_verify()` on login | `user_model.php` |
| XSS (server) | `esc()` wraps every value printed into HTML | `helpers.php`, all views |
| XSS (client) | `esc()` in JavaScript before dynamic AJAX DOM insertion | `app.js` |
| CSRF | A cryptographic token in every POST form and state-modifying link | `helpers.php`, all views |
| Session fixation | `session_regenerate_id(true)` right after a successful login | `auth_controller.php` |
| Cookie theft | `httponly` + `samesite=Lax` on session & remember cookies | `config.php`, `auth_controller.php` |
| Idle machines | Automatic sign-out after 30 minutes of inactivity | `check_session_timeout()` |
| Wrong role | `require_role()` before the controller; each AJAX action re-checks | `index.php`, `ajax_controller.php` |
| URL tampering | Students and agencies can only access their own linked records | `student_model.php`, `agency_model.php` |
| Username guessing | Wrong username and wrong password give the same message | `auth_controller.php` |
| Self-lockout | An admin cannot delete, suspend or demote themselves | `admin_controller.php` |

Two things worth saying out loud to students:

1. **JavaScript validation is a convenience, not a defence.** Anyone can turn
   JavaScript off. That is why every controller repeats the checks in PHP.
2. **"Remember me" only refills the username**, never the password.

---

## 7. Settings you can change

All in `config/config.php`:

```php
define('APP_NAME',        'AbroadHub');
define('CURRENCY',        '$');
define('COMMISSION_RATE', 10);    // Platform cut (%) on consultancy bookings
define('SESSION_TIMEOUT', 1800);  // Idle sign-out after 30 minutes (in seconds)
```

---

## 8. Test accounts

| Role | Username | Password |
| --- | --- | --- |
| **Admin** | `admin` | `password123` |
| **Agency** | `global_edu` | `password123` |
| **University** | `oxford_rep` | `password123` |
| **Student** | `rahim99` | `password123` |

New users can also sign up directly on the register page (`index.php?page=register`) for Student, Agency, or University roles. Admin accounts are managed securely by existing administrators.
