<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style>
        body {
            font-family: Arial, sans-serif;
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
        }
        .header {
            padding: 10px 0;
            text-align: center;
            background: #f5f5f5;
        }
        .header img {
            width: 150px; /* Ubah ukuran gambar */
            height: auto;
        }
        .body {
            background: #ffffff;
            padding: 20px;
            border: 1px solid #dddddd;
        }
        .content-cell {
            padding: 10px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            background: #f5f5f5;
            font-size: 12px;
            color: #888888;
        }
        .button {
            display: inline-block;
            padding: 15px 25px;
            margin: 10px 0;
            color: #ffffff;
            background-color: #4cbf22;
            border-radius: 5px;
            text-decoration: none;
            font-size: 18px; /* Besarkan huruf tombol */
            text-align: center;
            transition: background-color 0.3s ease; /* Animasi perubahan warna background */
        }

        /* Tombol untuk layar lebih kecil */
        @media (max-width: 768px) {
            .button {
                padding: 12px 20px;
                font-size: 16px; /* Ukuran huruf lebih kecil */
            }
        }

        /* Tombol untuk layar sangat kecil */
        @media (max-width: 480px) {
            .button {
                padding: 10px 15px;
                font-size: 14px; /* Ukuran huruf lebih kecil lagi */
                width: 100%; /* Tombol mengambil seluruh lebar layar */
                box-sizing: border-box; /* Menghitung padding dalam lebar total */
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
                        <td class="body" width="100%" cellpadding="0" cellspacing="0">
                            <table class="inner-body" align="center" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="content-cell">
                                        <strong>SEMANGAT PAGI DAN SELAMAT DATANG</strong>
                                        <br><br>
                                        Hi {{ $fullname }},
                                        <br><br>
                                        Berikut adalah kode OTP Anda. Mohon untuk diinput di sistem:
                                        <br><br>
                                        <div style="text-align: center;"> <!-- Center tombol -->
                                            <a class="button" href="#" style="color: #ffffff;">{{ $otp }}</a>
                                        </div>
                                        <br>
                                        Demikian informasinya. Jika ada kendala atau ada yang ingin ditanyakan, silakan langsung ditanyakan kepada PIC terkait.
                                        <br><br>
                                        Terima kasih dan semoga harinya menyenangkan!
                                        <br><br>
                                        Best Regards,<br>
                                        TS3 Indonesia
                                        <br><br>
                                        Our office: Jl. Basudewa Raya 3A Ruko River View, Kel. Bulustalan, Kec. Semarang Selatan, 50245
                                        <br>
                                        Phone: 024-86042357 / +628179557744
                                        <br>
                                        Email: contact@ts3.co.id
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table class="footer" align="center" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="content-cell" align="center">
                                        © {{ date('Y') }} {{ 'www.ts3.co.id' }}. All rights reserved.
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
