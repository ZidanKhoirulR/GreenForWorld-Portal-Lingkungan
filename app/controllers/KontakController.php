<?php
namespace App\Controllers;

use App\Models\ArtikelModel;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class KontakController {

	protected $model;

	// ─── Email Pengirim (Sistem) ───────────────────────────────
	const MAIL_HOST      = 'smtp.hostinger.com';
	const MAIL_PORT      = 465;
	const MAIL_SECURE    = 'ssl';
	const MAIL_USERNAME  = 'hallo@greenforworld.com';
	const MAIL_PASSWORD  = ''; // Dikelola via environment variable / config lokal untuk keamanan
	const MAIL_FROM      = 'hallo@greenforworld.com';
	const MAIL_FROM_NAME = 'GREEN FOR WORLD';
	// Email Penerima (inbox admin)
	const MAIL_TO        = 'hallo@greenforworld.com';
	const MAIL_TO_NAME   = 'Admin GREEN FOR WORLD';
	// ──────────────────────────────────────────────────────────

	public function __construct($f3) {
		$this->model = new ArtikelModel($f3->get('DB'));
	}

	public function index($f3) {

		$f3->set('active_menu_1', '');
		$f3->set('active_menu_2', '');
		$f3->set('active_menu_3', '');
		$f3->set('active_menu_4', '');
		$f3->set('active_menu_5', '');
		$f3->set('active_menu_6', 'active');

		echo \Template::instance()->render('gui-lieind/pages/kontak.html');
	}

	/* ===============================
	   SEND CONTACT FORM  (AJAX / JSON)
	   =============================== */
	public function send($f3)
	{
		// Agar PHP tidak timeout saat koneksi SMTP di production
		@set_time_limit(60);

		// Tangkap semua output sebelumnya agar tidak merusak JSON
		while (ob_get_level()) ob_end_clean();
		ob_start();

		$nama  = trim($_POST['nama']  ?? '');
		$email = trim($_POST['email'] ?? '');
		$pesan = trim($_POST['pesan'] ?? '');

		// Validasi sederhana
		if (!$nama || !$email || !$pesan) {
			$this->jsonOut(['success' => false, 'message' => 'Semua field wajib diisi.']);
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->jsonOut(['success' => false, 'message' => 'Format email tidak valid.']);
		}

		// Load PHPMailer
		require_once __DIR__ . '/../../lib/PHPMailer/src/Exception.php';
		require_once __DIR__ . '/../../lib/PHPMailer/src/PHPMailer.php';
		require_once __DIR__ . '/../../lib/PHPMailer/src/SMTP.php';

		$mail = new \PHPMailer\PHPMailer\PHPMailer(true);

		try {
			// Ambil password SMTP secara aman dari environment variable atau file config lokal
			$mailPassword = getenv('SMTP_PASSWORD');
			if (!$mailPassword && file_exists(__DIR__ . '/../../config.local.php')) {
				$localConfig = @include __DIR__ . '/../../config.local.php';
				if (is_array($localConfig) && !empty($localConfig['SMTP_PASSWORD'])) {
					$mailPassword = $localConfig['SMTP_PASSWORD'];
				}
			}

			// Konfigurasi SMTP Hostinger
			$mail->isSMTP();
			$mail->SMTPDebug  = 0;
			$mail->Host       = getenv('SMTP_HOST') ?: self::MAIL_HOST;
			$mail->SMTPAuth   = true;
			$mail->Username   = getenv('SMTP_USERNAME') ?: self::MAIL_USERNAME;
			$mail->Password   = $mailPassword ?: self::MAIL_PASSWORD;
			$mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
			$mail->Port       = (int)(getenv('SMTP_PORT') ?: self::MAIL_PORT);
			$mail->CharSet    = 'UTF-8';
			$mail->Timeout    = 20;

			// Matikan verifikasi SSL peer — diperlukan di beberapa shared hosting
			$mail->SMTPOptions = [
				'ssl' => [
					'verify_peer'       => false,
					'verify_peer_name'  => false,
					'allow_self_signed' => true,
				],
			];

			// Pengirim & Penerima
			$mail->setFrom(self::MAIL_FROM, self::MAIL_FROM_NAME);
			$mail->addReplyTo($email, $nama);
			$mail->addAddress(self::MAIL_TO, self::MAIL_TO_NAME);

			// Konten Email
			$mail->isHTML(true);
			$mail->Subject = "[Contact Form: {$nama}] - Green For World";
			$mail->Body    = $this->buildEmailBody($nama, $email, $pesan);
			$mail->AltBody = "Nama: {$nama}\nEmail: {$email}\n\nPesan:\n{$pesan}";

			$mail->send();

			$this->jsonOut([
				'success' => true,
				'message' => 'Pesan Anda berhasil dikirim! Kami akan segera menghubungi Anda.'
			]);

		} catch (\Exception $e) {
			$this->jsonOut([
				'success' => false,
				'message' => 'Gagal mengirim pesan. Silakan coba lagi atau hubungi kami langsung.'
			]);
		}
	}

	/* Helper: bersihkan buffer lalu kirim JSON */
	private function jsonOut(array $data)
	{
		while (ob_get_level()) ob_end_clean();
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
		exit;
	}


	/* ===============================
	   HELPER: Bangun Template Email HTML
	   =============================== */
	private function buildEmailBody($nama, $email, $pesan)
	{
		$pesanHtml = nl2br(htmlspecialchars($pesan));
		$tahun     = date('Y');
		return "
<!DOCTYPE html>
<html lang='id'>
<head><meta charset='UTF-8'></head>
<body style='margin:0;padding:0;background:#f0f4f0;font-family:Arial,sans-serif;'>
  <table width='100%' cellpadding='0' cellspacing='0' style='background:#f0f4f0;padding:32px 0;'>
    <tr><td align='center'>
      <table width='600' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);'>

        <!-- Header -->
        <tr>
          <td style='background:linear-gradient(135deg,#2e7d32,#66bb6a);padding:36px 40px;text-align:center;'>
            <h1 style='margin:0;color:#fff;font-size:24px;letter-spacing:1px;'>GREEN FOR WORLD</h1>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style='padding:40px;'>
            <table width='100%' cellpadding='0' cellspacing='0'>
              <tr>
                <td style='padding-bottom:20px;'>
                  <p style='margin:0 0 4px;font-size:12px;color:#888;text-transform:uppercase;letter-spacing:.5px;'>Nama Pengirim</p>
                  <p style='margin:0;font-size:16px;color:#1b5e20;font-weight:bold;'>{$nama}</p>
                </td>
              </tr>
              <tr>
                <td style='padding-bottom:20px;'>
                  <p style='margin:0 0 4px;font-size:12px;color:#888;text-transform:uppercase;letter-spacing:.5px;'>Alamat Email</p>
                  <p style='margin:0;font-size:16px;color:#333;'><a href='mailto:{$email}' style='color:#2e7d32;text-decoration:none;'>{$email}</a></p>
                </td>
              </tr>
              <tr>
                <td style='padding-bottom:8px;'>
                  <p style='margin:0 0 4px;font-size:12px;color:#888;text-transform:uppercase;letter-spacing:.5px;'>Pesan</p>
                  <div style='background:#f9fbe7;border-left:4px solid #66bb6a;border-radius:4px;padding:16px 20px;font-size:15px;color:#333;line-height:1.7;'>{$pesanHtml}</div>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style='background:#f5f5f5;padding:20px 40px;text-align:center;border-top:1px solid #e8f5e9;'>
            <p style='margin:0;font-size:12px;color:#999;'>©2026 <a href='https://greenforworld.com' style='color:#2e7d32;text-decoration:none;'>greenforworld.com</a></p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>";
	}

	public function detail($f3, $params) {
		// kosong
	}
}