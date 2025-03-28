<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Expired</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f8f9fa;
            color: #333;
            padding: 50px;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            display: inline-block;
        }
        .countdown {
            font-size: 24px;
            font-weight: bold;
            color: red;
        }
        .redirect-text {
            margin-top: 10px;
            font-size: 14px;
            color: #666;
        }
    </style>
    <script>
        let seconds = 30;
        function countdown() {
            document.getElementById('timer').innerText = seconds;
            if (seconds > 0) {
                seconds--;
                setTimeout(countdown, 1000);
            } else {
                window.location.href = "https://ts3.co.id/";
            }
        }
        window.onload = countdown;
    </script>
</head>
<body>
    <div class="container">
        <h2>Verifikasi Success</h2>
        <p>Akun Anda Sudah Active silakan lakukan Login Pada Aplikasi.</p>
        <p>Halaman ini akan tertutup dalam <span id="timer" class="countdown">30</span> detik.</p>
        <p class="redirect-text">Jika tidak dialihkan, klik <a href="https://ts3.co.id/">di sini</a>.</p>
    </div>
</body>
</html>
