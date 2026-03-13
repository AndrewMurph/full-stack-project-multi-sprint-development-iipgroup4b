<?php 

     // include __DIR__ . "/../../api/config/db.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View User</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!--<style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }
        table {
            width: 70%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        h2 {
            margin-bottom: 20px;
        }
    </style>-->
</head>
<body>

<?php include __DIR__ . "/layout/nav.php"; ?>

<h2>User List</h2>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
    <tr>
        <th>User Nr</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Email</th>
        <th>Mobile</th>
        <th>UserID</th>
        <th>User Enabled</th>
        <th>UserTypeNr</th>
        <th>ID County</th>
    </tr>
    </thead>
    <tbody id="userTableBody"></tbody>

    <script>

        fetch("../controllers/auth/view_users.php")
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById("userTableBody");
                tbody.innerHTML = "";

                if (!data.users || data.users.length === 0) {
                    tbody.innerHTML =
                        `<tr><td colspan="9">No users found</td></tr>`;
                    return;
                }

                data.users.forEach(u => {
                    tbody.innerHTML += `
        <tr>
          <td>${u.UserNr}</td>
          <td>${u.FirstName}</td>
          <td>${u.LastName}</td>
          <td>${u.email}</td>
          <td>${u.mobile}</td>
          <td>${u.userID}</td>
          <td>${u.userEnabled}</td>
          <td>${u.userTypeNr}</td>
          <td>${u.idcounty}</td>
        </tr>
      `;
                });
            })
            .catch(err => {
                console.error(err);
                document.getElementById("userTableBody").innerHTML =
                    `<tr><td colspan="9">Error loading users</td></tr>`;
            });
    </script>

</body>



</html>
