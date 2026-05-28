var CLOUDINARY_NAME   = "dxrvbwycv";
var CLOUDINARY_PRESET = "ormoc_job_matching_preset";

window.uploadMediaFile = async function uploadMediaFile(file, folder) {
    folder = folder || "general";

    var formData = new FormData();
    formData.append("file", file);
    formData.append("upload_preset", CLOUDINARY_PRESET);
    formData.append("folder", folder);

    var url      = "https://api.cloudinary.com/v1_1/" + CLOUDINARY_NAME + "/auto/upload";
    var response = await axios.post(url, formData);

    if (response.data && response.data.secure_url) {
        return response.data.secure_url;
    }

    throw new Error("Cloudinary did not return a URL.");
}