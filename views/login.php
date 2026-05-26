<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Ormoc Job Matching Platform</title>
  
  <link rel="stylesheet" href="/ormoc-job-platform/assets/css/style.css">

  <script src="https://accounts.google.com/gsi/client" async defer></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body data-google-client-id="457247832144-vos0rhcnost6rau41c29iaejba4i9981.apps.googleusercontent.com">

  <div class="login-top-logos">
    <a href="https://apps.evsu.edu.ph/" target="_blank" rel="noopener noreferrer" class="evsu-top-left-link" aria-label="Open EVSU App">
      <img src="/ormoc-job-platform/assets/EVSU_Official_Logo.png" alt="EVSU Logo" class="evsu-logo" />
    </a>
    <a href="/ormoc-job-platform/" target="_blank" rel="noopener noreferrer" class="evsu-top-left-link" aria-label="Open Ormoc Job Seeker">
      <img src="/ormoc-job-platform/assets/ormocjobseeek.png" alt="Ormoc Job Seeker Logo" class="evsu-logo evsu-logo--secondary" />
    </a>
  </div>

  <div class="login-body-wrapper">
    
    
    <div class="login-container">
      <h2>Welcome to Ormoc Job Matching Platform</h2>
      
      <label for="user_role">I'm a/an</label>
      <select id="user_role">
        <option value="seeker">Job Seeker</option>
        <option value="employer">Employer</option>
      </select>

      <hr> 
      
      <h3>Manual Login / Sign Up</h3>
      <div>
        <label>Name (Only required for Sign Up):</label>
        <input type="text" id="manual_name" placeholder="John Doe">

        <label>Email Address:</label>
        <input type="email" id="manual_email" placeholder="example@gmail.com">

        <label>Password:</label>
        <input type="password" id="manual_password" placeholder="******">

        <button onclick="handleManualLogin()">Log In</button>
        <button onclick="handleManualSignUp()">Sign Up</button>
      </div>

      <hr>

      <h3>Or Use Single Sign-On</h3>
      <div id="googleSignInButton"></div>
    </div>

  </div> <script src="../assets/js/auth.js"></script>
</body>
</html>