// assets/js/cloudinary-helper.js
// Uploads a file to Cloudinary and returns the URL.
// Used by both the employer (logo upload) and seeker (resume upload) dashboards.

var CLOUDINARY_NAME   = "dxrvbwycv";
var CLOUDINARY_PRESET = "ormoc_job_matching_preset";

// Uploads a file to Cloudinary.
// folder: the subfolder to store it in (e.g. "company_logos", "candidate_resumes")
// Returns a promise that resolves to the file's URL string.
window.uploadMediaFile = async function uploadMediaFile(file, folder) {
    folder = folder || "general";

    var formData = new FormData();
    formData.append("file", file);
    formData.append("upload_preset", CLOUDINARY_PRESET);
    formData.append("folder", folder);

    // Use /raw/upload for documents (PDF, Word, etc.), /image/upload for images.
    // Cloudinary will 404 if you send a PDF through the image endpoint.
    var isDocument = file.type === "application/pdf"
        || file.type === "application/msword"
        || file.type === "application/vnd.openxmlformats-officedocument.wordprocessingml.document";

    var endpoint = isDocument ? "raw" : "image";
    var url = "https://api.cloudinary.com/v1_1/" + CLOUDINARY_NAME + "/" + endpoint + "/upload";
    var response = await axios.post(url, formData);

    if (response.data && response.data.secure_url) {
        return response.data.secure_url;
    }

    throw new Error("Cloudinary did not return a URL.");
}