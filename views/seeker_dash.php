<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Seeker Dashboard</title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="../assets/js/cloudinary-env.js"></script>
    <script src="../assets/js/cloudinary-helper.js"></script>
    <script src="../assets/js/seeker.js"></script>

    <link rel="stylesheet" href="../assets/css/seeker_dash.css">
</head>
<body>

    <div id="sidebar">
        <div id="profile-block">
            <h2 id="seekerName">Loading Seeker...</h2>
            <p>Job Seeker Portal</p>
        </div>
        
        <nav>
            <a href="#explore">Explore Jobs</a>
            <a href="#applications">My Applications</a>
            <a href="#profile">Edit Profile/Skills</a>
        </nav>

        <button id="logoutBtn">Sign Out</button>
    </div>

    <div id="main-content">
        
        <header>
            <h1>Find Local Jobs in Ormoc City</h1>
            <span id="userGreeting">Welcome!</span>
        </header>

        <div id="stats-container">
            <div class="stat-card">
                <p>Submitted Applications</p>
                <h3 id="statTotalApps">0</h3>
            </div>
            <div class="stat-card">
                <p>Interview Invites</p>
                <h3 id="statInterviews">0</h3>
            </div>
        </div>

        <div id="dashboard-grid">
            
            <div id="feed-section">
                <h2>Available Job Openings</h2>
                
                <div id="jobListingsContainer">
                    <p>Loading active job posts around Ormoc...</p>
                </div>
            </div>

            <div id="map-section">
                <h2>Map-Based Job Locator</h2>
                <p>Click a marker on the map to view company info</p>
                
                <div id="map" style="height: 400px; background: #eee;">
                    <p>Loading Interactive Map Layer...</p>
                </div>
            </div>

        </div>

        <div id="application-modal" style="display:none;">
            <h3>Submit Application for: <span id="modalJobTitle">Job Title</span></h3>
            
            <form id="submitApplicationForm">
                <input type="hidden" id="modalJobId" name="job_id">
                
                <div>
                    <label>Upload Resume (PDF/Word)</label>
                    <input type="file" name="resume" id="resumeFile" required>
                </div>

                <button type="submit">Confirm & Send Application</button>
                <button type="button" id="closeModalBtn">Cancel</button>
            </form>
        </div>

    </div>

    <script src="../assets/js/seeker.js"></script>
</body>
</html>