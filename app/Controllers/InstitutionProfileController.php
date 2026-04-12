<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\AuthService;
use App\Services\InstitutionProfileService;
use RuntimeException;

final class InstitutionProfileController
{
    private AuthService $auth;
    private InstitutionProfileService $profiles;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
        $this->profiles = new InstitutionProfileService();
    }

    public function edit(): void
    {
        View::render('settings/institution-profile', [
            'title' => 'Profile Instansi/Masjid',
            'pageTitle' => 'Profile Instansi/Masjid',
            'breadcrumbs' => ['Pengaturan', 'Profile Instansi/Masjid'],
            'csrfToken' => Session::csrfToken(),
            'user' => $this->auth->user(),
        ]);
    }

    public function showApi(): void
    {
        try {
            $profile = $this->profiles->getProfile();

            if (!is_array($profile)) {
                Response::json([], 204);
            }

            Response::json([
                'namaInstansi' => (string) ($profile['nama_instansi'] ?? ''),
                'kotaKabupaten' => (string) ($profile['kota_kabupaten'] ?? ''),
                'alamatLengkap' => (string) ($profile['alamat_lengkap'] ?? ''),
                'nomorTelepon' => (string) ($profile['nomor_telepon'] ?? ''),
                'email' => (string) ($profile['email'] ?? ''),
                'namaKetua' => (string) ($profile['nama_ketua'] ?? ''),
                'namaBendahara' => (string) ($profile['nama_bendahara'] ?? ''),
            ]);
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function update(): void
    {
        $csrfToken = $_POST['_csrf'] ?? null;

        if (!Session::verifyCsrf(is_string($csrfToken) ? $csrfToken : null)) {
            Session::flash('error', 'Token keamanan tidak valid. Silakan coba lagi.');
            Response::redirect('/settings/institution-profile');
        }

        $payload = [
            'nama_instansi' => trim((string) ($_POST['nama_instansi'] ?? '')),
            'kota_kabupaten' => trim((string) ($_POST['kota_kabupaten'] ?? '')),
            'alamat_lengkap' => trim((string) ($_POST['alamat_lengkap'] ?? '')),
            'nomor_telepon' => trim((string) ($_POST['nomor_telepon'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'nama_ketua' => trim((string) ($_POST['nama_ketua'] ?? '')),
            'nama_bendahara' => trim((string) ($_POST['nama_bendahara'] ?? '')),
        ];

        if (
            $payload['nama_instansi'] === ''
            || $payload['kota_kabupaten'] === ''
            || $payload['alamat_lengkap'] === ''
        ) {
            Session::flash('error', 'Nama instansi, kota/kabupaten, dan alamat lengkap wajib diisi.');
            Response::redirect('/settings/institution-profile');
        }

        try {
            $this->profiles->update($payload);
            Session::flash('success', 'Profile instansi berhasil diperbarui.');
        } catch (RuntimeException $exception) {
            Session::flash('error', $exception->getMessage());
        }

        Response::redirect('/settings/institution-profile');
    }

    public function updateApi(): void
    {
        $rawBody = file_get_contents('php://input');
        $payload = json_decode(is_string($rawBody) ? $rawBody : '', true);

        if (!is_array($payload)) {
            Response::json([
                'success' => false,
                'message' => 'Payload tidak valid.',
            ], 422);
        }

        $csrfToken = $payload['_csrf'] ?? null;

        if (!Session::verifyCsrf(is_string($csrfToken) ? $csrfToken : null)) {
            Response::json([
                'success' => false,
                'message' => 'Token keamanan tidak valid. Silakan muat ulang halaman.',
            ], 419);
        }

        $normalized = [
            'nama_instansi' => trim((string) ($payload['namaInstansi'] ?? '')),
            'kota_kabupaten' => trim((string) ($payload['kotaKabupaten'] ?? '')),
            'alamat_lengkap' => trim((string) ($payload['alamatLengkap'] ?? '')),
            'nomor_telepon' => trim((string) ($payload['nomorTelepon'] ?? '')),
            'email' => trim((string) ($payload['email'] ?? '')),
            'nama_ketua' => trim((string) ($payload['namaKetua'] ?? '')),
            'nama_bendahara' => trim((string) ($payload['namaBendahara'] ?? '')),
        ];

        if (
            $normalized['nama_instansi'] === ''
            || $normalized['kota_kabupaten'] === ''
            || $normalized['alamat_lengkap'] === ''
        ) {
            $message = 'Nama instansi wajib diisi';
            if ($normalized['kota_kabupaten'] === '') {
                $message = 'Kota/Kabupaten wajib diisi';
            }
            if ($normalized['alamat_lengkap'] === '') {
                $message = 'Alamat lengkap wajib diisi';
            }

            Response::json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        try {
            $this->profiles->update($normalized);
            Response::json([
                'success' => true,
                'message' => 'Profile disimpan',
            ]);
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
