"""
Tailored Interview Question Generator (Domain-Aware & Diverse)
Generates dynamic Technical, Contextual Project Deep-Dive, and STAR Behavioral
interview questions tailored to candidate's verified skills and project domains.
Group 17 - AI Resume Analyzer
"""

import random
import re

# Comprehensive Technical Question Bank across major stacks
TECH_QUESTION_BANK = {
    "Flutter": [
        {
            "question": "Explain the difference between StatelessWidget and StatefulWidget in Flutter. How does the Flutter rendering pipeline (Element tree and RenderObject) minimize redraw overhead?",
            "difficulty": "Medium",
            "eval_criteria": "Understanding widget immutability, BuildContext, Element lifecycle, and how Flutter diffs the widget tree.",
            "answer_hints": "StatelessWidgets are immutable and build only when parent properties change. StatefulWidgets maintain a persistent State object across builds. Flutter creates an Element tree that compares old and new widget types/keys, updating RenderObjects only when necessary rather than destroying the layout.",
            "keywords": ["StatelessWidget", "StatefulWidget", "Element Tree", "RenderObject", "Widget Lifecycle"]
        },
        {
            "question": "How do you manage complex application state in Flutter (e.g. Provider, Riverpod, or Bloc), and how do you prevent unnecessary widget tree rebuilds?",
            "difficulty": "Medium to Hard",
            "eval_criteria": "State separation, architectural patterns, avoiding global setState, and using selectors/consumers.",
            "answer_hints": "Discuss separating UI from business logic using patterns like Provider or Bloc. Explain using 'context.select' or Consumer widgets to scope rebuilds to only the specific child widgets that depend on the changed state, keeping animations and parent layouts untouched.",
            "keywords": ["Provider", "Bloc", "State Management", "Consumer", "Rebuild Optimization"]
        }
    ],
    "Firebase": [
        {
            "question": "What is the architectural difference between Cloud Firestore and Realtime Database? How do Firestore security rules protect client data?",
            "difficulty": "Medium",
            "eval_criteria": "Document/collection model vs JSON tree, shallow queries, indexing, and declarative security rules.",
            "answer_hints": "Firestore uses a document-collection model with automatic multi-field indexing and shallow queries (fetching documents doesn't fetch subcollections). Realtime DB is a large JSON tree. Security rules evaluate request.auth, incoming data, and existing resource state before permitting read/write.",
            "keywords": ["Cloud Firestore", "Security Rules", "Collections", "Realtime Database", "Indexing"]
        },
        {
            "question": "How does Firebase handle offline persistence and real-time synchronization when mobile devices experience network drops?",
            "difficulty": "Medium",
            "eval_criteria": "Local cache queue, optimistic UI updates, and synchronization upon reconnection.",
            "answer_hints": "Firebase SDKs maintain a local SQLite/IndexedDB cache on the device. Writes are applied locally immediately (optimistic update) and queued. When internet connectivity is restored, the SDK syncs the queued changes with the cloud server and handles conflict resolution.",
            "keywords": ["Offline Persistence", "Local Cache", "Optimistic UI", "Sync Queue", "Network Intermittency"]
        }
    ],
    "Flask": [
        {
            "question": "In Flask, what is the difference between the Application Context and the Request Context? When would you use 'current_app' vs 'g'?",
            "difficulty": "Medium",
            "eval_criteria": "Understanding WSGI context proxies, thread-local storage, and request lifecycle.",
            "answer_hints": "The application context binds application-level globals (current_app, g) for the running app. The request context binds incoming HTTP data (request, session). 'current_app' accesses app configuration without circular imports; 'g' is a per-request temporary storage for user data or DB connections.",
            "keywords": ["Application Context", "Request Context", "current_app", "g object", "WSGI"]
        },
        {
            "question": "How do Flask Blueprints support clean, modular backend architectures in production environments?",
            "difficulty": "Easy to Medium",
            "eval_criteria": "Code modularity, URL prefixing, reusable API route controllers, and separation of concerns.",
            "answer_hints": "Blueprints group related routes, templates, and error handlers into distinct modules (e.g. auth, api, dashboard). They allow registering endpoints under specific URL prefixes and middleware without cluttering the main app entrypoint.",
            "keywords": ["Blueprints", "Modularity", "URL Prefix", "Route Separation", "Middleware"]
        }
    ],
    "IoT": [
        {
            "question": "What is the difference between I2C and SPI communication protocols in IoT systems, and how do you choose between them when interfacing multiple sensors with an ESP32?",
            "difficulty": "Medium",
            "eval_criteria": "Wiring complexity, clock speed, master-slave addressing, full vs half duplex.",
            "answer_hints": "I2C uses only 2 wires (SDA, SCL) and supports multiple devices using unique hardware 7-bit addresses, but maxes out at ~400kHz. SPI uses 4 wires (MOSI, MISO, SCK, CS) and individual chip-select pins, but runs at much higher speeds (MHz+) with full-duplex transmission. I2C is preferred for pin-constrained sensor buses; SPI is chosen for high-bandwidth displays or SD storage.",
            "keywords": ["I2C", "SPI", "SDA/SCL", "Chip Select", "Bus Addressing", "Transmission Speed"]
        },
        {
            "question": "How do you optimize power consumption in battery-powered IoT microcontrollers (like the ESP32) that continuously monitor patient vitals?",
            "difficulty": "Medium to Hard",
            "eval_criteria": "Deep sleep modes, timer/GPIO wakeups, sensor interrupt-driven polling, and radio duty cycles.",
            "answer_hints": "Keep power-hungry components (Wi-Fi/Bluetooth radio) disabled most of the time. Use ESP32 Light Sleep or Deep Sleep modes, waking up via hardware timer or sensor threshold interrupts (e.g. MPU6050 motion interrupt). Buffer sensor readings in ULP (Ultra Low Power) coprocessor memory and transmit in batch bursts.",
            "keywords": ["Deep Sleep", "ULP Coprocessor", "Hardware Interrupts", "Power Optimization", "Duty Cycle"]
        }
    ],
    "Sensors": [
        {
            "question": "How does the MAX30102 sensor calculate heart rate and blood oxygen saturation (SpO2) using optical Photoplethysmography (PPG)?",
            "difficulty": "Medium to Hard",
            "eval_criteria": "Red vs Infrared light absorption, oxygenated vs deoxygenated hemoglobin, AC/DC signal components.",
            "answer_hints": "The sensor emits Red (660nm) and Infrared (880nm) light through skin tissue. Oxygenated hemoglobin absorbs more IR light and lets Red light pass; deoxygenated hemoglobin absorbs more Red. By measuring the ratio of pulsatile (AC) to continuous (DC) reflected light (Ratio-of-Ratios), SpO2 and pulse waveforms are calculated.",
            "keywords": ["Photoplethysmography (PPG)", "SpO2", "Red vs Infrared Absorption", "Oxygenated Hemoglobin", "AC/DC Ratio"]
        },
        {
            "question": "When collecting biometric data with an accelerometer/gyroscope (like the MPU6050), how do you filter out motion artifacts and sensor noise?",
            "difficulty": "Medium",
            "eval_criteria": "Digital low-pass filtering, moving average filters, and complementary/Kalman filters.",
            "answer_hints": "Raw accelerometer readings contain high-frequency noise from body tremors and sudden movements. A digital low-pass filter or rolling median filter smooths high-frequency spikes. To fuse accelerometer and gyroscope readings for orientation tracking, a complementary filter or Kalman filter balances long-term gravity stability with short-term angular rate responsiveness.",
            "keywords": ["Motion Artifacts", "Digital Low-Pass Filter", "Moving Average", "Kalman Filter", "Signal-to-Noise Ratio"]
        }
    ],
    "Machine Learning": [
        {
            "question": "What is the Bias-Variance tradeoff in Machine Learning, and how do you diagnose whether your model suffers from high bias vs high variance?",
            "difficulty": "Medium",
            "eval_criteria": "Understanding underfitting vs overfitting, training vs validation loss curves.",
            "answer_hints": "High bias (underfitting) means the model is too simple to capture patterns, resulting in high error on both training and test data. High variance (overfitting) means the model memorized training noise, showing very low training error but high test error. Fix bias with more expressive models/features; fix variance with regularization (L1/L2), dropout, cross-validation, or more data.",
            "keywords": ["Bias-Variance Tradeoff", "Underfitting", "Overfitting", "Regularization", "Learning Curves"]
        },
        {
            "question": "When evaluating an imbalanced dataset (e.g. rare medical conditions or severe pollution spikes), why is Accuracy misleading, and which metrics should you prioritize?",
            "difficulty": "Medium",
            "eval_criteria": "Accuracy paradox, Precision, Recall, F1-score, and PR-AUC.",
            "answer_hints": "If 98% of readings are normal and 2% are critical anomalies, a naive model predicting 'normal' achieves 98% accuracy while failing 100% of medical emergencies. In critical detection, prioritize Recall (minimizing false negatives), along with Precision and the F1-score (harmonic mean) or Area Under the Precision-Recall Curve.",
            "keywords": ["Accuracy Paradox", "Recall", "Precision", "F1-Score", "False Negatives", "Imbalanced Data"]
        }
    ],
    "Python": [
        {
            "question": "What is the Global Interpreter Lock (GIL) in CPython, and how does it impact multi-threaded CPU-bound programs vs I/O-bound programs?",
            "difficulty": "Medium to Hard",
            "eval_criteria": "Mutex protecting Python objects, thread switching, multiprocessing vs threading.",
            "answer_hints": "The GIL is a mutex that allows only one native thread to execute Python bytecode at a time. For CPU-bound tasks (e.g. heavy mathematical loops), multi-threading does not utilize multiple cores and adds overhead; the 'multiprocessing' module is required. For I/O-bound tasks (e.g. network requests or file access), the GIL is released while waiting, making threading or asyncio effective.",
            "keywords": ["Global Interpreter Lock (GIL)", "CPython", "CPU-bound vs I/O-bound", "Multiprocessing", "Thread Safety"]
        },
        {
            "question": "Explain Python generators and the 'yield' keyword. How do they optimize memory when processing large datasets?",
            "difficulty": "Easy to Medium",
            "eval_criteria": "Lazy evaluation, generator objects, iterator protocol (__iter__, __next__).",
            "answer_hints": "Standard functions load all returned elements into memory at once. A generator function yields one item at a time on demand (lazy evaluation), preserving the execution state and local variables between calls. This prevents Out-Of-Memory (OOM) errors when processing gigabyte-scale logs or sensor streams.",
            "keywords": ["yield", "Generators", "Lazy Evaluation", "Memory Optimization", "Iterator Protocol"]
        }
    ],
    "MySQL": [
        {
            "question": "What are ACID properties in relational databases, and how does write-ahead logging (WAL) ensure durability during sudden power failure?",
            "difficulty": "Medium",
            "eval_criteria": "Atomicity, Consistency, Isolation, Durability, redo/undo logs.",
            "answer_hints": "Atomicity ensures all-or-nothing execution. Consistency preserves foreign keys and constraints. Isolation prevents dirty reads across concurrent transactions. Durability ensures committed transactions survive crashes by writing transaction changes to an append-only redo log on disk before updating the actual database tables.",
            "keywords": ["ACID", "Durability", "Redo Log", "Write-Ahead Logging", "Isolation Levels"]
        },
        {
            "question": "How do B-Tree indexes work in MySQL, and how do you decide which columns in a database table should have indexes?",
            "difficulty": "Medium",
            "eval_criteria": "O(log N) search, index selectivity, write performance penalty, composite index leftmost prefix rule.",
            "answer_hints": "B-Tree indexes maintain a balanced sorted tree of keys and pointers, enabling O(log N) searches. Index columns with high cardinality that appear frequently in WHERE clauses, JOIN conditions, and ORDER BY clauses. Avoid indexing small tables or columns with very low distinct values, as every INSERT/UPDATE/DELETE requires updating all index trees.",
            "keywords": ["B-Tree Index", "Query Optimization", "Cardinality", "Write Overhead", "EXPLAIN"]
        }
    ],
    "React": [
        {
            "question": "Explain the Virtual DOM in React. How does reconciliation and the diffing algorithm optimize UI rendering?",
            "difficulty": "Medium",
            "eval_criteria": "In-memory DOM tree, heuristic O(n) diffing, Fiber batching, and keys in lists.",
            "answer_hints": "Direct browser DOM manipulation is slow. React keeps an in-memory Virtual DOM representation. When state changes, React diffs the new virtual tree against the previous one using an O(n) heuristic algorithm and applies the minimal necessary mutations in batch via the Fiber reconciliation engine.",
            "keywords": ["Virtual DOM", "Reconciliation", "Fiber", "Diffing Algorithm", "Batching"]
        }
    ],
    "Node.js": [
        {
            "question": "How does the Node.js Event Loop work, and what role does libuv play in enabling non-blocking asynchronous I/O?",
            "difficulty": "Medium to Hard",
            "eval_criteria": "Call stack, event loop phases (timers, poll, check), thread pool for file/crypto I/O.",
            "answer_hints": "Node runs JavaScript on a single thread via V8. libuv provides the event loop and a thread pool (default 4 threads). Network I/O is handled non-blockingly via OS epoll/kqueue. When asynchronous operations finish, callbacks are pushed to the event loop queues and executed when the call stack clears.",
            "keywords": ["Event Loop", "libuv", "Non-blocking I/O", "Thread Pool", "Callback Queue"]
        }
    ],
    "Docker": [
        {
            "question": "Explain Docker multi-stage builds and why they are essential for production container deployments.",
            "difficulty": "Medium",
            "eval_criteria": "Separation of build environment from runtime, minimal image footprint, security attack surface reduction.",
            "answer_hints": "Multi-stage builds allow using a heavy compiler/SDK container in stage 1 to build code, and copying only the compiled artifacts into a lightweight runtime image (like Alpine or Distroless) in stage 2. This drops image sizes from gigabytes to megabytes and removes build compilers from production containers.",
            "keywords": ["Multi-stage Build", "Alpine", "Image Size Optimization", "Security", "Distroless"]
        }
    ],
    "Git": [
        {
            "question": "What is the difference between 'git merge' and 'git rebase', and in which team scenario would you choose each?",
            "difficulty": "Easy to Medium",
            "eval_criteria": "Preserving true chronological commit history vs maintaining a clean linear history.",
            "answer_hints": "Git merge combines branches with a dedicated merge commit, preserving exact chronological history and timestamps. Git rebase moves the feature branch base to the latest main branch commit, rewriting commit hashes for a clean, linear git history. Never rebase shared public branches.",
            "keywords": ["git merge", "git rebase", "Linear History", "Merge Commit", "Commit Rewriting"]
        }
    ]
}

BEHAVIORAL_QUESTIONS = [
    {
        "question": "Tell me about a challenging technical roadblock or unexpected bug you encountered during a project. How did you methodically diagnose and resolve it?",
        "framework": "STAR Method (Situation -> Task -> Action -> Result)",
        "eval_criteria": "Methodical debugging mindset, logging tools utilized, resilience under pressure, and quantified outcome.",
        "answer_hints": "Describe the bug symptoms (S), your role/goal (T), specific debugging tools (network tab, hardware serial monitor, logs) and hypothesis testing (A), and the quantified outcome or fix deployed (R)."
    },
    {
        "question": "Describe a time when you worked on a team project where team members had conflicting technical opinions or differing priorities.",
        "framework": "STAR Method (Collaboration & Conflict Resolution)",
        "eval_criteria": "Empathy, active listening, objective data-driven decision making, and team cohesion.",
        "answer_hints": "Show that you listened to both viewpoints, proposed benchmarking or proof-of-concept testing rather than arguing, and reached a consensus that delivered the project on time."
    },
    {
        "question": "How do you prioritize your time when you have upcoming exam deadlines, project submissions, and placement preparation happening simultaneously?",
        "framework": "STAR Method (Time Management & Prioritization)",
        "eval_criteria": "Time blocking, Eisenhower matrix (urgent vs important), milestone planning, and stress resilience.",
        "answer_hints": "Discuss breaking large tasks into daily sprints, using tools like Notion or Trello, eliminating distractions, and delivering reliable incremental progress."
    }
]

def detect_project_domain(title, desc_lines):
    """Classifies project into domain: iot, ml, mobile, or web."""
    combined = (title + " " + " ".join(desc_lines)).lower()
    
    if any(k in combined for k in ["esp32", "sensor", "sensors", "iot", "arduino", "max30102", "mpu6050", "hardware", "postpartum", "vital"]):
        return "iot"
    if any(k in combined for k in ["aqi", "pollution", "model", "machine learning", "ml", "hotspot", "dataset", "prediction", "forecast"]):
        return "ml"
    if any(k in combined for k in ["flutter", "firebase", "whatsapp", "chatbot", "mobile", "android", "ios"]):
        return "mobile"
    return "web"

def generate_project_specific_questions(project_obj):
    """
    Generates domain-aware, non-generic interview questions and model answers
    tailored to the actual project technologies and deliverables.
    """
    title = project_obj.get("title", "Featured Technical Project")
    desc_lines = project_obj.get("description", [])
    domain = detect_project_domain(title, desc_lines)
    
    questions = []
    
    if domain == "iot":
        questions.append({
            "project_title": title,
            "question": f"In '{title}', how did you interface the ESP32 with the MAX30102 and MPU6050 sensors over I2C, and what digital filtering did you apply to eliminate motion artifacts from maternal vital readings?",
            "eval_criteria": "Understanding I2C bus communication, clock speeds, noise filtering (moving average or low-pass filter), and optical PPG signal processing.",
            "answer_hints": "Discuss sampling frequency (e.g. 50-100 Hz), handling I2C bus addressing between the sensors, using MPU6050 accelerometer readings to detect patient movement and filter motion noise from the MAX30102 optical PPG sensor, and managing ESP32 power consumption."
        })
        questions.append({
            "project_title": title,
            "question": f"When critical health thresholds (e.g., severe preeclampsia or hemorrhage vitals) are triggered in '{title}', how does the system ensure fail-safe alert transmission through Flask, Firebase, and WhatsApp without message loss?",
            "eval_criteria": "Low-latency event streaming, network disconnect handling, offline sensor buffering, and webhook reliability.",
            "answer_hints": "Explain the fail-safe pipeline: ESP32 local threshold trigger -> local buffer if Wi-Fi drops -> HTTP/MQTT post to Flask API -> immediate real-time sync with Firebase for Flutter UI -> asynchronous dispatch to WhatsApp alert service with retry mechanisms."
        })
        
    elif domain == "ml":
        questions.append({
            "project_title": title,
            "question": f"In '{title}', how did you preprocess the multi-station AQI dataset, handle missing sensor readings across geographic locations, and prevent temporal data leakage during model validation?",
            "eval_criteria": "Time-series validation (TimeSeriesSplit), spatial interpolation, handling sensor outliers, and feature scaling.",
            "answer_hints": "Discuss handling sensor dropout with spatial-temporal interpolation, engineering lagged pollution features and meteorological indicators (humidity, wind speed), and using time-based rolling window validation instead of random train/test splits to avoid data leakage."
        })
        questions.append({
            "project_title": title,
            "question": f"Which machine learning algorithms did you benchmark for identifying pollution hotspots in '{title}', and what evaluation metrics (e.g. RMSE vs Recall for high-risk zones) determined your final choice?",
            "eval_criteria": "Model justification, handling imbalanced high-pollution anomalies, and metric alignment with environmental health risks.",
            "answer_hints": "Explain that severe pollution spikes are rare anomalies where false negatives are dangerous, so optimizing Recall and F1-score is critical over raw accuracy. Describe hyperparameter tuning and feature importance analysis."
        })
        
    elif domain == "mobile":
        questions.append({
            "project_title": title,
            "question": f"In '{title}', how did you structure state management in Flutter, and how did you optimize rendering performance to prevent unnecessary widget tree rebuilds?",
            "eval_criteria": "State management pattern (Provider / Bloc), widget lifecycle, separation of UI and business logic, and 60fps UI smoothness.",
            "answer_hints": "Explain why you chose your state management solution, using const constructors and selector widgets to isolate rebuilds, and offloading heavy JSON/data computations to Dart Isolates."
        })
        questions.append({
            "project_title": title,
            "question": f"How did '{title}' handle network intermittency and offline data synchronization between the mobile app and Firebase/backend?",
            "eval_criteria": "Local caching, offline Firestore persistence, conflict resolution, and optimistic UI updates.",
            "answer_hints": "Describe enabling offline persistence, queuing user actions locally while offline, and synchronizing with the remote cloud database upon reconnection with timestamp-based conflict resolution."
        })
        
    else: # web / general
        questions.append({
            "project_title": title,
            "question": f"In '{title}', how did you design your database schema and indexing strategy to handle concurrent user requests without query degradation?",
            "eval_criteria": "Schema normalization (3NF), B-Tree composite indexing, connection pooling, and ACID transaction isolation.",
            "answer_hints": "Detail foreign key constraints, indexing frequently queried columns, utilizing connection pools, and preventing race conditions using transactions."
        })
        questions.append({
            "project_title": title,
            "question": f"How did you implement secure authentication, input validation, and graceful error handling in '{title}'?",
            "eval_criteria": "JWT lifecycle, CORS configuration, parameterized SQL queries, and defense-in-depth security.",
            "answer_hints": "Explain JWT token expiration and refresh flow, sanitizing inputs to prevent SQL injection and XSS, and centralizing error-handling middleware with appropriate HTTP status codes."
        })

    return questions

def generate_interview_questions(candidate_skills, extracted_projects=None):
    """
    Dynamically generates personalized technical, project, and behavioral interview questions.
    Ensures question diversity and project-specific questions and answer guidelines.
    """
    technical_questions = []
    
    # Priority sorting of skills: match niche/distinctive skills first
    priority_order = [
        "Flutter", "Firebase", "ESP32", "IoT", "Sensors", "Flask", "FastAPI",
        "Machine Learning", "Deep Learning", "NLP", "React", "Node.js", "Docker",
        "Python", "MySQL", "PostgreSQL", "MongoDB", "Git"
    ]
    
    sorted_candidate_skills = sorted(
        candidate_skills,
        key=lambda s: priority_order.index(s) if s in priority_order else 99
    )
    
    # Gather matching questions
    seen_questions = set()
    for skill in sorted_candidate_skills:
        if skill in TECH_QUESTION_BANK:
            available = TECH_QUESTION_BANK[skill]
            for q in available:
                if q["question"] not in seen_questions:
                    technical_questions.append(q)
                    seen_questions.add(q["question"])
                    if len(technical_questions) >= 6:
                        break
        if len(technical_questions) >= 6:
            break

    # Fallback if few technical questions found
    if len(technical_questions) < 3:
        for s in ["Python", "Machine Learning", "MySQL", "Git"]:
            if s in TECH_QUESTION_BANK:
                for q in TECH_QUESTION_BANK[s]:
                    if q["question"] not in seen_questions:
                        technical_questions.append(q)
                        seen_questions.add(q["question"])
                    if len(technical_questions) >= 5:
                        break
            if len(technical_questions) >= 5:
                break

    # Project-specific questions: deeply domain-aware
    project_questions = []
    if extracted_projects:
        for proj in extracted_projects[:2]:
            domain_q = generate_project_specific_questions(proj)
            project_questions.extend(domain_q)
    else:
        project_questions = generate_project_specific_questions({"title": "Featured Technical Project"})

    return {
        "technical_questions": technical_questions[:5],
        "project_questions": project_questions[:3],
        "behavioral_questions": BEHAVIORAL_QUESTIONS
    }
