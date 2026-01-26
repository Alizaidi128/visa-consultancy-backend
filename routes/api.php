<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VisaApiController;
use App\Http\Controllers\Api\ReferenceController;
use App\Http\Controllers\Api\VisaDocumentApiController;
use App\Http\Controllers\Api\VisaReviewApiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ActivityLogApiController;
use App\Http\Controllers\Api\UserApprovalApiController;
use App\Http\Controllers\Api\VisaAssignmentApiController;
use Illuminate\Notifications\DatabaseNotification;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\ClientApiController;
use App\Http\Controllers\Api\ClientFamilyMemberApiController;
use App\Http\Controllers\Api\VisaFamilyMemberApiController;
use App\Http\Controllers\Api\AppointmentApiController;


Route::prefix('v1')->group(function () {
    Route::get('/debug/firebase', function () {
        return [
            'project_id' => config('services.firebase.project_id'),
            'credentials' => config('firebase.projects.app.credentials'),
        ];
    });

    // 🔓 Public
    Route::post('/auth/login', [AuthController::class, 'login']);

    // 🔐 Protected
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::patch('/profile', [ProfileController::class, 'update']);
        Route::get('/my-visas', [VisaApiController::class, 'myVisas']);
        Route::get('/visa/{visa}', [VisaApiController::class, 'show']);
        Route::get('/countries', [ReferenceController::class, 'countries']);
        Route::get('/countries/{country}/visa-types', [ReferenceController::class, 'visaTypes']);
        Route::get('/visa-types/{visaType}/required-documents', [ReferenceController::class, 'requiredDocuments']);
        Route::post('/visas', [VisaApiController::class, 'store']);
        Route::post('/visas/{visa}/documents', [VisaDocumentApiController::class, 'upload']);
        Route::post('/visas/{visa}/documents/bulk', [VisaDocumentApiController::class, 'bulkUpload']);
        Route::post('/visas/{visa}/submit', [VisaApiController::class, 'submit']);
        // 🔍 CONSULTANT / ADMIN REVIEWS
        Route::post('/documents/{document}/approve', [VisaReviewApiController::class, 'approveDocument']);
        Route::post('/documents/{document}/reject', [VisaReviewApiController::class, 'rejectDocument']);

        // 🛂 VISA DECISION (ADMIN)
        Route::post('/visas/{visa}/approve', [VisaReviewApiController::class, 'approveVisa']);
        Route::post('/visas/{visa}/reject', [VisaReviewApiController::class, 'rejectVisa']);
        Route::get('/visas/{visa}/activity-logs', [ActivityLogApiController::class, 'visaLogs']);
        Route::get('/my-activity-logs', [ActivityLogApiController::class, 'myLogs']);
        Route::get('/admin/user-approvals', [UserApprovalApiController::class, 'index']);
        Route::post('/admin/users/{user}/approve', [UserApprovalApiController::class, 'approve']);
        Route::post('/admin/users/{user}/reject', [UserApprovalApiController::class, 'reject']);
        Route::get('/admin/consultants', function () {
            return \App\Models\User::role('consultant')
                ->whereNotNull('approved_at')
                ->select('id', 'name', 'email')
                ->get();
        });
        Route::post(
            '/admin/visas/{visa}/assign-consultant',
            [VisaAssignmentApiController::class, 'assign']
        );

        Route::get('/notifications', function () {
            return auth()->user()
                ->notifications()
                ->latest()
                ->get();
        });

        Route::post('/notifications/{notification}/read', function (
            DatabaseNotification $notification
        ) {
            abort_unless(
                $notification->notifiable_id === auth()->id(),
                403
            );

            $notification->markAsRead();

            return response()->json(['success' => true]);
        });

        Route::post('/notifications/read-all', function () {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            if (! $user) {
                abort(401);
            }

            $user->unreadNotifications->markAsRead();

            return response()->json(['success' => true]);
        });


        Route::post('/fcm-token', [FcmTokenController::class, 'store']);
        Route::post('/test-fcm', [\App\Http\Controllers\Api\TestFcmController::class, 'send']);
        Route::get('/notifications/unread-count', function () {
            return [
                'count' => auth()->user()->unreadNotifications()->count(),
            ];
        });
        Route::delete('/fcm-token', [FcmTokenController::class, 'destroy']);

// Clients
        Route::get('/clients', [ClientApiController::class, 'index']);
        Route::post('/clients', [ClientApiController::class, 'store']);
        Route::get('/clients/{client}', [ClientApiController::class, 'show']);
        Route::put('/clients/{client}', [ClientApiController::class, 'update']);
        Route::delete('/clients/{client}', [ClientApiController::class, 'destroy']);

// Family Members
        Route::get(
            '/clients/{client}/family-members',
            [ClientFamilyMemberApiController::class, 'index']
        );
        Route::post(
            '/clients/{client}/family-members',
            [ClientFamilyMemberApiController::class, 'store']
        );
        Route::patch(
            '/family-members/{familyMember}',
            [ClientFamilyMemberApiController::class, 'update']
        );
        Route::delete(
            '/family-members/{familyMember}',
            [ClientFamilyMemberApiController::class, 'destroy']
        );

// Visa ↔ Family
        Route::post(
            '/visas/{visa}/family-members',
            [VisaFamilyMemberApiController::class, 'store']
        );
        Route::delete(
            '/visas/{visa}/family-members/{familyMember}',
            [VisaFamilyMemberApiController::class, 'destroy']
        );
        Route::get(
            '/documents/{document}/view',
            [VisaDocumentApiController::class, 'view']
        );
        // 📅 Appointments
        Route::get('/appointments', [AppointmentApiController::class, 'index']);
        Route::get('/appointments/{appointment}', [AppointmentApiController::class, 'show']);
        Route::post('/appointments', [AppointmentApiController::class, 'store']);
        Route::put(
            '/appointments/{appointment}/reschedule',
            [AppointmentApiController::class, 'reschedule']
        );
        Route::put(
            '/appointments/{appointment}/cancel',
            [AppointmentApiController::class, 'cancel']
        );
        Route::get(
            '/visas/{visa}/documents',
            [VisaDocumentApiController::class, 'index']
        );
    });
});
