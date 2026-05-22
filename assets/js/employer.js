// 📍 Coordinates for Ormoc City Center
const ORMOC_LAT = 11.0044;
const ORMOC_LNG = 124.6075;


let employerMap;
let selectedMarker = null;

function initEmployerMap() {
  // 1. Instantly tap into our global maps.js wrapper asset function!
  employerMap = createBaseOrmocMap('map');
  if (!employerMap) return;

  // 2. Setup the simple map click listener for coordinate pinning
  employerMap.on('click', function(event) {
    const lat = event.latlng.lat;
    const lng = event.latlng.lng;

    // Sync inputs
    document.getElementById('jobLat').value = lat;
    document.getElementById('jobLng').value = lng;
    document.getElementById('coordDisplay').innerText = `Lat: ${lat.toFixed(5)}, Lng: ${lng.toFixed(5)}`;

    if (selectedMarker) {
        selectedMarker.setLatLng(event.latlng);
    } else {
        selectedMarker = L.marker(event.latlng).addTo(employerMap);
    }
  });
}

// =========================================================
// 📸 2. CLOUDINARY LOGO MANAGEMENT HANDLER
// =========================================================
async function handleLogoChange(event) {
  const file = event.target.files[0];
  if (!file) return;

  const nameLabel = document.getElementById('companyName');
  const originalText = nameLabel.innerText;
  nameLabel.innerText = "Uploading Media Asset...";

  try {
    // Run your pre-existing global helper, routing into the logos folder
    const directCloudUrl = await uploadMediaFile(file, 'company_logos');
    
    // Push string to backend profile updater case track
    const response = await axios.post('../routes/api.php?action=save_employer_profile', {
      company_name: originalText === "Loading..." ? "My Company" : originalText,
      company_logo_url: directCloudUrl,
      website: ""
    });

    if (response.data.status === 'success') {
      document.getElementById('companyLogo').src = directCloudUrl;
      nameLabel.innerText = "Profile Image Updated!";
      setTimeout(() => { nameLabel.innerText = originalText; }, 2000);
    } else {
      alert("Database synchronization failure: " + response.data.message);
    }
  } catch (error) {
    console.error("Cloudinary deployment crash:", error);
    alert("Failed to push image to storage cluster.");
    nameLabel.innerText = originalText;
  }
}

// =========================================================
// 🏢 3. CREATE & PUBLISH VACANCY DATA (POST)
// =========================================================
async function handleJobSubmission(event) {
  event.preventDefault();

  const title = document.getElementById('jobTitle').value;
  const description = document.getElementById('jobDesc').value;
  const latitude = document.getElementById('jobLat').value;
  const longitude = document.getElementById('jobLng').value;

  // Safety constraint validation loop check
  if (!latitude || !longitude) {
    alert("Error: You must pinpoint your business address location coordinate on the map layout.");
    return;
  }

  const jobPayload = {
    title: title,
    description: description,
    latitude: latitude,
    longitude: longitude
  };

  try {
    const response = await axios.post('../routes/api.php?action=publish_job', jobPayload);

    if (response.data.status === 'success') {
      alert("Success: Vacancy active on map views!");
      document.getElementById('jobForm').reset();
      if (selectedMarker && employerMap) {
        employerMap.removeLayer(selectedMarker);
        selectedMarker = null;
      }
      document.getElementById('coordDisplay').innerText = "No location chosen";
      // Refresh local lookups data tables asynchronously
      loadEmployerDashboardData();
    } else {
        alert("Publishing rejected: " + response.data.message);
    }
  } catch (error) {
      console.error("Network interface fault:", error);
      alert("Could not push job metadata array to endpoint system.");
  }
}

// assets/js/employer.js

// 📥 FETCH AND REFRESH ALL DASHBOARD METRICS (GET)
async function loadEmployerDashboardData() {
  try {
    // --- 1. FETCH & RENDER ACTIVE JOB POSTS ---
    const jobsResponse = await axios.get('../routes/api.php?action=get_employer_jobs');
    const jobsTableBody = document.getElementById('jobsTableBody'); // Make sure this matches your HTML ID

    if (jobsResponse.data.status === 'success' && jobsTableBody) {
      jobsTableBody.innerHTML = ""; // Clear old rows or loading text
      
      if (jobsResponse.data.data.length === 0) {
        jobsTableBody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:#888;">No active vacancies published yet.</td></tr>`;
      } else {
        jobsResponse.data.data.forEach(job => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td><strong>${job.title}</strong></td>
            <td>${new Date(job.created_at).toLocaleDateString()}</td>
            <td>Lat: ${parseFloat(job.latitude).toFixed(4)}, Lng: ${parseFloat(job.longitude).toFixed(4)}</td>
            <td>
              <button onclick="deleteJobListing(${job.id})" style="background:#dc3545; color:white; border:none; padding:4px 8px; border-radius:4px; cursor:pointer; font-size:11px;">Delete</button>
            </td>
          `;
          jobsTableBody.appendChild(tr);
        });
      }
    }

    // --- 2. FETCH & RENDER INCOMING APPLICATIONS ---
    const appResponse = await axios.get('../routes/api.php?action=get_employer_applications');
    const applicantsTableBody = document.getElementById('applicantsTableBody');

    if (appResponse.data.status === 'success' && applicantsTableBody) {
        applicantsTableBody.innerHTML = ""; // Clear old rows
        
      if (appResponse.data.data.length === 0) {
          applicantsTableBody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:#888;">No applications received yet.</td></tr>`;
      } else {
        appResponse.data.data.forEach(app => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
              <td><strong>${app.applicant_name}</strong></td>
              <td>${app.job_title}</td>
              <td><a href="${app.resume_url}" target="_blank" style="color: blue; text-decoration: underline;">Open Document</a></td>
              <td><span class="status-pill status-${app.status}">${app.status.toUpperCase()}</span></td>
              <td>
                  <button onclick="updateStatus(${app.application_id}, 'accepted')" style="background:green; color:white; font-size:11px; border:none; padding:2px 6px; cursor:pointer; margin-right:2px;">Accept</button>
                  <button onclick="updateStatus(${app.application_id}, 'rejected')" style="background:red; color:white; font-size:11px; border:none; padding:2px 6px; cursor:pointer;">Reject</button>
              </td>`;
            applicantsTableBody.appendChild(tr);
        });
      }
    }
  } catch (error) {
    console.error("Error loading dashboard data streams:", error);
  }
}

// Action utility case track to change application tracking state values
async function updateStatus(applicationId, newStatus) {
  try {
    const response = await axios.post('../routes/api.php?action=update_application_status', {
      application_id: applicationId,
      status: newStatus
    });
    if (response.data.status === 'success') {
      loadEmployerDashboardData(); // Reload structural view tables instantly
    }
  } catch (err) {
    console.error(err);
  }
}

// =========================================================
// 🚀 ENGINE LAUNCH EVENT INITIALIZATION LOOP
// =========================================================
document.addEventListener("DOMContentLoaded", function() {
  initEmployerMap();
  loadEmployerDashboardData();

  // Dynamically wire up forms and inputs safely without breaking old templates
  document.getElementById('jobForm')?.addEventListener('submit', handleJobSubmission);
  
  // Create click event hook shortcut to listen for profile image alterations
  const fileInput = document.getElementById('logoFileInput');
  if (fileInput) {
      fileInput.addEventListener('change', handleLogoChange);
  }
});

/**
 * Sends an asynchronous request to remove a job listing after confirmation.
 * @param {number} jobId - The auto-incremented primary key ID of the job listing
 */
async function deleteJobListing(jobId) {
    // Show a clean native browser verification box to prevent accidents
    const userConfirmed = confirm("Are you absolute sure you want to permanently delete this job post? This action cannot be undone.");
    if (!userConfirmed) return;

    try {
        const response = await axios.post('../routes/api.php?action=delete_job', {
            job_id: jobId
        });

        if (response.data.status === 'success') {
            alert("Vacancy deleted successfully.");
            // Refresh both tables seamlessly without page refresh flicker!
            loadEmployerDashboardData();
        } else {
            alert("Failed to delete entry: " + response.data.message);
        }
    } catch (error) {
        console.error("Deletion pipeline failure:", error);
        alert("Could not process record destruction request.");
    }
}