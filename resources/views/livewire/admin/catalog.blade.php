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
            <x-admin.field name="category.meta_title" label="Judul SEO (maks. 70)">
                <input id="category.meta_title" wire:model="category.meta_title" class="admin-input" maxlength="70" placeholder="kosongkan = judul bawaan">
            </x-admin.field>
            <x-admin.field name="category.meta_description" label="Deskripsi SEO (maks. 170)" class="sm:col-span-2">
                <input id="category.meta_description" wire:model="category.meta_description" class="admin-input" maxlength="170" placeholder="kosongkan = deskripsi bawaan">
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
                <x-admin.field name="photo" label="Foto menu (JPG/PNG/WebP, maks. 5 MB)" class="sm:col-span-2">
                    <input id="photo" type="file" wire:model="photo" accept="image/jpeg,image/png,image/webp" class="admin-input">
                    <p wire:loading wire:target="photo" class="text-xs text-muted-foreground">Mengunggah…</p>
                    @if ($photo && $photo->isPreviewable())
                        <img src="{{ $photo->temporaryUrl() }}" alt="Pratinjau foto baru" class="mt-1 size-24 rounded-lg object-cover">
                    @elseif ($currentPhoto)
                        <div class="mt-1 flex items-end gap-3">
                            <img src="{{ $currentPhoto }}" alt="Foto menu saat ini" class="size-24 rounded-lg object-cover">
                            <button type="button" wire:click="removePhoto" wire:confirm="Hapus foto menu ini?" class="admin-button">Hapus foto</button>
                        </div>
                    @endif
                </x-admin.field>
                <x-admin.field name="item.photo_alt" label="Teks alternatif foto" class="sm:col-span-2">
                    <input id="item.photo_alt" wire:model="item.photo_alt" class="admin-input" maxlength="125" placeholder="kosongkan = pakai nama menu">
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
                <fieldset class="grid gap-4 border-t border-border pt-5 sm:col-span-2 sm:grid-cols-2">
                    <legend class="text-sm font-medium">Judul &amp; deskripsi mesin pencari (SEO)</legend>
                    <x-admin.field name="item.meta_title" label="Judul SEO (maks. 70)">
                        <input id="item.meta_title" wire:model="item.meta_title" class="admin-input" maxlength="70" placeholder="kosongkan = judul bawaan">
                    </x-admin.field>
                    <x-admin.field name="item.meta_description" label="Deskripsi SEO (maks. 170)">
                        <input id="item.meta_description" wire:model="item.meta_description" class="admin-input" maxlength="170" placeholder="kosongkan = deskripsi bawaan">
                    </x-admin.field>
                </fieldset>
                <fieldset class="border-t border-border pt-5 sm:col-span-2">
                    <legend class="text-sm font-medium">Label</legend>
                    <div class="flex flex-wrap gap-x-4">
                        @foreach (\App\Models\MenuItem::LABELS as $label)
                            <label class="inline-flex min-h-11 items-center gap-2 text-sm">
                                <input type="checkbox" wire:model="item.labels" value="{{ $label }}" class="size-4"> {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('item.labels.*') <p role="alert" class="text-sm text-destructive">{{ $message }}</p> @enderror
                </fieldset>
                <fieldset class="grid gap-3 border-t border-border pt-5 sm:col-span-2">
                    <legend class="text-sm font-medium">Varian / opsi (mis. level pedas, ukuran)</legend>
                    <p class="text-xs text-muted-foreground">Pelanggan memilih satu opsi per grup. Grup bertanda wajib harus dipilih sebelum masuk keranjang. Tambahan harga tidak ikut didiskon.</p>
                    @foreach ($groups as $gi => $group)
                        <div wire:key="group-{{ $group['id'] ?? 'baru-'.$gi }}" class="grid gap-3 rounded-lg border border-border p-3">
                            <div class="grid gap-3 sm:grid-cols-[1fr_auto_auto] sm:items-end">
                                <x-admin.field :name="'groups.'.$gi.'.name'" label="Nama grup">
                                    <input id="groups.{{ $gi }}.name" wire:model="groups.{{ $gi }}.name" class="admin-input" maxlength="50" placeholder="mis. Level pedas">
                                </x-admin.field>
                                <label class="inline-flex min-h-11 items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model="groups.{{ $gi }}.is_required" class="size-4"> Wajib dipilih
                                </label>
                                <button type="button" wire:click="removeGroup({{ $gi }})" wire:confirm="Hapus grup ini beserta opsinya?" class="admin-button admin-button-outline">Hapus grup</button>
                            </div>
                            @error('groups.'.$gi.'.options') <p role="alert" class="text-sm text-destructive">{{ $message }}</p> @enderror
                            @foreach ($group['options'] as $oi => $option)
                                <div wire:key="option-{{ $option['id'] ?? 'baru-'.$gi.'-'.$oi }}" class="grid grid-cols-2 items-end gap-2 sm:grid-cols-[1fr_8rem_8rem_auto]">
                                    <x-admin.field :name="'groups.'.$gi.'.options.'.$oi.'.name'" label="Opsi" class="col-span-2 sm:col-span-1">
                                        <input id="groups.{{ $gi }}.options.{{ $oi }}.name" wire:model="groups.{{ $gi }}.options.{{ $oi }}.name" class="admin-input" maxlength="50" placeholder="mis. Level 3">
                                    </x-admin.field>
                                    <x-admin.field :name="'groups.'.$gi.'.options.'.$oi.'.extra_price'" label="+ Harga (Rp)">
                                        <input id="groups.{{ $gi }}.options.{{ $oi }}.extra_price" wire:model="groups.{{ $gi }}.options.{{ $oi }}.extra_price" class="admin-input" type="number" min="0" inputmode="numeric">
                                    </x-admin.field>
                                    <x-admin.field :name="'groups.'.$gi.'.options.'.$oi.'.extra_weight_grams'" label="+ Berat (g)">
                                        <input id="groups.{{ $gi }}.options.{{ $oi }}.extra_weight_grams" wire:model="groups.{{ $gi }}.options.{{ $oi }}.extra_weight_grams" class="admin-input" type="number" min="0" max="65535" inputmode="numeric">
                                    </x-admin.field>
                                    <button type="button" wire:click="removeOption({{ $gi }}, {{ $oi }})" class="admin-button admin-button-outline" aria-label="Hapus opsi">Hapus</button>
                                </div>
                            @endforeach
                            <div>
                                <button type="button" wire:click="addOption({{ $gi }})" class="admin-button admin-button-outline">Tambah opsi</button>
                            </div>
                        </div>
                    @endforeach
                    <div>
                        <button type="button" wire:click="addGroup" class="admin-button admin-button-outline">Tambah grup varian</button>
                    </div>
                </fieldset>
                <div class="flex flex-wrap gap-x-4 border-t border-border pt-4 sm:col-span-2">
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
        <p class="text-sm text-muted-foreground">Menu unggulan: {{ $featuredCount }} dari {{ $maxFeatured }}. Tampil di beranda.</p>
        @error('featured') <p role="alert" class="text-sm text-destructive">{{ $message }}</p> @enderror
        <ul class="grid divide-y divide-border">
            @forelse ($items as $product)
                <li wire:key="item-{{ $product->id }}" class="grid gap-2 py-2 sm:flex sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        @if ($product->photo_url)
                            <img src="{{ $product->photo_url }}" alt="{{ $product->photo_alt_text }}" width="48" height="48" loading="lazy" class="size-12 shrink-0 rounded-lg object-cover">
                        @endif
                        <div>
                            <p class="break-words font-medium">{{ $product->name }}</p>
                            <p class="text-sm text-muted-foreground">{{ $product->category->name }} · Rp {{ number_format($product->price, 0, ',', '.') }} · {{ $product->is_visible ? 'Tampil' : 'Tersembunyi' }}{{ $product->is_featured ? ' · Unggulan' : '' }}{{ $product->labels ? ' · '.implode(', ', $product->labels) : '' }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="toggleFeatured({{ $product->id }})" wire:loading.attr="disabled" aria-pressed="{{ $product->is_featured ? 'true' : 'false' }}" class="admin-button admin-button-outline">{{ $product->is_featured ? '★ Unggulan' : '☆ Unggulan' }}</button>
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
