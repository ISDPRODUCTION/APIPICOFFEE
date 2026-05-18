<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $employees = User::all();

        $logo = null;
        try {
            $logo = Storage::disk('s3')->exists('settings/logo.png')
                ? Storage::disk('s3')->url('settings/logo.png')
                : null;
        } catch (\Exception $e) {
            // R2 tidak tersedia, skip
        }

        $settings = [
            'business_name' => config('app.name', 'Apipi Coffee'),
            'logo'          => $logo,
        ];

        return view('settings.index', compact('employees', 'settings'));
    }

    public function profile(): View
    {
        $user = Auth::user();
        return view('settings.profile', compact('user'));
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user      = Auth::user();
        $validated = $request->validate([
            'name'   => 'required|string|max:100',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            try {
                if ($user->avatar) {
                    Storage::disk('s3')->delete($user->avatar);
                }
                $path = $request->file('avatar')->store('avatars', 's3');
                if (!$path) {
                    return response()->json(['success' => false, 'message' => 'Upload gagal: path kosong'], 500);
                }
                $validated['avatar'] = $path;
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Storage error: ' . $e->getMessage()], 500);
            }
        }

        $user->update($validated);
        return response()->json(['success' => true]);
    }
    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|confirmed|min:8',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Password lama salah.'], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['success' => true]);
    }

    // ── Business Identity ─────────────────────────────────
    public function updateIdentity(Request $request): JsonResponse
    {
        $request->validate([
            'business_name' => 'required|string|max:100',
            'logo'          => 'nullable|image|max:2048',
        ]);

        // Update app name di .env
        $this->setEnvValue('APP_NAME', '"' . $request->business_name . '"');

        if ($request->hasFile('logo')) {
            try {
                if (Storage::disk('s3')->exists('settings/logo.png')) {
                    Storage::disk('s3')->delete('settings/logo.png');
                }
                $request->file('logo')->storeAs('settings', 'logo.png', 's3');
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Upload logo gagal: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['success' => true]);
    }

    // ── Employee CRUD ─────────────────────────────────────
    public function storeEmployee(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'     => 'nullable|string|max:100',
                'email'    => 'required|email|unique:users',
                'password' => 'required|min:8',
                'role'     => 'required|in:cashier,admin,supervisor,manager,barista',
            ]);

            $validated['name']        = $validated['name'] ?? explode('@', $validated['email'])[0];
            $validated['password']    = Hash::make($validated['password']);
            $validated['employee_id'] = 'EMP-' . str_pad(User::withTrashed()->count() + 1, 3, '0', STR_PAD_LEFT);
            $validated['status']      = 'active';

            $employee = User::create($validated);

            return response()->json(['success' => true, 'employee' => $employee]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateEmployee(Request $request, int $id): JsonResponse
    {
        $employee = User::findOrFail($id);

        $validated = $request->validate([
            'name'   => 'required|string|max:100',
            'email'  => 'required|email|unique:users,email,' . $id,
            'role'   => 'required|in:cashier,admin,supervisor,manager,barista',
            'status' => 'required|in:active,inactive,leave',
        ]);

        $employee->update($validated);

        return response()->json(['success' => true]);
    }

    public function destroyEmployee(int $id): JsonResponse
    {
        // Jangan hapus diri sendiri
        if (Auth::id() === $id) {
            return response()->json(['success' => false, 'message' => 'Tidak bisa menghapus akun sendiri.'], 403);
        }

        User::destroy($id);
        return response()->json(['success' => true]);
    }

    // ── Helper: update .env value ─────────────────────────
    private function setEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);
        $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
        file_put_contents($envPath, $content);
    }
}