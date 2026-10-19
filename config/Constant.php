<?php
function templateEmailResetPPassword($userName, $otpCode): string
{
  return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi OTP</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 500px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);">
                    
                    <tr>
                        <td align="center" style="padding: 30px; background-color: #0f172a;">
                            <div style="width: 45px; height: 45px; background-color: #4f46e5; border-radius: 10px; display: inline-block; text-align: center; line-height: 45px;">
                                <span style="color: #ffffff; font-size: 24px; font-weight: bold;">F</span>
                            </div>
                            <h1 style="color: #ffffff; font-size: 18px; font-weight: 700; margin: 15px 0 0 0; letter-spacing: 0.5px;">SPK FUZZY SUGENO</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 10px 0; font-size: 15px; color: #64748b;">Halo, <strong>' . $userName . '</strong></p>
                            <p style="margin: 0 0 25px 0; font-size: 15px; color: #0f172a; line-height: 1.6;">
                                Gunakan kode keamanan di bawah ini untuk memverifikasi akun Anda. Kode ini hanya berlaku selama <strong>10 menit</strong>.
                            </p>

                            <div style="background-color: #f1f5f9; border: 2px dashed #cbd5e1; border-radius: 10px; padding: 25px; text-align: center; margin-bottom: 25px;">
                                <span style="display: block; font-family: \'Courier New\', Courier, monospace; font-size: 36px; font-weight: 800; color: #4f46e5; letter-spacing: 8px;">' . $otpCode . '</span>
                            </div>

                            <p style="margin: 0; font-size: 13px; color: #94a3b8; line-height: 1.5;">
                                Jika Anda tidak merasa meminta kode ini, harap abaikan email ini atau hubungi tim support kami jika merasa ada aktivitas mencurigakan.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 20px 30px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #94a3b8;">
                                &copy; ' . date("Y") . ' <strong>' . APP_NAME . '</strong>. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>

                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 500px;">
                    <tr>
                        <td style="padding: 20px; text-align: center;">
                            <p style="margin: 0; font-size: 11px; color: #cbd5e1; text-transform: uppercase; letter-spacing: 1px;">
                                Sent via Automated Decision System
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
}