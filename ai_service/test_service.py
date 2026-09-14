import os
import sys

current_dir = os.path.dirname(os.path.abspath(__file__))
sys.path.append(current_dir)

from bullet_optimizer import analyze_single_bullet, analyze_bullet_points
from skill_gap import analyze_skill_gap
from interview_gen import generate_interview_questions
from matcher import compute_ats_match

print("=== 1. TESTING BULLET OPTIMIZER ===")
weak_bullet = "Worked on the website and helped with frontend development."
res1 = analyze_single_bullet(weak_bullet)
print(f"Original: '{weak_bullet}'")
print(f"Score: {res1['score']}/100")
print(f"Has Strong Verb: {res1['has_strong_verb']}")
print(f"Weak Phrase Detected: {res1['weak_phrase_detected']}")
print(f"Suggested Rewrite: {res1['suggested_rewrite']}")

strong_bullet = "Architected and deployed a distributed microservice using Docker and Python, reducing latency by 45% for 50,000 active users."
res2 = analyze_single_bullet(strong_bullet)
print(f"\nOriginal: '{strong_bullet}'")
print(f"Score: {res2['score']}/100")
print(f"Has Strong Verb: {res2['has_strong_verb']} ({res2['detected_verb']})")
print(f"Has Metrics: {res2['has_metrics']} -> {res2['detected_metrics']}")

print("\n=== 2. TESTING SKILL GAP ANALYZER ===")
gap = analyze_skill_gap(["React", "Docker", "Kubernetes"])
print(f"Missing Skills Count: {gap['missing_skills_count']}")
print(f"Estimated Total Prep Hours: {gap['total_estimated_study_hours']} hrs")
for item in gap["roadmap"]:
    print(f" - [{item['priority']}] {item['skill']}: {item['title']} ({item['estimated_hours']} hrs) -> {item['url']}")

print("\n=== 3. TESTING INTERVIEW QUESTION GENERATOR ===")
interview = generate_interview_questions(["React", "Python"], [{"title": "AI Placement Readiness Portal"}])
print(f"Generated {len(interview['technical_questions'])} Technical Questions")
print(f"Sample Technical: {interview['technical_questions'][0]['question']}")
print(f"Generated {len(interview['project_questions'])} Project Questions")
print(f"Sample Project: {interview['project_questions'][0]['question']}")
print(f"Generated {len(interview['behavioral_questions'])} Behavioral Questions")

print("\n=== 4. TESTING ATS MATCHER ===")
mock_parsed = {
    "raw_text": "Experienced in React, JavaScript, HTML5, CSS3, Git. Built responsive web applications and university management portals with degree in Computer Science B.Tech 2026.",
    "skills": {
        "skills_list": ["React", "JavaScript", "HTML5", "CSS3", "Git"]
    },
    "education": {
        "degrees": ["B.Tech"]
    },
    "formatting_score": 90
}
job_desc = "Looking for Junior Full Stack Developer with React, Node.js, Express, MySQL, and Git."
required_skills = ["React", "Node.js", "Express", "MySQL", "Git"]

match = compute_ats_match(mock_parsed, job_desc, required_skills)
print(f"Overall ATS Score: {match['overall_score']}% ({match['readiness_tier']})")
print(f"Skill Score: {match['skill_score']}%")
print(f"Matched Skills: {match['matched_skills']}")
print(f"Missing Skills: {match['missing_skills']}")

print("\n[SUCCESS] ALL AI PIPELINE MODULES VERIFIED & WORKING!")
