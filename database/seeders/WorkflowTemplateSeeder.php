<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Cari department IDs
        $legalDept = DB::table('departments')->where('code', 'LEGAL')->first();
        $financeDept = DB::table('departments')->where('code', 'FIN')->first();
        $accountingDept = DB::table('departments')->where('code', 'ACC')->first();
        $taxDept = DB::table('departments')->where('code', 'TAX')->first();

        $templates = [];

        // Legal Department Templates
        if ($legalDept) {
            $templates[] = [
                'department_id' => $legalDept->id,
                'name' => 'Standard Legal Review',
                'stages_config' => json_encode([
                    [
                        'stage_key' => 'legal_admin',
                        'stage_name' => 'Admin Legal',
                        'stage_type' => 'legal',
                        'description' => 'Document control and initial review',
                        'is_final' => false,
                        'required_role' => 'legal',
                    ],
                    [
                        'stage_key' => 'legal_officer_1',
                        'stage_name' => 'Legal Officer 1',
                        'stage_type' => 'legal',
                        'description' => 'Primary legal review',
                        'is_final' => false,
                        'required_role' => 'legal',
                    ],
                    [
                        'stage_key' => 'legal_officer_2',
                        'stage_name' => 'Legal Officer 2',
                        'stage_type' => 'legal',
                        'description' => 'Secondary legal review',
                        'is_final' => true,
                        'required_role' => 'legal',
                    ],
                ]),
                'is_default' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $templates[] = [
                'department_id' => $legalDept->id,
                'name' => 'Quick Legal Review',
                'stages_config' => json_encode([
                    [
                        'stage_key' => 'single_legal_review',
                        'stage_name' => 'Legal Reviewer',
                        'stage_type' => 'legal',
                        'description' => 'Combined legal review',
                        'is_final' => true,
                        'required_role' => 'legal',
                    ],
                ]),
                'is_default' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Finance Department Templates
        if ($financeDept) {
            $templates[] = [
                'department_id' => $financeDept->id,
                'name' => 'Standard Finance Approval',
                'stages_config' => json_encode([
                    [
                        'stage_key' => 'finance_reviewer',
                        'stage_name' => 'Finance Reviewer',
                        'stage_type' => 'finance',
                        'description' => 'Financial terms review',
                        'is_final' => false,
                        'required_role' => 'finance',
                    ],
                    [
                        'stage_key' => 'finance_manager',
                        'stage_name' => 'Finance Manager',
                        'stage_type' => 'finance',
                        'description' => 'Budget approval',
                        'is_final' => true,
                        'required_role' => 'finance',
                    ],
                ]),
                'is_default' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Accounting Department Templates
        if ($accountingDept) {
            $templates[] = [
                'department_id' => $accountingDept->id,
                'name' => 'Accounting Verification',
                'stages_config' => json_encode([
                    [
                        'stage_key' => 'accounting_verifier',
                        'stage_name' => 'Accounting Verifier',
                        'stage_type' => 'accounting',
                        'description' => 'Payment terms verification',
                        'is_final' => true,
                        'required_role' => 'accounting',
                    ],
                ]),
                'is_default' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Tax Department Templates
        if ($taxDept) {
            $templates[] = [
                'department_id' => $taxDept->id,
                'name' => 'Tax Compliance Review',
                'stages_config' => json_encode([
                    [
                        'stage_key' => 'tax_reviewer',
                        'stage_name' => 'Tax Reviewer',
                        'stage_type' => 'tax',
                        'description' => 'Tax implications review',
                        'is_final' => true,
                        'required_role' => 'tax',
                    ],
                ]),
                'is_default' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert templates
        if (!empty($templates)) {
            DB::table('department_workflow_templates')->insert($templates);
            $this->command->info('✓ Workflow templates seeded successfully. (' . count($templates) . ' templates)');
        } else {
            $this->command->warn('No departments found to create templates for.');
        }
    }
}