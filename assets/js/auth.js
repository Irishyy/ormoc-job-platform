// assets/js/auth.js

// =========================================================
// 🔐 GOOGLE SIGN-IN INITIALIZATION
// =========================================================
function initGoogleSignIn() {
    if (!window.google || !google.accounts || !google.accounts.id) {
        setTimeout(initGoogleSignIn, 100);
        return;
    }

    var clientId = document.body.getAttribute("data-google-client-id");

    google.accounts.id.initialize({
        client_id: clientId,
        callback: handleCredentialResponse,
        ux_mode: "popup",
        use_fedcm_for_prompt: false
    });

    google.accounts.id.renderButton(
        document.getElementById("googleSignInButton"),
        { type: "standard", size: "large" }
    );
}

document.addEventListener("DOMContentLoaded", function() {
    initGoogleSignIn();
});

// =========================================================
// 🔧 SAFE RESPONSE PARSER
// Extracts a clean JSON object even if PHP printed warnings
// before the json_encode() output.
// =========================================================
function parseApiResponse(rawData) {
    // If Axios already parsed it as an object, return it directly
    if (rawData && typeof rawData === "object") {
        return rawData;
    }

    // PHP sometimes prepends notices/warnings to the JSON string.
    // Find the first '{' and parse from there.
    if (typeof rawData === "string") {
        var jsonStart = rawData.indexOf('{');
        if (jsonStart !== -1) {
            try {
                return JSON.parse(rawData.substring(jsonStart));
            } catch (e) {
                console.error("JSON parse failed after trimming PHP output:", e);
                console.error("Raw response was:", rawData);
            }
        }
    }

    return null;
}

// =========================================================
// 🌐 GOOGLE OAUTH HANDLER
// =========================================================
async function handleCredentialResponse(response) {
    var selectedRole = document.getElementById("user_role").value;
    var googleToken  = response.credential;

    try {
        var res = await axios.post('/ormoc-job-platform/routes/api.php?action=oauth_login', {
            credential: googleToken,
            role: selectedRole
        });

        var data = parseApiResponse(res.data);

        if (!data) {
            console.error("Unparseable server response:", res.data);
            alert("Server returned an unexpected response. Check the browser console for details.");
            return;
        }

        if (data.status === 'success') {
            if (data.role === 'employer') {
                window.location.href = '/ormoc-job-platform/views/employer_dash.php';
            } else {
                window.location.href = '/ormoc-job-platform/views/seeker_dash.php';
            }
        } else {
            alert(data.message || "Google login failed.");
        }
    } catch (err) {
        console.error("OAuth Error:", err);
        if (err.response) {
            console.error("Server responded with:", err.response.data);
        }
        alert("Google login failed. Please try again.");
    }
}

// =========================================================
// 🔑 MANUAL LOGIN HANDLER
// =========================================================
async function handleManualLogin() {
    var selectedRole   = document.getElementById("user_role").value;
    var emailInput     = document.getElementById("manual_email").value.trim();
    var passwordInput  = document.getElementById("manual_password").value;

    if (!emailInput || !passwordInput) {
        alert("Please enter your email and password.");
        return;
    }

    try {
        var res = await axios.post('/ormoc-job-platform/routes/api.php?action=manual_login', {
            email:    emailInput,
            password: passwordInput,
            role:     selectedRole
        });

        var data = parseApiResponse(res.data);

        if (!data) {
            console.error("Unparseable server response:", res.data);
            alert("Server returned an unexpected response. Check the browser console for details.");
            return;
        }

        if (data.status === 'success') {
            if (data.role === 'employer') {
                window.location.href = '/ormoc-job-platform/views/employer_dash.php';
            } else {
                window.location.href = '/ormoc-job-platform/views/seeker_dash.php';
            }
        } else {
            alert(data.message || "Login failed. Please check your credentials.");
        }
    } catch (err) {
        console.error("Login Error:", err);
        if (err.response) {
            console.error("Server responded with:", err.response.data);
        }
        alert("Login request failed. Please try again.");
    }
}

// =========================================================
// 📝 MANUAL SIGN UP HANDLER
// =========================================================
async function handleManualSignUp() {
    var selectedRole  = document.getElementById("user_role").value;
    var nameInput     = document.getElementById("manual_name").value.trim();
    var emailInput    = document.getElementById("manual_email").value.trim();
    var passwordInput = document.getElementById("manual_password").value;

    if (!nameInput || !emailInput || !passwordInput) {
        alert("Please fill in your name, email, and password to sign up.");
        return;
    }

    try {
        var res = await axios.post('/ormoc-job-platform/routes/api.php?action=manual_signup', {
            name:     nameInput,
            email:    emailInput,
            password: passwordInput,
            role:     selectedRole
        });

        var data = parseApiResponse(res.data);

        if (!data) {
            console.error("Unparseable server response:", res.data);
            alert("Server returned an unexpected response. Check the browser console for details.");
            return;
        }

        if (data.status === 'success') {
            alert(data.message || "Registration successful! You can now log in.");
        } else {
            alert(data.message || "Sign up failed. Please try again.");
        }
    } catch (err) {
        console.error("SignUp Error:", err);
        if (err.response) {
            console.error("Server responded with:", err.response.data);
        }
        alert("Sign up request failed. Please try again.");
    }
}