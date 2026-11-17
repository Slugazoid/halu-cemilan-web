<?php

namespace App\Livewire\Admin;

use App\Models\MenuItem;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class Setup extends Component
{
    public function boot(): void
    {
        Gate::forUser(auth('admin')->user())->authorize('admin-owner');
        abort_if(auth('admin')->user()->must_change_password, 403);
    }

    public function mount(): void
    {
        if (StoreSetting::query()->findOrFail(StoreSetting::SINGLETON_ID)->is_published) {
            $this->redirectRoute('admin.dashboard');
        }
    }

    public function publish(): void
    {
        DB::transaction(function (): void {
            $store = StoreSetting::query()->lockForUpdate()->findOrFail(StoreSetting::SINGLETON_ID);

            if (blank($store->name) || ! preg_match('/^628\d{7,12}$/', $store->whatsapp_number ?? '') || ! $this->visibleMenuCount()) {
                throw ValidationException::withMessages(['publish' => 'Isi nama toko, nomor WhatsApp, dan satu menu yang tampil dan tersedia.']);
            }

            $store->forceFill(['is_published' => true])->save();
        });

        session()->flash('status', 'Situs diterbitkan.');
        $this->redirectRoute('admin.dashboard');
    }

    public function render(): View
    {
        return view('livewire.admin.setup', [
            'store' => StoreSetting::query()->findOrFail(StoreSetting::SINGLETON_ID),
            'menuCount' => $this->visibleMenuCount(),
        ]);
    }

    private function visibleMenuCount(): int
    {
        return MenuItem::query()->visible()->where('is_available', true)->count();
    }
}
