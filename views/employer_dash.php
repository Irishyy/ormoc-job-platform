<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard</title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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

        /* Profile Block Spacing */
        .profile-upload-container { 
            text-align: center; 
            margin-bottom: 30px; 
        }
        .profile-upload-container img { 
            width: 100px; 
            height: 100px; 
            border-radius: 50%; 
            object-fit: cover; 
            cursor: pointer; 
        }

        /* Company Name Input Row */
        .company-name-row {
            display: flex;
            gap: 6px;
            margin-top: 10px;
            justify-content: center;
        }
        .company-name-row input {
            width: 140px;
            padding: 6px 8px;
            font-size: 13px;
            box-sizing: border-box;
        }
        .company-name-row button {
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

        /* Workspace Grid Split for Overview */
        .dashboard-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 30px; 
            margin-top: 20px; 
        }
        
        /* Form Field Element Spacing */
        .form-group { 
            margin-bottom: 20px; 
        }
        .form-group label { 
            display: block; 
            margin-bottom: 8px; 
        }
        .form-group input, 
        .form-group textarea { 
            width: 100%; 
            padding: 10px; 
            box-sizing: border-box; 
        }
        
        button[type="submit"] { 
            width: 100%; 
            padding: 12px; 
            cursor: pointer; 
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

    <a href="https://apps.evsu.edu.ph/" target="_blank" rel="noopener noreferrer" class="evsu-top-left-link" aria-label="Open EVSU App">
    <img src="/ormoc-job-platform/EVSU_Official_Logo.png" alt="EVSU Logo" class="evsu-logo" />
    </a>

    <main class="main-content">

        <section id="panel-overview" class="dashboard-panel">
            <h2>📈 Performance Analytics Matrix</h2>
            <hr>
            
            <div class="dashboard-grid">
                <div class="workspace-card">
                    <h3>Post a New Job Listing</h3>
                    <form id="jobForm" onsubmit="event.preventDefault();">
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