<?php

namespace App\Livewire\Admin;

use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class StaffAccounts extends Component
{
    use WithPagination;

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public function boot(): void
    {
        Gate::forUser(auth('admin')->user())->authorize('admin-owner');
        abort_if(auth('admin')->user()->must_change_password, 403);
    }

    public function addStaff(): void
    {
        $this->phone = Admin::normalizePhone($this->phone);
        $this->email = Str::lower(trim($this->email));
        $data = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:50'],
            'phone' => ['required', 'regex:/^628\d{7,12}$/', 'unique:admins,phone'],
            'email' => ['nullable', 'email', 'max:255', 'unique:admins,email'],
        ]);
        $password = Str::password(20, symbols: false);

        (new Admin([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?: null,
            'password' => $password,
        ]))->forceFill(['temporary_password_expires_at' => now()->addDay()])->save();

        $this->reset('name', 'phone', 'email');
        $this->resetPage();
        session()->flash('status', 'Akun staf ditambahkan.');
        session()->flash('temporaryPassword', $password);
    }

    public function toggleStaff(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $staff = Admin::query()->where('role', 'staff')->lockForUpdate()->findOrFail($id);
            $staff->forceFill(['is_active' => ! $staff->is_active])->save();
        });

        session()->flash('status', 'Akses staf diperbarui.');
    }

    public function resetStaffPassword(int $id): void
    {
        $password = Str::password(20, symbols: false);

        DB::transaction(function () use ($id, $password): void {
            $staff = Admin::query()->where('role', 'staff')->lockForUpdate()->findOrFail($id);
            $staff->forceFill([
                'password' => $password,
                'must_change_password' => true,
                'temporary_password_expires_at' => now()->addDay(),
            ])->save();

            DB::table('admin_password_resets')->insert([
                'admin_id' => $staff->id,
                'reset_by' => auth('admin')->id(),
                'created_at' => now(),
            ]);
        });

        session()->flash('status', 'Kata sandi sementara dibuat.');
        session()->flash('temporaryPassword', $password);
    }

    public function render(): View
    {
        return view('livewire.admin.staff-accounts', [
            'staff' => Admin::query()->where('role', 'staff')->latest('id')->paginate(15),
        ]);
    }
}
