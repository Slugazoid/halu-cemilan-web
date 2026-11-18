<?php

namespace App\Livewire\Admin;

use App\Models\Admin;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class StoreSettings extends Component
{
    #[Locked]
    public string $section;

    public array $fields = [];

    public function boot(): void
    {
        Gate::forUser(auth('admin')->user())->authorize('admin-owner');
        abort_if(auth('admin')->user()->must_change_password, 403);
    }

    public function mount(string $section): void
    {
        $this->section = $section;
        $this->fields = StoreSetting::query()->findOrFail(StoreSetting::SINGLETON_ID)->only(array_keys($this->fieldRules()));
    }

    public function save(): void
    {
        if ($this->section === 'pengaturan' && is_string($this->fields['whatsapp_number'] ?? null)) {
            $this->fields['whatsapp_number'] = Admin::normalizePhone($this->fields['whatsapp_number']);
        }

        $rules = collect($this->fieldRules())->mapWithKeys(fn (array $rules, string $field): array => ['fields.'.$field => $rules]);
        $data = $this->validate($rules->all())['fields'];

        StoreSetting::query()->findOrFail(StoreSetting::SINGLETON_ID)->update($data);

        $this->redirectRoute('admin.'.$this->section);
        session()->flash('status', 'Perubahan disimpan.');
    }

    private function fieldRules(): array
    {
        return match ($this->section) {
            'profil' => [
                'name' => ['required', 'string', 'max:100'],
                'tagline' => ['nullable', 'string', 'max:150'],
                'description' => ['nullable', 'string', 'max:8000'],
                'address' => ['nullable', 'string', 'max:1000'],
                'city' => ['nullable', 'string', 'max:100'],
                'maps_url' => ['nullable', 'url:https', 'max:255'],
            ],
            'tampilan' => [
                'primary_color' => ['required', 'regex:/^#[a-f0-9]{6}$/i'],
                'accent_color' => ['required', 'regex:/^#[a-f0-9]{6}$/i'],
                'theme' => ['required', Rule::in(['netral', 'hangat', 'kontras'])],
            ],
            'pengaturan' => [
                'whatsapp_number' => ['required', 'regex:/^628\d{7,12}$/'],
                'opening_hours' => ['nullable', 'string', 'max:1000'],
                'shipping_info' => ['nullable', 'string', 'max:1500'],
                'order_code_prefix' => ['required', 'regex:/^[A-Z]{2,3}$/'],
                'message_greeting' => ['nullable', 'string', 'max:200'],
                'message_closing' => ['nullable', 'string', 'max:200'],
                'privacy_policy' => ['nullable', 'string', 'max:30000'],
                'terms' => ['nullable', 'string', 'max:30000'],
            ],
            'seo' => [
                'meta_title' => ['nullable', 'string', 'max:70'],
                'meta_description' => ['nullable', 'string', 'max:170'],
                'gsc_verification' => ['nullable', 'string', 'max:255'],
            ],
        };
    }

    public function render(): View
    {
        return view('livewire.admin.store-settings', ['fieldNames' => array_keys($this->fieldRules())]);
    }
}
