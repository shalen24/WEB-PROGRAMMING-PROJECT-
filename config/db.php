<?php
/**
 * Database Configuration & PDO Factory
 * Supports MySQL / MariaDB with automatic graceful SQLite fallback.
 * Group 17 - AI Resume Analyzer
 */

define('DB_TYPE', 'auto'); // 'mysql', 'sqlite', or 'auto'
define('MYSQL_HOST', '127.0.0.1');
define('MYSQL_PORT', 3306);
define('MYSQL_NAME', 'resume_analyzer_db');
define('MYSQL_USER', 'root');
define('MYSQL_PASS', ''); // Set your MySQL password if configured

$pdo = null;

function getDB()
{
    global $pdo;
    if ($pdo !== null) {
        return $pdo;
    }

    // Try MySQL if DB_TYPE is 'mysql' or 'auto'
    if (DB_TYPE === 'mysql' || DB_TYPE === 'auto') {
        try {
            $dsn = "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, MYSQL_USER, MYSQL_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 2
            ]);
            return $pdo;
        } catch (PDOException $e) {
            if (DB_TYPE === 'mysql') {
                die("MySQL Connection Error: " . $e->getMessage());
            }
            // Auto fallback to SQLite below
        }
    }

    // SQLite fallback for standalone plug-and-play execution
    $sqlitePath = dirname(__DIR__) . '/database/resume_analyzer.sqlite';
    $dir = dirname($sqlitePath);
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }

    $isNew = !file_exists($sqlitePath);
    $pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    if ($isNew) {
        initializeSqliteDatabase($pdo);
    }

    return $pdo;
}

function initializeSqliteDatabase($db)
{
    // 1. Users
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'student',
        phone TEXT,
        department TEXT DEFAULT 'Computer Science',
        college TEXT DEFAULT 'Engineering College',
        graduation_year INTEGER DEFAULT 2026,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 2. Resumes
    $db->exec("CREATE TABLE IF NOT EXISTS resumes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        file_name TEXT NOT NULL,
        file_path TEXT NOT NULL,
        file_type TEXT NOT NULL,
        parsed_text TEXT,
        candidate_name TEXT,
        candidate_email TEXT,
        candidate_phone TEXT,
        extracted_skills TEXT,
        extracted_education TEXT,
        extracted_projects TEXT,
        extracted_experience TEXT,
        uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );");

    // 3. Job Postings
    $db->exec("CREATE TABLE IF NOT EXISTS job_postings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        recruiter_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        company TEXT NOT NULL,
        location TEXT DEFAULT 'Remote / On-site',
        job_type TEXT DEFAULT 'Full-time',
        description TEXT NOT NULL,
        required_skills TEXT NOT NULL,
        experience_level TEXT DEFAULT '0-2 Years',
        min_match_score REAL DEFAULT 60.00,
        status TEXT DEFAULT 'open',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE
    );");

    // 4. Resume Analyses
    $db->exec("CREATE TABLE IF NOT EXISTS resume_analyses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        resume_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        job_id INTEGER NULL,
        target_role TEXT NOT NULL,
        overall_score REAL NOT NULL,
        skill_score REAL NOT NULL,
        experience_score REAL NOT NULL,
        education_score REAL NOT NULL,
        formatting_score REAL NOT NULL,
        matched_skills TEXT,
        missing_skills TEXT,
        feedback_summary TEXT,
        bullet_analysis TEXT,
        analysis_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );");

    // 5. Curated Learning Resources
    $db->exec("CREATE TABLE IF NOT EXISTS learning_resources (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        skill_name TEXT NOT NULL,
        category TEXT DEFAULT 'Technical',
        resource_title TEXT NOT NULL,
        resource_type TEXT NOT NULL,
        resource_url TEXT NOT NULL,
        difficulty_level TEXT DEFAULT 'Beginner',
        estimated_hours INTEGER DEFAULT 6
    );");

    // 6. Interview Questions
    $db->exec("CREATE TABLE IF NOT EXISTS interview_questions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        skill_or_topic TEXT NOT NULL,
        question_type TEXT NOT NULL,
        difficulty TEXT DEFAULT 'Medium',
        question_text TEXT NOT NULL,
        answer_hints TEXT NOT NULL,
        key_concepts TEXT
    );");

    // 7. Job Shortlists
    $db->exec("CREATE TABLE IF NOT EXISTS job_shortlists (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        job_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        resume_id INTEGER NOT NULL,
        match_score REAL NOT NULL,
        status TEXT DEFAULT 'under_review',
        recruiter_notes TEXT,
        applied_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (job_id) REFERENCES job_postings(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );");

    // Seed default initial users (password: 'password123')
    $passHash = password_hash('password123', PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO users (name, email, password_hash, role, department, college, graduation_year) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['System Recruiter (Virtual)', 'recruiter@techcorp.com', $passHash, 'recruiter', 'Talent Acquisition', 'Virtual Tech Solutions', 2026]);
    $stmt->execute(['Sanoj P V', 'sanoj@student.edu', $passHash, 'student', 'Computer Science & Engineering', 'Apex Institute of Technology', 2026]);
    $stmt->execute(['Shalen Ann Regi', 'shalen@student.edu', $passHash, 'student', 'Information Technology', 'Apex Institute of Technology', 2026]);
    $stmt->execute(['Shifa Usman', 'shifa@student.edu', $passHash, 'student', 'Computer Science & Engineering', 'Apex Institute of Technology', 2026]);

    // Seed sample job postings
    $jStmt = $db->prepare("INSERT INTO job_postings (recruiter_id, title, company, location, job_type, description, required_skills, experience_level, min_match_score) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $jStmt->execute([2, 'Junior Full Stack Developer', 'TechCorp Solutions', 'Bangalore / Hybrid', 'Full-time', 'Seeking a Junior Full Stack Developer proficient in React, Node.js, Express, and MySQL/MongoDB with RESTful API experience.', json_encode(['JavaScript', 'React', 'Node.js', 'Express', 'MySQL', 'REST API', 'Git', 'HTML5', 'CSS3']), '0-2 Years', 65.00]);
    $jStmt->execute([2, 'AI / ML Engineer Intern', 'NeuroData Labs', 'Remote', 'Internship', 'Looking for an AI/ML intern experienced in Python, PyTorch/TensorFlow, Scikit-learn, and Natural Language Processing.', json_encode(['Python', 'Machine Learning', 'NLP', 'Scikit-Learn', 'Flask', 'Pandas', 'NumPy', 'Git']), 'Fresher / Intern', 70.00]);
    $jStmt->execute([2, 'Cloud & DevOps Associate', 'CloudScale Systems', 'Hyderabad', 'Full-time', 'Manage CI/CD pipelines, Docker containerization, Kubernetes clusters, and AWS infrastructure.', json_encode(['Linux', 'Docker', 'Kubernetes', 'AWS', 'CI/CD', 'Git', 'Bash', 'Python']), '0-2 Years', 60.00]);

    // Seed learning resources
    $rStmt = $db->prepare("INSERT INTO learning_resources (skill_name, category, resource_title, resource_type, resource_url, difficulty_level, estimated_hours) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $rStmt->execute(['React', 'Frontend', 'Official React Documentation & Interactive Tutorials', 'Documentation', 'https://react.dev/learn', 'Beginner', 10]);
    $rStmt->execute(['React', 'Frontend', 'React.js Full Course 2024 by freeCodeCamp', 'Course', 'https://www.freecodecamp.org/news/learn-react-full-course/', 'Intermediate', 12]);
    $rStmt->execute(['Node.js', 'Backend', 'Node.js Crash Course & RESTful API Architecture', 'Tutorial', 'https://nodejs.org/en/learn/getting-started/introduction-to-nodejs', 'Beginner', 8]);
    $rStmt->execute(['Docker', 'DevOps', 'Docker for Beginners - Complete Hands-On Guide', 'Course', 'https://docs.docker.com/get-started/', 'Beginner', 6]);
    $rStmt->execute(['Kubernetes', 'DevOps', 'Kubernetes Basics & Architecture by CNCF', 'Documentation', 'https://kubernetes.io/docs/tutorials/kubernetes-basics/', 'Intermediate', 14]);
    $rStmt->execute(['Python', 'Programming', 'Python Official Docs & Interactive Tutorial', 'Documentation', 'https://docs.python.org/3/tutorial/', 'Beginner', 8]);
    $rStmt->execute(['Machine Learning', 'AI/ML', 'Machine Learning Specialization by Andrew Ng (Coursera)', 'Course', 'https://www.coursera.org/specializations/machine-learning-introduction', 'Intermediate', 25]);
    $rStmt->execute(['NLP', 'AI/ML', 'Natural Language Processing with NLTK & spaCy', 'Tutorial', 'https://spacy.io/usage/spacy-101', 'Intermediate', 10]);
    $rStmt->execute(['MySQL', 'Database', 'MySQL Tutorial: Queries, Indexing & Normalization', 'Tutorial', 'https://dev.mysql.com/doc/refman/8.0/en/tutorial.html', 'Beginner', 6]);
    $rStmt->execute(['AWS', 'Cloud', 'AWS Cloud Practitioner Essentials Free Digital Training', 'Course', 'https://aws.amazon.com/training/digital/aws-cloud-practitioner-essentials/', 'Beginner', 15]);
    $rStmt->execute(['Git', 'Tools', 'Pro Git Book & Interactive Branching Exercises', 'Documentation', 'https://git-scm.com/book/en/v2', 'Beginner', 4]);

    // Seed interview questions
    $qStmt = $db->prepare("INSERT INTO interview_questions (skill_or_topic, question_type, difficulty, question_text, answer_hints, key_concepts) VALUES (?, ?, ?, ?, ?, ?)");
    $qStmt->execute(['Flutter', 'technical', 'Medium', 'Explain the difference between StatelessWidget and StatefulWidget in Flutter. How does the Flutter rendering pipeline minimize redraw overhead?', 'StatelessWidgets are immutable; StatefulWidgets maintain state. Flutter uses the Element tree to diff widgets and updates RenderObjects without re-creating the entire tree.', 'StatelessWidget, StatefulWidget, Element Tree, RenderObject']);
    $qStmt->execute(['Flutter', 'technical', 'Hard', 'How do you manage complex application state in Flutter (Provider/Bloc), and how do you prevent unnecessary widget tree rebuilds?', 'Use selector/consumer widgets to scope rebuilds to child widgets that depend on changed state, keeping parent layout and animations untouched.', 'Provider, Bloc, Consumer, Rebuild Optimization']);
    $qStmt->execute(['Firebase', 'technical', 'Medium', 'What is the architectural difference between Cloud Firestore and Realtime Database? How do Firestore security rules protect client data?', 'Firestore uses collections and documents with shallow queries; Realtime DB is a JSON tree. Security rules check request.auth and incoming resource state.', 'Cloud Firestore, Realtime Database, Security Rules, Collections']);
    $qStmt->execute(['Flask', 'technical', 'Medium', 'In Flask, what is the difference between Application Context and Request Context? When would you use current_app vs g?', 'Application context binds app globals (current_app, g); request context binds HTTP parameters (request, session). g holds per-request data.', 'Application Context, Request Context, current_app, g object']);
    $qStmt->execute(['IoT', 'technical', 'Medium', 'What is the difference between I2C and SPI protocols in IoT, and how do you interface multiple sensors with an ESP32?', 'I2C uses 2 wires (SDA/SCL) and 7-bit addressing up to 400kHz; SPI uses 4 wires with chip-select lines running at MHz speeds.', 'I2C, SPI, SDA/SCL, Chip Select, Bus Addressing']);
    $qStmt->execute(['Sensors', 'technical', 'Hard', 'How does the MAX30102 sensor calculate heart rate and SpO2 using optical Photoplethysmography (PPG)?', 'Measures ratio of pulsatile (AC) to continuous (DC) red and infrared light absorption through hemoglobin.', 'Photoplethysmography, SpO2, Red/IR Absorption, PPG Signal']);
    $qStmt->execute(['React', 'technical', 'Medium', 'Explain the Virtual DOM in React and how reconciliation works.', 'Mention diffing algorithm, Fiber architecture, state updates triggering re-renders, and performance benefits over direct DOM manipulation.', 'Virtual DOM, Reconciliation, Diffing, Fiber']);
    $qStmt->execute(['Node.js', 'technical', 'Medium', 'How does the Node.js Event Loop work, and what role does libuv play?', 'Explain single-threaded non-blocking I/O, phases of the event loop (timers, I/O callbacks, poll, check, close), and microtasks.', 'Event Loop, Non-blocking I/O, libuv, Microtasks']);
    $qStmt->execute(['Python', 'technical', 'Medium', 'What is the Global Interpreter Lock (GIL) in CPython and when does it impact multi-threading?', 'GIL allows only one native thread to execute bytecode at once. Affects CPU-bound tasks, while I/O-bound tasks release the GIL.', 'GIL, Multi-threading, Multiprocessing, Thread Safety']);
    $qStmt->execute(['Machine Learning', 'technical', 'Medium', 'When evaluating an imbalanced dataset (e.g. rare medical anomalies), why is Accuracy misleading and which metrics should you prioritize?', 'In imbalanced detection, prioritize Recall (minimizing false negatives) and F1-score/PR-AUC over raw accuracy.', 'Accuracy Paradox, Recall, Precision, F1-Score']);
    $qStmt->execute(['MySQL', 'technical', 'Medium', 'What are B-Tree indexes in MySQL and when might an index degrade query performance?', 'Explain how B-Trees speed up SELECT lookups (O(log N)), but add overhead to INSERT/UPDATE/DELETE operations.', 'B-Tree, Indexing, Query Optimization, Write overhead']);
    $qStmt->execute(['Project Experience', 'project', 'Medium', 'Can you walk us through the hardest technical bug you encountered in your key project and how you diagnosed it?', 'Use the STAR format: Explain symptoms, tools used for debugging (logs, profilers), root cause, and resolution.', 'Debugging, Root Cause Analysis, Logging, Verification']);
    $qStmt->execute(['Behavioral', 'behavioral', 'Medium', 'Tell me about a time when you had to meet a tight deadline on a team project where requirements were ambiguous.', 'Focus on communication with team members, breaking down ambiguity into MVP deliverables, and proactive status reporting.', 'STAR Method, Teamwork, Communication, Time Management']);
}

// Ensure PDO is accessible globally
$pdo = getDB();
