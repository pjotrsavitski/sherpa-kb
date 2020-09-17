<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:administrator');
    }

    /**
     * Show the application dashboard or respond with list of users.
     *
     * @return \Illuminate\Contracts\Support\Renderable | \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json(User::with('roles')->get());
        }
        
        return view('users');
    }

    /**
     * Respond with all user roles.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function roles()
    {
        return Role::all();
    }

    /**
     * Create new User and respond with JSON.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['exists:Spatie\Permission\Models\Role,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->markEmailAsVerified();

        if (isset($data['roles']) && $data['roles']) {
            $user->syncRoles($data['roles']);
        }

        return $user->loadMissing('roles');
    }

    /**
     * Update existing User and respond with JSON.
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['sometimes', 'required', 'string', 'min:8', 'confirmed'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['exists:Spatie\Permission\Models\Role,id'],
        ]);

        $updated = [
            'name' => $data['name'],
        ];

        $user->name = $data['name'];

        if (isset($data['email']) && $user->email !== $data['email']) {
            $updated['email'] = $data['email'];
        }
        
        if (isset($data['password']) && $data['password']) {
            $updated['password'] = Hash::make($data['password']);
        }

        $user->update($updated);

        $roles = $data['roles'] ?? [];

        // Retain administrator role for current user
        if (Auth::user()->is($user) && $user->hasRole('administrator')) {
            $administrator = Role::findByName('administrator');
            if (!in_array($administrator->id, $roles)) {
                $roles[] = $administrator->id;
            }
        }
        
        $user->syncRoles($roles);

        return $user->refresh()->loadMissing('roles');
    }
}
