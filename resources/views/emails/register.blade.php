<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .table_approved {
            font-family: Arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
            font-size: 12px;
        }
        .table_approved tr:nth-child(even) {
            background-color: #dddddd;
        }
        .wrapper {
            width: 100%;
            padding: 20px;
        }
        .content {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            padding: 20px;
            text-align: center;
            background: #f5f5f5;
        }
        .header img {
            width: 150px;
            height: auto;
        }
        .body {
            padding: 20px;
            font-size: 14px;
            color: #333333;
            line-height: 1.6;
        }
        .content-cell {
            padding: 10px;
        }
        .footer {
            text-align: center;
            padding: 15px;
            background: #f5f5f5;
            font-size: 12px;
            color: #888888;
        }
        .button {
            display: inline-block;
            padding: 12px 20px;
            margin: 20px 0;
            color: #ffffff;
            background-color: #4cbf22;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #3a9f1e;
        }
        /* Responsif */
        @media (max-width: 480px) {
            .button {
                width: 100%;
                padding: 14px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table class="content" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="header">
                            <a href="https://ts3.co.id/">
                                <img src="https://ts3.co.id/assets/upload/image/2.png" alt="TS3 Logo">
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="body">
                            <p><strong>Semangat Pagi dan Selamat Datang, {{ $fullname }}!</strong></p>

                            <p>Terima kasih telah mendaftar di sistem kami. Berikut adalah informasi akun Anda:</p>

                            <ul>
                                <li><strong>Username:</strong> {{ $username }}</li>
                                <li><strong>Email:</strong> {{ $email }}</li>
                            </ul>

                            <p>Silakan klik tombol di bawah ini untuk verifikasi akun Anda:</p>

                            <div style="text-align: center;">
                                <a class="button" href="{{ $url }}" style="color: #ffffff;">Verifikasi Akun</a>
                            </div>

                            <p><strong>Catatan:</strong> Link verifikasi hanya berlaku selama <strong>1 jam</strong> setelah email ini diterima.</p>

                            <p>Jika Anda tidak merasa melakukan pendaftaran, harap abaikan email ini.</p>

                            <p>Terima kasih dan semoga harimu menyenangkan!</p>

                            <p><strong>Best Regards,</strong><br>
                            TS3 Indonesia</p>

                            <p><strong>Kontak Kami:</strong><br>
                            Jl. Basudewa Raya 3A Ruko River View, Kel. Bulustalan, Kec. Semarang Selatan, 50245<br>
                            Phone: 024-86042357 / +628179557744<br>
                            Email: contact@ts3.co.id</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table class="footer" align="center" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="content-cell" align="center">
                                        © {{ date('Y') }} <a href="https://www.ts3.co.id" style="color: #888888; text-decoration: none;">www.ts3.co.id</a>. All rights reserved.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
