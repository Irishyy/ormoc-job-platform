// assets/js/employer.js
// Powers the employer dashboard:
//   - Tab switching
//   - Map for picking a job location
//   - Uploading a company logo
//   - Saving the company name
//   - Posting, viewing, and deleting jobs
//   - Viewing applicants and updating their status

var employerMap     = null;
var selectedMarker  = null;

// ─── Tab Switching ────────────────────────────────────────

function initTabs() {
    var tabs   = document.querySelectorAll(".nav-tab");
    var panels = document.querySelectorAll(".dashboard-panel");

    tabs.forEach(function(tab) {
        tab.addEventListener("click", function(e) {
            e.preventDefault();

            // Deactivate all tabs
            tabs.forEach(function(t) { t.classList.remove("active"); });
            // Hide all panels
            panels.forEach(function(p) { p.style.display = "none"; });

            // Activate the clicked tab and show its panel
            tab.classList.add("active");
            var targetId = tab.getAttribute("data-target");
            document.getElementById(targetId).style.display = "block";

            // Leaflet needs a nudge when its container becomes visible
            if (targetedPanelId === 'panel-overview' && window.employerMap) {
                setTimeout(function() {
                    window.employerMap.invalidateSize();
                }, 100); // Gihatagan og 100ms aron makamata og hapsay ang map container
            }
        });
    });
}


// ─── Map ──────────────────────────────────────────────────

function initMap() {
    employerMap = createBaseOrmocMap("map");
    if (!employerMap) return;

    employerMap.on("click", function(e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;

        // Save coordinates into the hidden form inputs
        document.getElementById("jobLat").value = lat;
        document.getElementById("jobLng").value = lng;

        // Show the picked coordinates on screen
        document.getElementById("coordDisplay").innerText =
            "Lat: " + lat.toFixed(5) + ", Lng: " + lng.toFixed(5);

        // Move the existing marker or place a new one
        if (selectedMarker) {
            selectedMarker.setLatLng(e.latlng);
        } else {
            selectedMarker = L.marker(e.latlng).addTo(employerMap);
        }
    });
}


// ─── Logo Upload ──────────────────────────────────────────

async function handleLogoUpload(event) {
    var file = event.target.files[0];
    if (!file) return;

    // require_once './assets/js/cloudinary-helper.js';

    try {
        var logoUrl = await uploadMediaFile(file, "company_logos");

        await axios.post("../routes/api.php?action=save_employer_profile", {
            company_name:      document.getElementById("companyNameInput").value.trim() || "My Company",
            company_logo_url:  logoUrl
        });

        document.getElementById("companyLogo").src = logoUrl;

    } catch (err) {
        console.error("Logo upload failed:", err);
        alert("Logo upload failed. Please try again.");
    }
}


// ─── Save Company Name ────────────────────────────────────

async function handleSaveCompanyName() {
    var name = document.getElementById("companyNameInput").value.trim();

    if (!name) {
        alert("Please enter a company name.");
        return;
    }

    try {
        var res = await axios.post("../routes/api.php?action=save_employer_profile", {
            company_name:     name,
            company_logo_url: document.getElementById("companyLogo").src
        });

        if (res.data.status === "success") {
            document.getElementById("companyName").innerText = name;
            alert("Company name saved!");
        } else {
            alert("Save failed: " + res.data.message);
        }
    } catch (err) {
        console.error("Save name error:", err);
        alert("Could not save company name.");
    }
}


// ─── Post a Job ───────────────────────────────────────────

async function handleJobSubmit(event) {
    event.preventDefault();

    var lat = document.getElementById("jobLat").value;
    var lng = document.getElementById("jobLng").value;

    if (!lat || !lng) {
        alert("Please click a location on the map first.");
        return;
    }

    try {
        var res = await axios.post("../routes/api.php?action=publish_job", {
            title:       document.getElementById("jobTitle").value,
            description: document.getElementById("jobDesc").value,
            latitude:    lat,
            longitude:   lng
        });

        if (res.data.status === "success") {
            alert("Job posted!");
            document.getElementById("jobForm").reset();
            document.getElementById("coordDisplay").innerText = "No location chosen";

            if (selectedMarker) {
                employerMap.removeLayer(selectedMarker);
                selectedMarker = null;
            }

            loadDashboardData();
        } else {
            alert("Failed to post job: " + res.data.message);
        }
    } catch (err) {
        console.error("Post job error:", err);
        alert("Could not post the job.");
    }
}


// ─── Delete a Job ─────────────────────────────────────────

async function deleteJob(jobId) {
    if (!confirm("Delete this job? This cannot be undone.")) return;

    try {
        var res = await axios.post("../routes/api.php?action=delete_job", { job_id: jobId });

        if (res.data.status === "success") {
            loadDashboardData();
        } else {
            alert("Delete failed: " + res.data.message);
        }
    } catch (err) {
        console.error("Delete job error:", err);
        alert("Could not delete the job.");
    }
}


// ─── Update Applicant Status ──────────────────────────────

async function updateStatus(applicationId, newStatus) {
    try {
        var res = await axios.post("../routes/api.php?action=update_application_status", {
            application_id: applicationId,
            status:  newStatus
        });

        if (res.data.status !== "success") {
            alert("Status update failed: " + res.data.message);
        }
    } catch (err) {
        console.error("Status update error:", err);
    }
}


// ─── Load All Dashboard Data ──────────────────────────────

async function loadDashboardData() {
    await loadJobsTable();
    await loadApplicantsTable();
}

async function loadJobsTable() {
    try {
        var res  = await axios.get("../routes/api.php?action=get_employer_jobs");
        var body = document.getElementById("jobsTableBody");

        if (res.data.status !== "success" || !body) return;

        var jobs = res.data.data;

        if (jobs.length === 0) {
            body.innerHTML = "<tr><td colspan='4' style='text-align:center;'>No jobs posted yet.</td></tr>";
            return;
        }

        body.innerHTML = jobs.map(function(job) {
            return "<tr>" +
                "<td><strong>" + job.title + "</strong></td>" +
                "<td>" + new Date(job.created_at).toLocaleDateString() + "</td>" +
                "<td>Lat: " + parseFloat(job.latitude).toFixed(4) + ", Lng: " + parseFloat(job.longitude).toFixed(4) + "</td>" +
                "<td><button onclick='deleteJob(" + job.id + ")' style='background:#dc3545;color:white;border:none;padding:4px 8px;border-radius:4px;cursor:pointer;'>Delete</button></td>" +
            "</tr>";
        }).join("");

    } catch (err) {
        console.error("Load jobs error:", err);
    }
}

async function loadApplicantsTable() {
    try {
        var res  = await axios.get("../routes/api.php?action=get_employer_applications");
        var body = document.getElementById("applicantsTableBody");

        if (res.data.status !== "success" || !body) return;

        var apps = res.data.data;

        if (apps.length === 0) {
            body.innerHTML = "<tr><td colspan='5' style='text-align:center;'>No applications yet.</td></tr>";
            return;
        }

        body.innerHTML = apps.map(function(app) {
            var statuses = ["pending", "reviewed", "accepted", "rejected"];

            var dropdown = 
            "<select onchange='updateStatus(" + app.application_id + ", this.value)'>" +
                statuses.map(function(s) {
                    return "<option value='" + s + "'" + (app.status === s ? " selected" : "") + ">" + s + "</option>";
                }).join("") +
            "</select>";

            return `
            <tr>
                <td><strong>${app.applicant_name || "Candidate Profile"}</strong></td>
                <td>${app.job_title}</td>
                <td>
                    <a href="${app.resume_url}" target="_blank" style="color:#da291c; font-weight:600; text-decoration:none;">Open Document</a>
                </td>
                <td><span class="status-pill status-${app.status}">${app.status.toUpperCase()}</span></td>
                <td>
                    <select onchange="updateStatus(${app.application_id}, this.value)">
                        ${dropdownOptions}
                    </select>
                </td>
            </tr>
        `;
        }).join("");

    } catch (err) {
        console.error("Load applicants error:", err);
    }
}


// ─── Boot ─────────────────────────────────────────────────

document.addEventListener("DOMContentLoaded", function() {
    initTabs();
    initMap();
    loadDashboardData();

    document.getElementById("jobForm").addEventListener("submit", handleJobSubmit);
    document.getElementById("logoFileInput").addEventListener("change", handleLogoUpload);
    document.getElementById("saveNameBtn").addEventListener("click", handleSaveCompanyName);
});