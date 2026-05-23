// assets/js/jobs.js

let seekerMap;
let currentSelectedJobId = null;

// =========================================================
// 🚀 DOM ELEMENT CACHE LAYER
// =========================================================
const mapContainer       = document.getElementById('map');
const seekerNameEl       = document.getElementById('seekerName');
const seekerNameInput    = document.getElementById('seekerNameInput');
const saveNameBtn        = document.getElementById('saveNameBtn');
const tabExploreLink     = document.getElementById('tab-explore');
const tabTracksLink      = document.getElementById('tab-tracks');
const panelExploreMap    = document.getElementById('panel-explore-map');
const panelMyApplications = document.getElementById('panel-my-applications');
const activeJobContent   = document.getElementById('activeJobContent');
const displayJobTitle    = document.getElementById('displayJobTitle');
const displayJobDesc     = document.getElementById('displayJobDesc');
const applyJobIdInput    = document.getElementById('applyJobId');
const applyForm          = document.getElementById('applyForm');
const resumeFileInput    = document.getElementById('resumeFileInput');
const submitAppBtn       = document.getElementById('submitAppBtn');
const appTableBody       = document.getElementById('seekerApplicationsTableBody');

// =========================================================
// 🗺️ MAP INITIALIZATION
// =========================================================
if (mapContainer) {
    seekerMap = L.map('map').setView([11.0050, 124.6050], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(seekerMap);

    // Force Leaflet to recalculate map container size
    setTimeout(function() {
        seekerMap.invalidateSize();
    }, 200);
}

// =========================================================
// 🗂️ TAB SINGLE-PAGE SWITCHING ROUTINES
// =========================================================
if (tabExploreLink) {
    tabExploreLink.onclick = function(e) {
        e.preventDefault();
        if (panelExploreMap) {
            panelExploreMap.style.display = "block";
        }
        if (panelMyApplications) {
            panelMyApplications.style.display = "none";
        }
        if (seekerMap) {
            setTimeout(function() {
                seekerMap.invalidateSize();
            }, 50);
        }
    };
}

if (tabTracksLink) {
    tabTracksLink.onclick = async function(e) {
        e.preventDefault();
        if (panelExploreMap) {
            panelExploreMap.style.display = "none";
        }
        if (panelMyApplications) {
            panelMyApplications.style.display = "block";
        }

        try {
            var appResponse = await axios.get('../routes/api.php?action=get_seeker_applications');
            if (appResponse.data && appResponse.data.data) {
                renderApplicationsTable(appResponse.data.data);
            } else {
                renderApplicationsTable([]);
            }
        } catch (err) {
            console.log("Failed to refresh application tracking lines:", err);
        }
    };
}

// =========================================================
// 💾 SAVE SEEKER NAME HANDLER
// =========================================================
if (saveNameBtn) {
    saveNameBtn.onclick = function() {
        if (!seekerNameInput) return;
        var newName = seekerNameInput.value.trim();
        if (!newName) {
            alert("Please enter a valid name.");
            return;
        }
        if (seekerNameEl) {
            seekerNameEl.innerText = newName;
        }
        alert("Display name updated locally!");
    };
}

// =========================================================
// 📥 DATA FETCH ENGINE
// =========================================================
async function loadSeekerDashboardData() {
    if (seekerNameEl) {
        seekerNameEl.innerText = "Active Candidate";
    }

    try {
        // 1. Fetch all job listings
        var jobsResponse = await axios.get('../routes/api.php?action=get_all_jobs');
        var jobsList = [];

        if (jobsResponse.data && jobsResponse.data.data) {
            jobsList = jobsResponse.data.data;
        }

        // Plot map pin markers manually
        for (var i = 0; i < jobsList.length; i++) {
            var job = jobsList[i];

            if (job.latitude && job.longitude && seekerMap) {
                var lat = parseFloat(job.latitude);
                var lng = parseFloat(job.longitude);
                var marker = L.marker([lat, lng]).addTo(seekerMap);

                // Wrap in IIFE to preserve job reference inside async loop
                (function(capturedJob) {
                    marker.on('click', function() {
                        currentSelectedJobId = capturedJob.id;

                        if (displayJobTitle) {
                            displayJobTitle.innerText = capturedJob.title;
                        }
                        if (displayJobDesc) {
                            displayJobDesc.innerText = capturedJob.description;
                        }
                        if (applyJobIdInput) {
                            applyJobIdInput.value = capturedJob.id;
                        }
                        if (activeJobContent) {
                            activeJobContent.style.display = "block";
                        }
                    });
                })(job);
            }
        }

        // 2. Fetch and render seeker's own applications
        var appResponse = await axios.get('../routes/api.php?action=get_seeker_applications');
        if (appResponse.data && appResponse.data.data) {
            renderApplicationsTable(appResponse.data.data);
        } else {
            renderApplicationsTable([]);
        }

    } catch (err) {
        console.log("Data loading failed:", err);
        renderApplicationsTable([]);
    }
}

// =========================================================
// 📤 APPLY FORM SUBMISSION ENGINE
// =========================================================
if (applyForm) {
    applyForm.onsubmit = async function(e) {
        e.preventDefault();

        if (!resumeFileInput) return;
        var targetFile = resumeFileInput.files[0];

        if (!targetFile || !currentSelectedJobId) {
            alert("Please select a job and attach your resume.");
            return;
        }

        if (submitAppBtn) {
            submitAppBtn.innerText = "Uploading resume...";
        }

        try {
            // Upload resume file to Cloudinary storage
            var secureResumeUrl = await uploadMediaFile(targetFile, 'candidate_resumes');

            var payload = {
                job_id: currentSelectedJobId,
                resume_url: secureResumeUrl
            };

            var response = await axios.post('../routes/api.php?action=apply_to_job', payload);

            if (response.data.status === 'success') {
                alert("Application transmitted successfully!");
                resumeFileInput.value = "";

                if (activeJobContent) {
                    activeJobContent.style.display = "none";
                }

                // Reload seeker's application list
                var reloadResponse = await axios.get('../routes/api.php?action=get_seeker_applications');
                if (reloadResponse.data && reloadResponse.data.data) {
                    renderApplicationsTable(reloadResponse.data.data);
                } else {
                    renderApplicationsTable([]);
                }
            } else {
                alert("Failed: " + response.data.message);
            }

        } catch (err) {
            console.log("Transmission error:", err);
            alert("Could not submit application. Please try again.");
        } finally {
            if (submitAppBtn) {
                submitAppBtn.innerText = "Submit Application Packet";
            }
        }
    };
}

// =========================================================
// 🧱 TABLE RENDER ENGINE
// =========================================================
function renderApplicationsTable(applications) {
    if (!appTableBody) return;

    if (!applications || applications.length === 0) {
        appTableBody.innerHTML = "<tr><td colspan='4' style='text-align: center;'>No applications submitted yet.</td></tr>";
        return;
    }

    var rowsHtml = "";
    for (var i = 0; i < applications.length; i++) {
        var app = applications[i];

        var companyDisplay = app.company_name ? app.company_name : 'Corporate Entity';
        var titleDisplay   = app.title ? app.title : (app.job_title ? app.job_title : 'Unknown Position');
        var dateDisplay    = app.applied_at ? app.applied_at : 'Just Now';
        var statusDisplay  = app.status ? app.status.toUpperCase() : 'PENDING';

        rowsHtml += "<tr>" +
            "<td>" + companyDisplay + "</td>" +
            "<td>" + titleDisplay + "</td>" +
            "<td>" + dateDisplay + "</td>" +
            "<td><strong>" + statusDisplay + "</strong></td>" +
        "</tr>";
    }

    appTableBody.innerHTML = rowsHtml;
}

// =========================================================
// 🚀 BOOT ON PAGE LOAD
// =========================================================
loadSeekerDashboardData();