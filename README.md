# AI-Powered Resume Analyzer & Placement Preparation Platform

> **Group No:** 17  
> **Team Members:**  
> 1. Sanoj P V (48)  
> 2. Shalen Ann Regi (49)  
> 3. Shifa Usman (50)  
> 
> **Sustainable Development Goals (SDG) Alignment:**  
> - 🎯 **SDG 8: Decent Work and Economic Growth** (Target 8.5 & 8.6: Objective, merit-based screening, reducing youth unemployment via guided upskilling)  
> - 🎯 **SDG 9: Industry, Innovation and Infrastructure** (Target 9.5: Automated digital recruitment infrastructure leveraging NLP microservices)

---

## 🚀 Overview

Recruiters and campus placement cells face significant delays and subjective bias during manual resume screening, while traditional Application Tracking Systems (ATS) only output a static match percentage without providing actionable improvement roadmaps. 

This platform bridges both sides:
1. **For Students / Candidates**: Evaluates resumes against target Job Descriptions, diagnoses bullet points using the **Google XYZ formula**, identifies skill gaps with direct learning pathways, and generates personalized technical and STAR behavioral interview questions.
2. **For Recruiters & Campus Cells (TPO)**: Automates screening and candidate ranking across entire student batches, enabling merit-based shortlisting and instant CSV/Excel roster export.

---

## 🛠️ Tech Stack

- **Frontend**: HTML5, CSS3 (Modern Glassmorphism Design), JavaScript (ES6+), Bootstrap 5.3, FontAwesome 6, Chart.js.
- **Backend Application Layer**: PHP 8.2 (Running on Apache / XAMPP or PHP built-in server), cURL REST client, Session RBAC.
- **Database Layer**: MySQL 8.0 / MariaDB (via PDO) with seamless SQLite fallback (`database/resume_analyzer.sqlite`) for instant zero-config presentation.
- **AI & NLP Microservice**: Python 3.14, Flask, Flask-CORS, PyPDF, python-docx, Scikit-learn (TF-IDF Vectorization & Cosine Similarity), NLTK & regular expression entity extractors.

---

## 🌟 Key Features

### 🎓 1. Candidate / Student Portal
- **Multi-Format Resume Parser**: Supports `.pdf`, `.docx`, and `.txt` documents. Extracts name, contact info, degrees, CGPA, graduation year, technical skills, and projects.
- **Multi-Criteria ATS Match Engine**:
  - Skill Alignment (50%)
  - Experience & Semantic Relevance (25%)
  - Education & Degree Check (15%)
  - Structural ATS Readability (10%)
- **Readiness Tool 1: Bullet-Point Optimizer**:
  - Evaluates bullet points against Google's formula: *"Accomplished [X] as measured by [Y], by doing [Z]"*.
  - Detects passive phrases (*"worked on"*, *"helped with"*) and recommends active verbs (*"Architected"*, *"Spearheaded"*, *"Optimized"*).
  - Flags missing quantifiable metrics and suggests intelligent rewrites.
- **Readiness Tool 2: Skill Gap Roadmap**:
  - Identifies missing skills required by the target job.
  - Maps missing proficiencies to curated courses, official documentation, and estimated study hours.
  - Suggests hands-on portfolio projects to prove competence.
- **Readiness Tool 3: AI Interview Preparation Studio**:
  - Generates technical questions on verified candidate skills.
  - Generates architectural deep-dive questions on candidate's actual projects.
  - Generates behavioral questions structured via the **STAR Method** (*Situation, Task, Action, Result*).
- **Export Placement Report**: One-click printable PDF Placement Readiness Dossier.

### 🏢 2. Recruiter & Placement Cell (TPO) Portal
- **Drive Management**: Create and edit campus placement drives with minimum cutoff scores and mandatory skill requirements.
- **Batch Screening & Leaderboard**:
  - Screen entire candidate pools against job criteria.
  - Automated ranking from 1st to Nth by objective match score.
  - Filter candidates by score cutoff.
- **Deep-Dive Candidate Profile**: Side-by-side comparison of candidate resume vs target job requirements.
- **One-Click Shortlisting & Export**: Update status (*Shortlisted*, *Interview Scheduled*, *Rejected*) and export rosters to CSV.

### ⚙️ 3. Admin & TPO Intelligence Portal
- Institutional placement readiness metrics and batch analytics.
- User management and access control.
- SDG 8 and SDG 9 impact indicators.

---

## ⚡ How to Run the Project

### Option A: Running with One-Click Batch Files (Recommended)

1. **Step 1 - Start the Python AI Microservice:**
   Double-click `run_ai_service.bat` (or open terminal in project directory and run):
   ```cmd
   "C:\Python314\python.exe" ai_service/app.py
   ```
   *Runs Flask REST API on `http://127.0.0.1:5000`.*

2. **Step 2 - Start the PHP Web Server:**
   Double-click `run_php_server.bat` (or open terminal in project directory and run):
   ```cmd
   "C:\xampp\php\php.exe" -S 127.0.0.1:8000
   ```
   *Serves application on `http://127.0.0.1:8000`.*

3. **Step 3 - Open in Browser:**
   Open your browser and navigate to:
   👉 **`http://127.0.0.1:8000`**

---

### Option B: Running under XAMPP Apache

1. Ensure the project folder `WEB PROGRAMMING` is located inside `C:\xampp\htdocs\`.
2. Start Apache from the **XAMPP Control Panel**.
3. Import `database/schema.sql` into phpMyAdmin (if using MySQL).
4. Run the Python AI microservice:
   ```cmd
   python ai_service/app.py
   ```
5. Visit: `http://localhost/WEB%20PROGRAMMING/`

---

## 🔑 Pre-Configured Demo Login Accounts

| Role | Email | Password | Purpose |
|---|---|---|---|
| **Student** | `sanoj@student.edu` | `password123` | Resume analysis, bullet optimizer, interview prep |
| **Student** | `shalen@student.edu` | `password123` | Testing candidate score tracking |
| **Student** | `shifa@student.edu` | `password123` | Testing candidate score tracking |
| **Recruiter** | `recruiter@techcorp.com` | `password123` | Post drives, batch screening & shortlisting |
| **Admin / TPO** | `admin@placement.edu` | `password123` | College placement analytics, SDG reporting |

*(Note: The login screen also features 1-click preset buttons to fill these credentials automatically for seamless viva evaluations!)*

---

## 🧪 Testing with Sample Resumes

We have pre-generated sample resumes in `uploads/sample_resumes/`:
- `sample_student_resume.docx`: Formatted Word document for candidate Sanoj P V.
- `sample_student_resume.txt`: Plain text resume.

You can upload either file directly on the **Analyze Resume** page to observe full entity extraction, ATS scoring, and interview question generation!
