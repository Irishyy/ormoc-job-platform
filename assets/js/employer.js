// assets/js/employer.js

// =========================================================
// 🗂️ TAB SINGLE-PAGE SWITCHING ROUTINES (GI-AYO PARA SA CSS)
// =========================================================
function initializeTabSwitchingEngine() {
    var tabLinks     = document.querySelectorAll('.nav-tab');
    var displayPanels = document.querySelectorAll('.dashboard-panel');

    tabLinks.forEach(function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault();

            // 1. Tangtangon ang 'active' class sa tanang tabs (Gikuha ang JS inline colors)
            tabLinks.forEach(function(t) {
                t.classList.remove('active');
            });

            // 2. I-add ang 'active' class sa tab nga gipili aron mosalmot sa style.css
            this.classList.add('active');

            // 3. I-switch ang visibility sa mga panels
            var targetedPanelId = this.getAttribute('data-target');

            displayPanels.forEach(function(panel) {
                if (panel.id === targetedPanelId) {
                    panel.style.display = "block";
                } else {
                    panel.style.display = "none";
                }
            });

            // 🔥 FIX SA SIZING BUG (#2): Re-calibrate sa Leaflet Map container sa sakto nga timing
            if (targetedPanelId === 'panel-overview' && window.employerMap) {
                setTimeout(function() {
                    window.employerMap.invalidateSize();
                }, 100); // Gihatagan og 100ms aron makamata og hapsay ang map container
            }
        });
    });
}

// =========================================================
// 🗺️ MAP WORKSPACE INITIALIZATION
// =========================================================
var employerMap;
var selectedMarker = null;

function initEmployerMap() {
    employerMap = createBaseOrmocMap('map');
    if (!employerMap) return;

    window.employerMap = employerMap;

    employerMap.on('click', function(event) {
        var lat = event.latlng.lat;
        var lng = event.latlng.lng;

        var jobLatInput  = document.getElementById('jobLat');
        var jobLngInput  = document.getElementById('jobLng');
        var coordDisplay = document.getElementById('coordDisplay');

        if (jobLatInput)  jobLatInput.value  = lat;
        if (jobLngInput)  jobLngInput.value  = lng;
        if (coordDisplay) coordDisplay.innerText = 'Lat: ' + lat.toFixed(5) + ', Lng: ' + lng.toFixed(5);

        if (selectedMarker) {
            selectedMarker.setLatLng(event.latlng);
        } else {
            selectedMarker = L.marker(event.latlng).addTo(employerMap);
        }
    });
}

// =========================================================
// 📸 CLOUDINARY LOGO UPLOAD HANDLER
// =========================================================
async function handleLogoChange(event) {
    var file = event.target.files[0];
    if (!file) return;

    var nameLabel    = document.getElementById('companyName');
    var companyInput = document.getElementById('companyNameInput');
    var originalText = nameLabel ? nameLabel.innerText : "My Company";

    if (nameLabel) nameLabel.innerText = "Uploading Media Asset...";

    try {
        var directCloudUrl = await uploadMediaFile(file, 'company_logos');

        var currentName = companyInput ? companyInput.value.trim() : originalText;
        if (!currentName || currentName === "Loading...") {
            currentName = originalText !== "Loading..." ? originalText : "My Company";
        }

        var response = await axios.post('../routes/api.php?action=save_employer_profile', {
            company_name: currentName,
            company_logo_url: directCloudUrl,
            website: ""
        });

        if (response.data.status === 'success') {
            var companyLogo = document.getElementById('companyLogo');
            if (companyLogo) companyLogo.src = directCloudUrl;
            if (nameLabel) nameLabel.innerText = "Profile Image Updated!";
            setTimeout(function() {
                if (nameLabel) nameLabel.innerText = currentName;
            }, 2000);
        } else {
            alert("Database synchronization failure: " + response.data.message);
            if (nameLabel) nameLabel.innerText = originalText;
        }
    } catch (error) {
        console.error("Cloudinary deployment crash:", error);
        alert("Failed to push image to storage cluster.");
        if (nameLabel) nameLabel.innerText = originalText;
    }
}

// =========================================================
// 💾 SAVE COMPANY NAME HANDLER
// =========================================================
async function handleSaveCompanyName() {
    var companyInput = document.getElementById('companyNameInput');
    var companyLogo  = document.getElementById('companyLogo');
    var nameLabel    = document.getElementById('companyName');

    if (!companyInput) return;

    var newName = companyInput.value.trim();
    if (!newName) {
        alert("Please enter a valid company name.");
        return;
    }

    var currentLogoUrl = companyLogo ? companyLogo.src : "";

    try {
        var response = await axios.post('../routes/api.php?action=save_employer_profile', {
            company_name: newName,
            company_logo_url: currentLogoUrl,
            website: ""
        });

        if (response.data.status === 'success') {
            if (nameLabel) nameLabel.innerText = newName;
            alert("Company name saved successfully!");
        } else {
            alert("Save failed: " + response.data.message);
        }
    } catch (error) {
        console.error("Save company name error:", error);
        alert("Could not save company name.");
    }
}

// =========================================================
// 🏢 PUBLISH JOB VACANCY HANDLER (POST)
// =========================================================
async function handleJobSubmission(event) {
    event.preventDefault();

    var jobTitleEl = document.getElementById('jobTitle');
    var jobDescEl  = document.getElementById('jobDesc');
    var jobLatEl   = document.getElementById('jobLat');
    var jobLngEl   = document.getElementById('jobLng');

    var title       = jobTitleEl   ? jobTitleEl.value   : '';
    var description = jobDescEl    ? jobDescEl.value    : '';
    var latitude    = jobLatEl     ? jobLatEl.value     : '';
    var longitude   = jobLngEl     ? jobLngEl.value     : '';

    if (!latitude || !longitude) {
        alert("Error: You must pinpoint your business address location on the map.");
        return;
    }

    try {
        var response = await axios.post('../routes/api.php?action=publish_job', {
            title:       title,
            description: description,
            latitude:    latitude,
            longitude:   longitude
        });

        if (response.data.status === 'success') {
            alert("Success: Vacancy active on map views!");

            var jobForm = document.getElementById('jobForm');
            if (jobForm) jobForm.reset();

            if (selectedMarker && employerMap) {
                employerMap.removeLayer(selectedMarker);
                selectedMarker = null;
            }

            var coordDisplay = document.getElementById('coordDisplay');
            if (coordDisplay) coordDisplay.innerText = "No location chosen";

            loadEmployerDashboardData();
        } else {
            alert("Publishing rejected: " + response.data.message);
        }
    } catch (error) {
        console.error("Network interface fault:", error);
        alert("Could not push job metadata to endpoint.");
    }
}

// =========================================================
// 📥 LOAD ALL DASHBOARD METRICS (GET)
// =========================================================
async function loadEmployerDashboardData() {
    try {
        // --- 1. JOBS TABLE ---
        var jobsResponse = await axios.get('../routes/api.php?action=get_employer_jobs');
        var jobsTableBody = document.getElementById('jobsTableBody');

        if (jobsResponse.data.status === 'success' && jobsTableBody) {
            jobsTableBody.innerHTML = "";

            if (jobsResponse.data.data.length === 0) {
                jobsTableBody.innerHTML = "<tr><td colspan='4' style='text-align:center; color:#888;'>No active vacancies published yet.</td></tr>";
            } else {
                jobsResponse.data.data.forEach(function(job) {
                    var tr = document.createElement('tr');
                    tr.innerHTML =
                        "<td><strong>" + job.title + "</strong></td>" +
                        "<td>" + new Date(job.created_at).toLocaleDateString() + "</td>" +
                        "<td>Lat: " + parseFloat(job.latitude).toFixed(4) + ", Lng: " + parseFloat(job.longitude).toFixed(4) + "</td>" +
                        "<td>" +
                            "<button onclick=\"deleteJobListing(" + job.id + ")\" style=\"background:#dc3545; color:white; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-size:12px; font-weight:600;\">Delete</button>" +
                        "</td>";
                    jobsTableBody.appendChild(tr);
                });
            }
        }

        // --- 2. APPLICANTS TABLE ---
        var appResponse = await axios.get('../routes/api.php?action=get_employer_applications');
        var applicantsTableBody = document.getElementById('applicantsTableBody');

        if (appResponse.data.status === 'success' && applicantsTableBody) {
            applicantsTableBody.innerHTML = "";

            if (appResponse.data.data.length === 0) {
                applicantsTableBody.innerHTML = "<tr><td colspan='5' style='text-align:center; color:#888;'>No applications received yet.</td></tr>";
            } else {
                appResponse.data.data.forEach(function(app) {
                    var tr = document.createElement('tr');

                    var selectHtml =
                        "<select onchange=\"updateStatus(" + app.application_id + ", this.value)\">" +
                            "<option value='pending'"     + (app.status === 'pending'     ? ' selected' : '') + ">⏳ Pending</option>" +
                            "<option value='reviewed'"    + (app.status === 'reviewed'    ? ' selected' : '') + ">📋 Reviewed</option>" +
                            "<option value='accepted'"    + (app.status === 'accepted'    ? ' selected' : '') + ">✅ Accepted</option>" +
                            "<option value='rejected'"    + (app.status === 'rejected'    ? ' selected' : '') + ">❌ Rejected</option>" +
                        "</select>";

                    tr.innerHTML =
                        "<td><strong>" + (app.applicant_name ? app.applicant_name : 'Candidate Profile') + "</strong></td>" +
                        "<td>" + app.job_title + "</td>" +
                        "<td><a href='" + app.resume_url + "' target='_blank' style='color:#da291c; font-weight:600; text-decoration:none;'>Open Document</a></td>" +
                        "<td><span class='status-pill status-" + app.status + "'>" + app.status.toUpperCase() + "</span></td>" +
                        "<td>" + selectHtml + "</td>";

                    applicantsTableBody.appendChild(tr);
                });
            }
        }
    } catch (error) {
        console.error("Error loading dashboard data streams:", error);
    }
}

// =========================================================
// 🔄 UPDATE APPLICATION STATUS (POST)
// =========================================================
async function updateStatus(applicationId, newStatus) {
    try {
        var response = await axios.post('../routes/api.php?action=update_application_status', {
            application_id: applicationId,
            status: newStatus
        });
        if (response.data.status === 'success') {
            loadEmployerDashboardData();
        } else {
            alert("Failed to save tracking change: " + response.data.message);
        }
    } catch (err) {
        console.error("Status update error:", err);
    }
}

// =========================================================
// 🗑️ DELETE JOB LISTING (POST)
// =========================================================
async function deleteJobListing(jobId) {
    var userConfirmed = confirm("Are you sure you want to permanently delete this job post? This cannot be undone.");
    if (!userConfirmed) return;

    try {
        var response = await axios.post('../routes/api.php?action=delete_job', {
            job_id: jobId
        });

        if (response.data.status === 'success') {
            alert("Vacancy deleted successfully.");
            loadEmployerDashboardData();
        } else {
            alert("Failed to delete entry: " + response.data.message);
        }
    } catch (error) {
        console.error("Deletion pipeline failure:", error);
        alert("Could not process record deletion.");
    }
}

// =========================================================
// 🚀 ENGINE LAUNCH ON DOM READY
// =========================================================
document.addEventListener("DOMContentLoaded", function() {
    initializeTabSwitchingEngine();
    initEmployerMap();
    loadEmployerDashboardData();

    var jobForm = document.getElementById('jobForm');
    if (jobForm) {
        jobForm.addEventListener('submit', handleJobSubmission);
    }

    var fileInput = document.getElementById('logoFileInput');
    if (fileInput) {
        fileInput.addEventListener('change', handleLogoChange);
    }

    var saveNameBtn = document.getElementById('saveNameBtn');
    if (saveNameBtn) {
        saveNameBtn.addEventListener('click', handleSaveCompanyName);
    }
});