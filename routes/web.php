<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Admin Controllers
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\VisaApplicationController;
use App\Http\Controllers\Admin\VisaAssignmentController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\UserApprovalController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;

// Consultant Controllers
use App\Http\Controllers\Consultant\DashboardController as ConsultantDashboard;
use App\Http\Controllers\Consultant\VisaApplicationController as ConsultantVisaController;

// Client Controllers
use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\VisaApplicationController as ClientVisaController;
use App\Http\Controllers\Client\VisaBulkDocumentController;

// Shared
use App\Http\Controllers\DocumentReviewController;
use App\Http\Controllers\VisaReviewController;
use App\Http\Controllers\Admin\ClientFamilyMemberController;
use App\Http\Controllers\Admin\VisaFamilyMemberController;
use App\Http\Controllers\Admin\VisaDeadlineController;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Consultant\AppointmentController as ConsultantAppointmentController;
use App\Http\Controllers\Client\AppointmentController as ClientAppointmentController;

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});
Route::view('/privacy-policy', 'privacy-policy');

/*
|--------------------------------------------------------------------------
| Email Verification Success
|--------------------------------------------------------------------------
*/
Route::get('/email-verified', function () {
    return Inertia::render('Auth/EmailVerified');
})->name('verification.success');

/*
|--------------------------------------------------------------------------
| Admin Dashboard
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:admin'])
    ->get('/dashboard', [AdminDashboard::class, 'index'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Authenticated Shared Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::get('/notifications', fn () => Inertia::render('Notifications/Index'))
        ->name('notifications.index');

    Route::post('/notifications/{notification}/read', function ($id) {
        auth()->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail()
            ->markAsRead();

        return back();
    })->name('notifications.read');

    // Document review
    Route::post('/documents/{document}/approve', [DocumentReviewController::class, 'approve'])
        ->name('documents.approve');

    Route::post('/documents/{document}/reject', [DocumentReviewController::class, 'reject'])
        ->name('documents.reject');

    // Visa review
    Route::post('/visas/{visa}/approve', [VisaReviewController::class, 'approve'])
        ->name('visas.approve');

    Route::post('/visas/{visa}/reject', [VisaReviewController::class, 'reject'])
        ->name('visas.reject');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Clients
        Route::get('/clients', [ClientController::class, 'index'])
            ->name('clients.index');

        Route::get('/clients/create', [ClientController::class, 'create'])
            ->name('clients.create');

        Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])
            ->name('clients.edit');

        Route::patch('/clients/{client}', [ClientController::class, 'update'])
            ->name('clients.update');

        Route::delete('/clients/{client}', [ClientController::class, 'destroy'])
            ->name('clients.destroy');

        Route::post('/clients', [ClientController::class, 'store'])
            ->name('clients.store');

        Route::get('/clients/{client}', [ClientController::class, 'show'])
            ->name('clients.show');

        Route::post('/clients/check-email', [ClientController::class, 'checkEmail'])
            ->name('clients.check-email');

        // Visa Applications
        Route::get(
            '/visa-applications/create',
            [VisaApplicationController::class, 'create']
        )->name('visa.create');

        Route::post(
            '/visa-applications',
            [VisaApplicationController::class, 'store']
        )->name('visa.store');
        Route::get('/visa-applications', [VisaApplicationController::class, 'index'])
            ->name('visa.index');

        Route::get('/visa-applications/{visa}', [VisaApplicationController::class, 'show'])
            ->name('visa.show');

        Route::post('/visa-applications/{visa}/assign-consultant', [VisaAssignmentController::class, 'assign'])
            ->name('visa.assign');

        // Activity Logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])
            ->name('activity.logs');

        // User Approvals
        Route::get('/user-approvals', [UserApprovalController::class, 'index'])
            ->name('user-approvals.index');

        Route::post('/user-approvals/{user}/approve', [UserApprovalController::class, 'approve'])
            ->name('user-approvals.approve');

        Route::post('/user-approvals/{user}/reject', [UserApprovalController::class, 'reject'])
            ->name('user-approvals.reject');
        Route::get('/visa-documents', [\App\Http\Controllers\Admin\VisaTypeDocumentController::class, 'index'])
            ->name('visa-documents.index');

        Route::get('/countries/{country}/visa-types', [\App\Http\Controllers\Admin\VisaTypeDocumentController::class, 'getVisaTypes']);

        Route::get('/visa-types/{visaType}/documents', [\App\Http\Controllers\Admin\VisaTypeDocumentController::class, 'getMappedDocuments']);

        Route::post('/visa-documents', [\App\Http\Controllers\Admin\VisaTypeDocumentController::class, 'store'])
            ->name('visa-documents.store');
        Route::post(
            '/clients/{client}/family-members',
            [ClientFamilyMemberController::class, 'store']
        )->name('admin.clients.family-members.store');

        Route::patch(
            '/family-members/{familyMember}',
            [ClientFamilyMemberController::class, 'update']
        )->name('family-members.update');

        Route::delete(
            '/family-members/{familyMember}',
            [ClientFamilyMemberController::class, 'destroy']
        )->name('family-members.destroy');

        Route::post(
            '/visa-applications/{visa}/family-members',
            [VisaFamilyMemberController::class, 'store']
        )->name('visa.family-members.store');

        Route::delete(
            '/visa-applications/{visa}/family-members/{familyMember}',
            [VisaFamilyMemberController::class, 'destroy']
        )->name('visa.family-members.destroy');
        /*
        |--------------------------------------------------------------------------
        | Visa Deadlines (Admin + Consultant)
        |--------------------------------------------------------------------------
        */
        Route::middleware(['role:admin|consultant'])->group(function () {

            // Create deadline for a visa
            Route::post(
                '/visas/{visa}/deadlines',
                [VisaDeadlineController::class, 'store']
            )->name('visas.deadlines.store');

            // Update deadline
            Route::put(
                '/deadlines/{deadline}',
                [VisaDeadlineController::class, 'update']
            )->name('deadlines.update');

            // Mark deadline as completed
            Route::post(
                '/deadlines/{deadline}/complete',
                [VisaDeadlineController::class, 'complete']
            )->name('deadlines.complete');

            // Delete deadline
            Route::delete(
                '/deadlines/{deadline}',
                [VisaDeadlineController::class, 'destroy']
            )->name('deadlines.destroy');

        });
        /*
        |--------------------------------------------------------------------------
        | Appointments (Admin)
        |--------------------------------------------------------------------------
        */
        Route::get('/appointments', [AdminAppointmentController::class, 'index'])
            ->name('appointments.index');
        Route::get('/appointments/create', [AdminAppointmentController::class, 'create'])
            ->name('appointments.create');

        Route::post('/appointments', [AdminAppointmentController::class, 'store'])
            ->name('appointments.store');
        Route::get('/appointments/calendar', [AdminAppointmentController::class, 'calendar'])
            ->name('appointments.calendar');
        Route::put(
            '/appointments/{appointment}/reschedule',
            [AdminAppointmentController::class, 'reschedule']
        )->name('appointments.reschedule');
        Route::get('/appointments/{appointment}', [AdminAppointmentController::class, 'show'])
            ->name('appointments.show');

        Route::put(
            'appointments/{appointment}/cancel',
            [AdminAppointmentController::class, 'cancel']
        )->name('appointments.cancel');

    });

/*
|--------------------------------------------------------------------------
| Consultant Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:consultant'])
    ->prefix('consultant')
    ->name('consultant.')
    ->group(function () {

        Route::get('/dashboard', [ConsultantDashboard::class, 'index'])
            ->name('dashboard');

        Route::get('/visas', [ConsultantVisaController::class, 'index'])
            ->name('visas.index');

        Route::get('/visas/{visa}', [ConsultantVisaController::class, 'show'])
            ->name('visas.show');

        Route::get('/appointments', [ConsultantAppointmentController::class, 'index'])
            ->name('appointments.index');

        Route::get('/appointments/create', [ConsultantAppointmentController::class, 'create'])
            ->name('appointments.create');

        Route::post('/appointments', [ConsultantAppointmentController::class, 'store'])
            ->name('appointments.store');

        Route::get('/appointments/calendar', [ConsultantAppointmentController::class, 'calendar'])
            ->name('appointments.calendar');

        Route::put(
            '/appointments/{appointment}/reschedule',
            [ConsultantAppointmentController::class, 'reschedule']
        )->name('appointments.reschedule');

        Route::put(
            '/appointments/{appointment}/cancel',
            [ConsultantAppointmentController::class, 'cancel']
        )->name('appointments.cancel');

        Route::get('/appointments/{appointment}', [ConsultantAppointmentController::class, 'show'])
            ->name('appointments.show');
    });

/*
|--------------------------------------------------------------------------
| Client Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:client'])
    ->prefix('client')
    ->name('client.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/visas/create', [ClientVisaController::class, 'create'])
            ->name('visas.create');

        Route::post('/visas', [ClientVisaController::class, 'store'])
            ->name('visas.store');

        Route::get('/visas', [ClientVisaController::class, 'index'])
            ->name('visas.index');

        Route::get('/visas/{visa}', [ClientVisaController::class, 'show'])
            ->name('visas.show');

        Route::post('/visa-applications/{visa}/documents', [
            \App\Http\Controllers\Client\DocumentController::class,
            'store',
        ])->name('visa.documents.store');

        Route::post('/visa-applications/{visa}/documents/bulk', [
            VisaBulkDocumentController::class,
            'store',
        ])->name('visa.documents.bulk');
        Route::get('/appointments', [ClientAppointmentController::class, 'index'])
            ->name('appointments.index');

        Route::get('/appointments/{appointment}', [ClientAppointmentController::class, 'show'])
            ->name('appointments.show');

    });

require __DIR__ . '/auth.php';
