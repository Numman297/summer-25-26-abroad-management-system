-- ================================================================
-- DATABASE SCHEMA: Abroad Management System (AbroadHub)
-- Relational MySQL Schema with Normalized Tables and Seed Data
-- ================================================================

CREATE DATABASE IF NOT EXISTS `abroad_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `abroad_db`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `feedbacks`;
DROP TABLE IF EXISTS `revenue_transactions`;
DROP TABLE IF EXISTS `travel_companions`;
DROP TABLE IF EXISTS `mock_tests`;
DROP TABLE IF EXISTS `scholarship_applications`;
DROP TABLE IF EXISTS `student_applications`;
DROP TABLE IF EXISTS `student_education`;
DROP TABLE IF EXISTS `student_profiles`;
DROP TABLE IF EXISTS `service_packages`;
DROP TABLE IF EXISTS `scholarships`;
DROP TABLE IF EXISTS `program_requirements`;
DROP TABLE IF EXISTS `programs`;
DROP TABLE IF EXISTS `university_profiles`;
DROP TABLE IF EXISTS `agency_profiles`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. USERS TABLE
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `contact` VARCHAR(30) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'agency', 'university', 'student') NOT NULL,
  `status` ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. AGENCY PROFILES
CREATE TABLE `agency_profiles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `company_name` VARCHAR(150) NOT NULL,
  `license_no` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `website` VARCHAR(200) DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `verified_status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `verification_notes` TEXT DEFAULT NULL,
  `verified_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. UNIVERSITY PROFILES
CREATE TABLE `university_profiles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `uni_name` VARCHAR(200) NOT NULL,
  `country` VARCHAR(100) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `website` VARCHAR(200) DEFAULT NULL,
  `ranking` VARCHAR(50) DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `verified_status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `verification_notes` TEXT DEFAULT NULL,
  `verified_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. DEGREE PROGRAMS
CREATE TABLE `programs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `university_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `degree_level` ENUM('Bachelor', 'Master', 'PhD', 'Diploma') NOT NULL,
  `department` VARCHAR(150) NOT NULL,
  `duration_years` DECIMAL(3,1) DEFAULT 2.0,
  `tuition_fee` DECIMAL(10,2) NOT NULL,
  `intake_season` VARCHAR(100) DEFAULT 'Fall 2026, Spring 2027',
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`university_id`) REFERENCES `university_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. PROGRAM REQUIREMENTS
CREATE TABLE `program_requirements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `program_id` INT NOT NULL UNIQUE,
  `min_cgpa` DECIMAL(3,2) DEFAULT 3.00,
  `min_ielts` DECIMAL(3,1) DEFAULT 6.5,
  `min_toefl` INT DEFAULT 80,
  `min_gre` INT DEFAULT 300,
  `visa_guidelines` TEXT DEFAULT NULL,
  `documents_required` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. SCHOLARSHIPS
CREATE TABLE `scholarships` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `university_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `grant_amount` DECIMAL(10,2) NOT NULL,
  `eligibility_criteria` TEXT NOT NULL,
  `coverage_type` ENUM('Full Tuition', 'Partial Tuition', 'Living Stipend', 'One-time Grant') DEFAULT 'Partial Tuition',
  `deadline` DATE NOT NULL,
  `max_recipients` INT DEFAULT 5,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`university_id`) REFERENCES `university_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. SERVICE PACKAGES
CREATE TABLE `service_packages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `agency_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `duration_weeks` INT DEFAULT 4,
  `description` TEXT NOT NULL,
  `features_list` TEXT NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`agency_id`) REFERENCES `agency_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. STUDENT PROFILES
CREATE TABLE `student_profiles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `passport_no` VARCHAR(50) DEFAULT NULL,
  `target_country` VARCHAR(100) DEFAULT NULL,
  `target_degree` VARCHAR(50) DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. STUDENT EDUCATIONAL RECORDS
CREATE TABLE `student_education` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `degree_title` VARCHAR(150) NOT NULL,
  `institute_name` VARCHAR(200) NOT NULL,
  `board_or_uni` VARCHAR(150) NOT NULL,
  `passing_year` INT NOT NULL,
  `result_cgpa` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. STUDENT APPLICATIONS
CREATE TABLE `student_applications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `program_id` INT DEFAULT NULL,
  `agency_id` INT DEFAULT NULL,
  `package_id` INT DEFAULT NULL,
  `intake` VARCHAR(50) NOT NULL,
  `application_type` ENUM('university', 'agency') NOT NULL DEFAULT 'university',
  `status` ENUM('pending', 'under_review', 'offer_issued', 'visa_in_progress', 'visa_approved', 'rejected') DEFAULT 'pending',
  `offer_letter_file` VARCHAR(255) DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`agency_id`) REFERENCES `agency_profiles` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`package_id`) REFERENCES `service_packages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. SCHOLARSHIP APPLICATIONS
CREATE TABLE `scholarship_applications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `scholarship_id` INT NOT NULL,
  `student_id` INT NOT NULL,
  `statement` TEXT NOT NULL,
  `status` ENUM('applied', 'under_review', 'awarded', 'rejected') DEFAULT 'applied',
  `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. MOCK TESTS
CREATE TABLE `mock_tests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `test_type` ENUM('IELTS', 'TOEFL', 'GRE', 'SAT', 'Duolingo') NOT NULL,
  `test_date` DATE NOT NULL,
  `reading_score` DECIMAL(4,1) NOT NULL,
  `listening_score` DECIMAL(4,1) NOT NULL,
  `writing_score` DECIMAL(4,1) NOT NULL,
  `speaking_score` DECIMAL(4,1) NOT NULL,
  `overall_score` DECIMAL(4,1) NOT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. TRAVEL COMPANIONS
CREATE TABLE `travel_companions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `destination_country` VARCHAR(100) NOT NULL,
  `destination_city` VARCHAR(100) NOT NULL,
  `destination_uni` VARCHAR(200) DEFAULT NULL,
  `travel_date` DATE NOT NULL,
  `airline` VARCHAR(100) DEFAULT NULL,
  `flight_no` VARCHAR(50) DEFAULT NULL,
  `contact_info` VARCHAR(200) NOT NULL,
  `notes` TEXT DEFAULT NULL,
  `status` ENUM('open', 'closed') DEFAULT 'open',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 14. REVENUE TRANSACTIONS
CREATE TABLE `revenue_transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `transaction_type` ENUM('package_booking', 'listing_fee', 'commission', 'consultation_fee') NOT NULL,
  `user_id` INT NOT NULL,
  `related_id` INT DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `platform_commission` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT 'bKash / Card',
  `status` ENUM('completed', 'pending', 'refunded') DEFAULT 'completed',
  `transaction_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 15. FEEDBACKS
CREATE TABLE `feedbacks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `target_type` ENUM('platform', 'agency', 'university') NOT NULL DEFAULT 'platform',
  `target_id` INT DEFAULT NULL,
  `rating` INT NOT NULL DEFAULT 5,
  `subject` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `admin_response` TEXT DEFAULT NULL,
  `status` ENUM('pending', 'resolved') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 16. SYSTEM ACTIVITY LOGS
CREATE TABLE `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `role` VARCHAR(50) DEFAULT NULL,
  `action` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================================
-- INITIAL SEED DATA
-- Passwords for all demo accounts: 'password123'
-- ================================================================
INSERT INTO `users` (`id`, `name`, `email`, `contact`, `username`, `password`, `role`, `status`) VALUES
(1, 'System Administrator', 'admin@abroad.com', '+8801711000001', 'admin', '$2y$10$FTfNNZWgSAGV1ntnC3NHFelMMncHyIbFLPXiOnGTePBHHV7SMF/OS', 'admin', 'active'),
(2, 'Global Pathway Education', 'agency@pathway.com', '+8801811000002', 'agency1', '$2y$10$FTfNNZWgSAGV1ntnC3NHFelMMncHyIbFLPXiOnGTePBHHV7SMF/OS', 'agency', 'active'),
(3, 'Nexus Visa Consultancy', 'agency@nexus.com', '+8801911000003', 'agency2', '$2y$10$FTfNNZWgSAGV1ntnC3NHFelMMncHyIbFLPXiOnGTePBHHV7SMF/OS', 'agency', 'active'),
(4, 'Harvard Global Admissions', 'rep@harvard.edu', '+16174951000', 'harvard_rep', '$2y$10$FTfNNZWgSAGV1ntnC3NHFelMMncHyIbFLPXiOnGTePBHHV7SMF/OS', 'university', 'active'),
(5, 'Oxford International Rep', 'rep@oxford.ac.uk', '+441865270000', 'oxford_rep', '$2y$10$FTfNNZWgSAGV1ntnC3NHFelMMncHyIbFLPXiOnGTePBHHV7SMF/OS', 'university', 'active'),
(6, 'Rahim Chowdhury', 'student@gmail.com', '+8801511000004', 'rahim99', '$2y$10$FTfNNZWgSAGV1ntnC3NHFelMMncHyIbFLPXiOnGTePBHHV7SMF/OS', 'student', 'active'),
(7, 'Anika Tabassum', 'anika@gmail.com', '+8801611000005', 'anika22', '$2y$10$FTfNNZWgSAGV1ntnC3NHFelMMncHyIbFLPXiOnGTePBHHV7SMF/OS', 'student', 'active');

INSERT INTO `agency_profiles` (`id`, `user_id`, `company_name`, `license_no`, `address`, `website`, `bio`, `verified_status`, `verified_at`) VALUES
(1, 2, 'Global Pathway Education', 'BD-EDU-2024-88', 'House 45, Road 11, Banani, Dhaka', 'https://globalpathway.org', 'Overseas educational consultancy for USA, UK, Canada and Australia admissions.', 'approved', '2026-01-15 10:00:00'),
(2, 3, 'Nexus Visa Consultancy', 'BD-EDU-2025-102', 'Suite 7B, GEC Circle, Chittagong', 'https://nexusvisa.com', 'Specialized admission counseling for European universities.', 'pending', NULL);

INSERT INTO `university_profiles` (`id`, `user_id`, `uni_name`, `country`, `city`, `website`, `ranking`, `bio`, `verified_status`, `verified_at`) VALUES
(1, 4, 'Harvard University (Global Programs)', 'United States', 'Cambridge, MA', 'https://harvard.edu', '#4 World QS', 'Prestigious institution offering engineering, business, and liberal arts degrees.', 'approved', '2026-01-10 11:30:00'),
(2, 5, 'University of Oxford', 'United Kingdom', 'Oxford', 'https://ox.ac.uk', '#2 World QS', 'Renowned university globally with research opportunities.', 'pending', NULL);

INSERT INTO `programs` (`id`, `university_id`, `title`, `degree_level`, `department`, `duration_years`, `tuition_fee`, `intake_season`, `description`) VALUES
(1, 1, 'MSc in Computer Science', 'Master', 'School of Engineering', 2.0, 32000.00, 'Fall 2026, Spring 2027', 'Advanced curriculum covering Artificial Intelligence, Cloud Systems, and Software Engineering.'),
(2, 1, 'MBA in Global Business', 'Master', 'School of Business', 2.0, 45000.00, 'Fall 2026', 'Comprehensive management program focusing on international commerce and leadership.'),
(3, 1, 'BSc in Software Engineering', 'Bachelor', 'Department of CS', 4.0, 28000.00, 'Fall 2026', 'Foundational software development, data structures, and algorithmic engineering.');

INSERT INTO `program_requirements` (`id`, `program_id`, `min_cgpa`, `min_ielts`, `min_toefl`, `min_gre`, `visa_guidelines`, `documents_required`) VALUES
(1, 1, 3.25, 7.0, 95, 315, 'Student visa required. Proof of financial solvency covering 1st year tuition and living costs.', 'Valid Passport, Academic Transcripts, 2 Letters of Recommendation, Statement of Purpose, English Certificate'),
(2, 2, 3.50, 7.5, 100, 320, 'Student visa required. Relevant work experience recommended.', 'Transcripts, Resume, Recommendations, Statement of Purpose'),
(3, 3, 3.00, 6.5, 85, 0, 'Standard undergraduate student visa checklist applies.', 'HSC Transcripts, Diploma, IELTS/TOEFL Score, Passport Copy');

INSERT INTO `scholarships` (`id`, `university_id`, `title`, `grant_amount`, `eligibility_criteria`, `coverage_type`, `deadline`, `max_recipients`) VALUES
(1, 1, 'Global STEM Fellowship', 15000.00, 'Minimum CGPA 3.60, IELTS 7.5 or GRE 320+. Open to international postgraduate students.', 'Partial Tuition', '2026-11-30', 5),
(2, 1, 'Dean Merit Grant', 10000.00, 'Undergraduate applicants with outstanding academic distinction.', 'Partial Tuition', '2026-10-15', 10);

INSERT INTO `service_packages` (`id`, `agency_id`, `title`, `price`, `duration_weeks`, `description`, `features_list`) VALUES
(1, 1, 'Full Study Abroad Guidance', 450.00, 12, 'Comprehensive support from university shortlisting, SOP review to visa filing.', 'University Shortlisting, SOP Review, Mock Interviews, Visa Support'),
(2, 1, 'Visa Assistance & Review', 200.00, 4, 'Targeted review of visa documentation and embassy mock interview coaching.', 'Visa Document Audit, Appointment Guidance, Mock Interviews');

INSERT INTO `student_profiles` (`id`, `user_id`, `passport_no`, `target_country`, `target_degree`, `bio`) VALUES
(1, 6, 'A04589211', 'United States', 'Master', 'Graduate student planning for higher studies abroad in CS.'),
(2, 7, 'B09871234', 'United Kingdom', 'Bachelor', 'Undergraduate applicant interested in Business Management.');

INSERT INTO `student_education` (`id`, `student_id`, `degree_title`, `institute_name`, `board_or_uni`, `passing_year`, `result_cgpa`) VALUES
(1, 6, 'BSc in Computer Science and Engineering', 'BRAC University', 'UGC', 2025, '3.78 / 4.00'),
(2, 6, 'Higher Secondary Certificate (HSC)', 'Dhaka City College', 'Dhaka Board', 2021, 'GPA 5.00 / 5.00'),
(3, 6, 'Secondary School Certificate (SSC)', 'Government Laboratory High School', 'Dhaka Board', 2019, 'GPA 5.00 / 5.00');

INSERT INTO `student_applications` (`id`, `student_id`, `program_id`, `agency_id`, `package_id`, `intake`, `application_type`, `status`, `remarks`) VALUES
(1, 6, 1, 1, NULL, 'Fall 2026', 'university', 'under_review', 'Application documents received and forwarded to Graduate Admissions.'),
(2, 6, NULL, 1, 1, 'Fall 2026', 'agency', 'visa_in_progress', 'Visa dossier prepared; awaiting embassy slot.');

INSERT INTO `mock_tests` (`id`, `student_id`, `test_type`, `test_date`, `reading_score`, `listening_score`, `writing_score`, `speaking_score`, `overall_score`, `notes`) VALUES
(1, 6, 'IELTS', '2026-05-10', 7.5, 8.0, 6.5, 7.0, 7.5, 'Practice test. Need to focus on Writing Task 2.'),
(2, 6, 'IELTS', '2026-07-20', 8.5, 8.5, 7.5, 7.5, 8.0, 'Good improvement across all modules.');

INSERT INTO `travel_companions` (`id`, `student_id`, `destination_country`, `destination_city`, `destination_uni`, `travel_date`, `airline`, `flight_no`, `contact_info`, `notes`, `status`) VALUES
(1, 6, 'United States', 'Boston, MA', 'Harvard University', '2026-08-15', 'Qatar Airways', 'QR-639', 'rahim@gmail.com', 'Looking for fellow students flying from Dhaka to Boston around mid-August.', 'open');

INSERT INTO `revenue_transactions` (`id`, `transaction_type`, `user_id`, `related_id`, `amount`, `platform_commission`, `payment_method`, `status`) VALUES
(1, 'package_booking', 6, 1, 450.00, 45.00, 'bKash', 'completed'),
(2, 'listing_fee', 2, 1, 100.00, 100.00, 'Card', 'completed'),
(3, 'commission', 4, 1, 500.00, 150.00, 'Bank Transfer', 'completed');

INSERT INTO `feedbacks` (`id`, `student_id`, `target_type`, `target_id`, `rating`, `subject`, `message`, `admin_response`, `status`) VALUES
(1, 6, 'platform', NULL, 5, 'Helpful portal for university research', 'The mock test tracker and travel companion tools are very useful.', 'Thank you for the feedback!', 'resolved'),
(2, 7, 'agency', 1, 4, 'Professional counseling', 'The agency helped review my documents properly.', NULL, 'pending');

INSERT INTO `activity_logs` (`id`, `user_id`, `role`, `action`, `created_at`) VALUES
(1, 1, 'admin', 'System initialized with initial seed records', NOW()),
(2, 6, 'student', 'Logged IELTS mock test score (Overall: 8.0)', NOW()),
(3, 2, 'agency', 'Published new service package: Full Study Abroad Guidance', NOW());
