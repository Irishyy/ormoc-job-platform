// Cloudinary settings for unsigned browser uploads (upload preset only).
// Do NOT put api_secret here. Keep secrets in config/DatabaseConnection.php for PHP curl.

const CloudinaryEnv = {
  cloudName: "dxrvbwycv",
  uploadPreset: "ormoc_job_matching_preset",
  uploadUrl: "https://api.cloudinary.com/v1_1/dxrvbwycv/image/upload"
};
