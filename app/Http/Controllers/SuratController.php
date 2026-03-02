<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Contract;
use App\Models\ContractReviewLog;
use App\Services\ContractNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SuratNoteNotification;
use App\Notifications\ContractRejectedNotification;
use App\Notifications\DocumentSubmittedNotification;
use App\Notifications\ContractNumberGeneratedNotification;
use App\Notifications\SuratApprovedNotification;
use App\Notifications\SuratFileUploadedNotification;
use App\Notifications\SuratReleasedNotification;
use App\Models\TblUser;

class SuratController extends Controller
{
    protected ContractNumberService $numberService;

    public function __construct(ContractNumberService $numberService)
    {
        $this->numberService = $numberService;
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE FORM - FIXED VERSION
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $user = auth()->user();

        $departmentCode = null;
        $departmentName = null;
        $hasError = false;
        $errorMessage = null;

        try {
            if ($user && $user->email) {
                $hrmsUser = DB::table('tbl_user')
                    ->where('email', $user->email)
                    ->first(['kode_department', 'nama_department']);

                if ($hrmsUser) {
                    $departmentCode = $hrmsUser->kode_department ?? null;
                    $departmentName = $hrmsUser->nama_department ?? null;
                    
                    // ✅ WARNING jika department kosong (tapi form tetap bisa dibuka)
                    if (empty($departmentCode)) {
                        $errorMessage = 'Your department code is not set in HRMS. Please contact HR department before submitting.';
                        $hasError = true;
                    }
                } else {
                    $errorMessage = 'Your account is not found in HRMS system. Please contact admin before submitting.';
                    $hasError = true;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error loading department for surat create', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            
            $errorMessage = 'Unable to load department information. You can still fill the form, but contact support if this persists.';
            $hasError = true;
        }

        return view('contracts.create-surat', compact(
            'departmentCode',
            'departmentName',
            'hasError',
            'errorMessage'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW SURAT REQUEST - FIXED VERSION
    |--------------------------------------------------------------------------
    */
    public function show(Contract $contract)
{
    // ============================================
    // LOGGING DEBUG - UNTUK MEMBANTU IDENTIFIKASI MASALAH
    // ============================================
    Log::info('=== SURAT SHOW ACCESSED ===', [
        'contract_id' => $contract->id,
        'contract_title' => $contract->title,
        'contract_type' => $contract->contract_type,
        'workflow_type' => $contract->workflow_type,
        'status' => $contract->status,
        'has_file' => !is_null($contract->surat_file_path),
        'file_path' => $contract->surat_file_path,
        'is_surat_request' => $contract->isSuratRequest(),
        'user_id' => Auth::id(),
        'user_email' => Auth::user()->email,
        'user_role' => Auth::user()->getRoleNames()->first(),
        'url' => request()->fullUrl(),
    ]);

    // ============================================
    // VALIDASI: PASTIKAN INI SURAT REQUEST
    // ============================================
    if (!$contract->isSuratRequest()) {
        Log::warning('SURAT ACCESS DENIED - Not a surat request', [
            'contract_id' => $contract->id,
            'contract_title' => $contract->title,
            'contract_type' => $contract->contract_type,
            'workflow_type' => $contract->workflow_type,
            'has_file' => !is_null($contract->surat_file_path),
            'user_id' => Auth::id(),
            'user_email' => Auth::user()->email,
            'reason' => 'isSuratRequest() returned false'
        ]);
        
        // OPSI 1: LANGSUNG 404
        abort(404, 'Bukan surat request nomor.');
        
        // OPSI 2: REDIRECT KE CONTRACT SHOW (comment salah satu)
        // return redirect()->route('contracts.show', $contract)
        //     ->with('info', 'Dokumen ini diakses sebagai kontrak, bukan surat.');
    }

    // ============================================
    // CEK PERMISSION AKSES
    // ============================================
    $user = Auth::user();
    
    if (!$this->canUserAccessSurat($contract, $user)) {
        Log::warning('SURAT ACCESS DENIED - Permission denied', [
            'contract_id' => $contract->id,
            'contract_title' => $contract->title,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_role' => $user->getRoleNames()->first(),
            'contract_owner_id' => $contract->user_id,
        ]);
        
        abort(403, 'You do not have permission to access this letter.');
    }

    // ============================================
    // CEK FILE EXISTS (UNTUK SURAT YANG SUDAH DIAPPROVE)
    // ============================================
    if (in_array($contract->status, [
        Contract::STATUS_SUBMITTED,
        Contract::STATUS_FINAL_APPROVED,
        Contract::STATUS_NUMBER_ISSUED,
        Contract::STATUS_RELEASED
    ]) && $contract->surat_file_path) {
        
        $fileExists = \Storage::disk('public')->exists($contract->surat_file_path);
        
        if (!$fileExists) {
            Log::error('SURAT FILE MISSING', [
                'contract_id' => $contract->id,
                'contract_title' => $contract->title,
                'file_path' => $contract->surat_file_path,
                'status' => $contract->status,
            ]);
            
            // Tampilkan warning tapi tetap lanjut
            session()->flash('warning', 'File surat tidak ditemukan di storage. Silakan upload ulang.');
        }
        
        Log::info('SURAT FILE CHECK', [
            'contract_id' => $contract->id,
            'file_exists' => $fileExists,
            'file_path' => $contract->surat_file_path,
            'file_size' => $contract->surat_file_size,
        ]);
    }

    // ============================================
    // LOAD RELATIONS
    // ============================================
    $contract->load([
        'user',
        'legalAssigned',
    ]);

    // ============================================
    // LOAD REVIEW LOGS
    // ============================================
    $reviewLogs = $contract->reviewLogs()
        ->with([
            'user:id_user,nama_user,email,jabatan',
            'stage:id,contract_id,stage_name,stage_type,sequence,assigned_user_id,status,is_user_stage',
            'stage.assignedUser:id_user,nama_user,email,jabatan'
        ])
        ->orderBy('created_at', 'desc')
        ->get();

    Log::info('SURAT SHOW SUCCESS', [
        'contract_id' => $contract->id,
        'contract_title' => $contract->title,
        'logs_count' => $reviewLogs->count(),
        'user_id' => Auth::id(),
    ]);

    // ============================================
    // RETURN VIEW
    // ============================================
    return view('contracts.show-surat', compact('contract', 'reviewLogs'));
}

    /**
     * Check if user can access surat request
     */
    private function canUserAccessSurat(Contract $contract, $user): bool
    {
        // Admin selalu bisa
        if ($user->hasRole('admin')) {
            return true;
        }

        // Legal selalu bisa
        if ($user->hasRole('legal')) {
            return true;
        }

        // Pemilik surat
        if ($contract->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | STORE (DRAFT) - FIXED VERSION
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
{
    Log::info('=== SURAT STORE START ===', [
        'user_id' => Auth::id(),
        'has_file' => $request->hasFile('surat_file'),
    ]);

    $request->validate([
        'title'           => 'required|string|max:255',
        'effective_date'  => 'required|date',
        'description'     => 'nullable|string',
        'surat_file'      => 'required|file|mimes:pdf|max:10240', // 10MB
        'department_code' => 'required|string|max:10',
    ]);

    DB::beginTransaction();

    try {
        // ✅ FILE CHECK
        if (!$request->hasFile('surat_file')) {
            throw new \Exception('File surat wajib diupload.');
        }

        $file = $request->file('surat_file');

        if (!$file->isValid()) {
            throw new \Exception('File upload gagal. Silakan coba lagi.');
        }

        // =========================================================
        // ✅ GENERATE NOMOR URUT RESET PER HARI (001_ddmmyy)
        // =========================================================

        $tanggal = \Carbon\Carbon::parse($request->effective_date);
        $formatTanggal = $tanggal->format('dmy'); // ddmmyy

        $countToday = Contract::where('contract_type', 'surat')
            ->whereDate('effective_date', $tanggal->toDateString())
            ->lockForUpdate()
            ->count();

        $sequence = str_pad($countToday + 1, 3, '0', STR_PAD_LEFT);

        $fileName = $sequence . '_' . $formatTanggal . '.pdf';

        // ✅ STORE FILE DENGAN NAMA CUSTOM
        $path = $file->storeAs('surat_files', $fileName, 'public');

        Log::info('File stored successfully', [
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $fileName,
            'stored_path' => $path,
            'size' => $file->getSize(),
        ]);

        // ✅ VERIFY FILE EXISTS
        if (!Storage::disk('public')->exists($path)) {
            throw new \Exception('File gagal tersimpan di storage.');
        }

        // =========================================================
        // ✅ CREATE CONTRACT
        // =========================================================

        $contract = Contract::create([
            'title'                => strtoupper($request->title),
            'description'          => $request->description,
            'effective_date'       => $request->effective_date,
            'contract_type'        => 'surat',
            'workflow_type'        => 'static',
            'status'               => Contract::STATUS_DRAFT,
            'user_id'              => Auth::id(),
            'department_code'      => strtoupper($request->department_code),
            'surat_file_path'      => $path,
            'surat_file_size'      => $file->getSize(),
            'allow_stage_addition' => false,
            'current_stage'        => 0,
        ]);

        Log::info('Contract created', [
            'contract_id' => $contract->id,
            'file_path'   => $contract->surat_file_path,
        ]);

        // ✅ LOG ACTIVITY
        ContractReviewLog::create([
            'contract_id' => $contract->id,
            'user_id'     => Auth::id(),
            'action'      => 'surat_created',
            'description' => 'Surat draft dibuat',
        ]);

        DB::commit();

        return redirect()
            ->route('surat.show', $contract)
            ->with('success', '✅ Draft surat berhasil dibuat. Silakan submit untuk approval Legal.');

    } catch (\Exception $e) {

        DB::rollBack();

        Log::error('Surat store failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return back()
            ->withInput()
            ->with('error', '❌ Failed to save letter: ' . $e->getMessage());
    }
}


    /*
    |--------------------------------------------------------------------------
    | SUBMIT (DRAFT → SUBMITTED) - FIXED
    |--------------------------------------------------------------------------
    */
    public function submit(Contract $contract)
    {
        if ($contract->contract_type !== 'surat') {
            abort(404);
        }

        if ($contract->status !== Contract::STATUS_DRAFT) {
            return back()->with('error', 'Hanya draft yang bisa disubmit.');
        }

        if (!$contract->surat_file_path) {
            return back()->with('error', 'File draft wajib ada.');
        }

        if (!Storage::disk('public')->exists($contract->surat_file_path)) {
            return back()->with('error', 'File tidak ditemukan di storage.');
        }

        DB::transaction(function () use ($contract) {

            $contract->update([
                'status' => Contract::STATUS_SUBMITTED,
                'submitted_at' => now(),
            ]);

            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'user_id'     => Auth::id(),
                'action'      => 'surat_submitted',
                'description' => 'Surat disubmit ke Legal',
            ]);

            // 🔥 KIRIM NOTIF KE LEGAL
            $legalUsers = TblUser::role('legal')->get();

            foreach ($legalUsers as $legal) {
                $legal->notify(
                    new DocumentSubmittedNotification($contract, Auth::user())
                );
            }
        });

        return back()->with('success', '✅ Letter successfully submitted for Legal approval.');
    }

    /*
    |--------------------------------------------------------------------------
    | LEGAL APPROVAL (SUBMITTED → FINAL_APPROVED)
    |--------------------------------------------------------------------------
    */
    public function approve(Contract $contract)
    {
        if ($contract->contract_type !== 'surat') {
            abort(404);
        }

        if (!auth()->user()->hasAnyRole(['admin', 'legal'])) {
            abort(403);
        }

        if ($contract->status !== Contract::STATUS_SUBMITTED) {
            return back()->with('error', 'Only submitted letters can be approved.');
        }

        DB::beginTransaction();

        try {

            $contract->update([
                'status'            => Contract::STATUS_FINAL_APPROVED,
                'final_approved_at' => now(),
                'final_approved_by' => auth()->id(),
            ]);

            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'user_id'     => Auth::id(),
                'action'      => 'surat_approved',
                'description' => 'Letter approved by Legal (awaiting number generation)',
            ]);

            /*
            |----------------------------------------------------------
            | SEND NOTIFICATION TO LETTER OWNER
            |----------------------------------------------------------
            */
            if ($contract->user) {
                $contract->user->notify(
                    (new SuratApprovedNotification(
                        $contract,
                        auth()->user()
                    ))->afterCommit()
                );
            }

            DB::commit();

            return back()->with(
                'success',
                'Letter successfully approved. You may now generate the letter number.'
            );

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Letter approval failed', [
                'contract_id' => $contract->id,
                'error'       => $e->getMessage(),
            ]);

            return back()->with(
                'error',
                'Failed to approve letter: ' . $e->getMessage()
            );
        }
    }

       /*
        |--------------------------------------------------------------------------
        | SEND NOTE TO USER (LEGAL ONLY) - Status tetap tidak berubah
        |--------------------------------------------------------------------------
        */
        public function sendNote(Request $request, Contract $contract)
        {
            // Validasi: hanya untuk surat
            if ($contract->contract_type !== 'surat') {
                abort(404);
            }

            // Hanya legal/admin yang boleh kirim notes
            if (!auth()->user()->hasAnyRole(['admin', 'legal'])) {
                abort(403, 'Only Legal or Admin can send notes.');
            }

            $request->validate([
                'notes' => 'required|string|min:10|max:2000',
            ], [
                'notes.required' => 'Catatan wajib diisi.',
                'notes.min'      => 'Catatan minimal 10 karakter.',
                'notes.max'      => 'Catatan maksimal 2000 karakter.',
            ]);

            DB::beginTransaction();

            try {
                $sender = \App\Models\TblUser::find(Auth::id());

                // Simpan ke contract_review_logs
                ContractReviewLog::create([
                    'contract_id' => $contract->id,
                    'user_id'     => Auth::id(),
                    'action'      => 'surat_note_sent',
                    'description' => 'Legal mengirim catatan kepada user',
                    'notes'       => $request->notes,
                    'metadata'    => [
                        'sender_name'  => $sender->nama_user ?? $sender->name,
                        'sender_email' => $sender->email,
                        'notes'        => $request->notes,
                        'sent_at'      => now()->toISOString(),
                    ],
                ]);

                DB::commit();

                // Kirim notifikasi ke pemilik surat
                if ($contract->user) {
                    try {
                        $contract->user->notify(
                            new \App\Notifications\SuratNoteNotification(
                                $contract,
                                $sender,
                                $request->notes
                            )
                        );
                    } catch (\Exception $e) {
                        Log::error('Failed to send note notification', [
                            'contract_id' => $contract->id,
                            'error'       => $e->getMessage(),
                        ]);
                    }
                }

                return back()->with('success', '✅ Catatan berhasil dikirim kepada ' . ($contract->user->nama_user ?? 'user') . '.');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Send note failed', [
                    'contract_id' => $contract->id,
                    'error'       => $e->getMessage(),
                ]);

                return back()->with('error', '❌ Gagal mengirim catatan: ' . $e->getMessage());
            }
        }

    /*
    |--------------------------------------------------------------------------
    | LEGAL GENERATE NOMOR (LEGAL APPROVED)
    |--------------------------------------------------------------------------
    */
    public function generateNumber(Contract $contract)
    {
        if ($contract->contract_type !== 'surat') {
            abort(404);
        }

        if (!auth()->user()->hasAnyRole(['admin', 'legal'])) {
            abort(403);
        }

        if ($contract->status !== Contract::STATUS_FINAL_APPROVED) {
            return back()->with('error',
                'Only approved letters can generate number. Please approve first.'
            );
        }

        DB::beginTransaction();

        try {

            // 🔒 (Optional tapi recommended)
            $contract->refresh();

            // Generate nomor
            $number = $this->numberService->generateForContract($contract);

            $contract->update([
                'contract_number'  => $number,
                'status'           => Contract::STATUS_NUMBER_ISSUED,
                'number_issued_at' => now(),
            ]);

            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'user_id'     => Auth::id(),
                'action'      => 'surat_number_generated',
                'description' => 'Nomor surat digenerate: ' . $number,
            ]);

            /*
            |----------------------------------------------------------
            | 🔥 KIRIM NOTIF KE PEMILIK SURAT
            |----------------------------------------------------------
            */
            if ($contract->user) {
                $contract->user->notify(
                    new ContractNumberGeneratedNotification(
                        $contract,
                        $number,
                        Auth::user()
                    )
                );
            }

            DB::commit();

            return back()->with(
                'success',
                '✅ Letter number generated successfully: ' . $number . ' (Status: Number Issued)'
            );

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Generate number failed', [
                'contract_id' => $contract->id,
                'error'       => $e->getMessage(),
            ]);

            return back()->with(
                'error',
                '❌ Failed to generate number: ' . $e->getMessage()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | USER UPLOAD FILE - FIXED VERSION
    |--------------------------------------------------------------------------
    */
    public function uploadFile(Request $request, Contract $contract)
    {   
        Log::info('=== UPLOAD FILE START ===', [
            'contract_id' => $contract->id,
            'current_status' => $contract->status,
            'has_file' => $request->hasFile('file'),
            'user_id' => Auth::id(),
        ]);
        
        if ($contract->contract_type !== 'surat') {
            abort(404);
        }

        if ($contract->status === Contract::STATUS_RELEASED) {
            return back()->with('error', 'This letter has already been released.');
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240'
        ]);

        DB::beginTransaction();

        try {

            $actor = Auth::user();
            $originalStatus = $contract->status;

            $file = $request->file('file');

            if (!$file || !$file->isValid()) {
                throw new \Exception('Invalid file upload.');
            }

            if ($contract->status === Contract::STATUS_NUMBER_ISSUED && $contract->contract_number) {
                $fileName = $contract->contract_number . '.pdf';
            } else {
                $fileName = 'draft_' . $contract->id . '.pdf';
            }

            if ($contract->surat_file_path && Storage::disk('public')->exists($contract->surat_file_path)) {
                Storage::disk('public')->delete($contract->surat_file_path);
            }

            $path = $file->storeAs('surat_files', $fileName, 'public');

            if (!Storage::disk('public')->exists($path)) {
                throw new \Exception('File storage failed.');
            }

            $updateData = [
                'surat_file_path' => $path,
                'surat_file_size' => $file->getSize(),
            ];

            $isReleasedNow = false;

            if ($originalStatus === Contract::STATUS_NUMBER_ISSUED) {
                $updateData['status'] = Contract::STATUS_RELEASED;
                $updateData['released_at'] = now();
                $isReleasedNow = true;
            }

            $contract->update($updateData);

            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'user_id' => $actor->id,
                'action' => 'file_uploaded',
                'description' => $isReleasedNow
                    ? 'Final file uploaded and released'
                    : 'File uploaded',
            ]);

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | SEND NOTIFICATION (AFTER COMMIT)
            |--------------------------------------------------------------------------
            */

            // Ambil semua user legal (sesuaikan role kamu)
            $legalUsers = TblUser::role('legal')->get();

            if ($isReleasedNow) {

                Notification::send(
                    $legalUsers,
                    new SuratReleasedNotification($contract, $actor)
                );

            } else {

                Notification::send(
                    $legalUsers,
                    new SuratFileUploadedNotification($contract, $actor)
                );
            }

            $successMessage = $isReleasedNow
                ? '✅ File uploaded successfully. Letter has been released!'
                : '✅ File uploaded successfully.';

            return back()->with('success', $successMessage);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Upload failed', [
                'contract_id' => $contract->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', '❌ Upload failed: ' . $e->getMessage());
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE FILE - FIXED
    |--------------------------------------------------------------------------
    */
    public function deleteFile(Contract $contract)
    {
        if ($contract->contract_type !== 'surat') {
            abort(404);
        }

        if ($contract->status === Contract::STATUS_RELEASED) {
            return back()->with('error', 'The letter has been released. Cannot delete the file.');
        }

        try {
            if ($contract->surat_file_path && Storage::disk('public')->exists($contract->surat_file_path)) {
                Storage::disk('public')->delete($contract->surat_file_path);
            }

            $contract->update([
                'surat_file_path' => null,
                'surat_file_size' => null,
            ]);

            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'user_id'     => Auth::id(),
                'action'      => 'file_deleted',
                'description' => 'File surat dihapus',
            ]);

            return back()->with('success', '✅ File has been deleted.');

        } catch (\Exception $e) {
            Log::error('Delete file failed', [
                'contract_id' => $contract->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', '❌ Failed to delete file: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PREVIEW FILE - NEW
    |--------------------------------------------------------------------------
    */
    public function preview(Contract $contract){
        if ($contract->contract_type !== 'surat' || !$contract->surat_file_path) {
            abort(404);
        }

        if (!Storage::disk('public')->exists($contract->surat_file_path)) {
            abort(404);
        }

        return redirect(Storage::disk('public')->url($contract->surat_file_path));
    }


    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD FILE - NEW
    |--------------------------------------------------------------------------
    */
    public function download(Contract $contract)
    {
        if ($contract->contract_type !== 'surat' || !$contract->surat_file_path) {
            abort(404);
        }

        if (!Storage::disk('public')->exists($contract->surat_file_path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        $fileName = $contract->contract_number 
            ? $contract->contract_number . '.pdf'
            : $contract->title . '.pdf';

        return Storage::disk('public')->download($contract->surat_file_path, $fileName);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE SURAT - VERSI BARU (dengan reason/notes wajib)
    | GANTIKAN method destroy() yang lama dengan ini
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, Contract $contract)
    {
        if ($contract->contract_type !== 'surat') {
            abort(404);
        }

        // Hanya owner (hanya untuk draft) atau admin/legal yang boleh hapus
        if ($contract->user_id !== Auth::id() && !Auth::user()->hasAnyRole(['admin', 'legal'])) {
            abort(403);
        }

        // Jika bukan owner draft, wajib isi alasan
        $isOwnerDraft = ($contract->user_id === Auth::id() && $contract->status === 'draft');

        if (!$isOwnerDraft) {
            $request->validate([
                'delete_reason' => 'required|string|min:10|max:1000',
            ], [
                'delete_reason.required' => 'Reason for deletion must be filled in.',
                'delete_reason.min'      => 'Reason minimum 10 characters.',
            ]);
        }

        DB::beginTransaction();

        try {
            $sender      = \App\Models\TblUser::find(Auth::id());
            $title       = $contract->title;
            $contractId  = $contract->id;
            $owner       = $contract->user; // simpan relasi sebelum delete
            $deleteReason = $request->delete_reason ?? 'Draft deleted by the owner';

            // Simpan log SEBELUM dihapus
            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'user_id'     => Auth::id(),
                'action'      => 'surat_deleted',
                'description' => 'Surat dihapus',
                'notes'       => $deleteReason,
                'metadata'    => [
                    'deleted_by'   => $sender->nama_user ?? $sender->name,
                    'deleted_by_email' => $sender->email,
                    'reason'       => $deleteReason,
                    'deleted_at'   => now()->toISOString(),
                    'old_status'   => $contract->status,
                ],
            ]);

            // Hapus file jika ada
            if ($contract->surat_file_path && Storage::disk('public')->exists($contract->surat_file_path)) {
                Storage::disk('public')->delete($contract->surat_file_path);
            }

            $contract->delete();

            DB::commit();

            // Kirim notifikasi ke pemilik surat (jika yang menghapus bukan pemiliknya)
            // Menggunakan ContractRejectedNotification yang sudah ada
            // Constructor: ($contract, $stage, $reason, $rejectedBy)
            // $stage = null karena surat tidak pakai review stage system
            if ($owner && $owner->id_user !== Auth::id()) {
                try {
                    $owner->notify(
                        new \App\Notifications\ContractRejectedNotification(
                            $contract,    // contract
                            null,         // stage (null, surat tidak pakai stage system)
                            $deleteReason, // reason
                            $sender       // rejectedBy
                        )
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to send delete notification', [
                        'contract_id' => $contractId,
                        'error'       => $e->getMessage(),
                    ]);
                }
            }

            return redirect()
                ->route('contracts.index')
                ->with('success', '✅ Letter "' . $title . '" successfully deleted.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete letter failed', [
                'contract_id' => $contract->id,
                'error'       => $e->getMessage(),
            ]);

            return back()->with('error', '❌ failed to delete letter: ' . $e->getMessage());
        }
    }
}