<div class="grid gap-4">
    <x-admin.card title="Terbitkan situs toko">
        <ol class="grid gap-1 divide-y divide-border">
            <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                <span>Isi identitas toko</span>
                <a class="admin-button admin-button-outline" href="{{ route('admin.profil') }}">{{ filled($store->name) ? 'Ubah profil' : 'Isi profil' }}</a>
            </li>
            <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                <span>Isi nomor WhatsApp pesanan</span>
                <a class="admin-button admin-button-outline" href="{{ route('admin.pengaturan') }}">{{ filled($store->whatsapp_number) ? 'Ubah nomor' : 'Isi nomor' }}</a>
            </li>
            <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                <div>
                    <p>Tambahkan tiga menu pertama</p>
                    <p class="text-sm text-muted-foreground">{{ $menuCount }} menu tampil dan tersedia</p>
                </div>
                <a class="admin-button admin-button-outline" href="{{ route('admin.menu') }}">Kelola menu</a>
            </li>
            <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                <span>Pilih tema dan warna</span>
                <a class="admin-button admin-button-outline" href="{{ route('admin.tampilan') }}">Atur tampilan</a>
            </li>
        </ol>
        <form wire:submit="publish" class="grid gap-2 justify-items-start">
            <p class="text-sm text-muted-foreground">Untuk menerbitkan, isi nama toko dan nomor WhatsApp, lalu siapkan setidaknya satu menu yang tampil dan tersedia.</p>
            @error('publish') <p role="alert" class="text-sm text-destructive">{{ $message }}</p> @enderror
            <button class="admin-button admin-button-primary">Terbitkan situs</button>
        </form>
    </x-admin.card>
</div>
