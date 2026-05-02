<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    /**
     * List all branches for the current company.
     * (The BelongsToCompany trait auto-filters by company_id.)
     */
    public function index(): View
    {
        $branches = Branch::latest()->get();
        return view('branches.index', compact('branches'));
    }

    public function create(): View
    {
        return view('branches.create');
    }

    /**
     * Create a new branch under the current company.
     */
    public function store(Request $request): RedirectResponse
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                // Unique per company — Shop A's "Main Branch" doesn't conflict with Shop B's
                Rule::unique('branches', 'name')->where('company_id', $companyId),
            ],
            'code' => [
                'nullable', 'string', 'max:50',
                Rule::unique('branches', 'code')->where('company_id', $companyId),
            ],
            'address' => 'nullable|string',
            'phone'   => 'nullable|string',
            'is_main' => 'nullable|boolean',
            'stock_distribution_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $validated['is_main']  = $request->has('is_main');
        $validated['owner_id'] = auth()->id();
        // company_id auto-set by the BelongsToCompany trait's creating() hook,
        // but we set it explicitly here as a belt-and-braces safeguard.
        $validated['company_id'] = $companyId;

        // Demote any other "main" branch in THIS company.
        // (The global scope already filters by company_id, so this is safe.)
        if ($validated['is_main']) {
            Branch::where('is_main', true)->update(['is_main' => false]);
        }

        Branch::create($validated);

        return redirect()->route('branches.index')->with('success', 'Branch created successfully.');
    }

    /**
     * Show a branch — protected by global scope.
     * If $branch belongs to another company, the trait's scope returns null and Laravel 404s.
     */
    public function show(Branch $branch): View
    {
        $this->authorizeCompany($branch);
        $branch->load('productBranchStocks');
        return view('branches.show', compact('branch'));
    }

    public function edit(Branch $branch): View
    {
        $this->authorizeCompany($branch);
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $this->authorizeCompany($branch);

        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('branches', 'name')
                    ->where('company_id', $companyId)
                    ->ignore($branch->id),
            ],
            'code' => [
                'nullable', 'string', 'max:50',
                Rule::unique('branches', 'code')
                    ->where('company_id', $companyId)
                    ->ignore($branch->id),
            ],
            'address' => 'nullable|string',
            'phone'   => 'nullable|string',
            'is_main' => 'nullable|boolean',
            'stock_distribution_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $validated['is_main'] = $request->has('is_main');

        if ($validated['is_main']) {
            Branch::where('is_main', true)
                ->where('id', '!=', $branch->id)
                ->update(['is_main' => false]);
        }

        $branch->update($validated);

        return redirect()->route('branches.index')->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $this->authorizeCompany($branch);

        // Prevent deleting the only/main branch — leaves the company without one
        if ($branch->is_main) {
            return back()->withErrors(['error' => 'Cannot delete the main branch.']);
        }

        $branch->delete();
        return redirect()->route('branches.index')->with('success', 'Branch deleted successfully.');
    }

    /**
     * Defensive check: even though the global scope already protects against
     * cross-company access (route model binding returns 404 for other companies),
     * we double-check here in case the scope is ever bypassed.
     */
    private function authorizeCompany(Branch $branch): void
    {
        if ($branch->company_id !== auth()->user()->company_id) {
            abort(404);
        }
    }
}