<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\InstitutionProfileController;
use App\Controllers\InternalController;
use App\Controllers\PublicDashboardController;
use App\Controllers\ReportController;
use App\Controllers\UserManagementController;
use App\Controllers\ZakatPaymentController;
use App\Controllers\ZakatQualityController;
use App\Core\Response;
use App\Core\Router;
use App\Services\AuthService;

return static function (Router $router): void {
    $router->get('/', static function (): void {
        $auth = new AuthService();
        Response::redirect($auth->check() ? '/dashboard' : '/login');
    });

    $router->get('/public/dashboard', [PublicDashboardController::class, 'page']);
    $router->get('/api/public/dashboard/summary', [PublicDashboardController::class, 'summaryApi']);
    $router->get('/login', [AuthController::class, 'showLogin'], ['guest' => true]);
    $router->post('/login', [AuthController::class, 'login'], ['guest' => true]);
    $router->get('/dashboard', [DashboardController::class, 'index'], ['auth' => true]);
    $router->get('/api/dashboard/summary', [DashboardController::class, 'summaryApi'], ['auth' => true]);
    $router->get('/zakat-payments/new', [ZakatPaymentController::class, 'createForm'], [
        'auth' => true,
    ]);
    $router->get('/zakat-payments/{id}/edit', [ZakatPaymentController::class, 'editForm'], [
        'auth' => true,
        'roles' => ['ADMIN', 'OPERATOR'],
    ]);
    $router->get('/zakat-payments/list', [ZakatPaymentController::class, 'listPage'], [
        'auth' => true,
    ]);
    $router->post('/api/zakat-payments', [ZakatPaymentController::class, 'createApi'], [
        'auth' => true,
    ]);
    $router->get('/api/zakat-payments', [ZakatPaymentController::class, 'listApi'], [
        'auth' => true,
    ]);
    $router->get('/api/zakat-payments/{id}', [ZakatPaymentController::class, 'showApi'], [
        'auth' => true,
    ]);
    $router->put('/api/zakat-payments/{id}', [ZakatPaymentController::class, 'updateApi'], [
        'auth' => true,
        'roles' => ['ADMIN', 'OPERATOR'],
    ]);
    $router->get('/api/zakat-payments/received-by-suggestions', [ZakatPaymentController::class, 'receivedBySuggestionsApi'], [
        'auth' => true,
    ]);
    $router->post('/api/zakat-payments/{id}/cancel', [ZakatPaymentController::class, 'cancelApi'], [
        'auth' => true,
        'roles' => ['ADMIN', 'OPERATOR'],
    ]);
    $router->get('/api/reports/kwitansi/{paymentId}', [ReportController::class, 'kwitansiApi'], [
        'auth' => true,
    ]);
    $router->get('/reports/rekap', [ReportController::class, 'rekapPage'], [
        'auth' => true,
    ]);
    $router->get('/reports/muzakki-detail', [ReportController::class, 'muzakkiDetailPage'], [
        'auth' => true,
    ]);
    $router->get('/api/reports/rekap-zis', [ReportController::class, 'rekapZisApi'], [
        'auth' => true,
    ]);
    $router->get('/api/reports/muzakki-detail', [ReportController::class, 'muzakkiDetailApi'], [
        'auth' => true,
    ]);
    $router->get('/api/reports/muzakki-detail.csv', [ReportController::class, 'muzakkiDetailCsv'], [
        'auth' => true,
    ]);
    $router->get('/api/reports/kwitansi/{paymentId}/template.pdf', [ReportController::class, 'kwitansiTemplatePdf'], [
        'auth' => true,
    ]);
    $router->get('/api/reports/kwitansi/{paymentId}/template/print', [ReportController::class, 'kwitansiTemplatePrint'], [
        'auth' => true,
    ]);
    $router->get('/settings/institution-profile', [InstitutionProfileController::class, 'edit'], [
        'auth' => true,
        'roles' => ['ADMIN'],
    ]);
    $router->get('/api/institution-profile', [InstitutionProfileController::class, 'showApi'], [
        'auth' => true,
        'roles' => ['ADMIN'],
    ]);
    $router->post('/settings/institution-profile', [InstitutionProfileController::class, 'update'], [
        'auth' => true,
        'roles' => ['ADMIN'],
    ]);
    $router->put('/api/institution-profile', [InstitutionProfileController::class, 'updateApi'], [
        'auth' => true,
        'roles' => ['ADMIN'],
    ]);
    $router->get('/settings/zakat-qualities', [ZakatQualityController::class, 'index'], [
        'auth' => true,
        'roles' => ['ADMIN'],
    ]);
    $router->get('/api/zakat-qualities', [ZakatQualityController::class, 'listApi'], [
        'auth' => true,
    ]);
    $router->post('/api/zakat-qualities', [ZakatQualityController::class, 'createApi'], [
        'auth' => true,
        'roles' => ['ADMIN'],
    ]);
    $router->delete('/api/zakat-qualities/{id}', [ZakatQualityController::class, 'deactivateApi'], [
        'auth' => true,
        'roles' => ['ADMIN'],
    ]);
    $router->get('/settings/users', [UserManagementController::class, 'settings'], [
        'auth' => true,
        'roles' => ['ADMIN'],
    ]);
    $router->get('/user/add', [UserManagementController::class, 'addForm'], [
        'auth' => true,
        'roles' => ['ADMIN'],
    ]);
    $router->post('/user/add', [UserManagementController::class, 'addSubmit'], [
        'auth' => true,
        'roles' => ['ADMIN'],
    ]);
    $router->get('/user/edit/{id}', [UserManagementController::class, 'editForm'], [
        'auth' => true,
        'roles' => ['ADMIN'],
    ]);
    $router->post('/user/edit/{id}', [UserManagementController::class, 'editSubmit'], [
        'auth' => true,
        'roles' => ['ADMIN'],
    ]);
    $router->get('/api/users', [UserManagementController::class, 'listApi'], [
        'auth' => true,
        'roles' => ['ADMIN'],
    ]);
    $router->delete('/api/users/{id}', [UserManagementController::class, 'deactivateApi'], [
        'auth' => true,
        'roles' => ['ADMIN'],
    ]);
    $router->post('/logout', [AuthController::class, 'logout'], ['auth' => true]);
    $router->post('/internal/migrate', [InternalController::class, 'runMigrations']);
};
