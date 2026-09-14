"""
Skill Gap Analyzer & Curated Learning Roadmap Engine
Maps missing technical proficiencies to high-quality learning resources,
hands-on micro-projects, and estimated study hours.
Group 17 - AI Resume Analyzer
"""

from skill_ontology import get_skill_category

# Pre-seeded comprehensive resource roadmap catalog
CURATED_ROADMAP_DATABASE = {
    "React": {
        "title": "React - The Modern Web Framework",
        "description": "Learn component architecture, hooks (useState, useEffect), state management, and SPA routing.",
        "type": "Interactive Documentation & Course",
        "url": "https://react.dev/learn",
        "alt_url": "https://www.freecodecamp.org/news/learn-react-full-course/",
        "estimated_hours": 12,
        "difficulty": "Beginner to Intermediate",
        "practice_project": "Build an interactive Task/Issue Tracker with filtering and localStorage persistence."
    },
    "Node.js": {
        "title": "Node.js & Express RESTful API Mastery",
        "description": "Master asynchronous programming, event loop, Express routing, middleware, and JWT authentication.",
        "type": "Tutorial & Guide",
        "url": "https://nodejs.org/en/learn/getting-started/introduction-to-nodejs",
        "alt_url": "https://expressjs.com/en/starter/installing.html",
        "estimated_hours": 10,
        "difficulty": "Intermediate",
        "practice_project": "Develop a REST API with CRUD operations, input validation, and JWT token authentication."
    },
    "Docker": {
        "title": "Docker Containerization Fundamentals",
        "description": "Understand images, containers, Dockerfiles, docker-compose, and multi-stage builds.",
        "type": "Hands-on Guide",
        "url": "https://docs.docker.com/get-started/",
        "alt_url": "https://www.docker.com/101-tutorial/",
        "estimated_hours": 8,
        "difficulty": "Beginner to Intermediate",
        "practice_project": "Containerize a full-stack web application (frontend + backend + database) using docker-compose."
    },
    "Kubernetes": {
        "title": "Kubernetes Orchestration & Clusters",
        "description": "Learn Pods, Deployments, Services, ConfigMaps, and cluster scaling.",
        "type": "Official Tutorial",
        "url": "https://kubernetes.io/docs/tutorials/kubernetes-basics/",
        "alt_url": "https://killercoda.com/playgrounds",
        "estimated_hours": 16,
        "difficulty": "Advanced",
        "practice_project": "Deploy a containerized microservice to a local Minikube cluster with auto-restart."
    },
    "Python": {
        "title": "Modern Python Programming & Best Practices",
        "description": "Object-oriented Python, list comprehensions, decorators, generators, and package management.",
        "type": "Documentation",
        "url": "https://docs.python.org/3/tutorial/",
        "alt_url": "https://realpython.com/",
        "estimated_hours": 10,
        "difficulty": "Beginner",
        "practice_project": "Build an automated web scraping CLI tool that exports tabular data to CSV."
    },
    "Machine Learning": {
        "title": "Applied Machine Learning with Scikit-Learn",
        "description": "Supervised & unsupervised learning, classification, regression, cross-validation, and metrics.",
        "type": "Course & Docs",
        "url": "https://scikit-learn.org/stable/tutorial/index.html",
        "alt_url": "https://www.coursera.org/specializations/machine-learning-introduction",
        "estimated_hours": 20,
        "difficulty": "Intermediate",
        "practice_project": "Train and evaluate a Random Forest model on a Kaggle dataset with hyperparameter tuning."
    },
    "NLP": {
        "title": "Natural Language Processing with NLTK & spaCy",
        "description": "Tokenization, lemmatization, named entity recognition (NER), TF-IDF, and sentiment analysis.",
        "type": "Tutorial",
        "url": "https://spacy.io/usage/spacy-101",
        "alt_url": "https://www.nltk.org/book/",
        "estimated_hours": 12,
        "difficulty": "Intermediate",
        "practice_project": "Build an automated resume text keyword extractor and job match calculator."
    },
    "MySQL": {
        "title": "Relational Database Design, SQL & Indexing",
        "description": "Schema normalization (1NF-3NF), complex JOINs, subqueries, B-Tree indexes, and transactions (ACID).",
        "type": "Documentation",
        "url": "https://dev.mysql.com/doc/refman/8.0/en/tutorial.html",
        "alt_url": "https://sqlbolt.com/",
        "estimated_hours": 8,
        "difficulty": "Beginner",
        "practice_project": "Design a 5-table normalized schema for an e-commerce platform with foreign key constraints."
    },
    "PostgreSQL": {
        "title": "PostgreSQL Advanced Queries & JSON Operations",
        "description": "ACID compliance, JSONB columns, window functions, and indexing strategies.",
        "type": "Documentation",
        "url": "https://www.postgresql.org/docs/current/tutorial.html",
        "alt_url": "https://www.postgresqltutorial.com/",
        "estimated_hours": 8,
        "difficulty": "Intermediate",
        "practice_project": "Build an audit logging system utilizing PostgreSQL JSONB columns and triggers."
    },
    "MongoDB": {
        "title": "NoSQL Document Databases with MongoDB",
        "description": "CRUD operations, aggregation pipelines, document embedding vs referencing, and Mongoose ODM.",
        "type": "Course",
        "url": "https://learn.mongodb.com/",
        "alt_url": "https://www.mongodb.com/docs/manual/tutorial/getting-started/",
        "estimated_hours": 8,
        "difficulty": "Beginner",
        "practice_project": "Build an analytical logging system using MongoDB aggregation pipelines."
    },
    "AWS": {
        "title": "AWS Cloud Foundations (EC2, S3, RDS, IAM)",
        "description": "Deploy virtual servers (EC2), store objects (S3), manage permissions (IAM), and host databases (RDS).",
        "type": "Digital Training",
        "url": "https://aws.amazon.com/training/digital/aws-cloud-practitioner-essentials/",
        "alt_url": "https://docs.aws.amazon.com/",
        "estimated_hours": 14,
        "difficulty": "Beginner to Intermediate",
        "practice_project": "Host a static website on Amazon S3 with CloudFront CDN distribution."
    },
    "Git": {
        "title": "Git Version Control & Collaborative Workflows",
        "description": "Branching strategies, merge conflict resolution, rebasing, pull requests, and Git hooks.",
        "type": "Interactive Guide",
        "url": "https://git-scm.com/book/en/v2",
        "alt_url": "https://learngitbranching.js.org/",
        "estimated_hours": 5,
        "difficulty": "Beginner",
        "practice_project": "Simulate a collaborative team git workflow with feature branching and conflict resolution."
    },
    "REST API": {
        "title": "RESTful API Architectural Principles & Standards",
        "description": "HTTP status codes, idempotency, REST constraints, resource URIs, OpenAPI/Swagger documentation.",
        "type": "Guide",
        "url": "https://restfulapi.net/",
        "alt_url": "https://swagger.io/docs/specification/about/",
        "estimated_hours": 6,
        "difficulty": "Beginner",
        "practice_project": "Write an OpenAPI 3.0 specification for an authentication and user management API."
    },
    "CI/CD": {
        "title": "Automated CI/CD with GitHub Actions",
        "description": "Workflows, jobs, triggers, secret management, automated linting, test runners, and deployment.",
        "type": "Documentation",
        "url": "https://docs.github.com/en/actions",
        "alt_url": "https://lab.github.com/",
        "estimated_hours": 6,
        "difficulty": "Intermediate",
        "practice_project": "Create a GitHub Actions workflow that automatically runs test suites on every pull request."
    }
}

def analyze_skill_gap(missing_skills_list):
    """
    Analyzes missing skills and builds a personalized learning roadmap.
    """
    roadmap = []
    total_prep_hours = 0

    for skill in missing_skills_list:
        category = get_skill_category(skill)
        resource_info = CURATED_ROADMAP_DATABASE.get(skill)
        
        if resource_info:
            hours = resource_info["estimated_hours"]
            total_prep_hours += hours
            roadmap.append({
                "skill": skill,
                "category": category,
                "priority": "High" if category in ["Programming Languages", "Databases & Data Storage", "Backend Frameworks & Technologies"] else "Medium",
                "title": resource_info["title"],
                "description": resource_info["description"],
                "type": resource_info["type"],
                "url": resource_info["url"],
                "alt_url": resource_info["alt_url"],
                "estimated_hours": hours,
                "difficulty": resource_info["difficulty"],
                "practice_project": resource_info["practice_project"]
            })
        else:
            # Fallback for other skills in ontology
            hours = 8
            total_prep_hours += hours
            roadmap.append({
                "skill": skill,
                "category": category,
                "priority": "Medium",
                "title": f"Mastering {skill}: Fundamentals & Practice",
                "description": f"Learn industry core concepts, standards, and practical application of {skill}.",
                "type": "Documentation & Exercises",
                "url": f"https://www.google.com/search?q={skill.replace(' ', '+')}+official+tutorial+documentation",
                "alt_url": "https://developer.mozilla.org/",
                "estimated_hours": hours,
                "difficulty": "Intermediate",
                "practice_project": f"Create a sample mini-module or portfolio demo showcasing {skill}."
            })

    # Sort roadmap: High Priority first
    roadmap.sort(key=lambda x: (0 if x["priority"] == "High" else 1, x["skill"]))

    return {
        "missing_skills_count": len(missing_skills_list),
        "total_estimated_study_hours": total_prep_hours,
        "roadmap": roadmap
    }
