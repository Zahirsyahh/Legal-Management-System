<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\TblUser;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        // 1. CLEAR CACHE DULU
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $this->command->info('🚀 Starting Role & Permission Seeding...');
        
        // 2. HAPUS DATA LAMA (jika ada)
        $this->cleanupOldData();
        
        // 3. CREATE PERMISSIONS DULU dengan firstOrCreate
        $this->createPermissions();
        
        // 4. CREATE ROLES
        $this->createRoles();
        
        // 5. CREATE DEFAULT USERS JIKA BELUM ADA
        $this->createDefaultUsers();
        
        // 6. ASSIGN ROLES TO USERS
        $this->assignRolesToUsers();
        
        // 7. VERIFY ASSIGNMENTS
        $this->verifyAssignments();
        
        // 8. CLEAR CACHE LAGI
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $this->command->info('✅ Roles and permissions created successfully!');
    }
  
    /**
     * Clean up old data
     */
    private function cleanupOldData(): void
    {
        $this->command->warn('🗑️  Cleaning up old data...');
        
        // Hapus role FAT jika ada
        if ($role = Role::where('name', 'fat')->first()) {
            $role->delete();
            $this->command->warn('  • Deleted FAT role (replaced by Finance, Accounting, Tax)');
        }
        
        // Hapus permission FAT jika ada
        $fatPermissions = Permission::where('name', 'like', 'fat_%')->get();
        if ($fatPermissions->count() > 0) {
            Permission::where('name', 'like', 'fat_%')->delete();
            $this->command->warn('  • Deleted FAT permissions');
        }
        
        // Hapus assignment yang rusak (model_id = 0 atau NULL)
        $brokenAssignments = DB::table('model_has_roles')
            ->where('model_type', TblUser::class)
            ->where(function($q) {
                $q->where('model_id', 0)
                  ->orWhereNull('model_id');
            })
            ->count();
            
        if ($brokenAssignments > 0) {
            DB::table('model_has_roles')
                ->where('model_type', TblUser::class)
                ->where(function($q) {
                    $q->where('model_id', 0)
                      ->orWhereNull('model_id');
                })
                ->delete();
            $this->command->warn("  • Deleted {$brokenAssignments} broken role assignments");
        }
        
        // Hapus broken permission assignments
        $brokenPerms = DB::table('model_has_permissions')
            ->where('model_type', TblUser::class)
            ->where(function($q) {
                $q->where('model_id', 0)
                  ->orWhereNull('model_id');
            })
            ->count();
            
        if ($brokenPerms > 0) {
            DB::table('model_has_permissions')
                ->where('model_type', TblUser::class)
                ->where(function($q) {
                    $q->where('model_id', 0)
                      ->orWhereNull('model_id');
                })
                ->delete();
            $this->command->warn("  • Deleted {$brokenPerms} broken permission assignments");
        }
    }
    
    /**
     * Create all permissions
     */
    private function createPermissions(): void
    {
        $this->command->info('📋 Creating permissions...');
        
        $allPermissions = [
            // USER PERMISSIONS
            'contract_request_create',
            'contract_request_view',
            'contract_request_edit',
            'contract_request_delete',
            'contract_request_submit',
            'contract_revise_submit',
            'contract_revise_view',
            'contract_track_status',
            'contract_view_final',
            'contract_download_final',
            'contract_add_comment',
            'contract_upload_attachment',
            
            // LEGAL PERMISSIONS
            'contract_view_all',
            'contract_progress_track',
            'contract_dashboard_view',
            'contract_status_update',
            'status_mark_reviewing',
            'status_mark_revision_needed',
            'status_mark_legal_approved',
            'status_mark_final_approved',
            'status_mark_released',
            'review_add_comment',
            'review_request_revision',
            'legal_approve',
            'legal_reject',
            'department_coordination',
            'receive_department_feedback',
            
            // FINANCE DEPARTMENT PERMISSIONS
            'finance_contract_view',
            'finance_dashboard_view',
            'finance_review',
            'finance_approve',
            'finance_request_revision',
            'finance_add_comment',
            'finance_upload_attachment',
            
            // ACCOUNTING DEPARTMENT PERMISSIONS
            'accounting_contract_view',
            'accounting_dashboard_view',
            'accounting_review',
            'accounting_approve',
            'accounting_request_revision',
            'accounting_add_comment',
            'accounting_upload_attachment',
            
            // TAX DEPARTMENT PERMISSIONS
            'tax_contract_view',
            'tax_dashboard_view',
            'tax_review',
            'tax_approve',
            'tax_request_revision',
            'tax_add_comment',
            'tax_upload_attachment',
            
            // DEPARTMENT ADMIN PERMISSIONS
            'department_admin_view',
            'department_admin_assign',
            'department_admin_track',
            'department_admin_notify',
            'department_admin_approve',
            
            // GENERAL PERMISSIONS
            'dashboard_view',
            'notification_view',
            'notification_mark_read',
            'profile_view',
            'profile_edit',
            'password_change',
            
            // ADMIN PERMISSIONS
            'user_view',
            'user_create',
            'user_edit',
            'user_delete',
            'user_role_assign',
            'role_view',
            'role_create',
            'role_edit',
            'role_delete',
            'permission_manage',
            'system_config_view',
            'system_config_edit',
            'report_view',
            'report_generate',
            'report_export',
            'audit_log_view',
            'activity_log_view',
            'department_manage',
        ];
        
        $createdCount = 0;
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
            $createdCount++;
        }
        
        $this->command->info("  ✓ Created/verified {$createdCount} permissions");
    }
    
    /**
     * Create roles and assign permissions
     */
    private function createRoles(): void
{
    $this->command->info('👥 Creating roles and assigning permissions...');
    
    // ========================================
    // 1. USER ROLE - Regular contract submitter
    // ========================================
    $roleUser = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
    $roleUser->syncPermissions([
        'contract_request_create',
        'contract_request_view',
        'contract_request_edit',
        'contract_request_delete',
        'contract_request_submit',
        'contract_revise_submit',
        'contract_revise_view',
        'contract_track_status',
        'contract_view_final',
        'contract_download_final',
        'contract_add_comment',
        'contract_upload_attachment',
        'dashboard_view',
        'notification_view',
        'notification_mark_read',
        'profile_view',
        'profile_edit',
        'password_change',
    ]);
    
    // ========================================
    // 2. LEGAL ROLE - Legal department reviewer
    // ========================================
    $roleLegal = Role::firstOrCreate(['name' => 'legal', 'guard_name' => 'web']);
    $roleLegal->syncPermissions([
        // Legal-specific
        'contract_view_all',
        'contract_progress_track',
        'contract_dashboard_view',
        'contract_status_update',
        'status_mark_reviewing',
        'status_mark_revision_needed',
        'status_mark_legal_approved',
        'status_mark_final_approved',
        'status_mark_released',
        'review_add_comment',
        'review_request_revision',
        'legal_approve',
        'legal_reject',
        'department_coordination',
        'receive_department_feedback',
        
        // Contract interactions
        'contract_add_comment',
        'contract_upload_attachment',
        'contract_view_final',
        'contract_download_final',
        
        // General
        'dashboard_view',
        'notification_view',
        'notification_mark_read',
        'profile_view',
        'profile_edit',
        'password_change',
    ]);
    
    // ========================================
    // 3. STAFF FINANCE - Finance staff reviewer
    // ✅ BISA: approve, request revision, add stage, CREATE CONTRACT
    // ❌ TIDAK BISA: view all contracts, manage department users
    // ========================================
    $roleStaffFin = Role::firstOrCreate(['name' => 'staff_fin', 'guard_name' => 'web']);
    $roleStaffFin->syncPermissions([
        // Finance review (✅ FULL POWER dalam assigned contracts)
        'finance_contract_view',          // Lihat contracts yang di-assign ke dia
        'finance_dashboard_view',
        'finance_review',
        'finance_approve',                // ✅ BISA APPROVE
        'finance_request_revision',       // ✅ BISA REQUEST REVISION
        'finance_add_comment',
        'finance_upload_attachment',
        
        // ✅ TAMBAHAN: BISA CREATE CONTRACT
        'contract_request_create',
        'contract_request_view',
        'contract_request_edit',
        'contract_request_submit',
        'contract_revise_submit',
        
        // General contract
        'contract_add_comment',
        'contract_upload_attachment',
        'contract_track_status',
        
        // General
        'dashboard_view',
        'notification_view',
        'notification_mark_read',
        'profile_view',
        'profile_edit',
        'password_change',
    ]);
    
    // ========================================
    // 4. STAFF ACCOUNTING - Accounting staff reviewer
    // ========================================
    $roleStaffAcc = Role::firstOrCreate(['name' => 'staff_acc', 'guard_name' => 'web']);
    $roleStaffAcc->syncPermissions([
        'accounting_contract_view',
        'accounting_dashboard_view',
        'accounting_review',
        'accounting_approve',              // ✅ BISA APPROVE
        'accounting_request_revision',     // ✅ BISA REQUEST REVISION
        'accounting_add_comment',
        'accounting_upload_attachment',
        
        // ✅ TAMBAHAN: BISA CREATE CONTRACT
        'contract_request_create',
        'contract_request_view',
        'contract_request_edit',
        'contract_request_submit',
        'contract_revise_submit',
        
        'contract_add_comment',
        'contract_upload_attachment',
        'contract_track_status',
        
        'dashboard_view',
        'notification_view',
        'notification_mark_read',
        'profile_view',
        'profile_edit',
        'password_change',
    ]);
    
    // ========================================
    // 5. STAFF TAX - Tax staff reviewer
    // ========================================
    $roleStaffTax = Role::firstOrCreate(['name' => 'staff_tax', 'guard_name' => 'web']);
    $roleStaffTax->syncPermissions([
        'tax_contract_view',
        'tax_dashboard_view',
        'tax_review',
        'tax_approve',                     // ✅ BISA APPROVE
        'tax_request_revision',            // ✅ BISA REQUEST REVISION
        'tax_add_comment',
        'tax_upload_attachment',
        
        // ✅ TAMBAHAN: BISA CREATE CONTRACT
        'contract_request_create',
        'contract_request_view',
        'contract_request_edit',
        'contract_request_submit',
        'contract_revise_submit',
        
        'contract_add_comment',
        'contract_upload_attachment',
        'contract_track_status',
        
        'dashboard_view',
        'notification_view',
        'notification_mark_read',
        'profile_view',
        'profile_edit',
        'password_change',
    ]);
    
    // ========================================
    // 6. ADMIN FINANCE - Finance department admin
    // ✅ SEMUA PERMISSION STAFF + VIEW ALL + ASSIGN POWER + CREATE CONTRACT
    // ========================================
    $roleAdminFin = Role::firstOrCreate(['name' => 'admin_fin', 'guard_name' => 'web']);
    $roleAdminFin->syncPermissions([
        // ✅ SEMUA permission STAFF FINANCE
        'finance_contract_view',
        'finance_dashboard_view',
        'finance_review',
        'finance_approve',
        'finance_request_revision',
        'finance_add_comment',
        'finance_upload_attachment',
        
        // ✅ TAMBAHAN: BISA CREATE CONTRACT
        'contract_request_create',
        'contract_request_view',
        'contract_request_edit',
        'contract_request_submit',
        'contract_revise_submit',
        
        // ✅ PLUS: View ALL contracts & Department Admin powers
        'contract_view_all',               // ✅ Bisa lihat SEMUA contracts (bukan cuma yang di-assign)
        'department_admin_view',           // View department overview
        'department_admin_assign',         // Assign contracts ke staff lain
        'department_admin_track',          // Track semua progress
        'department_admin_notify',         // Send notifications
        'department_admin_approve',        // Override approvals
        
        'contract_add_comment',
        'contract_upload_attachment',
        'contract_track_status',
        
        'dashboard_view',
        'notification_view',
        'notification_mark_read',
        'profile_view',
        'profile_edit',
        'password_change',
    ]);
    
    // ========================================
    // 7. ADMIN ACCOUNTING
    // ========================================
    $roleAdminAcc = Role::firstOrCreate(['name' => 'admin_acc', 'guard_name' => 'web']);
    $roleAdminAcc->syncPermissions([
        'accounting_contract_view',
        'accounting_dashboard_view',
        'accounting_review',
        'accounting_approve',
        'accounting_request_revision',
        'accounting_add_comment',
        'accounting_upload_attachment',
        
        // ✅ TAMBAHAN: BISA CREATE CONTRACT
        'contract_request_create',
        'contract_request_view',
        'contract_request_edit',
        'contract_request_submit',
        'contract_revise_submit',
        
        'contract_view_all',
        'department_admin_view',
        'department_admin_assign',
        'department_admin_track',
        'department_admin_notify',
        'department_admin_approve',
        
        'contract_add_comment',
        'contract_upload_attachment',
        'contract_track_status',
        
        'dashboard_view',
        'notification_view',
        'notification_mark_read',
        'profile_view',
        'profile_edit',
        'password_change',
    ]);
    
    // ========================================
    // 8. ADMIN TAX
    // ========================================
    $roleAdminTax = Role::firstOrCreate(['name' => 'admin_tax', 'guard_name' => 'web']);
    $roleAdminTax->syncPermissions([
        'tax_contract_view',
        'tax_dashboard_view',
        'tax_review',
        'tax_approve',
        'tax_request_revision',
        'tax_add_comment',
        'tax_upload_attachment',
        
        // ✅ TAMBAHAN: BISA CREATE CONTRACT
        'contract_request_create',
        'contract_request_view',
        'contract_request_edit',
        'contract_request_submit',
        'contract_revise_submit',
        
        'contract_view_all',
        'department_admin_view',
        'department_admin_assign',
        'department_admin_track',
        'department_admin_notify',
        'department_admin_approve',
        
        'contract_add_comment',
        'contract_upload_attachment',
        'contract_track_status',
        
        'dashboard_view',
        'notification_view',
        'notification_mark_read',
        'profile_view',
        'profile_edit',
        'password_change',
    ]);
    
    // ========================================
    // 9. ADMIN - System administrator (SUPER ADMIN)
    // ========================================
    $roleAdmin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $roleAdmin->syncPermissions(Permission::all());
    
    // ========================================
    // SUMMARY TABLE
    // ========================================
    $this->command->info('  📊 Role Permission Summary:');
    $this->command->table(
        ['Role', 'Perms', 'Approve?', 'Create?', 'View All?', 'Assign?', 'Description'],
        [
            ['user', $roleUser->permissions->count(), '❌', '✅', '❌', '❌', 'Regular User'],
            ['legal', $roleLegal->permissions->count(), '✅', '❌', '✅', '✅', 'Legal Reviewer'],
            ['staff_fin', $roleStaffFin->permissions->count(), '✅', '✅', '❌', '✅', 'Finance Staff'],
            ['staff_acc', $roleStaffAcc->permissions->count(), '✅', '✅', '❌', '✅', 'Accounting Staff'],
            ['staff_tax', $roleStaffTax->permissions->count(), '✅', '✅', '❌', '✅', 'Tax Staff'],
            ['admin_fin', $roleAdminFin->permissions->count(), '✅', '✅', '✅', '✅', 'Finance Admin'],
            ['admin_acc', $roleAdminAcc->permissions->count(), '✅', '✅', '✅', '✅', 'Accounting Admin'],
            ['admin_tax', $roleAdminTax->permissions->count(), '✅', '✅', '✅', '✅', 'Tax Admin'],
            ['admin', $roleAdmin->permissions->count(), '✅', '✅', '✅', '✅', 'System Admin (ALL)'],
        ]
    );
    
    $this->command->info("\n  🔄 Key Differences:");
    $this->command->info("  • Staff: Review & approve ASSIGNED contracts + CREATE contracts");
    $this->command->info("  • Admin: View ALL dept contracts + assign to staff + CREATE contracts");
    $this->command->info("  • All department roles now can: approve, request revision, CREATE contracts");
}
    
    /**
     * Create default users if they don't exist
     */
    private function createDefaultUsers(): void
    {
        $this->command->info('👤 Creating/updating default users...');
        
        $users = [
            // SYSTEM USERS
            ['id' => 1, 'name' => 'Administrator', 'email' => 'admin@example.com', 'role' => 'admin', 'dept' => 'ADMIN'],
            ['id' => 2, 'name' => 'Legal Officer', 'email' => 'legal@example.com', 'role' => 'legal', 'dept' => 'LEGAL'],
            ['id' => 3, 'name' => 'Regular User', 'email' => 'user@example.com', 'role' => 'user', 'dept' => null],
            
            // DEPARTMENT ADMIN USERS
            ['id' => 4, 'name' => 'Finance Admin', 'email' => 'finance.admin@example.com', 'role' => 'admin_fin', 'dept' => 'FIN'],
            ['id' => 5, 'name' => 'Accounting Admin', 'email' => 'accounting.admin@example.com', 'role' => 'admin_acc', 'dept' => 'ACC'],
            ['id' => 6, 'name' => 'Tax Admin', 'email' => 'tax.admin@example.com', 'role' => 'admin_tax', 'dept' => 'TAX'],
            
            // DEPARTMENT STAFF USERS
            ['id' => 7, 'name' => 'Finance Staff 1', 'email' => 'finance.staff1@example.com', 'role' => 'staff_fin', 'dept' => 'FIN'],
            ['id' => 8, 'name' => 'Finance Staff 2', 'email' => 'finance.staff2@example.com', 'role' => 'staff_fin', 'dept' => 'FIN'],
            ['id' => 9, 'name' => 'Finance Staff 3', 'email' => 'finance.staff3@example.com', 'role' => 'staff_fin', 'dept' => 'FIN'],
            ['id' => 10, 'name' => 'Accounting Staff 1', 'email' => 'accounting.staff1@example.com', 'role' => 'staff_acc', 'dept' => 'ACC'],
            ['id' => 11, 'name' => 'Accounting Staff 2', 'email' => 'accounting.staff2@example.com', 'role' => 'staff_acc', 'dept' => 'ACC'],
            ['id' => 12, 'name' => 'Accounting Staff 3', 'email' => 'accounting.staff3@example.com', 'role' => 'staff_acc', 'dept' => 'ACC'],
            ['id' => 13, 'name' => 'Tax Staff 1', 'email' => 'tax.staff1@example.com', 'role' => 'staff_tax', 'dept' => 'TAX'],
            ['id' => 14, 'name' => 'Tax Staff 2', 'email' => 'tax.staff2@example.com', 'role' => 'staff_tax', 'dept' => 'TAX'],
            ['id' => 15, 'name' => 'Tax Staff 3', 'email' => 'tax.staff3@example.com', 'role' => 'staff_tax', 'dept' => 'TAX'],
            
            // LEGAL REVIEWERS
            ['id' => 16, 'name' => 'Legal Reviewer 1', 'email' => 'legal1@example.com', 'role' => 'legal', 'dept' => 'LEGAL'],
            ['id' => 17, 'name' => 'Legal Reviewer 2', 'email' => 'legal2@example.com', 'role' => 'legal', 'dept' => 'LEGAL'],
            ['id' => 18, 'name' => 'Legal Reviewer 3', 'email' => 'legal3@example.com', 'role' => 'legal', 'dept' => 'LEGAL'],
            ['id' => 19, 'name' => 'Legal Reviewer 4', 'email' => 'legal4@example.com', 'role' => 'legal', 'dept' => 'LEGAL'],
            ['id' => 20, 'name' => 'Legal Reviewer 5', 'email' => 'legal5@example.com', 'role' => 'legal', 'dept' => 'LEGAL'],
        ];
        
        $created = 0;
        $updated = 0;
        
        foreach ($users as $userData) {
            $user = TblUser::updateOrCreate(
                ['id_user' => $userData['id']],
                [
                    'nama_user' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make('password123'),
                    'status_karyawan' => 'AKTIF',
                    'kode_status_kepegawaian' => '1', 
                    'kode_department' => $userData['dept'],
                    'jabatan' => strtoupper($userData['role']),
                ]
            );
            
            $user->wasRecentlyCreated ? $created++ : $updated++;
        }
        
        $this->command->info("  ✓ Created {$created} users, updated {$updated} users");
    }
    
    /**
     * Assign roles to users using DIRECT DB INSERT
     */
    private function assignRolesToUsers(): void
    {
        $this->command->info('🔐 Assigning roles to users...');
        
        $assignments = [
            1 => 'admin',
            2 => 'legal',
            3 => 'user',
            4 => 'admin_fin',
            5 => 'admin_acc',
            6 => 'admin_tax',
            7 => 'staff_fin',
            8 => 'staff_fin',
            9 => 'staff_fin',
            10 => 'staff_acc',
            11 => 'staff_acc',
            12 => 'staff_acc',
            13 => 'staff_tax',
            14 => 'staff_tax',
            15 => 'staff_tax',
            16 => 'legal',
            17 => 'legal',
            18 => 'legal',
            19 => 'legal',
            20 => 'legal',
        ];
        
        $successCount = 0;
        $failCount = 0;
        
        foreach ($assignments as $userId => $roleName) {
            $user = TblUser::find($userId);
            
            if (!$user) {
                $this->command->error("  ✗ User ID {$userId} not found");
                $failCount++;
                continue;
            }
            
            // Hapus semua role lama untuk user ini
            DB::table('model_has_roles')
                ->where('model_type', TblUser::class)
                ->where('model_id', $userId)
                ->delete();
            
            // Cari role
            $role = Role::where('name', $roleName)->first();
            
            if (!$role) {
                $this->command->error("  ✗ Role '{$roleName}' not found");
                $failCount++;
                continue;
            }
            
            // ✅ DIRECT DB INSERT - Memastikan pakai id_user
            DB::table('model_has_roles')->insert([
                'role_id' => $role->id,
                'model_type' => TblUser::class,
                'model_id' => $user->id_user, // ✅ PAKAI id_user, BUKAN id
            ]);
            
            $successCount++;
        }
        
        $this->command->info("  ✓ Assigned {$successCount} roles successfully");
        if ($failCount > 0) {
            $this->command->warn("  ⚠️  {$failCount} assignments failed");
        }
    }
    
    /**
     * Verify all role assignments
     */
    private function verifyAssignments(): void
    {
        $this->command->info('🔍 Verifying role assignments...');
        
        $results = [];
        $errorCount = 0;
        
        for ($i = 1; $i <= 20; $i++) {
            $user = TblUser::find($i);
            
            if ($user) {
                // Force reload roles
                $user->load('roles');
                $roles = $user->getRoleNames()->toArray();
                $status = count($roles) > 0 ? '✅' : '❌';
                
                if (count($roles) === 0) {
                    $errorCount++;
                }
                
                $results[] = [
                    $user->id_user,
                    $user->email,
                    implode(', ', $roles) ?: 'NO ROLE',
                    $status
                ];
            }
        }
        
        $this->command->table(
            ['ID', 'Email', 'Roles', 'Status'],
            $results
        );
        
        if ($errorCount > 0) {
            $this->command->error("\n❌ {$errorCount} users have NO ROLES assigned!");
        } else {
            $this->command->info("\n✅ All users have roles assigned correctly!");
        }
        
        // Display login credentials
        $this->command->info("\n🔑 Login Credentials (Password: password123):");
        $this->command->table(
            ['Role Type', 'Email', 'Can Create?', 'Can Approve?'],
            [
                ['System Admin', 'admin@example.com', '✅', '✅ All'],
                ['Legal', 'legal@example.com', '❌', '✅ Legal'],
                ['User', 'user@example.com', '✅', '❌'],
                ['Finance Admin', 'finance.admin@example.com', '✅', '✅ Finance'],
                ['Accounting Admin', 'accounting.admin@example.com', '✅', '✅ Accounting'],
                ['Tax Admin', 'tax.admin@example.com', '✅', '✅ Tax'],
                ['Finance Staff', 'finance.staff1@example.com', '✅', '✅ Finance'],
                ['Accounting Staff', 'accounting.staff1@example.com', '✅', '✅ Accounting'],
                ['Tax Staff', 'tax.staff1@example.com', '✅', '✅ Tax'],
            ]
        );
    }
}