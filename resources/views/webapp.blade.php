<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>وب اپ تلگرام</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f0f0;
        }
        .container {
            text-align: center;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="container">
    <h1>خوش آمدید به وب اپ تلگرام</h1>
    <p>این یک نمونه ساده از وب اپ تلگرام در Laravel است.</p>
    <button onclick="sendData()">ارسال داده به ربات</button>
</div>

<script>
    function sendData() {
        const webApp = window.Telegram.WebApp;
        webApp.sendData("داده از وب اپ Laravel");
        webApp.close();
    }

    window.Telegram.WebApp.ready();
</script>
</body>
</html>
