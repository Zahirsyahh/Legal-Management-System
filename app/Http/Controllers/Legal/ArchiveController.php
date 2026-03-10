<?php

namespace App\Http\Controllers;

use App\Models\Archive;
use App\Services\ArchiveRecordIdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ArchiveController extends Controller
{
    /**
     * ==========================================
     * LIST ARCHIVES
     * ==========================================
     */
    public function index()
    {
        $archives = Archive::latest()->paginate(15);

        return view('archive.index', compact('archives'));
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

            // Direksi & Komisaris share kode yang sama
            'DK_DIR' => [
                'label' => 'Direksi',
                'code' => 'DK'
            ],

            'DK_KOM' => [
                'label' => 'Komisaris',
                'code' => 'DK'
            ],
        ];

        $docTypes = Archive::select('doc_type')
                    ->distinct()
                    ->pluck('doc_type');

        return view('archive.create', compact('departments','docTypes'));
    }

    //generate record id otomatis berdasarkan tahun, departemen, dan jenis dokumen
    public function generateRecordId(Request $request, ArchiveRecordIdService $recordService)
    {
        $request->validate([
            'year' => 'required',
            'department' => 'required',
            'doc_type' => 'required'
        ]);

        $recordId = $recordService->generate(
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
            'record_id' => 'required|string|max:255|unique:archives,record_id',
            'doc_number' => 'nullable|string|max:255',
            'doc_name' => 'required|string|max:255',
            'doc_type' => 'required|string|max:10',
            'department' => 'required|string|max:100',
            'counterparty' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'doc_status' => 'required|in:copy,scancopy,hardcopy,born-digital',
            'version_status' => 'required|in:active,obsolete,superseded,terminate',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'doc_location' => 'nullable|string|max:255',
            'synology_path' => 'nullable|string|max:255',
        ]);

        try {

            Archive::create([
                'record_id' => $request->record_id,
                'doc_number' => $request->doc_number,
                'doc_name' => $request->doc_name,
                'doc_type' => $request->doc_type,
                'department' => $request->department,
                'counterparty' => $request->counterparty,
                'description' => $request->description,
                'doc_status' => $request->doc_status,
                'version_status' => $request->version_status,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'doc_location' => $request->doc_location,
                'synology_path' => $request->synology_path,
                'created_by' => Auth::user()->id_user,
            ]);

            return redirect()
                ->route('archive.index')
                ->with('success', 'Archive created successfully');

        } catch (\Exception $e) {

            Log::error('Archive creation failed', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to create archive');
        }
    }

    /**
     * ==========================================
     * SHOW DETAIL
     * ==========================================
     */
    public function show($id)
    {
        $archive = Archive::findOrFail($id);

        return view('archive.show', compact('archive'));
    }

    /**
     * ==========================================
     * EDIT PAGE
     * ==========================================
     */
    public function edit($id)
    {
        $archive = Archive::findOrFail($id);

        $docTypes = Archive::DOC_TYPES;
        $docStatus = Archive::DOC_STATUS;
        $versionStatus = Archive::VERSION_STATUS;

        return view('archive.edit', compact(
            'archive',
            'docTypes',
            'docStatus',
            'versionStatus'
        ));
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
            'record_id' => 'required|string|max:255|unique:archives,record_id,' . $id,
            'doc_number' => 'nullable|string|max:255',
            'doc_name' => 'required|string|max:255',
            'doc_type' => 'required|string|max:10',
            'department' => 'required|string|max:100',
            'counterparty' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'doc_status' => 'required|in:copy,scancopy,hardcopy,born-digital',
            'version_status' => 'required|in:active,obsolete,superseded,terminate',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'doc_location' => 'nullable|string|max:255',
            'synology_path' => 'nullable|string|max:255',
        ]);

        try {

            $archive->update($request->all());

            return redirect()
                ->route('archive.index')
                ->with('success', 'Archive updated successfully');

        } catch (\Exception $e) {

            Log::error('Archive update failed', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to update archive');
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
                ->route('archive.index')
                ->with('success', 'Archive deleted successfully');

        } catch (\Exception $e) {

            Log::error('Archive delete failed', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to delete archive');
        }
    }
}
