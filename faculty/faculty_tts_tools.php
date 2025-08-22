<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'faculty') {
    header("Location: ../auth/login.php");
    exit;
}

$name = $_SESSION['user']['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Text-to-Speech Tools - SmartEdu Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #004080;
            --secondary-color: #0073e6;
            --accent-color: #ffcc00;
            --dark-color: #1e1e2f;
            --light-color: #f8f9fa;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--light-color);
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            height: 100vh;
            background: linear-gradient(180deg, var(--dark-color), #2a2a3c);
            color: white;
            position: fixed;
            width: 250px;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
        }

        .sidebar-header h4 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--accent-color);
        }

        .nav-item {
            padding: 0;
            margin: 5px 0;
        }

        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            text-decoration: none;
        }

        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left-color: var(--accent-color);
        }

        .nav-link i {
            width: 20px;
            margin-right: 10px;
            font-size: 1.1rem;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .welcome-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            animation: slideDown 0.5s ease;
        }

        .welcome-section h3 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 5px;
        }

        .welcome-section p {
            color: #666;
            margin-bottom: 0;
        }

        /* TTS Box Styles */
        .tts-box {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            animation: fadeIn 0.5s ease;
        }

        .form-label {
            color: var(--primary-color);
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-control {
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0,64,128,0.1);
        }

        textarea.form-control {
            resize: none;
            height: 150px;
            font-size: 1rem;
            line-height: 1.5;
        }

        .form-select {
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0,64,128,0.1);
        }

        .form-range {
            height: 8px;
            border-radius: 4px;
            background: #e1e1e1;
        }

        .form-range::-webkit-slider-thumb {
            background: var(--primary-color);
            width: 20px;
            height: 20px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-range::-webkit-slider-thumb:hover {
            transform: scale(1.1);
        }

        /* Button Styles */
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,64,128,0.2);
        }

        .btn-warning {
            background: var(--warning-color);
            border: none;
            color: #000;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,193,7,0.2);
        }

        .btn-success {
            background: var(--success-color);
            border: none;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40,167,69,0.2);
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108,117,125,0.2);
        }

        /* Animations */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }

            .sidebar-header h4 {
                display: none;
            }

            .nav-link span {
                display: none;
            }

            .main-content {
                margin-left: 70px;
            }

            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h4>👨‍🏫 Faculty Panel</h4>
    </div>
    <nav class="nav flex-column">
        <div class="nav-item">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="manage_courses.php" class="nav-link">
                <i class="fas fa-book"></i>
                <span>Manage Courses</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="manage_assignments.php" class="nav-link">
                <i class="fas fa-tasks"></i>
                <span>Assignments</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="students.php" class="nav-link">
                <i class="fas fa-user-graduate"></i>
                <span>Students</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="faculty_tts_tools.php" class="nav-link active">
                <i class="fas fa-microphone"></i>
                <span>TTS Tools</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="manage_quizzes.php" class="nav-link">
                <i class="fas fa-question-circle"></i>
                <span>Quizzes</span>
            </a>
        </div>
        <div class="nav-item mt-auto">
            <a href="../auth/logout.php" class="nav-link text-danger" onclick="return confirm('Are you sure you want to logout?');">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>
        </div>

<div class="main-content">
    <div class="welcome-section">
        <div class="d-flex justify-content-between align-items-center">
            <div>
            <h3>Text-to-Speech Tools 🗣️</h3>
            <p class="text-muted">Convert text to speech, adjust voice settings, and more.</p>
            </div>
        </div>
    </div>

    <div class="tts-box">
        <div class="mb-4">
                    <label for="ttsText" class="form-label">Enter Text</label>
                    <textarea class="form-control" id="ttsText" placeholder="Type something here...">Welcome to the AI Course Recommendation System. This is the faculty TTS dashboard.</textarea>
                </div>

        <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Voice</label>
                        <select id="voiceSelect" class="form-select"></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pitch</label>
                        <input type="range" class="form-range" min="0" max="2" value="1" step="0.1" id="pitchRange">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rate</label>
                        <input type="range" class="form-range" min="0.1" max="2" value="1" step="0.1" id="rateRange">
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-primary" onclick="speakText()">
                <i class="fas fa-play"></i> Speak
            </button>
            <button class="btn btn-warning" onclick="stopSpeech()">
                <i class="fas fa-stop"></i> Stop
            </button>
            <button class="btn btn-success" onclick="readClipboard()">
                <i class="fas fa-clipboard"></i> Read Clipboard
            </button>
            <button class="btn btn-secondary" onclick="highlightAndSpeak()">
                <i class="fas fa-highlighter"></i> Highlight & Read
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const synth = window.speechSynthesis;
    let voices = [];

    function populateVoices() {
        voices = synth.getVoices();
        const voiceSelect = document.getElementById("voiceSelect");
        voiceSelect.innerHTML = "";
        
        voices.forEach((voice, i) => {
            const option = document.createElement("option");
            option.value = i;
            option.textContent = `${voice.name} (${voice.lang})`;

            // Add Urdu and Punjabi voices
            if (voice.lang === 'ur-PK') {
                option.textContent = `Urdu: ${voice.name}`;
            }
            if (voice.lang === 'pa-IN') {
                option.textContent = `Punjabi: ${voice.name}`;
            }

            voiceSelect.appendChild(option);
        });

        // Default voice if no specific match found
        if (voices.length === 0) {
            const option = document.createElement("option");
            option.value = 0;
            option.textContent = 'Default: English';
            voiceSelect.appendChild(option);
        }
    }

    populateVoices();
    if (speechSynthesis.onvoiceschanged !== undefined) {
        speechSynthesis.onvoiceschanged = populateVoices;
    }

    function speakText() {
        stopSpeech();
        const text = document.getElementById("ttsText").value;
        const utterance = new SpeechSynthesisUtterance(text);

        // Select voice based on the selected language
        const selectedVoice = voices[document.getElementById("voiceSelect").value];
        utterance.voice = selectedVoice || voices[0];  // Default voice in case of errors

        // Adjust pitch and rate
        utterance.pitch = parseFloat(document.getElementById("pitchRange").value);
        utterance.rate = parseFloat(document.getElementById("rateRange").value);
        
        // Speak the text
        synth.speak(utterance);
    }

    function stopSpeech() {
        if (synth.speaking) synth.cancel();
    }

    function readClipboard() {
        navigator.clipboard.readText().then(text => {
            document.getElementById("ttsText").value = text;
            speakText();
        }).catch(err => {
            alert("Clipboard access denied or failed.");
        });
    }

    function highlightAndSpeak() {
        const textarea = document.getElementById("ttsText");
        const selectedText = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
        if (selectedText.trim()) {
            const utterance = new SpeechSynthesisUtterance(selectedText);
            utterance.voice = voices[document.getElementById("voiceSelect").value];
            synth.speak(utterance);
        } else {
            speakText();
        }
    }
</script>

</body>
</html>
