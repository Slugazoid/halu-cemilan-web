<div class="grid gap-5">
    @if (session('status'))
        <p role="status" class="rounded-lg bg-zinc-100 p-3 text-sm">{{ session('status') }}</p>
    @endif
    @if (session('temporaryPassword'))
        <div class="grid gap-1 rounded-lg border border-border p-4" role="status">
            <p class="text-sm">Salin kata sandi sementara untuk disampaikan ke staf. Berlaku 24 jam.</p>
            <code class="break-all font-semibold">{{ session('temporaryPassword') }}</code>
        </div>
    @endif
    <x-admin.card title="Tambah staf">
        <form wire:submit="addStaff" class="grid gap-4 sm:grid-cols-3">
            <x-admin.field name="name" label="Nama">
                <input id="name" wire:model="name" class="admin-input" required maxlength="50">
            </x-admin.field>
            <x-admin.field name="phone" label="Nomor HP">
                <input id="phone" wire:model="phone" type="tel" class="admin-input" required>
            </x-admin.field>
            <x-admin.field name="email" label="Email (opsional)">
                <input id="email" wire:model="email" type="email" class="admin-input">
            </x-admin.field>
            <button class="admin-button admin-button-primary justify-self-start">Tambah staf</button>
        </form>
    </x-admin.card>

    <x-admin.card title="Daftar staf">
        <ul class="grid divide-y divide-border">
            @forelse ($staff as $account)
                <li wire:key="staff-{{ $account->id }}" class="flex flex-wrap items-center justify-between gap-3 py-3">
                    <div>
                        <p class="font-medium">{{ $account->name }}</p>
                        <p class="text-sm text-muted-foreground">{{ $account->phone }} · {{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="toggleStaff({{ $account->id }})" class="admin-button admin-button-outline" wire:loading.attr="disabled">{{ $account->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        <button type="button" wire:click="resetStaffPassword({{ $account->id }})" wire:confirm="Buat kata sandi sementara baru untuk staf ini?" class="admin-button admin-button-outline" wire:loading.attr="disabled">Reset kata sandi</button>
                    </div>
                </li>
            @empty
                <li class="text-sm text-muted-foreground">Belum ada staf.</li>
            @endforelse
        </ul>
        {{ $staff->links() }}
    </x-admin.card>
</div>
