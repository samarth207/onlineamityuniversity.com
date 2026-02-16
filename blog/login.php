<?php
require_once __DIR__ . '/blog-config.php';
if (isAdminLoggedIn()) {
    header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Blog Admin Login | Amity Online University</title>
    <link rel="icon" href="../assets/images/favicon.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Open Sans', sans-serif;
            background: linear-gradient(135deg, #0a2e73 0%, #1e3a8a 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 16px;
            padding: 48px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-logo img {
            height: 50px;
            margin: 0 auto 16px;
        }
        .login-logo h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.3rem;
            color: #0a2e73;
        }
        .login-logo p {
            color: #666;
            font-size: 0.9rem;
            margin-top: 4px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
            font-family: 'Open Sans', sans-serif;
        }
        .form-group input:focus {
            outline: none;
            border-color: #0a2e73;
        }
        .login-btn {
            width: 100%;
            padding: 14px;
            background: #0a2e73;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            font-family: 'Montserrat', sans-serif;
        }
        .login-btn:hover {
            background: #082557;
        }
        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .error-msg {
            background: #fee2e2;
            color: #dc2626;
            padding: 10px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.9rem;
            display: none;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #0a2e73;
            text-decoration: none;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <img src="../assets/images/amity-logo.svg" alt="Amity Online University">
            <h1>Blog Admin Panel</h1>
            <p>Sign in to manage blog posts</p>
        </div>
        <div class="error-msg" id="errorMsg"></div>
        <form id="loginForm">
            <div class="form-group">
                <label>Username</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="login-btn" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>
        <div class="back-link">
            <a href="/"><i class="fas fa-arrow-left"></i> Back to Website</a>
        </div>
    </div>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('loginBtn');
            const errorMsg = document.getElementById('errorMsg');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
            errorMsg.style.display = 'none';
            
            try {
                const resp = await fetch('blog-api.php?action=admin_login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        username: document.getElementById('username').value,
                        password: document.getElementById('password').value
                    })
                });
                const data = await resp.json();
                
                if (data.success) {
                    window.location.href = 'admin.php';
                } else {
                    errorMsg.textContent = data.message;
                    errorMsg.style.display = 'block';
                }
            } catch (err) {
                errorMsg.textContent = 'Connection error. Please try again.';
                errorMsg.style.display = 'block';
            }
            
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
        });
    </script>
</body>
</html>
