<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Seeker Dashboard</title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="../assets/css/seeker_dash.css">
</head>
<body>

    <aside class="sidebar">
        <div class="profile-container">
            <h3 id="seekerName">Loading Candidate...</h3>
            <p style="font-size: 12px;">Job Seeker Account</p>

            <div class="seeker-name-row">
                <input type="text" id="seekerNameInput" placeholder="Your display name..." />
                <button id="saveNameBtn">Save</button>
            </div>
        </div>

        <nav class="sidebar-menu">
            <ul>
                <li>
                    <a href="#" id="tab-explore" class="nav-tab active" data-target="panel-explore-map">🗺️ Explore Job Map</a>
                </li>
                <li>
                    <a href="#" id="tab-tracks" class="nav-tab">📄 Track Applications</a>
                </li>
                <li style="margin-top: 40px; border-top: 1px solid #ccc; padding-top: 15px;">
                    <a href="/ormoc-job-platform/routes/logout.php" style="font-weight: bold;">🚪 Sign Out Account</a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">

        <section id="panel-explore-map" class="dashboard-panel">
            <h2>🗺️ Live Interactive Vacancy Feed</h2>
            <hr>
            
            <div class="seeker-grid">
                <div>
                    <div id="map" style="height: 500px; background: #eee;">
                        <p style="text-align: center; padding-top: 220px;">Loading Active Vacancies Map Layer...</p>
                    </div>
                </div>

                <div id="jobDetailsPane" class="workspace-card">
                    <h3>Select a Job Pin</h3>
                    <p>Click on any job pin marker located around the Ormoc city layout to pull up position metrics and credentials criteria instantly.</p>
                    
                    <div id="activeJobContent" style="display: none; margin-top: 20px;">
                        <h4 id="displayJobTitle" style="margin: 0 0 10px 0;"></h4>
                        <p id="displayJobDesc" style="font-size: 14px; line-height: 1.4;"></p>
                        
                        <div class="application-box">
                            <h5>Apply for this Position</h5>
                            <form id="applyForm" onsubmit="event.preventDefault();">
                                <input type="hidden" id="applyJobId">
                                <div class="form-group">
                                    <label>Upload Evaluation Resume (PDF/Word)</label>
                                    <input type="file" id="resumeFileInput" accept=".pdf,.doc,.docx" required>
                                </div>
                                <button type="submit" id="submitAppBtn" style="width:100%; padding:10px; cursor:pointer;">Submit Application Packet</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="panel-my-applications" class="dashboard-panel" style="display: none;">
            <h2>📄 Your Submitted Applications Status</h2>
            <hr>
            <table>
                <thead>
                    <tr>
                        <th>Company/Entity Name</th>
                        <th>Target Position</th>
                        <th>Date Sent</th>
                        <th>Evaluation Status</th>
                    </tr>
                </thead>
                <tbody id="seekerApplicationsTableBody">
                    <tr><td colspan="4" style="text-align: center;">Loading historical tracking lines...</td></tr>
                </tbody>
            </table>
        </section>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script src="/ormoc-job-platform/assets/js/cloudinary-helper.js"></script>
    <script src="/ormoc-job-platform/assets/js/maps.js"></script>
    <script src="/ormoc-job-platform/assets/js/jobs.js"></script>
</body>
</html>