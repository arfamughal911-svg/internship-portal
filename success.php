<?php
// success.php

session_start();

if (!isset($_SESSION['reg_success'])) {
    header('Location: index.php');
    exit;
}
unset($_SESSION['reg_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Registration Successful</title>
<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #1a1a2e, #0f3460);
    min-height: 100vh;
    display: flex; align-items: center; justify-content: center;
}
.box {
    background: #fff;
    border-radius: 16px;
    padding: 3rem 2.5rem;
    text-align: center;
    max-width: 480px;
    box-shadow: 0 20px 50px rgba(0,0,0,.4);
}
.icon   { font-size: 4rem; }
h1      { margin: 1rem 0 .5rem; color: #0f3460; }
p       { color: #6b7280; }
a       { display: inline-block; margin-top: 1.5rem; padding: .7rem 1.8rem;
          background: #0f3460; color: #fff; border-radius: 8px;
          text-decoration: none; font-weight: 700; }
</style>
</head>
<body>
<div class="box">
    <div class="icon">🎉</div>
    <h1>Registration Successful!</h1>
    <p>Your internship application has been submitted. You will be contacted via e-mail once reviewed.</p>
    <a href="index.php">Register another student</a>
</div>
</body>
</html>
