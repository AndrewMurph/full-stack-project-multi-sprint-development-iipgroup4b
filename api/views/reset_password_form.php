<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="./layout/style.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body{
            background:url("../img/loginbackground.jpg") center/cover no-repeat fixed;
            min-height:100vh;
        }

        .frame{
            width: min(1050px, 96vw);
            min-height: 620px;
            border: 2px solid rgba(13,110,253,.55);
            padding: 18px;
            position: relative;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .glass-card{
            width: min(720px, 92vw);
            background: rgba(255,255,255,0.72);
            backdrop-filter: blur(8px);
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.22);
            padding: 32px 42px 26px;
        }

        .logo{
            width: 110px;
            display:block;
            margin: 0 auto 10px;
            border-radius: 50%;
        }

        .form-control{
            height: 44px;
            border-radius: 14px;
        }

        .btn-soft{
            background:#8eaaf7;
            border: 1px solid rgba(0,0,0,.18);
            color:#111;
            border-radius: 12px;
            height: 40px;
            padding: 0 28px;
        }

        .message{
            margin-top: 14px;
            text-align: center;
            font-weight: 600;
        }
        .message.success{ color: #198754; }
        .message.error{ color: #dc3545; }
    </style>
</head>

<body>

<?php include __DIR__ . "/layout/nav.php"; ?>

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="frame">
        <div class="glass-card">
            <img src="../img/smalllogo.png" alt="logo" class="logo">

            <div class="text-center mb-3">
                <h4 class="mb-1">Reset Password</h4>
                <div class="text-muted">Enter your details below</div>
            </div>

            <form id="resetForm">
                <div class="mb-3">
                    <label class="form-label fw-semibold">User Number:</label>
                    <input type="number" id="userNr" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Old Password:</label>
                    <input type="password" id="oldPassword" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">New Password:</label>
                    <input type="password" id="newPassword" class="form-control" required>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    <button type="submit" class="btn btn-soft">Reset Password</button>
                </div>
            </form>

            <div id="message" class="message"></div>
        </div>
    </div>
</div>

<script>
    document.getElementById("resetForm").addEventListener("submit", async function (e) {
        e.preventDefault();

        const messageDiv = document.getElementById("message");
        messageDiv.textContent = "";
        messageDiv.className = "message";

        const data = {
            userNr: document.getElementById("userNr").value,
            oldPassword: document.getElementById("oldPassword").value,
            newPassword: document.getElementById("newPassword").value
        };

    try {
        const response = await fetch("../controllers/auth/change_password.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(data)
        });

            const result = await response.json();

            if (!response.ok) {
                messageDiv.textContent = result.error || "Reset failed.";
                messageDiv.classList.add("error");
            } else {
                messageDiv.textContent = result.success || "Password updated!";
                messageDiv.classList.add("success");
                document.getElementById("resetForm").reset();
            }

        } catch (error) {
            messageDiv.textContent = "Server error. Please try again.";
            messageDiv.classList.add("error");
        }
    });
</script>

</body>
</html>