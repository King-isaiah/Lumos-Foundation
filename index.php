<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting...</title>
    
    <!-- Method 1: HTML Meta Refresh (Immediate redirect, 0 seconds) -->
    <meta http-equiv="refresh" content="0; url=public/login.php">
    
    <!-- Method 2: JavaScript Redirect (Backup) -->
    <script>
        window.location.href = "public/login.php";
    </script>
</head>
<body>
    <p>Redirecting to login page...</p>
    <p>If you are not redirected automatically, <a href="public/login.php">click here to go to the login page</a>.</p>
</body>
</html>