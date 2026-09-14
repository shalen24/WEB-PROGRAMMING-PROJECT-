"""
Flask REST API Microservice for AI Resume Analyzer & Placement Platform
Handles document parsing, ATS matching, bullet optimization, skill gap analysis,
and tailored interview question generation.
Group 17 - AI Resume Analyzer
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import os
import traceback

from parser import parse_full_resume, extract_text_from_file, extract_skills_from_text
from matcher import compute_ats_match
from bullet_optimizer import analyze_bullet_points, analyze_single_bullet
from skill_gap import analyze_skill_gap
from interview_gen import generate_interview_questions

app = Flask(__name__)
CORS(app)

@app.route("/api/health", methods=["GET"])
def health_check():
    return jsonify({
        "status": "online",
        "service": "AI Resume Analyzer NLP Microservice",
        "version": "1.0.0",
        "group": "Group 17"
    })

@app.route("/api/parse-resume", methods=["POST"])
def parse_resume():
    """Parses a resume file and extracts structured entities."""
    try:
        data = request.get_json(force=True, silent=True) or {}
        file_path = data.get("file_path")

        # Also support direct multipart file upload
        if not file_path and "resume_file" in request.files:
            uploaded_file = request.files["resume_file"]
            temp_dir = os.path.join(os.path.dirname(__file__), "..", "uploads", "temp")
            os.makedirs(temp_dir, exist_ok=True)
            file_path = os.path.join(temp_dir, uploaded_file.filename)
            uploaded_file.save(file_path)

        if not file_path or not os.path.exists(file_path):
            return jsonify({"error": f"Invalid or missing file path: {file_path}"}), 400

        result = parse_full_resume(file_path)
        return jsonify({"success": True, "data": result})

    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": str(e)}), 500

@app.route("/api/match-job", methods=["POST"])
def match_job():
    """Matches a parsed resume against a job description."""
    try:
        data = request.get_json(force=True)
        parsed_resume = data.get("parsed_resume", {})
        job_description = data.get("job_description", "")
        required_skills = data.get("required_skills", [])

        if not job_description and not required_skills:
            return jsonify({"error": "Job description or required skills list required"}), 400

        result = compute_ats_match(parsed_resume, job_description, required_skills)
        return jsonify({"success": True, "data": result})

    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": str(e)}), 500

@app.route("/api/optimize-bullet", methods=["POST"])
def optimize_bullet():
    """Analyzes bullet point(s) against Google XYZ formula and recommends enhancements."""
    try:
        data = request.get_json(force=True)
        bullet_text = data.get("bullet_text", "")
        bullets_list = data.get("bullets_list", [])

        if bullet_text:
            result = analyze_single_bullet(bullet_text)
        elif bullets_list:
            result = analyze_bullet_points(bullets_list)
        else:
            return jsonify({"error": "bullet_text or bullets_list required"}), 400

        return jsonify({"success": True, "data": result})

    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": str(e)}), 500

@app.route("/api/skill-gap", methods=["POST"])
def skill_gap():
    """Generates curated learning roadmap for missing skills."""
    try:
        data = request.get_json(force=True)
        missing_skills = data.get("missing_skills", [])
        result = analyze_skill_gap(missing_skills)
        return jsonify({"success": True, "data": result})

    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": str(e)}), 500

@app.route("/api/generate-questions", methods=["POST"])
def generate_questions():
    """Generates tailored interview questions based on candidate profile."""
    try:
        data = request.get_json(force=True)
        candidate_skills = data.get("candidate_skills", [])
        extracted_projects = data.get("extracted_projects", [])

        result = generate_interview_questions(candidate_skills, extracted_projects)
        return jsonify({"success": True, "data": result})

    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": str(e)}), 500

@app.route("/api/analyze-full", methods=["POST"])
def analyze_full():
    """
    Comprehensive all-in-one analysis:
    Parses resume -> Computes ATS match -> Analyzes bullet points ->
    Computes skill gap with roadmap -> Generates tailored interview questions.
    """
    try:
        data = request.get_json(force=True, silent=True) or {}
        file_path = data.get("file_path")
        job_description = data.get("job_description", "")
        required_skills = data.get("required_skills", [])

        if not file_path or not os.path.exists(file_path):
            return jsonify({"error": f"Valid file_path is required. Received: {file_path}"}), 400

        # 1. Parse Resume
        parsed = parse_full_resume(file_path)

        # 2. ATS Match
        match_result = compute_ats_match(parsed, job_description, required_skills)

        # 3. Bullet Point Optimization
        bullet_result = analyze_bullet_points(parsed.get("bullet_points", []))

        # 4. Skill Gap & Roadmap
        gap_result = analyze_skill_gap(match_result["missing_skills"])

        # 5. Tailored Interview Questions
        questions_result = generate_interview_questions(
            parsed.get("skills", {}).get("skills_list", []),
            parsed.get("projects", [])
        )

        return jsonify({
            "success": True,
            "data": {
                "parsed_candidate": {
                    "name": parsed["candidate_name"],
                    "contact": parsed["contact"],
                    "skills": parsed["skills"],
                    "education": parsed["education"],
                    "projects": parsed["projects"],
                    "formatting_score": parsed["formatting_score"]
                },
                "match_analysis": match_result,
                "bullet_optimization": bullet_result,
                "skill_gap_roadmap": gap_result,
                "interview_preparation": questions_result
            }
        })

    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": str(e)}), 500

@app.route("/api/batch-screen", methods=["POST"])
def batch_screen():
    """
    Screens multiple candidate resumes against a single Job Description,
    returning an objective ranked candidate leaderboard.
    """
    try:
        data = request.get_json(force=True)
        candidates = data.get("candidates", []) # list of {"id": 1, "name": "...", "file_path": "..."}
        job_description = data.get("job_description", "")
        required_skills = data.get("required_skills", [])

        results = []
        for cand in candidates:
            path = cand.get("file_path")
            if path and os.path.exists(path):
                parsed = parse_full_resume(path)
                match = compute_ats_match(parsed, job_description, required_skills)
                results.append({
                    "id": cand.get("id"),
                    "name": cand.get("name", parsed["candidate_name"]),
                    "email": parsed["contact"]["email"],
                    "phone": parsed["contact"]["phone"],
                    "overall_score": match["overall_score"],
                    "skill_score": match["skill_score"],
                    "experience_score": match["experience_score"],
                    "education_score": match["education_score"],
                    "matched_skills": match["matched_skills"],
                    "missing_skills": match["missing_skills"],
                    "readiness_tier": match["readiness_tier"],
                    "badge_class": match["badge_class"]
                })

        # Sort descending by overall match score
        results.sort(key=lambda x: x["overall_score"], reverse=True)

        return jsonify({
            "success": True,
            "total_candidates": len(results),
            "ranked_candidates": results
        })

    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": str(e)}), 500

if __name__ == "__main__":
    print("[STARTING] AI Resume Analyzer & Placement NLP Microservice starting on port 5000...")
    app.run(host="127.0.0.1", port=5000, debug=False)
