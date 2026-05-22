/**
 * Uploads any media file directly to Cloudinary bypassing the PHP backend.
 * @param {File} file - Binary file array data from input fields
 * @param {String} [targetFolder] - Destination directory string name ('company_logos' or 'seeker_resumes')
 * @returns {Promise<String>} - The secure hosted absolute URL string
 */
async function uploadToCloudinary(file, targetFolder = '') {
    var formData = new FormData();
    formData.append("file", file);
    formData.append("upload_preset", CloudinaryEnv.uploadPreset);
    formData.append("folder", targetFolder);

    var res = await axios.post(CloudinaryEnv.uploadUrl, formData);

    if (res.data && res.data.secure_url) {
        return res.data.secure_url;
    }

    return null;
}