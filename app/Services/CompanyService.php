<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CompanyService
{
    /**
     * Update company custom labels
     */
    public function updateCustomLabels(int $companyId, array $labels): array
    {
        try {
            DB::beginTransaction();

            $company = Company::find($companyId);
            if (!$company) {
                throw new Exception('Company not found');
            }

            $company->custom_labels = $labels;
            $company->save();

            DB::commit();

            Log::info('Company custom labels updated', [
                'company_id' => $companyId,
                'labels' => $labels,
            ]);

            return [
                'success' => true,
                'message' => 'Custom labels updated successfully.',
                'data' => $labels,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update company custom labels', [
                'error' => $e->getMessage(),
                'company_id' => $companyId,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update custom labels. Please try again.',
            ];
        }
    }

    /**
     * Get company custom labels
     */
    public function getCustomLabels(int $companyId): ?array
    {
        $company = Company::find($companyId);
        return $company ? $company->custom_labels : null;
    }

    /**
     * Get company details
     */
    public function getCompany(int $companyId): ?Company
    {
        return Company::find($companyId);
    }

    /**
     * Check if company exists and is active
     */
    public function isCompanyActive(int $companyId): bool
    {
        return Company::where('id', $companyId)
            ->where('is_active', true)
            ->exists();
    }
}