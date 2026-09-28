-- Database: resume_analyzer_db
-- AI-POWERED RESUME ANALYZER AND PLACEMENT PREPARATION PLATFORM
-- Group 17: Sanoj P V, Shalen Ann Regi, Shifa Usman
-- SDG 8 & SDG 9

CREATE DATABASE IF NOT EXISTS `resume_analyzer_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `resume_analyzer_db`;

-- Drop existing tables if needed (in reverse foreign key order)
DROP TABLE IF EXISTS `job_shortlists`;
DROP TABLE IF EXISTS `resume_analyses`;
DROP TABLE IF EXISTS `resumes`;
DROP TABLE IF EXISTS `job_postings`;
DROP TABLE IF EXISTS `interview_questions`;
DROP TABLE IF EXISTS `learning_resources`;
DROP TABLE IF EXISTS `users`;

-- 1. Users Table
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(120) NOT NULL,
    `email` VARCHAR(150) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('student', 'recruiter', 'admin') NOT NULL DEFAULT 'student',
    `phone` VARCHAR(25),
    `department` VARCHAR(100) DEFAULT 'Computer Science',
    `college` VARCHAR(150) DEFAULT 'Engineering College',
    `graduation_year` INT DEFAULT 2026,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Resumes Table
CREATE TABLE `resumes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(30) NOT NULL,
    `parsed_text` LONGTEXT,
    `candidate_name` VARCHAR(120),
    `candidate_email` VARCHAR(150),
    `candidate_phone` VARCHAR(50),
    `extracted_skills` JSON,
    `extracted_education` JSON,
    `extracted_projects` JSON,
    `extracted_experience` JSON,
    `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Job Postings Table
CREATE TABLE `job_postings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `recruiter_id` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `company` VARCHAR(150) NOT NULL,
    `location` VARCHAR(120) DEFAULT 'Remote / On-site',
    `job_type` ENUM('Full-time', 'Internship', 'Contract') DEFAULT 'Full-time',
    `description` TEXT NOT NULL,
    `required_skills` JSON NOT NULL,
    `experience_level` VARCHAR(50) DEFAULT '0-2 Years (Entry Level)',
    `min_match_score` DECIMAL(5,2) DEFAULT 60.00,
    `status` ENUM('open', 'closed') DEFAULT 'open',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`recruiter_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Resume Analyses Table
CREATE TABLE `resume_analyses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `resume_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `job_id` INT NULL,
    `target_role` VARCHAR(150) NOT NULL,
    `overall_score` DECIMAL(5,2) NOT NULL,
    `skill_score` DECIMAL(5,2) NOT NULL,
    `experience_score` DECIMAL(5,2) NOT NULL,
    `education_score` DECIMAL(5,2) NOT NULL,
    `formatting_score` DECIMAL(5,2) NOT NULL,
    `matched_skills` JSON,
    `missing_skills` JSON,
    `feedback_summary` TEXT,
    `bullet_analysis` JSON,
    `analysis_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`resume_id`) REFERENCES `resumes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`job_id`) REFERENCES `job_postings`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Curated Learning Resources Table
CREATE TABLE `learning_resources` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `skill_name` VARCHAR(100) NOT NULL,
    `category` VARCHAR(50) DEFAULT 'Technical',
    `resource_title` VARCHAR(255) NOT NULL,
    `resource_type` ENUM('Course', 'Documentation', 'Tutorial', 'Practice') NOT NULL,
    `resource_url` VARCHAR(500) NOT NULL,
    `difficulty_level` ENUM('Beginner', 'Intermediate', 'Advanced') DEFAULT 'Beginner',
    `estimated_hours` INT DEFAULT 6
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tailored Interview Question Bank
CREATE TABLE `interview_questions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `skill_or_topic` VARCHAR(100) NOT NULL,
    `question_type` ENUM('technical', 'project', 'behavioral') NOT NULL,
    `difficulty` ENUM('Easy', 'Medium', 'Hard') DEFAULT 'Medium',
    `question_text` TEXT NOT NULL,
    `answer_hints` TEXT NOT NULL,
    `key_concepts` VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Recruiter Shortlists / Applications
CREATE TABLE `job_shortlists` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `job_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `resume_id` INT NOT NULL,
    `match_score` DECIMAL(5,2) NOT NULL,
    `status` ENUM('shortlisted', 'under_review', 'interview_scheduled', 'rejected') DEFAULT 'under_review',
    `recruiter_notes` TEXT,
    `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`job_id`) REFERENCES `job_postings`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`resume_id`) REFERENCES `resumes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================================
-- SEED DATA
-- =========================================================================

-- Pre-hashed password for 'password123' using BCRYPT
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `department`, `college`, `graduation_year`) VALUES
(2, 'System Recruiter (Virtual)', 'recruiter@techcorp.com', '$2y$10$w0yGz0qZ7R8YtK8K8p0q0.342l8Y3dG9F0eI1tK2l3m4n5o6p7q8r', 'recruiter', 'Talent Acquisition', 'Virtual Tech Solutions', 2026),
(3, 'Sanoj P V', 'sanoj@student.edu', '$2y$10$w0yGz0qZ7R8YtK8K8p0q0.342l8Y3dG9F0eI1tK2l3m4n5o6p7q8r', 'student', 'Computer Science & Engineering', 'Apex Institute of Technology', 2026),
(4, 'Shalen Ann Regi', 'shalen@student.edu', '$2y$10$w0yGz0qZ7R8YtK8K8p0q0.342l8Y3dG9F0eI1tK2l3m4n5o6p7q8r', 'student', 'Information Technology', 'Apex Institute of Technology', 2026),
(5, 'Shifa Usman', 'shifa@student.edu', '$2y$10$w0yGz0qZ7R8YtK8K8p0q0.342l8Y3dG9F0eI1tK2l3m4n5o6p7q8r', 'student', 'Computer Science & Engineering', 'Apex Institute of Technology', 2026);

-- Sample Job Postings
INSERT INTO `job_postings` (`id`, `recruiter_id`, `title`, `company`, `location`, `job_type`, `description`, `required_skills`, `experience_level`, `min_match_score`) VALUES
(1, 2, 'Junior Full Stack Developer', 'TechCorp Solutions', 'Bangalore / Hybrid', 'Full-time', 'Seeking an energetic Junior Full Stack Developer proficient in React, Node.js, Express, and MySQL/MongoDB. Candidate must understand RESTful API development, Git version control, and frontend responsive design.', '["JavaScript", "React", "Node.js", "Express", "MySQL", "REST API", "Git", "HTML5", "CSS3"]', '0-2 Years', 65.00),
(2, 2, 'AI / Machine Learning Engineer Intern', 'NeuroData Labs', 'Remote', 'Internship', 'Looking for an AI/ML intern experienced in Python, PyTorch/TensorFlow, Scikit-learn, and Natural Language Processing. Knowledge of data preprocessing, API deployment via Flask/FastAPI, and Git is required.', '["Python", "Machine Learning", "NLP", "Scikit-Learn", "Flask", "Pandas", "NumPy", "Git", "PyTorch"]', 'Fresher / Intern', 70.00),
(3, 2, 'Cloud & DevOps Associate', 'CloudScale Systems', 'Hyderabad', 'Full-time', 'Join our DevOps team to manage CI/CD pipelines, Docker containerization, Kubernetes clusters, and AWS cloud infrastructure. Linux shell scripting and automated testing skills are essential.', '["Linux", "Docker", "Kubernetes", "AWS", "CI/CD", "Git", "Bash", "Python"]', '0-2 Years', 60.00);

-- Curated Learning Resources Seed Data
INSERT INTO `learning_resources` (`skill_name`, `category`, `resource_title`, `resource_type`, `resource_url`, `difficulty_level`, `estimated_hours`) VALUES
('React', 'Frontend', 'Official React Documentation & Interactive Tutorials', 'Documentation', 'https://react.dev/learn', 'Beginner', 10),
('React', 'Frontend', 'React.js Full Course 2024 by freeCodeCamp', 'Course', 'https://www.freecodecamp.org/news/learn-react-full-course/', 'Intermediate', 12),
('Node.js', 'Backend', 'Node.js Crash Course & RESTful API Architecture', 'Tutorial', 'https://nodejs.org/en/learn/getting-started/introduction-to-nodejs', 'Beginner', 8),
('Docker', 'DevOps', 'Docker for Beginners - Complete Hands-On Guide', 'Course', 'https://docs.docker.com/get-started/', 'Beginner', 6),
('Kubernetes', 'DevOps', 'Kubernetes Basics & Architecture by CNCF', 'Documentation', 'https://kubernetes.io/docs/tutorials/kubernetes-basics/', 'Intermediate', 14),
('Python', 'Programming', 'Python Official Docs & Automate Boring Stuff', 'Documentation', 'https://docs.python.org/3/tutorial/', 'Beginner', 8),
('Machine Learning', 'AI/ML', 'Machine Learning Specialization by Andrew Ng (Coursera)', 'Course', 'https://www.coursera.org/specializations/machine-learning-introduction', 'Intermediate', 25),
('NLP', 'AI/ML', 'Natural Language Processing with NLTK & spaCy', 'Tutorial', 'https://spacy.io/usage/spacy-101', 'Intermediate', 10),
('MySQL', 'Database', 'MySQL Tutorial: Queries, Indexing & Normalization', 'Tutorial', 'https://dev.mysql.com/doc/refman/8.0/en/tutorial.html', 'Beginner', 6),
('AWS', 'Cloud', 'AWS Cloud Practitioner Essentials (Free Digital Training)', 'Course', 'https://aws.amazon.com/training/digital/aws-cloud-practitioner-essentials/', 'Beginner', 15),
('Git', 'Tools', 'Pro Git Book & Interactive Branching Exercises', 'Documentation', 'https://git-scm.com/book/en/v2', 'Beginner', 4),
('REST API', 'Backend', 'RESTful API Design Best Practices by Postman', 'Documentation', 'https://www.postman.com/what-is-rest-api/', 'Beginner', 5);

-- Pre-seeded Interview Questions
INSERT INTO `interview_questions` (`skill_or_topic`, `question_type`, `difficulty`, `question_text`, `answer_hints`, `key_concepts`) VALUES
('React', 'technical', 'Medium', 'Explain the Virtual DOM in React and how reconciliation works.', 'Mention diffing algorithm, Fiber architecture, state updates triggering re-renders, and performance benefits over direct DOM manipulation.', 'Virtual DOM, Reconciliation, Diffing, Fiber'),
('React', 'technical', 'Hard', 'What is the difference between useEffect, useMemo, and useCallback?', 'useMemo caches computed values, useCallback caches function references, and useEffect handles side effects after rendering.', 'Hooks, Memoization, Performance, Lifecycle'),
('Node.js', 'technical', 'Medium', 'How does the Node.js Event Loop work, and what role does libuv play?', 'Explain single-threaded non-blocking I/O, phases of the event loop (timers, I/O callbacks, poll, check, close), and microtasks (Promise.then, process.nextTick).', 'Event Loop, Non-blocking I/O, libuv, Microtasks'),
('Python', 'technical', 'Medium', 'How do Python generators work, and how do they differ from normal functions?', 'Mention the yield keyword, lazy evaluation, memory efficiency when working with large datasets, and the iterator protocol (__iter__ and __next__).', 'Generators, yield, Iterators, Memory optimization'),
('Machine Learning', 'technical', 'Medium', 'How do you detect and prevent overfitting in a machine learning model?', 'Discuss cross-validation, regularization (L1/L2), dropout for neural nets, early stopping, gathering more data, and feature pruning.', 'Overfitting, Regularization, Cross-Validation, Dropout'),
('MySQL', 'technical', 'Medium', 'What are B-Tree indexes in MySQL and when might an index degrade query performance?', 'Explain how B-Trees speed up SELECT lookups (O(log N)), but add overhead to INSERT/UPDATE/DELETE operations and consume storage space.', 'B-Tree, Indexing, Query Optimization, Write overhead'),
('Docker', 'technical', 'Medium', 'Explain the difference between a Docker image and a Docker container.', 'An image is a read-only immutable template with application code and dependencies; a container is a running, isolated instance of an image with a writable layer.', 'Images, Containers, Layers, Isolation'),
('Project Experience', 'project', 'Medium', 'Can you walk us through the hardest technical bug you encountered in your key project and how you diagnosed it?', 'Use the STAR format: Explain the symptoms, tools used for debugging (logs, profilers, dev tools), root cause identified, and resolution with preventative tests.', 'Debugging, Root Cause Analysis, Logging, Verification'),
('Behavioral', 'behavioral', 'Medium', 'Tell me about a time when you had to meet a tight deadline on a team project where requirements were ambiguous.', 'Focus on communication with team members, breaking down ambiguity into MVP deliverables, prioritizing tasks, and proactive status reporting.', 'STAR Method, Teamwork, Communication, Time Management');
