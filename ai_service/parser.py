"""
Resume Document Parser
Extracts text from PDF, DOCX, and TXT files, and extracts structured entities
(Name, Email, Phone, Skills, Education, Projects, Experience).
Group 17 - AI Resume Analyzer
"""

import re
import os
from pypdf import PdfReader
import docx
from skill_ontology import ALL_SKILLS_MAP, get_standard_skill, get_skill_category

def extract_text_from_file(file_path):
    """Extract raw text from PDF, DOCX, or TXT file."""
    if not os.path.exists(file_path):
        raise FileNotFoundError(f"File not found: {file_path}")
    
    ext = os.path.splitext(file_path)[1].lower()
    text = ""
    
    if ext == ".pdf":
        try:
            reader = PdfReader(file_path)
            for page in reader.pages:
                page_text = page.extract_text()
                if page_text:
                    text += page_text + "\n"
        except Exception as e:
            raise RuntimeError(f"Error reading PDF: {str(e)}")
            
    elif ext in [".docx", ".doc"]:
        try:
            doc = docx.Document(file_path)
            for para in doc.paragraphs:
                if para.text:
                    text += para.text + "\n"
            for table in doc.tables:
                for row in table.rows:
                    row_text = [cell.text.strip() for cell in row.cells if cell.text.strip()]
                    if row_text:
                        text += " | ".join(row_text) + "\n"
        except Exception as e:
            raise RuntimeError(f"Error reading DOCX: {str(e)}")
            
    elif ext in [".txt", ".rtf"]:
        try:
            with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
                text = f.read()
        except Exception as e:
            raise RuntimeError(f"Error reading text file: {str(e)}")
    else:
        raise ValueError(f"Unsupported file format: {ext}")
        
    return text.strip()

def extract_contact_info(text):
    """Extract email, phone, and professional links from text."""
    # Email regex
    email_pattern = r'[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+'
    emails = re.findall(email_pattern, text)
    email = emails[0] if emails else ""

    # Phone regex (Indian/International: +91, 10-digit, dashes/spaces)
    phone_pattern = r'(?:(?:\+|0{0,2})91[\s-]?)?[6789]\d{9}|(?:\+?1[\s-]?)?\(?\d{3}\)?[\s.-]?\d{3}[\s.-]?\d{4}'
    phones = re.findall(phone_pattern, text)
    phone = phones[0] if phones else ""

    # LinkedIn / GitHub
    linkedin = ""
    github = ""
    for line in text.splitlines():
        if "linkedin.com" in line.lower() and not linkedin:
            match = re.search(r'linkedin\.com/in/[a-zA-Z0-9_-]+', line, re.IGNORECASE)
            linkedin = match.group(0) if match else "LinkedIn Profile"
        if "github.com" in line.lower() and not github:
            match = re.search(r'github\.com/[a-zA-Z0-9_-]+', line, re.IGNORECASE)
            github = match.group(0) if match else "GitHub Profile"

    return {
        "email": email,
        "phone": phone,
        "linkedin": linkedin,
        "github": github
    }

def extract_candidate_name(text):
    """Heuristic to extract candidate name from top lines."""
    lines = [line.strip() for line in text.splitlines() if line.strip()]
    for line in lines[:5]:
        # Filter out lines with emails, phones, or URLs
        if "@" in line or "http" in line or re.search(r'\d', line):
            continue
        # Names are usually 2-4 words, capitalized
        words = line.split()
        if 1 < len(words) <= 4 and all(w[0].isupper() for w in words if w.isalpha()):
            return line
    return lines[0] if lines else "Candidate"

def extract_skills_from_text(text):
    """Extract all recognized technical and soft skills from resume text."""
    found_skills = set()
    lower_text = text.lower()
    
    # Check for both whole words and multi-word phrases
    for alias, standard_name in ALL_SKILLS_MAP.items():
        # Match word boundaries or symbols for languages like C++, C#, etc.
        escaped = re.escape(alias)
        pattern = r'(?<![a-zA-Z0-9])' + escaped + r'(?![a-zA-Z0-9])'
        if re.search(pattern, lower_text):
            found_skills.add(standard_name)

    categorized = {}
    for skill in sorted(list(found_skills)):
        cat = get_skill_category(skill)
        categorized.setdefault(cat, []).append(skill)

    return {
        "skills_list": sorted(list(found_skills)),
        "skills_by_category": categorized,
        "total_skills_count": len(found_skills)
    }

def extract_education(text):
    """Detect degrees, colleges, and graduation years."""
    degrees = []
    degree_patterns = [
        r'\b(?:B\.?Tech|B\.?E\.?|Bachelor\s+of\s+Technology|Bachelor\s+of\s+Engineering)\b',
        r'\b(?:M\.?Tech|M\.?E\.?|Master\s+of\s+Technology)\b',
        r'\b(?:B\.?Sc|Bachelor\s+of\s+Science)\b',
        r'\b(?:M\.?Sc|Master\s+of\s+Science)\b',
        r'\b(?:BCA|MCA|Bachelor\s+of\s+Computer\s+Applications)\b',
        r'\b(?:Ph\.?D|Doctorate)\b',
        r'\b(?:Diploma\s+in\s+[A-Za-z\s]+)\b'
    ]
    
    for pattern in degree_patterns:
        matches = re.findall(pattern, text, re.IGNORECASE)
        if matches:
            degrees.extend(matches)

    # Detect graduation year (e.g. 2020 - 2024, or 2025)
    years = re.findall(r'\b(20[12]\d)\b', text)
    grad_year = max(years) if years else "2026"

    # Detect CGPA / Percentage
    cgpa_match = re.search(r'\b(?:CGPA|GPA)[\s:]*([0-9]\.[0-9]{1,2}(?:\s*/\s*10)?)', text, re.IGNORECASE)
    cgpa = cgpa_match.group(1) if cgpa_match else ""

    return {
        "degrees": list(set(degrees)),
        "grad_year": grad_year,
        "cgpa": cgpa
    }

def extract_projects_and_experience(text):
    """Extract project titles and bullet points from resume text."""
    lines = [l.strip() for l in text.splitlines() if l.strip()]
    projects = []
    bullets = []
    
    in_project_section = False
    current_proj = None

    for line in lines:
        lower_line = line.lower()
        if any(h in lower_line for h in ["projects", "personal projects", "academic projects"]):
            in_project_section = True
            continue
        elif any(h in lower_line for h in ["education", "skills", "certifications", "achievements", "interests"]):
            if in_project_section:
                in_project_section = False

        if in_project_section:
            # Check if this line looks like a project title (short, title cased, or bold-like)
            is_bullet_lead = bool(re.match(r'^[\s\u2022\u2023\u25cf\u25cb\u25aa\u25b6\u2043\xb7\uf0b7\uf0a7\-\*\–\—\d\.\)\>]+', line))
            if len(line.split()) <= 7 and not is_bullet_lead and not line.endswith("."):
                current_proj = {"title": line, "description": []}
                projects.append(current_proj)
            elif current_proj:
                cleaned_l = re.sub(r'^[\s\u2022\u2023\u25cf\u25cb\u25aa\u25b6\u2043\xb7\uf0b7\uf0a7\-\*\–\—\d\.\)\>\:\•\s]+', '', line).strip()
                if cleaned_l:
                    current_proj["description"].append(cleaned_l)

        # Collect bullet points across the resume
        is_bullet_point = bool(re.match(r'^[\s\u2022\u2023\u25cf\u25cb\u25aa\u25b6\u2043\xb7\uf0b7\uf0a7\-\*\–\—\d\.\)\>]+', line)) or (len(line.split()) >= 8 and line.endswith("."))
        if is_bullet_point:
            c_bullet = re.sub(r'^[\s\u2022\u2023\u25cf\u25cb\u25aa\u25b6\u2043\xb7\uf0b7\uf0a7\-\*\–\—\d\.\)\>\:\•\s]+', '', line).strip()
            if len(c_bullet) > 12 and c_bullet not in bullets:
                bullets.append(c_bullet)

    return {
        "projects": projects[:5], # Keep top 5 projects
        "bullet_points": bullets[:15] # Keep key bullet points
    }

def parse_full_resume(file_path):
    """Full parsing pipeline returning all extracted metadata."""
    raw_text = extract_text_from_file(file_path)
    contact = extract_contact_info(raw_text)
    name = extract_candidate_name(raw_text)
    skills = extract_skills_from_text(raw_text)
    education = extract_education(raw_text)
    proj_exp = extract_projects_and_experience(raw_text)

    # Basic formatting health checks
    word_count = len(raw_text.split())
    has_contact = bool(contact["email"] and contact["phone"])
    has_skills = len(skills["skills_list"]) >= 5
    has_education = len(education["degrees"]) >= 1

    formatting_score = 100
    if not has_contact: formatting_score -= 20
    if not has_skills: formatting_score -= 25
    if not has_education: formatting_score -= 15
    if word_count < 150: formatting_score -= 25
    if word_count > 1000: formatting_score -= 10

    return {
        "raw_text": raw_text,
        "candidate_name": name,
        "contact": contact,
        "skills": skills,
        "education": education,
        "projects": proj_exp["projects"],
        "bullet_points": proj_exp["bullet_points"],
        "word_count": word_count,
        "formatting_score": max(formatting_score, 20)
    }
