<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Ormoc Job Matching Platform</title>

  <script src="https://accounts.google.com/gsi/client" async defer></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body data-google-client-id="457247832144-vos0rhcnost6rau41c29iaejba4i9981.apps.googleusercontent.com">

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
      <label>Name (Only required for Sign Up):</label><br>
      <input type="text" id="manual_name" placeholder="John Doe"><br><br>

      <label>Email Address:</label><br>
      <input type="email" id="manual_email" placeholder="example@gmail.com"><br><br>

      <label>Password:</label><br>
      <input type="password" id="manual_password" placeholder="******"><br><br>

      <button onclick="handleManualLogin()">Log In</button>
      <button onclick="handleManualSignUp()">Sign Up</button>
    </div>

    <hr>

    <h3>Or Use Single Sign-On</h3>
    <div id="googleSignInButton"></div>
  </div>

  <script src="../assets/js/auth.js"></script>
</body>
</html>
