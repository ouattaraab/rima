<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('username', 'like', "%{$s}%")
                  ->orWhere('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }
        if ($request->filled('role')) $query->where('role', $request->role);
        if ($request->filled('organization')) $query->where('organization', $request->organization);

        $users = $query->orderBy('username')->paginate(20)->withQueryString();
        return view('users.index', compact('users'));
    }

    public function create() { return view('users.create'); }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|email|max:100|unique:users',
            'password' => 'required|string|min:8',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'role' => 'required|in:agent_cidec,supervisor_cidec,supervisor_sodeci,admin_sodeci',
            'organization' => 'required|in:CIDEC,SODECI',
            'phone' => 'nullable|string|max:20',
            'region' => 'required|string|max:50',
        ], [
            'password.min' => 'Le mot de passe doit contenir au moins 8 caracteres.',
        ]);

        User::create([...$request->except('password'), 'password' => Hash::make($request->password)]);
        return redirect()->route('users.index')->with('success', 'Utilisateur cree avec succes.');
    }

    public function edit(string $id)
    {
        return view('users.edit', ['user' => User::findOrFail($id)]);
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:100', Rule::unique('users')->ignore($user->id)],
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'role' => 'required|in:agent_cidec,supervisor_cidec,supervisor_sodeci,admin_sodeci',
            'organization' => 'required|in:CIDEC,SODECI',
            'phone' => 'nullable|string|max:20',
            'region' => 'required|string|max:50',
            'is_active' => 'boolean',
            'password' => 'sometimes|nullable|string|min:8',
        ], [
            'password.min' => 'Le mot de passe doit contenir au moins 8 caracteres.',
        ]);

        $data = $request->except('password');
        $data['is_active'] = $request->boolean('is_active');
        if ($request->filled('password')) $data['password'] = Hash::make($request->password);

        $user->update($data);
        return redirect()->route('users.index')->with('success', 'Utilisateur mis a jour.');
    }

    /**
     * Export users list to Excel with current filters applied (US-018).
     */
    public function export(Request $request)
    {
        $filters = $request->only(['search', 'role', 'organization']);
        $filename = 'RIMA_UTILISATEURS_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new UsersExport($filters), $filename);
    }
}
