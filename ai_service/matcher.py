"""
ATS Resume Matcher & Semantic Scoring Engine
Computes objective match scores against target Job Descriptions using TF-IDF,
cosine similarity, and exact/fuzzy ontology cross-matching.
Group 17 - AI Resume Analyzer
"""

import re
import math
from collections import Counter
from skill_ontology import ALL_SKILLS_MAP, get_standard_skill
from parser import extract_skills_from_text

def compute_cosine_similarity(text1, text2):
    """
    High-performance native TF-IDF vectorization and cosine similarity calculation.
    Computes mathematical cosine similarity in sub-millisecond time without heavy DLL overhead.
    """
    def tokenize(t):
        # Extract alphanumeric words of length >= 2, lowercased
        return re.findall(r'\b[a-zA-Z]{2,}\b', t.lower())
    
    tokens1 = tokenize(text1)
    tokens2 = tokenize(text2)
    
    if not tokens1 or not tokens2:
        return 0.0
        
    c1 = Counter(tokens1)
    c2 = Counter(tokens2)
    
    # Combined vocabulary
    vocab = set(c1.keys()).union(set(c2.keys()))
    
    vec1 = []
    vec2 = []
    for term in vocab:
        df = (1 if term in c1 else 0) + (1 if term in c2 else 0)
        # Smoothed inverse document frequency (N=2)
        idf = math.log((2.0 + 1.0) / (df + 1.0)) + 1.0
        
        tf1 = (1.0 + math.log(c1[term])) if c1[term] > 0 else 0.0
        tf2 = (1.0 + math.log(c2[term])) if c2[term] > 0 else 0.0
        
        vec1.append(tf1 * idf)
        vec2.append(tf2 * idf)
        
    dot_product = sum(a * b for a, b in zip(vec1, vec2))
    mag1 = math.sqrt(sum(a * a for a in vec1))
    mag2 = math.sqrt(sum(b * b for b in vec2))
    
    if mag1 == 0 or mag2 == 0:
        return 0.0
    return dot_product / (mag1 * mag2)

def compute_ats_match(parsed_resume, job_description, required_skills_list=None):
    """
    Computes a multi-criteria ATS match score between parsed resume and target JD.
    - Skill Match (50%)
    - Experience & Project Semantic Alignment (25%)
    - Education Match (15%)
    - Formatting & Readability (10%)
    """
    resume_skills = set(parsed_resume.get("skills", {}).get("skills_list", []))
    resume_text = parsed_resume.get("raw_text", "")
    formatting_score = parsed_resume.get("formatting_score", 85)

    # 1. Determine Target Job Skills
    target_skills = set()
    if required_skills_list:
        for s in required_skills_list:
            std = get_standard_skill(s)
            target_skills.add(std if std else s.strip())
    else:
        # Extract skills directly from JD text
        jd_skills_res = extract_skills_from_text(job_description)
        target_skills = set(jd_skills_res.get("skills_list", []))

    # If target skills still empty, default to broad tech set detected in JD
    if not target_skills:
        target_skills = set(["Problem Solving", "Communication", "Git"])

    # 2. Skill Overlap Calculation
    matched_skills = sorted(list(resume_skills.intersection(target_skills)))
    missing_skills = sorted(list(target_skills.difference(resume_skills)))

    if target_skills:
        skill_score = (len(matched_skills) / len(target_skills)) * 100.0
    else:
        skill_score = 70.0
    skill_score = min(round(skill_score, 1), 100.0)

    # 3. Semantic Similarity (TF-IDF + Cosine Similarity for Experience/Context)
    experience_score = 65.0
    try:
        if resume_text and job_description:
            sim = compute_cosine_similarity(resume_text, job_description)
            # Cosine similarity for typical resume vs JD ranges from 0.15 to 0.65
            scaled_sim = min(max(sim * 150.0, 35.0), 98.0)
            experience_score = round(scaled_sim, 1)
    except Exception:
        experience_score = 65.0

    # 4. Education Score
    education_score = 80.0
    degrees = parsed_resume.get("education", {}).get("degrees", [])
    if degrees:
        education_score = 95.0
    elif any(term in resume_text.lower() for term in ["bachelor", "b.tech", "b.e", "mca", "degree"]):
        education_score = 85.0
    else:
        education_score = 65.0

    # 5. Calculate Weighted Overall Score
    # Weights: Skill (50%), Experience (25%), Education (15%), Formatting (10%)
    overall_score = (
        (0.50 * skill_score) +
        (0.25 * experience_score) +
        (0.15 * education_score) +
        (0.10 * formatting_score)
    )
    overall_score = min(max(round(overall_score, 1), 0.0), 100.0)

    # Determine readiness tier
    if overall_score >= 80:
        readiness_tier = "High Match (Ready for Immediate Shortlisting)"
        badge_class = "success"
    elif overall_score >= 60:
        readiness_tier = "Moderate Match (Competitive - Minor Skill Polish Recommended)"
        badge_class = "warning"
    else:
        readiness_tier = "Needs Preparation (Significant Skill Gaps to Bridge)"
        badge_class = "danger"

    # Actionable suggestions
    recommendations = []
    if missing_skills:
        recommendations.append(f"Incorporate these critical missing skills into your projects or coursework: {', '.join(missing_skills[:4])}.")
    if experience_score < 70:
        recommendations.append("Align project descriptions more closely with target job keywords (e.g. mention architecture, design patterns, and deployment).")
    if formatting_score < 80:
        recommendations.append("Enhance resume structure: ensure contact links (LinkedIn, GitHub) and quantifiable project results are prominent.")

    return {
        "overall_score": overall_score,
        "skill_score": skill_score,
        "experience_score": experience_score,
        "education_score": education_score,
        "formatting_score": formatting_score,
        "matched_skills": matched_skills,
        "missing_skills": missing_skills,
        "total_required_skills": len(target_skills),
        "readiness_tier": readiness_tier,
        "badge_class": badge_class,
        "recommendations": recommendations
    }
