<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\VisaApplicationController;
//use App\Http\Controllers\Admin\DocumentReviewController;
use App\Http\Controllers\Admin\VisaWorkflowController;
use App\Http\Controllers\Admin\VisaAssignmentController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Client\DashboardController;
use Inertia\Inertia;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Consultant\DashboardController as ConsultantDashboard;
use App\Http\Controllers\Consultant\VisaApplicationController as ConsultantVisaController;
use App\Http\Controllers\Client\VisaApplicationController as ClientVisaController;
use App\Http\Controllers\Admin\UserApprovalController;
use App\Http\Controllers\Client\VisaBulkDocumentController;
use App\Http\Controllers\DocumentReviewController;
use App\Http\Controllers\VisaReviewController;
use App\Http\Controllers\Auth\MobileAuthRedirectController;

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Email Verification Success Page
|--------------------------------------------------------------------------
*/
Route::get('/email-verified', function () {
    return Inertia::render('Auth/EmailVerified');
})->name('verification.success');

Route::middleware(['auth', 'verified', 'role:admin'])
    ->get('/dashboard', [AdminDashboard::class, 'index'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifications', function () {
        return Inertia::render('Notifications/Index');
    })->name('notifications.index');

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

        Route::get('/clients', [ClientController::class, 'index'])->name('admin.clients.index');
        Route::get('/clients/create', [ClientController::class, 'create'])->name('admin.clients.create');
        Route::post('/clients', [ClientController::class, 'store'])->name('admin.clients.store');

        Route::get('/visa-applications', [VisaApplicationController::class, 'index'])->name('admin.visa.index');
        Route::get('/visa-applications/{visa}', [VisaApplicationController::class, 'show'])->name('admin.visa.show');

       // Route::post('/documents/{document}/approve', [DocumentReviewController::class, 'approve'])->name('admin.documents.approve');
       // Route::post('/documents/{document}/reject', [DocumentReviewController::class, 'reject'])->name('admin.documents.reject');
        Route::post(
            '/visas/{visa}/approve',
            [\App\Http\Controllers\VisaReviewController::class, 'approve']
        )->name('visas.approve');

        Route::post(
            '/visas/{visa}/reject',
            [\App\Http\Controllers\VisaReviewController::class, 'reject']
        )->name('visas.reject');


      //  Route::post('/visa-applications/{visa}/status', [VisaWorkflowController::class, 'updateStatus'])->name('admin.visa.status.update');
        Route::post('/visa-applications/{visa}/assign-consultant', [VisaAssignmentController::class, 'assign'])->name('admin.visa.assign');

        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity.logs');
        Route::get('/clients/{client}', [ClientController::class, 'show'])->name('admin.clients.show');
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

        Route::get('/dashboard', [ConsultantDashboard::class, 'index'])->name('dashboard');
        Route::get('/visas', [ConsultantVisaController::class, 'index'])->name('visas.index');
        Route::get('/visas/{visa}', [ConsultantVisaController::class, 'show'])->name('visas.show');
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

        Route::post(
            '/visa-applications/{visa}/documents',
            [\App\Http\Controllers\Client\DocumentController::class, 'store']
        )->name('visa.documents.store');

        Route::post(
            '/visa-applications/{visa}/documents/bulk',
            [VisaBulkDocumentController::class, 'store']
        )->name('client.visa.documents.bulk');
    });


require __DIR__.'/auth.php';
