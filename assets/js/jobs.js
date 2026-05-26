// assets/js/jobs.js
// Powers the job seeker dashboard:
//   - Map showing all job pins
//   - Clicking a pin to see job details
//   - Submitting an application with a resume upload
//   - Viewing submitted applications

var seekerMap          = null;
var currentJobId       = null;


// ─── Map Setup ────────────────────────────────────────────

function initMap() {
    seekerMap = L.map("map").setView([11.0050, 124.6050], 13);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(seekerMap);

    setTimeout(function() { seekerMap.invalidateSize(); }, 200);
}


// ─── Tab Switching ────────────────────────────────────────

function initTabs() {
    document.getElementById("tab-explore").addEventListener("click", function(e) {
        e.preventDefault();
        document.getElementById("panel-explore-map").style.display  = "block";
        document.getElementById("panel-my-applications").style.display = "none";
        setTimeout(function() { seekerMap.invalidateSize(); }, 50);
    });

    document.getElementById("tab-tracks").addEventListener("click", async function(e) {
        e.preventDefault();
        document.getElementById("panel-explore-map").style.display     = "none";
        document.getElementById("panel-my-applications").style.display = "block";
        await loadMyApplications();
    });
}


// ─── Load All Jobs onto Map ───────────────────────────────

async function loadJobs() {
    try {
        var res  = await axios.get("../routes/api.php?action=get_all_jobs");
        var jobs = res.data.data || [];

        jobs.forEach(function(job) {
            if (!job.latitude || !job.longitude) return;

            var marker = L.marker([parseFloat(job.latitude), parseFloat(job.longitude)]).addTo(seekerMap);

            marker.on("click", function() {
                showJobDetails(job);
            });
        });

    } catch (err) {
        console.error("Load jobs error:", err);
    }
}

// Show a job's details in the side panel when a pin is clicked
function showJobDetails(job) {
    currentJobId = job.id;

    document.getElementById("displayJobTitle").innerText = job.title;
    document.getElementById("displayJobDesc").innerText  = job.description;
    document.getElementById("applyJobId").value          = job.id;
    document.getElementById("activeJobContent").style.display = "block";
}


// ─── Submit an Application ────────────────────────────────

async function handleApplySubmit(event) {
    event.preventDefault();

    var fileInput = document.getElementById("resumeFileInput");
    var file = fileInput.files[0];

    if (!file || !currentJobId) {
        alert("Please select a job pin and attach your resume.");
        return;
    }

    var submitBtn = document.getElementById("submitAppBtn");
    submitBtn.innerText = "Uploading...";
    submitBtn.disabled  = true;

    try {
        var resumeUrl = await uploadMediaFile(file, "candidate_resumes");

        var res = await axios.post("../routes/api.php?action=apply_to_job", {
            job_id:    currentJobId,
            resume_url: resumeUrl
        });

        if (res.data.status === "success") {
            alert("Application submitted!");
            fileInput.value = "";
            document.getElementById("activeJobContent").style.display = "none";
            await loadMyApplications();
        } else {
            alert("Failed: " + res.data.message);
        }
    } catch (err) {
        console.error("Apply error:", err);
        alert("Could not submit your application. Please try again.");
    } finally {
        submitBtn.innerText = "Submit Application";
        submitBtn.disabled  = false;
    }
}


// ─── Load My Applications Table ───────────────────────────

async function loadMyApplications() {
    try {
        var res  = await axios.get("../routes/api.php?action=get_seeker_applications");
        var apps = (res.data && res.data.data) ? res.data.data : [];
        renderApplicationsTable(apps);
    } catch (err) {
        console.error("Load applications error:", err);
        renderApplicationsTable([]);
    }
}

function renderApplicationsTable(apps) {
    var body = document.getElementById("seekerApplicationsTableBody");
    if (!body) return;

    if (apps.length === 0) {
        body.innerHTML = "<tr><td colspan='4' style='text-align:center;'>No applications submitted yet.</td></tr>";
        return;
    }

    body.innerHTML = apps.map(function(app) {
        return "<tr>" +
            "<td>" + (app.company_name || "Unknown Company") + "</td>" +
            "<td>" + (app.job_title    || "Unknown Position") + "</td>" +
            "<td>" + (app.applied_at   || "Just now") + "</td>" +
            "<td><strong>" + (app.status || "pending").toUpperCase() + "</strong></td>" +
        "</tr>";
    }).join("");
}


// ─── Boot ─────────────────────────────────────────────────

document.addEventListener("DOMContentLoaded", async function() {
    initMap();
    initTabs();

    document.getElementById("applyForm").addEventListener("submit", handleApplySubmit);

    await loadJobs();
    await loadMyApplications();
});