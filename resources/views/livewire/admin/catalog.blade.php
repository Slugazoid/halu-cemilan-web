<div class="grid gap-5">
    @if (session('status'))
        <p role="status" class="rounded-lg bg-zinc-100 p-3 text-sm">{{ session('status') }}</p>
    @endif
    <x-admin.card title="Kategori">
        <form wire:submit="saveCategory" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-admin.field name="category.name" label="Nama kategori">
                <input id="category.name" wire:model="category.name" class="admin-input" required maxlength="60">
            </x-admin.field>
            <x-admin.field name="category.slug" label="Alamat (slug)">
                <input id="category.slug" wire:model="category.slug" class="admin-input" maxlength="60" placeholder="otomatis dari nama">
            </x-admin.field>
            <x-admin.field name="category.description" label="Deskripsi singkat">
                <input id="category.description" wire:model="category.description" class="admin-input">
            </x-admin.field>
            <x-admin.field name="category.sort_order" label="Urutan">
                <input id="category.sort_order" wire:model="category.sort_order" class="admin-input" type="number" min="0" max="99999" required>
            </x-admin.field>
            <div class="flex flex-wrap items-end gap-2">
                <button class="admin-button admin-button-primary">{{ $editingCategory ? 'Simpan kategori' : 'Tambah kategori' }}</button>
                @if ($editingCategory)
                    <button type="button" wire:click="cancelCategory" class="admin-button">Batal</button>
                @endif
            </div>
        </form>
        @error('archive') <p role="alert" class="text-sm text-destructive">{{ $message }}</p> @enderror
        <ul class="grid divide-y divide-border">
            @forelse ($categories as $row)
                <li wire:key="category-{{ $row->id }}" class="flex flex-wrap items-center justify-between gap-2 py-2">
                    <span class="min-w-0 break-words">{{ $row->name }}</span>
                    <div class="flex gap-2">
                        <button type="button" wire:click="editCategory({{ $row->id }})" class="admin-button admin-button-outline">Edit</button>
                        <button type="button" wire:click="archiveCategory({{ $row->id }})" wire:confirm="Arsipkan kategori ini?" class="admin-button">Arsipkan</button>
                    </div>
                </li>
            @empty
                <li class="text-sm text-muted-foreground">Tambahkan kategori untuk mulai mengisi menu.</li>
            @endforelse
        </ul>
    </x-admin.card>

    @if ($categories->isNotEmpty())
        <x-admin.card :title="$editingItem ? 'Edit menu' : 'Tambah menu'" id="menu-form">
            <form wire:submit="saveItem" class="grid gap-4 sm:grid-cols-2">
                <x-admin.field name="item.name" label="Nama menu">
                    <input id="item.name" wire:model="item.name" class="admin-input" maxlength="80" required>
                </x-admin.field>
                <x-admin.field name="item.slug" label="Alamat halaman (slug)">
                    <input id="item.slug" wire:model="item.slug" class="admin-input" maxlength="60" placeholder="kosongkan = otomatis dari nama">
                    @if ($editingItem)
                        <p class="text-xs text-muted-foreground">Jika diubah, alamat lama otomatis dialihkan ke alamat baru.</p>
                    @endif
                </x-admin.field>
                <x-admin.field name="item.category_id" label="Kategori">
                    <select id="item.category_id" wire:model="item.category_id" class="admin-input" required>
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $row)
                            <option value="{{ $row->id }}">{{ $row->name }}</option>
                        @endforeach
                    </select>
                </x-admin.field>
                <x-admin.field name="item.price" label="Harga (Rp)">
                    <input id="item.price" wire:model="item.price" class="admin-input" type="number" min="1" max="4294967295" required>
                </x-admin.field>
                <x-admin.field name="item.weight_grams" label="Berat bersih (g)">
                    <input id="item.weight_grams" wire:model="item.weight_grams" class="admin-input" type="number" min="1" max="65535" required>
                </x-admin.field>
                <x-admin.field name="item.description" label="Deskripsi menu" class="sm:col-span-2">
                    <textarea id="item.description" wire:model="item.description" class="admin-input" rows="2"></textarea>
                </x-admin.field>
                <x-admin.field name="item.ingredients" label="Komposisi">
                    <input id="item.ingredients" wire:model="item.ingredients" class="admin-input">
                </x-admin.field>
                <x-admin.field name="item.shelf_life" label="Masa simpan">
                    <input id="item.shelf_life" wire:model="item.shelf_life" class="admin-input" maxlength="50">
                </x-admin.field>
                <x-admin.field name="item.sort_order" label="Urutan">
                    <input id="item.sort_order" wire:model="item.sort_order" class="admin-input" type="number" min="0" max="99999" required>
                </x-admin.field>
                <div class="flex flex-wrap gap-x-4 sm:col-span-2">
                    @foreach (['is_visible' => 'Tampilkan di situs', 'is_available' => 'Tersedia', 'is_featured' => 'Menu unggulan'] as $flag => $label)
                        <label class="inline-flex min-h-11 items-center gap-2 text-sm">
                            <input type="checkbox" wire:model="item.{{ $flag }}" class="size-4"> {{ $label }}
                        </label>
                    @endforeach
                </div>
                @error('item.is_featured') <p role="alert" class="text-sm text-destructive sm:col-span-2">{{ $message }}</p> @enderror
                <div class="flex gap-2 sm:col-span-2">
                    <button class="admin-button admin-button-primary">Simpan menu</button>
                    @if ($editingItem)
                        <button type="button" wire:click="cancelItem" class="admin-button">Batal</button>
                    @endif
                </div>
            </form>
        </x-admin.card>
    @endif

    <x-admin.card title="Daftar menu">
        <ul class="grid divide-y divide-border">
            @forelse ($items as $product)
                <li wire:key="item-{{ $product->id }}" class="grid gap-2 py-2 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="break-words font-medium">{{ $product->name }}</p>
                        <p class="text-sm text-muted-foreground">{{ $product->category->name }} · Rp {{ number_format($product->price, 0, ',', '.') }} · {{ $product->is_visible ? 'Tampil' : 'Tersembunyi' }}{{ $product->is_featured ? ' · Unggulan' : '' }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="toggleAvailability({{ $product->id }})" wire:loading.attr="disabled" aria-pressed="{{ $product->is_available ? 'true' : 'false' }}" class="admin-button admin-button-outline">{{ $product->is_available ? 'Tersedia' : 'Habis' }}</button>
                        <a href="#menu-form" wire:click="editItem({{ $product->id }})" class="admin-button admin-button-outline">Edit</a>
                        <button type="button" wire:click="archiveItem({{ $product->id }})" wire:confirm="Arsipkan menu ini?" class="admin-button">Arsipkan</button>
                    </div>
                </li>
            @empty
                <li class="text-sm text-muted-foreground">Tambahkan menu pertama.</li>
            @endforelse
        </ul>
        {{ $items->links() }}
    </x-admin.card>
</div>
