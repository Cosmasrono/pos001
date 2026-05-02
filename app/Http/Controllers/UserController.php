<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of the users in this company.
     */
    public function index()
    {
        $companyId = auth()->user()->company_id;

        $users = User::with('roles', 'branch')
            ->where('company_id', $companyId)              // ← scope to this company
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'owner');                // hide the company owner from list
            })
            ->latest()
            ->paginate(10);

        return view('users.index', compact('users'));
    }

    /**
     * Show the create-user form.
     */
    public function create()
    {
        $companyId = auth()->user()->company_id;

        // Roles are global — but we exclude 'owner' so it can't be assigned through the UI
        $roles = Role::where('name', '!=', 'owner')->get();

        // Only show THIS company's branches
        $branches = Branch::where('company_id', $companyId)->get();

        return view('users.create', compact('roles', 'branches'));
    }

    /**
     * Store a newly created user (attached to current company).
     */
    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone'     => ['nullable', 'string', 'max:15'],
            'password'  => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id'   => ['required', 'exists:roles,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        // Reject if branch_id doesn't belong to this company (prevents cross-company assignment)
        if ($request->branch_id) {
            $validBranch = Branch::where('id', $request->branch_id)
                ->where('company_id', $companyId)
                ->exists();
            if (!$validBranch) {
                return back()->withErrors(['branch_id' => 'Invalid branch selection.'])->withInput();
            }
        }

        // Reject the 'owner' role even if someone hand-edits the form
        $role = Role::find($request->role_id);
        if ($role && $role->name === 'owner') {
            return back()->withErrors(['role_id' => 'Cannot assign owner role.'])->withInput();
        }

        $user = User::create([
            'company_id' => $companyId,                    // ← attach to current company
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'password'   => Hash::make($request->password),
            'is_active'  => true,
            'branch_id'  => $request->branch_id,
        ]);

        $user->roles()->sync([$request->role_id]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show edit form — block access to users from other companies.
     */
    public function edit(User $user)
    {
        $this->authorizeCompany($user);

        $roles = Role::where('name', '!=', 'owner')->get();
        $branches = Branch::where('company_id', auth()->user()->company_id)->get();

        return view('users.edit', compact('user', 'roles', 'branches'));
    }

    /**
     * Update a user — block access to users from other companies.
     */
    public function update(Request $request, User $user)
    {
        $this->authorizeCompany($user);

        $companyId = auth()->user()->company_id;

        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone'     => ['nullable', 'string', 'max:15'],
            'password'  => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role_id'   => ['required', 'exists:roles,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        // Reject branches not in this company
        if ($request->branch_id) {
            $validBranch = Branch::where('id', $request->branch_id)
                ->where('company_id', $companyId)
                ->exists();
            if (!$validBranch) {
                return back()->withErrors(['branch_id' => 'Invalid branch selection.'])->withInput();
            }
        }

        // Reject 'owner' role assignment via form tampering
        $role = Role::find($request->role_id);
        if ($role && $role->name === 'owner') {
            return back()->withErrors(['role_id' => 'Cannot assign owner role.'])->withInput();
        }

        $userData = [
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'is_active' => $request->has('is_active'),
            'branch_id' => $request->branch_id,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);
        $user->roles()->sync([$request->role_id]);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Delete a user — block access to users from other companies.
     */
    public function destroy(User $user)
    {
        $this->authorizeCompany($user);

        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete yourself.']);
        }

        // Prevent deleting an owner (extra safety)
        if ($user->hasRole('owner')) {
            return back()->withErrors(['error' => 'Cannot delete an owner account.']);
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Self-service password change (any logged-in user).
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    /**
     * Block access if the target user belongs to a different company.
     * Returns 404 instead of 403 so attackers can't enumerate user IDs across companies.
     */
    private function authorizeCompany(User $user): void
    {
        if ($user->company_id !== auth()->user()->company_id) {
            abort(404);
        }
    }
}