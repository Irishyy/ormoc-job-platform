<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard</title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>

    <div id="sidebar">
        <div id="profile-block">
          <img id="companyLogo" src="https://via.placeholder.com/150" alt="Company Logo" style="width: 100px; height: 100px; border-radius: 50%;">
          <h2 id="companyName">Loading...</h2>
          <p>Employer Portal</p>
          
          <input type="file" id="logoFileInput" accept="image/*" style="display: none;">
          <button type="button" onclick="document.getElementById('logoFileInput').click()" style="font-size: 11px; margin-top: 5px;">Change Logo</button>
      </div>
        
        <nav>
            <a href="#overview">Dashboard Overview</a>
            <a href="#jobs">Manage Job Listings</a>
            <a href="#applicants">Review Applicants</a>
        </nav>

        <button id="logoutBtn">Sign Out</button>
    </div>

    <div id="main-content">
        
        <header>
            <h1>Ormoc Job Matching Dashboard</h1>
            <span id="userGreeting">Welcome back!</span>
        </header>

        <div id="stats-container">
            <div class="stat-card">
                <p>Active Job Posts</p>
                <h3 id="statActiveJobs">0</h3>
            </div>
            <div class="stat-card">
                <p>Total Applicants</p>
                <h3 id="statApplicants">0</h3>
            </div>
            <div class="stat-card">
                <p>Pending Decisions</p>
                <h3 id="statPending">0</h3>
            </div>
        </div>

        <div id="dashboard-grid">
            
            <div id="form-section">
                <h2>Post a New Job Listing</h2>
                
                <form id="jobPostingForm">
                    <div>
                        <label>Job Title</label>
                        <input type="text" name="title" required placeholder="e.g., Senior PHP Developer">
                    </div>
                    
                    <div>
                        <label>Job Description</label>
                        <textarea name="description" rows="4" required placeholder="Outline core responsibilities..."></textarea>
                    </div>

                    <input type="hidden" id="jobLat" name="latitude">
                    <input type="hidden" id="jobLng" name="longitude">

                    <button type="submit">Publish Job Listing</button>
                </form>
            </div>

            <div id="map-section">
                <h2>Job Location Pinpoint</h2>
                <p>Selected Coordinates: <span id="coordDisplay">No location chosen</span></p>
                
                <div id="map" style="height: 300px; background: #eee;">
                    <p>Loading Interactive Map Layer...</p>
                </div>
            </div>

        </div>

        <div id="table-section">
            <h2>Recent Applications Received</h2>
            
            <table border="1">
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
                    <tr>
                        <td colspan="5">No applications received yet.</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

    <script src="../assets/js/cloudinary-env.js"></script>
    <script src="../assets/js/cloudinary-helper.js"></script>
    <script src="../assets/js/employer.js"></script>
</body>
</html>