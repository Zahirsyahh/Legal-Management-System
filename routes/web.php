<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewStageController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ContractDepartmentController;
use App\Http\Controllers\DepartmentDashboardController;
use App\Http\Controllers\DepartmentAdminController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\MasterDepartmentController;
use App\Http\Controllers\ReportController as AdminReportController;
use App\Http\Controllers\HrmsLoginController;
use App\Http\Controllers\CompletedController;
use App\Http\Controllers\Legal\EditWorkflowController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\ContractLifecycleController;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Debug/Test Routes
|--------------------------------------------------------------------------
*/
Route::get('/test-email', function () {

    Mail::html('
        <div style="font-family: Arial, sans-serif; background:#f4f8fb; padding:40px;">
            <div style="max-width:600px; margin:auto; background:white; padding:30px; border-radius:12px; box-shadow:0 5px 20px rgba(0,0,0,0.08);">
                
                <h2 style="text-align:center; color:#e67e22; margin-bottom:5px;">
                    🌊 Cant Wait to Have Fun WIT U <3 🌅
                </h2>

                <p style="text-align:center; color:#555; margin-top:0;">
                    Pantai Kuta, Bali
                </p>

                <hr style="border:none; border-top:1px solid #eee; margin:20px 0;">

                <p>Kepada Yth,</p>
                <p><strong>Mr. RYan Aditya Putra </strong></p>

                <p>
                    Dengan penuh sukacita, kami mengundang Anda untuk menghadiri
                    pesta pantai yang akan diselenggarakan pada:
                </p>

                <table style="width:100%; margin:20px 0; font-size:14px;">
                    <tr>
                        <td><strong>📅 Tanggal</strong></td>
                        <td>: 25 Februari 2026</td>
                    </tr>
                    <tr>
                        <td><strong>📍 Lokasi</strong></td>
                        <td>: Pantai Kuta, Bali</td>
                    </tr>
                    <tr>
                        <td><strong>⏰ Waktu</strong></td>
                        <td>: 16.30 WITA – selesai</td>
                    </tr>
                </table>

                <p>
                    Acara ini akan menjadi momen kebersamaan yang istimewa bersama 
                    <strong>Jesslyn</strong>, menikmati senja Pantai Kuta,
                    alunan musik, dan suasana tropis yang penuh keceriaan.
                </p>

                <p style="margin-top:20px;">
                    <strong>Dress Code:</strong> Beach outfit (Putih / Pastel)
                </p>

                <div style="text-align:center; margin:30px 0;">
                    <a href="https://maps.google.com" 
                       style="background:#e67e22; color:white; padding:12px 25px; 
                              text-decoration:none; border-radius:8px; display:inline-block;">
                        📍 Lihat Lokasi
                    </a>
                </div>

                <p>
                    Merupakan suatu kebahagiaan bagi kami apabila Anda dapat hadir 
                    dan turut memeriahkan acara ini.
                </p>

                <p style="margin-top:30px;">
                    Hormat kami,<br>
                    <strong>Jesslyn & Panitia</strong>
                </p>

            </div>
        </div>
    ', function ($message) {
        $message->to('ryan.aditya.pn@gmail.com')
                ->subject('🌊 Undangan Pesta Pantai Kuta Bali - 25 Februari');
    });

    return 'Undangan berhasil dikirim 🎉';
});

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

Route::post('/hrms-login', [HrmsLoginController::class, 'login'])
    ->middleware('guest')
    ->name('hrms.login');

/*
|--------------------------------------------------------------------------
| Auth Routes (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Protected Routes - GENERAL (Authenticated Users)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/dashboard/data', [ContractController::class, 'getDashboardData'])
        ->name('dashboard.data');

    /*
    |--------------------------------------------------------------------------
    | Profile Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->group(function () {
        Route::get('/dark', [ProfileController::class, 'editDark'])
            ->name('profile.edit.dark');
        Route::get('/edit', [ProfileController::class, 'edit'])
            ->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])
            ->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])
            ->name('profile.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | My Reviews
    |--------------------------------------------------------------------------
    */
    Route::get('/my-reviews', [ReviewStageController::class, 'myReviews'])
        ->name('reviews.my-reviews');

    /*
    |--------------------------------------------------------------------------
    | Report Routes - Semua Role (Authenticated)
    |--------------------------------------------------------------------------
    */
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])
            ->name('index');
        Route::get('/contracts', [ReportController::class, 'contracts'])
            ->name('contracts');
        Route::get('/print', [ReportController::class, 'print'])
            ->name('print');
        Route::get('/export-excel', [ReportController::class, 'exportExcel'])
            ->name('export-excel');
    });

    /*
    |--------------------------------------------------------------------------
    | Contract Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('contracts')->group(function () {

        // Static routes first (no wildcard)
        Route::get('/create', [ContractController::class, 'create'])
            ->name('contracts.create');

        Route::get('/', [ContractController::class, 'index'])
            ->name('contracts.index');

        Route::post('/', [ContractController::class, 'store'])
            ->name('contracts.store');

        // Wildcard routes below
        Route::post('/{contract}/generate-number', [ReviewStageController::class, 'generateNumber'])
            ->name('contracts.generate-number');

        Route::get('/{contract}/chat', [ContractController::class, 'chat'])
            ->name('chat');

        Route::get('/{contract}/start-review-dynamic', [ReviewStageController::class, 'showStartReviewDynamic'])
            ->middleware(['can:start-review,contract'])
            ->name('contracts.start-review-dynamic');

        Route::post('/{contract}/start-review-dynamic', [ReviewStageController::class, 'processStartReviewDynamic'])
            ->middleware(['can:start-review,contract'])
            ->name('contracts.process-start-review-dynamic');

        Route::get('/{contract}/add-stage', [ReviewStageController::class, 'showAddStageForm'])
            ->name('contracts.add-stage-form');

        Route::post('/{contract}/add-stage', [ReviewStageController::class, 'addStageMidReview'])
            ->name('contracts.add-stage');

        Route::post('/{contract}/reorder-stages', [ReviewStageController::class, 'reorderStages'])
            ->name('contracts.reorder-stages');

        Route::post('/{contract}/add-legal-reviewer', [ContractDepartmentController::class, 'addLegalReviewer'])
            ->middleware('role:legal|admin')
            ->name('contracts.add-legal-reviewer');

        Route::post('/{contract}/submit', [ContractController::class, 'submit'])
            ->middleware('can:submit,contract')
            ->name('contracts.submit');

        Route::post('/{contract}/cancel', [ContractController::class, 'cancel'])
            ->middleware('can:cancel,contract')
            ->name('contracts.cancel');

        Route::post('/{contract}/return-to-draft', [ContractController::class, 'returnToDraft'])
            ->middleware('role:admin')
            ->name('contracts.return-to-draft');

        Route::post('/{contract}/mark-uploaded', [ContractController::class, 'markDocumentUploaded'])
            ->middleware('role:legal|admin')
            ->name('contracts.mark-uploaded');

        Route::post('/{contract}/submit-revision', [ContractController::class, 'submitRevision'])
            ->name('contracts.submit-revision');
        
        Route::post('/{contract}/execute', [ContractLifecycleController::class, 'markAsExecuted'])
            ->name('contracts.execute');

        Route::post('/{contract}/archive', [ContractLifecycleController::class, 'markAsArchived'])
            ->name('contracts.archive');
        
        Route::post('/{contract}/update-synology-path', [ContractController::class, 'updateSynologyPath'])
            ->middleware('role:admin')
            ->name('contracts.update-synology-path');

        Route::get('/{contract}/edit', [ContractController::class, 'edit'])
            ->name('contracts.edit');

        Route::put('/{contract}', [ContractController::class, 'update'])
            ->name('contracts.update');

        Route::delete('/{contract}', [ContractController::class, 'destroy'])
            ->name('contracts.destroy');

        Route::post('/contracts/{id}/legal-comment', [ContractController::class, 'storeLegalComment'])
        ->middleware('role:legal|admin')
        ->name('contracts.legal-comment');

        // SHOW - paling bawah supaya tidak bentrok
        Route::get('/{contract}', [ContractController::class, 'show'])
            ->name('contracts.show');
    });

    /*
    |--------------------------------------------------------------------------
    | Surat Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('surat')->name('surat.')->group(function () {

        // Static routes first
        Route::get('/create', [SuratController::class, 'create'])->name('create');
        Route::post('/store', [SuratController::class, 'store'])->name('store');

        // Wildcard routes last
        Route::get('/{contract}/preview', [SuratController::class, 'preview'])->name('preview');
        Route::get('/{contract}/download', [SuratController::class, 'download'])->name('download');

        // Temukan route group surat Anda, lalu tambahkan:
        Route::post('/{contract}/send-note', [SuratController::class, 'sendNote'])->name('send-note');


        Route::post('/{contract}/submit', [SuratController::class, 'submit'])->name('submit');

        Route::post('/{contract}/approve', [SuratController::class, 'approve'])
            ->middleware('role:legal|admin')
            ->name('approve');

        Route::post('/{contract}/generate-number', [SuratController::class, 'generateNumber'])
            ->middleware('role:legal|admin')
            ->name('generate-number');

        Route::post('/{contract}/upload', [SuratController::class, 'uploadFile'])->name('upload');
        Route::delete('/{contract}/delete-file', [SuratController::class, 'deleteFile'])->name('delete-file');

        Route::delete('/{contract}', [SuratController::class, 'destroy'])->name('destroy');

        Route::get('/{contract}', [SuratController::class, 'show'])->name('show');
    });

    /*
    |--------------------------------------------------------------------------
    | Notification Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/check', [NotificationController::class, 'check'])->name('check');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::delete('/', [NotificationController::class, 'clearAll'])->name('clear-all');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Review Stage Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('contracts/{contract}/review-stages')->group(function () {
        Route::get('/{stage}', [ReviewStageController::class, 'show'])
            ->name('review-stages.show');

        Route::get('/{stage}/user', [ReviewStageController::class, 'showUserStage'])
            ->name('review-stages.user');

        Route::post('/{stage}/start', [ReviewStageController::class, 'startReview'])
            ->name('review-stages.start');

        Route::post('/{stage}/approve-jump', [ReviewStageController::class, 'approveWithJump'])
            ->name('review-stages.approve-jump');

        Route::post('/{stage}/request-revision', [ReviewStageController::class, 'requestRevisionJump'])
            ->name('review-stages.request-revision');

        Route::post('/{stage}/reject', [ReviewStageController::class, 'reject'])
            ->name('review-stages.reject');

        Route::post('/{stage}/save-notes', [ReviewStageController::class, 'saveNotes'])
            ->name('review-stages.save-notes');

        Route::post('/{stage}/user-continue-review', [ReviewStageController::class, 'userContinueReview'])
            ->name('review-stages.user-continue-review');

        Route::post('/{stage}/user-request-clarification', [ReviewStageController::class, 'userRequestClarification'])
            ->name('review-stages.user-request-clarification');

        Route::delete('/stages/{reviewStage}/remove', [ReviewStageController::class, 'removeStage'])
            ->name('stages.remove');
    });

    /*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('api')->group(function () {
        Route::get('/notifications', function () {
            $user = auth()->user();
            $notifications = $user->notifications()
                ->orderBy('created_at', 'desc')
                ->take(20)
                ->get()
                ->map(function ($notification) {
                    $data = $notification->data;
                    return [
                        'id'         => $notification->id,
                        'title'      => $data['type'] ?? 'Notification',
                        'message'    => $data['message'] ?? 'You have a new notification',
                        'icon'       => $data['icon'] ?? 'fa-bell',
                        'action_url' => $data['action_url'] ?? $data['url'] ?? '#',
                        'read_at'    => $notification->read_at,
                        'created_at' => $notification->created_at->diffForHumans(),
                        'is_unread'  => is_null($notification->read_at),
                    ];
                });

            return response()->json([
                'notifications' => $notifications,
                'unread_count'  => $user->unreadNotifications()->count(),
            ]);
        });

        Route::post('/notifications/{notification}/read', function ($notificationId) {
            $notification = auth()->user()->notifications()
                ->where('id', $notificationId)
                ->first();

            if ($notification) {
                $notification->markAsRead();
            }

            return response()->json(['success' => true]);
        });

        Route::get('/users-by-role/{role}', [ReviewStageController::class, 'getUsersByRole'])
            ->name('api.users.by-role');

        Route::get('/contracts/{contract}/review-progress', [ContractController::class, 'getReviewProgress'])
            ->name('api.contracts.review-progress');

        Route::post('/review-stages/{stage}/update-notes', [ReviewStageController::class, 'updateStageNotes'])
            ->name('api.stages.update-notes');

        Route::get('/contracts/{contract}/stages', [ReviewStageController::class, 'getContractStages'])
            ->name('api.contracts.stages');
    });

    /*
    |--------------------------------------------------------------------------
    | Debug Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/debug-staff-data', [DashboardController::class, 'debugStaffData'])
        ->name('debug.staff.data');

    Route::get('/check-my-role', function () {
        $user = auth()->user();
        return response()->json([
            'user_id'              => $user->id,
            'name'                 => $user->name,
            'roles'                => $user->getRoleNames(),
            'is_staff_accounting'  => $user->hasRole('staff_acc'),
            'is_staff_finance'     => $user->hasRole('staff_fin'),
            'is_staff_tax'         => $user->hasRole('staff_tax'),
            'current_route'        => request()->path(),
        ]);
    });

}); // END auth middleware group

/*
|--------------------------------------------------------------------------
| Legal Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:legal|admin'])
    ->prefix('legal')
    ->name('legal.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return redirect()->route('dashboard');
        })->name('dashboard');

        Route::get('/contracts/create', [ContractController::class, 'createForLegal'])
            ->name('contracts.create');

        Route::get('/contracts/{contract}/edit', [ContractController::class, 'editForLegal'])
            ->name('contracts.edit');

        Route::get('/contracts/{contract}', [ContractController::class, 'showForLegal'])
            ->name('contracts.show');

        Route::get('/contracts', [ContractController::class, 'indexForLegal'])
            ->name('contracts.index');

        // DIHAPUS dari sini — reports.index sekarang sudah ada di group auth umum
    });

/*
|--------------------------------------------------------------------------
| Legal Workflow Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:legal|admin'])
    ->prefix('legal/workflow')
    ->name('legal.workflow.')
    ->group(function () {
        Route::get('/{contract}', [EditWorkflowController::class, 'edit'])
            ->name('edit');
        Route::post('/{contract}/update', [EditWorkflowController::class, 'update'])
            ->name('update');
        Route::delete('/{contract}/stage/{stage}', [EditWorkflowController::class, 'deleteReviewer'])
            ->name('delete');
    });

/*
|--------------------------------------------------------------------------
| Staff Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:staff_acc'])
    ->prefix('accounting-staff')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'accountingStaff'])
            ->name('accounting-staff.dashboard');
        Route::get('/my-reviews', function () {
            return redirect()->route('reviews.my-reviews');
        })->name('accounting-staff.my-reviews');
    });

Route::middleware(['auth', 'role:staff_fin'])
    ->prefix('finance-staff')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'financeStaff'])
            ->name('finance-staff.dashboard');
        Route::get('/my-reviews', function () {
            return redirect()->route('reviews.my-reviews');
        })->name('finance-staff.my-reviews');
    });

Route::middleware(['auth', 'role:staff_tax'])
    ->prefix('tax-staff')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'taxStaff'])
            ->name('tax-staff.dashboard');
        Route::get('/my-reviews', function () {
            return redirect()->route('reviews.my-reviews');
        })->name('tax-staff.my-reviews');
    });

/*
|--------------------------------------------------------------------------
| Department Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin_fin|admin'])
    ->prefix('finance-admin')
    ->name('finance-admin.')
    ->group(function () {
        Route::get('/dashboard', [DepartmentAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/pending', [DepartmentAdminController::class, 'pendingAssignments'])->name('pending');
        Route::get('/active', [DepartmentAdminController::class, 'activeReviews'])->name('active');
        Route::get('/completed', [DepartmentAdminController::class, 'completedReviews'])->name('completed');
        Route::get('/assign/{contractDepartment}', [DepartmentAdminController::class, 'showAssignForm'])->name('assign');
        Route::post('/assign/{contractDepartment}', [DepartmentAdminController::class, 'assignStaff'])->name('assign.post');
    });

Route::middleware(['auth', 'role:admin_acc|admin'])
    ->prefix('accounting-admin')
    ->name('accounting-admin.')
    ->group(function () {
        Route::get('/dashboard', [DepartmentAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/pending', [DepartmentAdminController::class, 'pendingAssignments'])->name('pending');
        Route::get('/active', [DepartmentAdminController::class, 'activeReviews'])->name('active');
        Route::get('/completed', [DepartmentAdminController::class, 'completedReviews'])->name('completed');
        Route::get('/assign/{contractDepartment}', [DepartmentAdminController::class, 'showAssignForm'])->name('assign');
        Route::post('/assign/{contractDepartment}', [DepartmentAdminController::class, 'assignStaff'])->name('assign.post');
    });

Route::middleware(['auth', 'role:admin_tax|admin'])
    ->prefix('tax-admin')
    ->name('tax-admin.')
    ->group(function () {
        Route::get('/dashboard', [DepartmentAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/pending', [DepartmentAdminController::class, 'pendingAssignments'])->name('pending');
        Route::get('/active', [DepartmentAdminController::class, 'activeReviews'])->name('active');
        Route::get('/completed', [DepartmentAdminController::class, 'completedReviews'])->name('completed');
        Route::get('/assign/{contractDepartment}', [DepartmentAdminController::class, 'showAssignForm'])->name('assign');
        Route::post('/assign/{contractDepartment}', [DepartmentAdminController::class, 'assignStaff'])->name('assign.post');
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
        Route::get('/dashboard', function () {
            return redirect()->route('dashboard');
        })->name('dashboard');

        // User Management
        Route::prefix('users')->group(function () {
            Route::get('/create', [UserController::class, 'create'])->name('users.create');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::get('/{user}', [UserController::class, 'show'])->name('users.show');
            Route::get('/', [UserController::class, 'index'])->name('users.index');
            Route::post('/', [UserController::class, 'store'])->name('users.store');
            Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        // Contract Management
        Route::get('/contracts/create', [ContractController::class, 'createForAdmin'])->name('contracts.create');
        Route::get('/contracts/{contract}/edit', [ContractController::class, 'editForAdmin'])->name('contracts.edit');
        Route::get('/contracts/{contract}', [ContractController::class, 'showForAdmin'])->name('contracts.show');
        Route::get('/contracts', [ContractController::class, 'indexForAdmin'])->name('contracts.index');

        // Settings
        Route::get('/settings', function () {
            return view('admin.settings');
        })->name('settings');

        // Master Departments
        Route::prefix('master-departments')->name('master-departments.')->group(function () {
            Route::get('/', [MasterDepartmentController::class, 'index'])->name('index');
            Route::get('/create', [MasterDepartmentController::class, 'create'])->name('create');
            Route::post('/', [MasterDepartmentController::class, 'store'])->name('store');
            Route::get('/{master_department}/edit', [MasterDepartmentController::class, 'edit'])->name('edit');
            Route::put('/{master_department}', [MasterDepartmentController::class, 'update'])->name('update');
            Route::delete('/{master_department}', [MasterDepartmentController::class, 'destroy'])->name('destroy');
        });
    });

/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    if (auth()->check()) {
        $user = auth()->user();

        if ($user->hasRole('staff_acc')) {
            return redirect()->route('accounting-staff.dashboard');
        } elseif ($user->hasRole('staff_fin')) {
            return redirect()->route('finance-staff.dashboard');
        } elseif ($user->hasRole('staff_tax')) {
            return redirect()->route('tax-staff.dashboard');
        } else {
            return redirect()->route('dashboard');
        }
    }

    return redirect()->route('login');
});