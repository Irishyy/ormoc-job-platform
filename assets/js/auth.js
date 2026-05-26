// assets/js/auth.js
// Handles everything on the login page:
//   - Google Sign-In button setup
//   - Manual login
//   - Manual sign-up

// ─── Google Sign-In ───────────────────────────────────────

// Wait for the Google library to load, then render the sign-in button.
function initGoogleSignIn() {
    if (!window.google || !google.accounts || !google.accounts.id) {
        setTimeout(initGoogleSignIn, 100);
        return;
    }

    var clientId = document.body.getAttribute("data-google-client-id");

    google.accounts.id.initialize({
        client_id: clientId,
        callback: handleGoogleResponse,
        ux_mode: "popup"
    });

    google.accounts.id.renderButton(
        document.getElementById("googleSignInButton"),
        { type: "standard", size: "large" }
    );
}

// Called by Google after the user picks their account.
async function handleGoogleResponse(response) {
    var role = document.getElementById("user_role").value;

    try {
        var res  = await axios.post("/ormoc-job-platform/routes/api.php?action=oauth_login", {
            credential: response.credential,
            role: role
        });

        var data = res.data;

        if (data.status === "success") {
            goToDashboard(data.role);
        } else {
            alert(data.message || "Google login failed.");
        }
    } catch (err) {
        console.error("Google login error:", err);
        alert("Google login failed. Please try again.");
    }
}


// ─── Manual Login ─────────────────────────────────────────

async function handleManualLogin() {
    var role = document.getElementById("user_role").value;
    var email = document.getElementById("manual_email").value.trim();
    var password = document.getElementById("manual_password").value;

    if (!email || !password) {
        alert("Please enter your email and password.");
        return;
    }

    try {
        var res = await axios.post("/ormoc-job-platform/routes/api.php?action=manual_login", {
            email: email,
            password: password,
            role: role
        });

        var data = res.data;

        if (data.status === "success") {
            goToDashboard(data.role);
        } else {
            alert(data.message || "Login failed. Check your credentials.");
        }
    } catch (err) {
        console.error("Login error:", err);
        alert("Login request failed. Please try again.");
    }
}


// ─── Manual Sign-Up ───────────────────────────────────────

async function handleManualSignUp() {
    var role = document.getElementById("user_role").value;
    var name = document.getElementById("manual_name").value.trim();
    var email    = document.getElementById("manual_email").value.trim();
    var password = document.getElementById("manual_password").value;

    if (!name || !email || !password) {
        alert("Please fill in your name, email, and password.");
        return;
    }

    try {
        var res  = await axios.post("/ormoc-job-platform/routes/api.php?action=manual_signup", {
            name: name,
            email: email,
            password: password,
            role: role
        });

        var data = res.data;

        if (data.status === "success") {
            alert(data.message || "Registration successful! You can now log in.");
        } else {
            alert(data.message || "Sign up failed. Please try again.");
        }
    } catch (err) {
        console.error("Sign up error:", err);
        alert("Sign up request failed. Please try again.");
    }
}


// ─── Helper ───────────────────────────────────────────────

// Redirect to the correct dashboard based on the user's role.
function goToDashboard(role) {
    if (role === "employer") {
        window.location.href = "/ormoc-job-platform/views/employer_dash.php";
    } else {
        window.location.href = "/ormoc-job-platform/views/seeker_dash.php";
    }
}


// ─── Boot ─────────────────────────────────────────────────

document.addEventListener("DOMContentLoaded", function() {
    initGoogleSignIn();
});