<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BranchScoped
{
    /**
     * Scope to filter records by user's branch
     */
    public function scopeForUserBranch(Builder $query)
    {
        $user = auth()->user();
        
        // If user has no branch or is super admin, show all
        if (!$user || !$user->branch_id) {
            return $query;
        }
        
        return $query->where('branch_id', $user->branch_id);
    }

    /**
     * Scope to filter by specific branch
     */
    public function scopeForBranch(Builder $query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
