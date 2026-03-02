<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\TblUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\MasterDepartment;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ContractsExport;

class ReportController extends Controller
{
    /**
     * Constants untuk status contract
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_FINAL_APPROVED = 'final_approved';
    const STATUS_NUMBER_ISSUED = 'number_issued';
    const STATUS_RELEASED = 'released';
    const STATUS_REVISION_NEEDED = 'revision_needed';
    const STATUS_DECLINED = 'declined';
    
    /**
     * Constants untuk tipe dokumen (berdasarkan contract_type)
     */
    const TYPE_SURAT = 'surat';
    const TYPE_KONTRAK = 'kontrak';
    
    /**
     * Tampilkan halaman utama report
     */
    /**
     * Tampilkan halaman utama report
     */
    public function index()
    {
        $user = Auth::user();
        $userRole = $this->getUserRole($user);
        $userDept = $user->kode_department;
        
        // Ambil daftar status untuk filter
        $statuses = $this->getStatusList();
        
        // Ambil daftar tipe dokumen untuk filter
        $documentTypes = $this->getDocumentTypeList();
        
        // AMBIL DATA DEPARTMENTS DARI DATABASE
        $departments = MasterDepartment::orderBy('nama_departemen')->get();
        
        return view('reports.index', compact(
            'userRole', 
            'userDept', 
            'statuses', 
            'documentTypes',
            'departments' // KIRIM KE VIEW
        ));
    }
    
  public function contracts(Request $request)
{
    $user = Auth::user();
    $userRole = $this->getUserRole($user);
    $userDept = $user->kode_department;

    // Validasi
    $request->validate([
        'start_date'    => 'nullable|date',
        'end_date'      => 'nullable|date|after_or_equal:start_date',
        'status'        => 'nullable|string|in:' . implode(',', array_keys($this->getStatusList())),
        'contract_type' => 'nullable|string|in:surat,kontrak',
        'department'    => 'nullable|string|max:10',
    ]);

    $query = $this->buildContractsQuery($request, $user, $userRole);

    // ============================================
    // EXPORT (HARUS SEBELUM PAGINATE)
    // ============================================
    if ($request->boolean('export')) {
        $contracts = $query->get();
        return Excel::download(
            new ContractsExport($contracts),
            'document_reports_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    // ============================================
    // PAGINATION
    // ============================================
    $contracts = $query->paginate(20)->withQueryString();

    $statuses = $this->getStatusList();
    $documentTypes = $this->getDocumentTypeList();
    $departments = MasterDepartment::orderBy('nama_departemen')->get();

    $activeFilters = [
        'start_date'    => $request->start_date,
        'end_date'      => $request->end_date,
        'status'        => $request->status,
        'contract_type' => $request->contract_type,
        'department'    => $request->department,
    ];

    return view('reports.contracts', compact(
        'contracts',
        'statuses',
        'documentTypes',
        'userRole',
        'userDept',
        'activeFilters',
        'departments'
    ));
}
    
    /**
     * Export ke Excel/CSV
     */
    public function exportExcel(Request $request)
    {
        $request->merge(['export' => true]);
        return $this->contracts($request);
    }
    
    /**
     * Print report
     */
    public function print(Request $request)
{
    $user = Auth::user();
    $userRole = $this->getUserRole($user);
    $userDept = $user->kode_department;

    $request->validate([
        'start_date'    => 'nullable|date',
        'end_date'      => 'nullable|date|after_or_equal:start_date',
        'status'        => 'nullable|string|in:' . implode(',', array_keys($this->getStatusList())),
        'contract_type' => 'nullable|string|in:surat,kontrak',
        'department'    => 'nullable|string|max:10',
    ]);

    $query = $this->buildContractsQuery($request, $user, $userRole);
    $contracts = $query->get();

    $departments = MasterDepartment::orderBy('nama_departemen')->get();
    $statuses = $this->getStatusList();
    $documentTypes = $this->getDocumentTypeList();

    return view('reports.print', compact(
        'contracts',
        'userRole',
        'userDept',
        'request',
        'statuses',
        'documentTypes',
        'departments'
    ));
}

    private function buildContractsQuery(Request $request, $user, $userRole)
{
    
    $query = Contract::with([
        'user',
        'legalAssigned',
        'financeAssigned',
        'accountingAssigned',
        'taxAssigned'
    ]);

    // ============================================
    // FILTER TANGGAL
    // ============================================
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('created_at', [
            Carbon::parse($request->start_date)->startOfDay(),
            Carbon::parse($request->end_date)->endOfDay()
        ]);
    } elseif ($request->filled('start_date')) {
        $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
    } elseif ($request->filled('end_date')) {
        $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
    }

    // ============================================
    // FILTER STATUS
    // ============================================
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // ============================================
    // FILTER TIPE DOKUMEN
    // ============================================
    if ($request->filled('contract_type')) {
        $query->where('contract_type', $request->contract_type);
    }

    // ============================================
    // FILTER DEPARTMENT (ADMIN / LEGAL)
    // ============================================
    if (in_array($userRole, ['admin', 'legal']) && $request->filled('department')) {
        $query->where('department_code', $request->department);
    }

    // ============================================
    // ROLE-BASED FILTERING
    // ============================================
    switch ($userRole) {
        case 'user':
            $query->where('user_id', $user->id_user);
            break;

        case 'admin_fin':
        case 'staff_fin':
            $query->where('department_code', 'FIN');
            break;

        case 'admin_acc':
        case 'staff_acc':
            $query->where('department_code', 'ACC');
            break;

        case 'admin_tax':
        case 'staff_tax':
            $query->where('department_code', 'TAX');
            break;

        case 'admin':
        case 'legal':
            // Sudah difilter di atas jika ada request department
            break;

        default:
            $query->where('user_id', $user->id_user);
            break;
    }

    return $query->latest('created_at');
}

    /**
     * Helper: Get user role dari Spatie roles
     */
    private function getUserRole($user)
    {
        if (!$user) return 'user';
        
        if ($user->hasRole('admin')) return 'admin';
        if ($user->hasRole('legal')) return 'legal';
        if ($user->hasRole('admin_fin')) return 'admin_fin';
        if ($user->hasRole('staff_fin')) return 'staff_fin';
        if ($user->hasRole('admin_acc')) return 'admin_acc';
        if ($user->hasRole('staff_acc')) return 'staff_acc';
        if ($user->hasRole('admin_tax')) return 'admin_tax';
        if ($user->hasRole('staff_tax')) return 'staff_tax';
        
        return 'user';
    }
    
    /**
     * Helper: Get list status untuk filter
     */
    private function getStatusList()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_FINAL_APPROVED => 'Final Approved',
            self::STATUS_NUMBER_ISSUED => 'Number Issued',
            self::STATUS_RELEASED => 'Released',
            self::STATUS_REVISION_NEEDED => 'Revision Needed',
            self::STATUS_DECLINED => 'Declined',
        ];
    }
    
    /**
     * Helper: Get list tipe dokumen untuk filter (berdasarkan contract_type)
     */
    private function getDocumentTypeList()
    {
        return [
            self::TYPE_SURAT => 'Surat',
            self::TYPE_KONTRAK => 'Kontrak',
        ];
    }
    
    /**
     * Helper: Format status untuk display
     */
    private function formatStatus($status)
    {
        $statusMap = [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_FINAL_APPROVED => 'Final Approved',
            self::STATUS_NUMBER_ISSUED => 'Number Issued',
            self::STATUS_RELEASED => 'Released',
            self::STATUS_REVISION_NEEDED => 'Revision Needed',
            self::STATUS_DECLINED => 'Declined',
        ];
        
        return $statusMap[$status] ?? $status;
    }
    
    /**
     * Helper: Format tipe dokumen untuk display
     */
    private function formatDocumentType($contractType)
    {
        if ($contractType === self::TYPE_SURAT) {
            return 'Surat';
        } elseif ($contractType === self::TYPE_KONTRAK) {
            return 'Kontrak';
        }
        
        return $contractType ?? '-';
    }
    
    /**
     * API endpoint untuk filter departments (untuk admin/legal)
     */
    public function getDepartments()
    {
        $user = Auth::user();
        $userRole = $this->getUserRole($user);
        
        // Hanya admin dan legal yang bisa akses
        if (!in_array($userRole, ['admin', 'legal'])) {
            return response()->json([]);
        }
        
        $departments = Contract::select('department_code')
            ->whereNotNull('department_code')
            ->distinct()
            ->orderBy('department_code')
            ->pluck('department_code');
            
        return response()->json($departments);
    }
}