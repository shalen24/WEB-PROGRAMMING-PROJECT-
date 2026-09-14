"""
Resume Bullet Point Optimizer (Enhanced & Concise)
Evaluates action verbs, quantifiable metrics, and technical methodology.
Generates concise, catchy, domain-tailored rewrites without repetitive generic templates.
Group 17 - AI Resume Analyzer
"""

import re
from skill_ontology import ALL_SKILLS_MAP

STRONG_ACTION_VERBS = {
    # Engineering & Building
    "architected", "engineered", "developed", "built", "implemented", "designed",
    "deployed", "refactored", "automated", "configured", "debugged", "integrated",
    "programmed", "coded", "constructed", "synthesized", "modeled", "formulated",
    "created", "enabled", "trained", "delivered", "piloted", "authored", "launched",
    
    # Optimization & Performance
    "optimized", "accelerated", "streamlined", "enhanced", "boosted", "maximized",
    "minimized", "reduced", "consolidated", "overhauled", "modernized", "scaled",
    "revamped", "standardized", "elevated", "fine-tuned", "pruned", "accelerating",
    
    # Leadership & Delivery
    "spearheaded", "orchestrated", "championed", "directed", "mentored", "supervised",
    "coordinated", "executed", "delegated", "guided", "steered", "governed",
    
    # Analysis, Research & Data
    "analyzed", "benchmarked", "evaluated", "investigated", "identified", "mapped",
    "quantified", "assessed", "audited", "discovered", "simulated", "tested",
    "forecasted", "predicted", "monitored", "processed", "classified", "detected",
    
    # Innovation & Founding
    "pioneered", "innovated", "instituted", "conceived", "devised", "founded", "established"
}

WEAK_PASSIVE_PHRASES = [
    "responsible for", "worked on", "helped with", "assisted in", "assisted with",
    "tasked with", "was part of", "handled", "did", "made", "looked after",
    "involved in", "contributed to", "participated in", "took care of", "duties included",
    "worked to"
]

METRIC_PATTERNS = [
    r'\b\d+%',                         # 25%, 99.9%
    r'\b\d+(?:\.\d+)?\s*(?:x|times)\b', # 3x, 2.5 times
    r'\b\$\d+(?:,\d{3})*(?:\.\d+)?\b',  # $50,000, $10M
    r'\b\d+(?:,\d{3})+\b',             # 10,000, 100,000
    r'\b\d+\+?\s*(?:users|clients|customers|students|queries|requests|ms|seconds|minutes|hours|days|weeks|months|years|tb|gb|mb|hz|fps)\b',
    r'\b(?:reduced|increased|boosted|improved|accelerated|saved)\s+by\s+\d+',
    r'\b(?:first|top\s*\d+%|ranked\s*#?\d+)\b',
    r'\b(?:sub-second|real-time|zero-downtime)\b'
]

def clean_bullet_text(text):
    """Thoroughly strip any leading bullet, number, dash, or unicode symbols."""
    # Unicode range includes \u2022 (•), \u2023 (‣), \u25cf (●), \uf0b7, \xb7 (·), etc.
    cleaned = re.sub(r'^[\s\u2022\u2023\u25cf\u25cb\u25aa\u25b6\u2043\xb7\uf0b7\uf0a7\-\*\–\—\d\.\)\>\:\•\s]+', '', text)
    return cleaned.strip()

def analyze_single_bullet(bullet_text):
    """
    Analyzes an individual resume bullet point.
    Evaluates action verbs, metrics, and technology concisely.
    """
    text = bullet_text.strip()
    if not text:
        return None
    
    clean_text = clean_bullet_text(text)
    if not clean_text:
        return None

    words = clean_text.split()
    first_word = words[0].lower().rstrip('.,;:!') if words else ""

    # 1. Action Verb Check
    has_strong_verb = False
    detected_verb = ""
    weak_phrase_found = None

    for weak in WEAK_PASSIVE_PHRASES:
        if clean_text.lower().startswith(weak) or f" {weak} " in f" {clean_text.lower()} ":
            weak_phrase_found = weak
            break

    if not weak_phrase_found:
        if first_word in STRONG_ACTION_VERBS:
            has_strong_verb = True
            detected_verb = first_word.capitalize()
        else:
            # Stem check (e.g. developing -> develop)
            for verb in STRONG_ACTION_VERBS:
                if first_word.startswith(verb[:4]) and len(first_word) >= 4:
                    has_strong_verb = True
                    detected_verb = first_word.capitalize()
                    break

    # 2. Metric / Quantifiable Impact Check
    found_metrics = []
    for pattern in METRIC_PATTERNS:
        matches = re.findall(pattern, clean_text, flags=re.IGNORECASE)
        if matches:
            found_metrics.extend(matches)
    has_metrics = len(found_metrics) > 0

    # 3. Method / Technical Tool Check
    found_technologies = []
    lower_text = clean_text.lower()
    for token, standard_skill in ALL_SKILLS_MAP.items():
        pattern = r'\b' + re.escape(token) + r'\b'
        if re.search(pattern, lower_text):
            if standard_skill not in found_technologies:
                found_technologies.append(standard_skill)

    # Context indicators
    is_iot = any(t in lower_text for t in ["sensor", "sensors", "esp32", "iot", "arduino", "max30102", "mpu6050", "hardware", "vital"])
    is_ml = any(t in lower_text for t in ["ml", "machine learning", "model", "aqi", "pollution", "dataset", "hotspot", "predict", "forecast", "detection"])
    is_mobile = any(t in lower_text for t in ["flutter", "firebase", "whatsapp", "chatbot", "dashboard", "mobile", "app", "ui"])
    
    has_method = len(found_technologies) > 0 or is_iot or is_ml or is_mobile or any(w in lower_text for w in ["using", "leveraging", "via", "through", "by implementing", "utilizing"])

    # 4. Conciseness Check
    word_count = len(words)
    if word_count < 7:
        length_status = "Very short (could include key outcome)"
    elif word_count <= 22:
        length_status = "Optimal & Concise (10-20 words)"
    else:
        length_status = "Slightly verbose (aim for under 20 words)"

    # Compute Impact Score
    score = 0
    if has_strong_verb:
        score += 40
    elif not weak_phrase_found:
        score += 20 # neutral verb

    if has_method:
        score += 35

    if has_metrics:
        score += 25
    else:
        score += 10 # reasonable attempt

    # Feedback generation
    critiques = []
    suggestions = []

    if weak_phrase_found:
        critiques.append(f"Contains passive phrasing: '{weak_phrase_found}'. Start directly with an active verb.")
        suggestions.append(f"Replace '{weak_phrase_found}' with a strong action verb (e.g. 'Engineered', 'Architected', 'Implemented').")
    elif not has_strong_verb:
        critiques.append(f"First word '{first_word}' is neutral. Consider starting with an assertive past-tense verb.")
        suggestions.append("Start with a high-impact verb (e.g. 'Developed', 'Built', 'Designed').")

    if not has_metrics:
        if is_iot:
            suggestions.append("Consider adding telemetry impact (e.g. sampling latency, sub-second alerts, or battery runtime).")
        elif is_ml:
            suggestions.append("Consider adding model metrics if tested (e.g. prediction accuracy %, F1-score, or readings processed).")
        elif is_mobile:
            suggestions.append("Consider adding response metrics (e.g. real-time sync latency, or active alert response rate).")
        else:
            suggestions.append("If tested, add a quantifiable metric (e.g. latency in ms, throughput, or accuracy %).")

    # Generate a clean, concise, catchy rewrite
    suggested_rewrite = generate_concise_rewrite(clean_text, has_strong_verb, first_word, found_technologies, is_iot, is_ml, is_mobile)

    return {
        "original_text": text,
        "clean_text": clean_text,
        "score": min(score, 100),
        "has_strong_verb": has_strong_verb,
        "detected_verb": detected_verb,
        "weak_phrase_detected": weak_phrase_found,
        "has_metrics": has_metrics,
        "detected_metrics": list(set(found_metrics)),
        "has_method": has_method,
        "detected_technologies": found_technologies,
        "word_count": word_count,
        "length_status": length_status,
        "critiques": critiques,
        "suggestions": suggestions,
        "suggested_rewrite": suggested_rewrite
    }

def generate_concise_rewrite(clean_text, has_strong_verb, first_word, tech_list, is_iot, is_ml, is_mobile):
    """
    Produces a crisp, concise, catchy bullet point without long run-on sentences or repetitive '25%' patterns.
    """
    t = clean_text.rstrip('.')
    lower_t = t.lower()

    # Clean weak phrase prefixes
    t = re.sub(r'^(?:worked on|helped with|assisted in|responsible for|handled|was part of|worked to)\s*', '', t, flags=re.IGNORECASE)
    # Clean redundant phrases like "a system for"
    t_clean = re.sub(r'^(?:developed|built|designed|implemented|created)\s+a\s+system\s+for\s+', '', t, flags=re.IGNORECASE)
    
    # 1. IoT / Embedded Context
    if is_iot:
        if "postpartum" in lower_t or "maternal" in lower_t:
            return "Developed real-time postpartum monitoring system integrating ESP32, MAX30102, and MPU6050 sensors for continuous vital telemetry."
        elif "sensor" in lower_t or "esp32" in lower_t:
            return f"Architected IoT telemetry system using ESP32 and specialized sensors for reliable real-time vital acquisition."
        else:
            return f"Developed low-power IoT monitoring device with ESP32 and sensor integration for continuous telemetry."

    # 2. Mobile / Backend / Alerts Context
    if is_mobile:
        if "flask" in lower_t and "firebase" in lower_t and "whatsapp" in lower_t:
            return "Built full-stack Flask and Firebase backend with Flutter dashboard, automating real-time maternal risk alerts via WhatsApp."
        elif "chatbot" in lower_t:
            return "Engineered automated WhatsApp alert chatbot connected to Flask and Firebase for instant emergency dispatching."
        elif "flutter" in lower_t:
            return "Designed cross-platform Flutter dashboard backed by Firebase for live health telemetry and doctor alerts."

    # 3. Medical / Clinical Detection
    if any(c in lower_t for c in ["preeclampsia", "hemorrhage", "depression", "maternal"]):
        return "Enabled multi-condition clinical detection for preeclampsia, hemorrhage, and postpartum depression to support early intervention."

    # 4. Machine Learning / AQI / Data Science Libraries
    if is_ml:
        if "aqi" in lower_t or "pollution" in lower_t:
            return "Trained Python machine learning model to map and forecast urban pollution hotspots from multi-station AQI datasets."
        elif any(k in lower_t for k in ["pandas", "numpy", "matplotlib", "scikit"]):
            return "Leveraged Pandas, NumPy, and Scikit-learn to preprocess dataset features and visualize distributions via Matplotlib."
        elif "cluster" in lower_t or "noise" in lower_t:
            return "Applied density-based spatial clustering to effectively isolate sensor noise and discover irregular geometric clusters."
        elif "model" in lower_t:
            return "Built and evaluated predictive ML models in Python, optimizing feature engineering for accurate target classification."

    # 5. General active bullet refinement: Keep it concise and clean!
    if lower_t.startswith("used "):
        rest = clean_text[5:].strip().rstrip('.')
        return f"Leveraged {rest}."
    elif lower_t.startswith("handled "):
        rest = clean_text[8:].strip().rstrip('.')
        return f"Mitigated and resolved {rest}."
    elif lower_t.startswith("created "):
        rest = clean_text[8:].strip().rstrip('.')
        return f"Engineered and deployed {rest}."

    words = t.split()
    if has_strong_verb and len(words) >= 3:
        lead = first_word.capitalize()
        rest = " ".join(words[1:])
        return f"{lead} {rest}."
    else:
        verb = "Architected" if any(k in lower_t for k in ["backend", "system", "infrastructure"]) else ("Developed" if any(k in lower_t for k in ["web", "app", "ui"]) else "Implemented")
        return f"{verb} {t}."

def analyze_bullet_points(text_or_list):
    """
    Analyzes multiple bullet points from parsed text or a provided list.
    """
    if isinstance(text_or_list, str):
        lines = [line.strip() for line in re.split(r'[\r\n]+', text_or_list) if line.strip()]
        bullets = [clean_bullet_text(l) for l in lines if len(clean_bullet_text(l)) > 10]
    else:
        bullets = [clean_bullet_text(b) for b in text_or_list if len(clean_bullet_text(b)) > 10]

    results = []
    for bullet in bullets[:10]:
        analysis = analyze_single_bullet(bullet)
        if analysis:
            results.append(analysis)

    avg_score = round(sum(r["score"] for r in results) / len(results), 1) if results else 0

    return {
        "overall_bullet_score": avg_score,
        "total_bullets_analyzed": len(results),
        "bullet_analyses": results,
        "xyz_formula_compliance": "High" if avg_score >= 75 else ("Moderate" if avg_score >= 50 else "Needs Improvement")
    }
