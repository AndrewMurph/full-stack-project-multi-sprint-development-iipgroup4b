<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="./layout/style.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

     <style>

            body{
                background:url("../img/loginbackground.jpg") center/cover no-repeat;
                height:100vh;
            }

            .login-box{
                background: rgba(255,255,255,0.8);
                padding:40px;
                border-radius:10px;
                box-shadow:0 10px 25px rgba(0,0,0,0.2);
                max-width:450px;
                .logo{
                    width:100px;
                    display:block;
                    margin:auto;
                }
            }

        </style>

</head>

<body>


<?php include __DIR__ . "/layout/nav.php"; ?>

<div class="container d-flex justify-content-center align-items-center vh-100">
  <div class="login-box">
    <img src="../img/smalllogo.png" alt="logo" class="logo">

    <h3 class="text-center mb-4">Welcome to Claddagh Watch</h3>

    <form method="POST" action="../controllers/auth/Login.php">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
      </div>

      <div class="d-grid mb-3">
        <button type="submit" class="btn btn-primary">Login</button>
      </div>
    </form>

    <div class="text-center">
      <a href="reset_password_form.php">Reset Password?</a>
    </div>

    <div class="text-center mt-2">
      Don't have account? <a href="register_form.php">Register</a>
    </div>
  </div>
</div>
</body>
</html>