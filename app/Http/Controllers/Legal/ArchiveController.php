<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Archive;
use App\Models\ArchiveCrossReference;
use App\Services\ArchiveRecordIdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArchiveController extends Controller
{
    /**
     * ==========================================
     * LIST ARCHIVES
     * ==========================================
     */
    public function index(Request $request)
    {
        $query = Archive::query();

        if ($request->doc_name) {
            $query->where('doc_name', 'like', '%' . $request->doc_name . '%');
        }

        if ($request->department) {
            $query->where('department_code', $request->department);
        }

        if ($request->doc_type) {
            $query->where('doc_type', $request->doc_type);
        }

        $archives = $query->latest()->paginate(15);

        return view('archives.index', compact('archives'));
    }

    /**
     * ==========================================
     * CREATE PAGE
     * ==========================================
     */
public function create()
{
    $departments = [
        'LG' => 'Legal',
        'HR' => 'HRD',
        'OP' => 'Operation',
        'AC' => 'Accounting',
        'FN' => 'Finance',
        'TX' => 'Tax',
        'EX' => 'Exim',
        'CC' => 'CorCom',
        'NP' => 'Nickel Ore',
        'HE' => 'HSE',
        'CP' => 'Coal',
        'SL' => 'Sales',
        'PC' => 'Purchasing',
        'IT' => 'IT',
        'GA' => 'GA',
        'DK' => 'Direksi & Komisaris',
    ];

    // ✅ TAMBAHKAN INI — untuk dropdown cross reference
    $archiveList = Archive::select('id', 'record_id', 'doc_name')
        ->orderBy('record_id')
        ->get()
        ->map(function ($a) {
            return [
                'id'    => $a->id,
                'label' => $a->record_id . ' — ' . $a->doc_name,
            ];
        });

    return view('archives.create', [
        'departments'   => $departments,
        'docTypes'      => Archive::DOC_TYPES,
        'docStatus'     => Archive::DOC_STATUS,
        'versionStatus' => Archive::VERSION_STATUS,
        'archiveList'   => $archiveList,  
    ]);
}

    /**
     * ==========================================
     * GENERATE RECORD ID (AJAX)
     * ==========================================
     */
    public function generateRecordId(Request $request, ArchiveRecordIdService $recordService)
    {
    $request->validate([
        'company'    => 'required|in:GNI,AMI',
        'year'       => 'required',
        'department' => 'required',
        'doc_type'   => 'required'
    ]);

    $recordId = $recordService->generate(
        $request->company,
        $request->year,
        $request->department, 
        $request->doc_type
    );

        return response()->json([
            'record_id' => $recordId
        ]);
    }

    /**
     * ==========================================
     * STORE ARCHIVE
     * ==========================================
     */
public function store(Request $request)
{
    $request->validate([
        'record_id'      => 'required|string|max:255|unique:archives,record_id',
        'doc_number'     => 'nullable|string|max:255',
        'doc_name'       => 'required|string|max:255',
        'company'        => 'required|in:GNI,AMI',
        'doc_type'       => 'required|in:' . implode(',', array_keys(Archive::DOC_TYPES)),
        'department'     => 'required|string|max:100',
        'counterparty'   => 'nullable|string|max:255',
        'description'    => 'nullable|string|max:500',
        'doc_status'     => 'required|array|min:1',
        'doc_status.*'   => 'in:' . implode(',', Archive::DOC_STATUS),
        'version_status' => 'required|in:' . implode(',', Archive::VERSION_STATUS),
        'start_date'     => 'nullable|date',
        'end_date'       => 'nullable|date|after_or_equal:start_date',
        'doc_location'   => 'nullable|string|max:255',
        'synology_path'  => 'nullable|string|max:255',
    ]);

    try {

        DB::beginTransaction();

        $archive = Archive::create([
            'record_id'      => $request->record_id,
            'doc_number'     => $request->doc_number,
            'doc_name'       => $request->doc_name,
            'company'        => $request->company,
            'doc_type'       => $request->doc_type,
            'department_code'     => $request->department,
            'counterparty'   => $request->counterparty,
            'description'    => $request->description,
            'doc_status'     => $request->doc_status, // array, disimpan sebagai JSON
            'version_status' => $request->version_status,
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date,
            'doc_location'   => $request->doc_location,
            'synology_path'  => $request->synology_path,
            'created_by'     => Auth::id(),
        ]);

        // ======================
        // CROSS REFERENCES
        // ======================
        if ($request->filled('ref_doc_name')) {
            foreach ($request->ref_doc_name as $index => $docName) {

                // Skip baris kosong
                if (empty(trim($docName))) continue;

                ArchiveCrossReference::create([
                    'archive_id'    => $archive->id,
                    'ref_doc_name'  => $docName,
                    'ref_record_id' => $request->ref_record_id[$index] ?? null,
                    'ref_location'  => $request->ref_location[$index] ?? null,
                    'ref_relation'  => $request->ref_relation[$index] ?? null,
                ]);
            }
        }

        DB::commit();

        return redirect()
            ->route('archives.index')
            ->with('success', 'Archive created successfully');

    } catch (\Exception $e) {

        DB::rollBack();

        Log::error('Archive creation failed', [
            'error' => $e->getMessage()
        ]);

        return back()->withInput()->with('error', 'Failed to create archive');
    }
}

    /**
     * ==========================================
     * SHOW DETAIL
     * ==========================================
     */
    public function show($id)
    {
        $archive = Archive::with('crossReferences')->findOrFail($id);

        return view('archives.show', compact('archive'));
    }

    /**
     * ==========================================
     * EDIT PAGE
     * ==========================================
     */
    public function edit($id)
    {
        $archive = Archive::with('crossReferences')->findOrFail($id);

        return view('archives.edit', [
            'archive'       => $archive,
            'docTypes'      => Archive::DOC_TYPES,
            'docStatus'     => Archive::DOC_STATUS,
            'versionStatus' => Archive::VERSION_STATUS,
        ]);
    }

    /**
     * ==========================================
     * UPDATE ARCHIVE
     * ==========================================
     */
    public function update(Request $request, $id)
    {
        $archive = Archive::findOrFail($id);

        $request->validate([
            'record_id'      => 'required|string|max:255|unique:archives,record_id,' . $id,
            'doc_number'     => 'nullable|string|max:255',
            'doc_name'       => 'required|string|max:255',
            'company'        => 'required|in:GNI,AMI',
            'doc_type'       => 'required|in:' . implode(',', array_keys(Archive::DOC_TYPES)),
            'department'     => 'required|string|max:100',
            'counterparty'   => 'nullable|string|max:255',
            'description'    => 'nullable|string|max:500',
            'doc_status'     => 'required|array|min:1',
            'doc_status.*'   => 'in:' . implode(',', Archive::DOC_STATUS),
            'version_status' => 'required|in:' . implode(',', Archive::VERSION_STATUS),
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'doc_location'   => 'nullable|string|max:255',
            'synology_path'  => 'nullable|string|max:255',
        ]);

        try {

            DB::beginTransaction();

            $archive->update([
                'record_id'      => $request->record_id,
                'doc_number'     => $request->doc_number,
                'doc_name'       => $request->doc_name,
                'company'        => $request->company,
                'doc_type'       => $request->doc_type,
                'department_code'     => $request->department,
                'counterparty'   => $request->counterparty,
                'description'    => $request->description,
                'doc_status'     => $request->doc_status, // array, disimpan sebagai JSON
                'version_status' => $request->version_status,
                'start_date'     => $request->start_date,
                'end_date'       => $request->end_date,
                'doc_location'   => $request->doc_location,
                'synology_path'  => $request->synology_path,
            ]);

            // ======================
            // RESET DAN SIMPAN ULANG CROSS REFERENCES
            // ======================
            ArchiveCrossReference::where('archive_id', $archive->id)->delete();

            if ($request->filled('ref_doc_name')) {
                foreach ($request->ref_doc_name as $index => $docName) {

                    // Skip baris kosong
                    if (empty(trim($docName))) continue;

                    ArchiveCrossReference::create([
                        'archive_id'    => $archive->id,
                        'ref_doc_name'  => $docName,
                        'ref_record_id' => $request->ref_record_id[$index] ?? null,
                        'ref_location'  => $request->ref_location[$index] ?? null,
                        'ref_relation'  => $request->ref_relation[$index] ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('archives.index')
                ->with('success', 'Archive updated successfully');

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Archive update failed', [
                'error' => $e->getMessage()
            ]);

            return back()->withInput()->with('error', 'Failed to update archive');
        }
    }

    /**
     * ==========================================
     * DELETE ARCHIVE
     * ==========================================
     */
    public function destroy($id)
    {
        try {

            $archive = Archive::findOrFail($id);
            $archive->delete();

            return redirect()
                ->route('archives.index')
                ->with('success', 'Archive deleted successfully');

        } catch (\Exception $e) {

            Log::error('Archive delete failed', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to delete archive');
        }
    }
}
