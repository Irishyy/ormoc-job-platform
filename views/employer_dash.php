<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard</title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="/ormoc-job-platform/assets/css/style.css">
</head>
<body>

    <aside class="sidebar">
        <div class="profile-upload-container">
            <img id="companyLogo" src="https://placehold.co/120" alt="Logo" onclick="document.getElementById('logoFileInput').click();" />
            <h3 id="companyName" style="margin-top: 10px; font-size: 16px;">Loading Entity...</h3>
            <input type="file" id="logoFileInput" accept="image/*" style="display: none;" />

            <div class="company-name-row">
                <input type="text" id="companyNameInput" placeholder="Company name..." />
                <button id="saveNameBtn">Save</button>
            </div>
        </div>

        <nav class="sidebar-menu">
            <ul>
                <li>
                    <a href="#" class="nav-tab active" data-target="panel-overview">📈 Dashboard Overview</a>
                </li>
                <li>
                    <a href="#" class="nav-tab" data-target="panel-manage-jobs">💼 Manage Job Listings</a>
                </li>
                <li>
                    <a href="#" class="nav-tab" data-target="panel-review-applicants">👥 Review Applicants</a>
                </li>
                <li style="margin-top: 40px; border-top: 1px solid #ccc; padding-top: 15px;">
                    <a href="/ormoc-job-platform/routes/logout.php" style="font-weight: bold;">🚪 Sign Out Account</a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">

        <section id="panel-overview" class="dashboard-panel">
            <h2>📈 Performance Analytics Matrix</h2>
            <hr>
            
            <div class="dashboard-grid">
                <div class="workspace-card">
                    <h3>Post a New Job Listing</h3>
                    <form id="jobForm" onsubmit="event.preventDefault();"> 
                        <!-- naa nay preventDefault daan -->
                        <div class="form-group">
                            <label>Job Title</label>
                            <input id="jobTitle" type="text" name="title" required placeholder="e.g., Senior PHP Developer">
                        </div>
                        
                        <div class="form-group">
                            <label>Job Description</label>
                            <textarea id="jobDesc" name="description" rows="6" required placeholder="Outline core responsibilities..."></textarea>
                        </div>

                        <input type="hidden" id="jobLat" name="latitude">
                        <input type="hidden" id="jobLng" name="longitude">

                        <button type="submit">Publish Job Listing</button>
                    </form>
                </div>

                <div class="workspace-card">
                    <h3>Job Location Pinpoint</h3>
                    <p>Selected Coordinates: <span id="coordDisplay">No location chosen</span></p>
                    
                    <div id="map" style="height: 340px; background: #eee;">
                        <p style="text-align: center; padding-top: 140px;">Loading Interactive Map Layer...</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="panel-manage-jobs" class="dashboard-panel" style="display: none;">
            <h2>💼 Your Published Open Vacancies</h2>
            <hr>
            <table>
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Date Posted</th>
                        <th>Location Pins</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="jobsTableBody">
                    <tr><td colspan="4" style="text-align: center;">Loading active positions...</td></tr>
                </tbody>
            </table>
        </section>

        <section id="panel-review-applicants" class="dashboard-panel" style="display: none;">
            <h2>👥 Received Candidate Profiles</h2>
            <hr>
            <table>
                <thead>
                    <tr>
                        <th>Applicant Name</th>
                        <th>Applied Position</th>
                        <th>Resume Attachment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="applicantsTableBody">
                    <tr><td colspan="5" style="text-align: center;">No applications received yet.</td></tr>
                </tbody>
            </table>
        </section>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script src="../assets/js/cloudinary-helper.js"></script>
    <script src="../assets/js/maps.js"></script>
    <script src="../assets/js/employer.js"></script>
</body>
</html>