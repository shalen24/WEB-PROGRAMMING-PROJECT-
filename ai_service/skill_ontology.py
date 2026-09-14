"""
Skill Ontology & Dictionary
Comprehensive collection of technical and professional skills, categorized with synonyms and aliases.
Group 17 - AI Resume Analyzer
"""

SKILL_CATEGORIES = {
    "Programming Languages": [
        "Python", "JavaScript", "TypeScript", "Java", "C++", "C", "C#", "PHP", 
        "Go", "Rust", "Ruby", "Kotlin", "Swift", "Dart", "R", "Scala", "Perl", 
        "SQL", "HTML5", "CSS3", "Bash", "Shell"
    ],
    "Mobile & Cross-Platform": [
        "Flutter", "React Native", "Android", "iOS", "Dart", "Mobile Development"
    ],
    "IoT & Embedded Systems": [
        "IoT", "ESP32", "Arduino", "Embedded Systems", "Sensors", "Microcontrollers", 
        "Raspberry Pi", "I2C", "SPI", "Hardware Interfacing"
    ],
    "Frontend Frameworks & Libraries": [
        "React", "Angular", "Vue", "Next.js", "Nuxt.js", "Redux", "Tailwind CSS", 
        "Bootstrap", "Sass", "jQuery", "Svelte", "Material UI", "Chakra UI", "Webpack", "Vite"
    ],
    "Backend Frameworks & Technologies": [
        "Flask", "FastAPI", "Django", "Node.js", "Express", "Spring Boot", "Laravel", 
        "ASP.NET", "Ruby on Rails", "NestJS", "REST API", "GraphQL"
    ],
    "Databases & Cloud Storage": [
        "Firebase", "MySQL", "PostgreSQL", "MongoDB", "Redis", "SQLite", "Oracle", 
        "Cassandra", "DynamoDB", "MariaDB", "Elasticsearch", "Firestore"
    ],
    "Cloud & DevOps": [
        "AWS", "Azure", "GCP", "Docker", "Kubernetes", "CI/CD", "Jenkins", 
        "GitHub Actions", "GitLab CI", "Terraform", "Ansible", "Linux", "Nginx", "Apache"
    ],
    "AI, ML & Data Science": [
        "Machine Learning", "Deep Learning", "Natural Language Processing", "NLP", 
        "Computer Vision", "TensorFlow", "PyTorch", "Scikit-Learn", "Pandas", "NumPy", 
        "OpenCV", "Keras", "Matplotlib", "Seaborn", "Data Preprocessing", "Predictive Modeling"
    ],
    "Software Engineering & Architecture": [
        "Data Structures", "Algorithms", "Object-Oriented Programming", "OOP", 
        "Microservices", "System Design", "Agile", "Scrum", 
        "Git", "GitHub", "Unit Testing", "Test-Driven Development", "TDD"
    ],
    "Soft Skills & Management": [
        "Problem Solving", "Critical Thinking", "Team Collaboration", "Leadership", 
        "Communication", "Time Management", "Adaptability", "Presentation Skills"
    ]
}

# Synonyms and aliases mapping to standardized name
SKILL_ALIASES = {
    "reactjs": "React",
    "react.js": "React",
    "nodejs": "Node.js",
    "node.js": "Node.js",
    "node": "Node.js",
    "expressjs": "Express",
    "express.js": "Express",
    "vuejs": "Vue",
    "vue.js": "Vue",
    "angularjs": "Angular",
    "nextjs": "Next.js",
    "tailwind": "Tailwind CSS",
    "postgres": "PostgreSQL",
    "psql": "PostgreSQL",
    "mongo": "MongoDB",
    "k8s": "Kubernetes",
    "gcp": "GCP",
    "google cloud": "GCP",
    "aws cloud": "AWS",
    "amazon web services": "AWS",
    "sklearn": "Scikit-Learn",
    "scikit learn": "Scikit-Learn",
    "tf": "TensorFlow",
    "ml": "Machine Learning",
    "dl": "Deep Learning",
    "nlp": "NLP",
    "natural language processing": "NLP",
    "restful api": "REST API",
    "rest apis": "REST API",
    "rest": "REST API",
    "html": "HTML5",
    "css": "CSS3",
    "js": "JavaScript",
    "ts": "TypeScript",
    "cpp": "C++",
    "c sharp": "C#",
    "golang": "Go",
    "git / github": "Git",
    "github": "GitHub",
    "dsa": "Data Structures",
    "data structures and algorithms": "Data Structures",
    "oop": "Object-Oriented Programming",
    "oops": "Object-Oriented Programming",
    "ci/cd": "CI/CD",
    "continuous integration": "CI/CD",
    "flutter": "Flutter",
    "firebase": "Firebase",
    "firestore": "Firebase",
    "esp32": "ESP32",
    "iot": "IoT",
    "internet of things": "IoT",
    "embedded": "Embedded Systems",
    "embedded systems": "Embedded Systems",
    "sensors": "Sensors",
    "sensor": "Sensors",
    "max30102": "Sensors",
    "mpu6050": "Sensors",
    "flask": "Flask",
    "fastapi": "FastAPI",
    "django": "Django",
    "pandas": "Pandas",
    "numpy": "NumPy"
}

# Flat list of all unique standardized skills (lowercased lookup for fast matching)
ALL_SKILLS_MAP = {}
for category, skills in SKILL_CATEGORIES.items():
    for skill in skills:
        ALL_SKILLS_MAP[skill.lower()] = skill

for alias, standard in SKILL_ALIASES.items():
    ALL_SKILLS_MAP[alias.lower()] = standard

def get_standard_skill(text):
    """Normalize and return standard skill name if matched, else None."""
    cleaned = text.strip().lower()
    return ALL_SKILLS_MAP.get(cleaned)

def get_skill_category(standard_skill):
    """Return the category name for a given standardized skill."""
    for category, skills in SKILL_CATEGORIES.items():
        if standard_skill in skills:
            return category
    return "Other Technical Skills"
