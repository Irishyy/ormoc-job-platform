<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Seeker Dashboard</title>
    
    <link rel="stylesheet" href="/ormoc-job-platform/assets/css/style.css">
    
    <style>
        /* Structural Layout & Spacing Engine Only */
        body { 
            margin: 0; 
            font-family: sans-serif; 
        }
        
        /* Layout Framework Split */
        .sidebar { 
            width: 260px; 
            height: 100vh; 
            position: fixed; 
            padding: 20px; 
            box-sizing: border-box; 
        }
        
        .main-content { 
            margin-left: 280px; 
            padding: 40px; 
            width: calc(100% - 280px); 
            box-sizing: border-box; 
        }

        /* Profile Area Spacing */
        .profile-container { 
            text-align: center; 
            margin-bottom: 30px; 
        }

        /* Seeker Name Input Row */
        .seeker-name-row {
            display: flex;
            gap: 6px;
            margin-top: 10px;
            justify-content: center;
        }
        .seeker-name-row input {
            width: 140px;
            padding: 6px 8px;
            font-size: 13px;
            box-sizing: border-box;
        }
        .seeker-name-row button {
            padding: 6px 10px;
            font-size: 12px;
            cursor: pointer;
        }

        /* Sidebar Navigation List Spacing */
        .sidebar-menu ul { 
            list-style: none; 
            padding: 0; 
            margin: 0; 
        }
        .sidebar-menu li { 
            margin-bottom: 15px; 
        }
        .sidebar-menu a { 
            text-decoration: none; 
            display: block; 
            padding: 10px; 
        }

        /* Workspace Grid Split for Interactive Map View Feed */
        .seeker-grid { 
            display: grid; 
            grid-template-columns: 1.2fr 0.8fr; 
            gap: 30px; 
            margin-top: 20px; 
        }
        
        /* Application Upload Field Spacing */
        .application-box {
            padding: 20px;
            margin-top: 15px;
        }
        .form-group { 
            margin-bottom: 20px; 
        }
        .form-group label { 
            display: block; 
            margin-bottom: 8px; 
        }

        /* Table Padding Blocks */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        th, td { 
            padding: 12px; 
            text-align: left; 
            border-bottom: 1px solid #ccc; 
        }
    </style>
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

<a href="https://apps.evsu.edu.ph/" target="_blank" rel="noopener noreferrer" class="evsu-top-left-link" aria-label="Open EVSU App">
    <img src="/ormoc-job-platform/EVSU_Official_Logo.png" alt="EVSU Logo" class="evsu-logo" />
</a>

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