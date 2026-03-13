<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Register</title>
    <link rel="stylesheet" href="./layout/style.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>

         .bg-wrap{
             min-height: 100vh;
             background: url("../img/loginbackground.jpg") center/cover no-repeat; /* 换成你的背景图 */
             position: relative;
             padding: 24px;
         }


         .frame{
             width: min(1100px, 96vw);
             min-height: 640px;
             border: 2px solid rgba(13,110,253,.45);
             padding: 18px;
             margin: 0 auto;
             position: relative;
             display: flex;
             align-items: center;
             justify-content: center;
         }


         .glass-card{
             width: min(780px, 92vw);
             background: rgba(255,255,255,.72);
             backdrop-filter: blur(7px);
             border-radius: 10px;
             box-shadow: 0 12px 30px rgba(0,0,0,.18);
             padding: 28px 36px 26px;
         }


         .logo{
             width: 95px;
             display: block;
             margin: 0 auto 10px;
         }


         .form-control{
             height: 44px;
             border-radius: 14px;
         }


         .form-label{
             margin-bottom: 0;
             font-weight: 600;
         }

         .btn-register{
             background: #8eaaf7;
             border: 1px solid rgba(0,0,0,.2);
             border-radius: 14px;
             padding: 10px 46px;
         }


         .brand-mark{
             position: absolute;
             right: 22px;
             bottom: 18px;
             display: flex;
             align-items: center;
             gap: 10px;
             opacity: .9;
             user-select: none;
         }
         .brand-mark img{
             width: 64px;
             height: 64px;
             border-radius: 50%;
             object-fit: cover;
         }
         .brand-mark .text{
             font-weight: 800;
             letter-spacing: .08em;
             line-height: 1.1;
             color: #fff;
             text-shadow: 0 2px 10px rgba(0,0,0,.35);
             font-size: 26px;
         }

         .page-label{
             position: absolute;
             top: 14px;
             left: 18px;
             color: rgba(255,255,255,.55);
             font-weight: 700;
         }
     </style>
</head>

<body>


<?php include __DIR__ . "/layout/nav.php"; ?>

<div class="bg-wrap">
    <div class="frame">
        <div class="glass-card">
            <img src="../img/smalllogo.png" alt="logo" class="logo"> <!-- 换成你的logo -->

            <h4 class="text-center mb-4">Register</h4>

<div class="container">
    <form method="POST" action="../controllers/auth/register.php">

   <input name="FirstName" placeholder="First name" required><br>
   <input name="LastName" placeholder="Last name" required><br>
   <input name="email" type="email" placeholder="Email" required><br>
   <input name="mobile" placeholder="Mobile" required><br>
   <input name="PassWord" type="password" placeholder="Password" required><br>

  <button type="submit">Register</button>
</form>
    </div>
</div>

</body>
</html>