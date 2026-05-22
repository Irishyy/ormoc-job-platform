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

document.addEventListener("DOMContentLoaded", function () {
  initGoogleSignIn();
});

async function handleCredentialResponse(response) {
  const selectedRole = document.getElementById("user_role").value;
  const googleToken = response.credential;

  try {
    const res = await axios.post('../routes/api.php?action=oauth_login', {
      credential: googleToken,
      role: selectedRole
    });

    if (res.data.status === 'success') {
      if (res.data.role === 'employer') {
        window.location.href = 'employer_dash.php';
      } else {
        window.location.href = 'seeker_dash.php';
      }
    } else {
      alert(res.data.message);
    }
  } catch (err) {
    console.error("OAuth Error:", err);
    alert("Google login failed. Please try again.");
  }
}

async function handleManualLogin() {
  const selectedRole = document.getElementById("user_role").value;
  const emailInput = document.getElementById("manual_email").value;
  const passwordInput = document.getElementById("manual_password").value;

  try {
    const res = await axios.post('../routes/api.php?action=manual_login', {
      email: emailInput,
      password: passwordInput,
      role: selectedRole
    });

    if (res.data.status === 'success') {
      if (res.data.role === 'employer') {
        window.location.href = 'employer_dash.php';
      } else {
        window.location.href = 'seeker_dash.php';
      }
    } else {
      alert(res.data.message);
    }
  } catch (err) {
    console.error("Login Error:", err);
    alert("Login failed. Please try again.");
  }
}

async function handleManualSignUp() {
  const selectedRole = document.getElementById("user_role").value;
  const nameInput = document.getElementById("manual_name").value;
  const emailInput = document.getElementById("manual_email").value;
  const passwordInput = document.getElementById("manual_password").value;

  if (!nameInput || !emailInput || !passwordInput) {
    alert("Please fill in name, email, and password to sign up.");
    return;
  }

  try {
    const res = await axios.post('../routes/api.php?action=manual_signup', {
      name: nameInput,
      email: emailInput,
      password: passwordInput,
      role: selectedRole
    });

    if (res.data && res.data.status === 'success') {
      alert(res.data.message || "Registration successful! You can now Log In.");
    } else {
      alert((res.data && res.data.message) ? res.data.message : "Sign up failed. Please try again.");
    }
  } catch (err) {
    console.error("SignUp Error:", err);
    var msg = "Sign up failed. Please try again.";
    if (err.response && err.response.data && err.response.data.message) {
      msg = err.response.data.message;
    }
    alert(msg);
  }
}
