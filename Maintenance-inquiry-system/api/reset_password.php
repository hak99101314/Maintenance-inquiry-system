<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>重設密碼</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">重設密碼</h2>
        <form id="reset-password-form" class="col-md-6 mx-auto">
            <input type="hidden" id="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
            <div class="mb-3">
                <label for="new-password" class="form-label">新密碼</label>
                <input type="password" id="new-password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">重設密碼</button>
        </form>
        <div id="response-message" class="mt-3 text-center"></div>
    </div>

    <script>
        document.getElementById('reset-password-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const token = document.getElementById('token').value;
            const password = document.getElementById('new-password').value;

            fetch('api/resetPassword.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token, password })
            })
            .then(response => response.json())
            .then(data => {
                const responseMessage = document.getElementById('response-message');
                if (data.success) {
                    responseMessage.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                } else {
                    responseMessage.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                }
            });
        });
    </script>
</body>
</html>
