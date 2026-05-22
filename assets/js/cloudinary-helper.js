// assets/js/cloudinary-helper.js

// =========================================================
// 🌐 GLOBAL CLOUDINARY CONFIGURATION CONFIG
// =========================================================
window.CLOUDINARY_NAME = "dxrvbwycv";
window.CLOUDINARY_PRESET = "ormoc_job_matching_preset"; // 👈 Your preset goes here!

// =========================================================
// 📸 DIRECT CORE UPLOAD UTILITY ENGINE
// =========================================================
/**
 * Uploads a raw binary file directly to your Cloudinary media bucket.
 * @param {File} fileBytes - The raw file object from the input field
 * @param {string} folderTarget - The directory folder name inside Cloudinary
 * @returns {Promise<string>} - The secure permanent URL string from Cloudinary
 */
async function uploadMediaFile(fileBytes, folderTarget = 'general') {
    // Structural verification check
    if (!window.CLOUDINARY_NAME || window.CLOUDINARY_PRESET === "your_actual_unsigned_preset_here") {
        console.error("Cloudinary Configuration Error: Please provide your actual upload preset name string.");
        throw new Error("Cloudinary setup parameters are incomplete.");
    }

    // Build the Multi-part Form Data payload container
    const formData = new FormData();
    formData.append('file', fileBytes);
    formData.append('upload_preset', window.CLOUDINARY_PRESET);
    formData.append('folder', folderTarget);

    const targetUrl = `https://api.cloudinary.com/v1_1/${window.CLOUDINARY_NAME}/image/upload`;

    // Fire the direct cloud delivery network stream
    const uploadResult = await axios.post(targetUrl, formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    });

    // Return the secure URL path back to the parent execution routine
    if (uploadResult.data && uploadResult.data.secure_url) {
        return uploadResult.data.secure_url;
    } else {
        throw new Error("Invalid response schema packet received from Cloudinary storage cluster.");
    }
}