<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\SaveCustomerAddressRequest;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Support\CustomerStreetAddress;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerAddressController extends Controller
{
    public function index(): View
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        return view('customer.addresses.index', [
            'addresses' => $customer->addresses()->with('village.district.regency.province')->orderByDesc('is_primary')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('customer.addresses.form', [
            'address' => null,
            'formValues' => [],
            'manualVillageName' => null,
        ]);
    }

    public function store(SaveCustomerAddressRequest $request): RedirectResponse
    {
        $data = $this->addressData($request->validated());
        $makePrimary = (bool) ($data['make_primary'] ?? false);
        unset($data['make_primary']);

        /** @var Customer $customer */
        $customer = auth('customer')->user();

        DB::transaction(function () use ($customer, $data, $makePrimary): void {
            $lockedCustomer = Customer::query()->lockForUpdate()->findOrFail($customer->id);
            $addressCount = $lockedCustomer->addresses()->count();

            if ($addressCount >= 5) {
                throw ValidationException::withMessages([
                    'addresses' => 'Setiap pelanggan hanya dapat menyimpan maksimal 5 alamat.',
                ]);
            }

            $isPrimary = $addressCount === 0 || $makePrimary;
            if ($isPrimary) {
                $lockedCustomer->addresses()->update(['is_primary' => false]);
            }

            $data['is_primary'] = $isPrimary;
            $lockedCustomer->addresses()->create($data);
        });

        return redirect()->route('customer.addresses.index')->with('status', 'Alamat berhasil ditambahkan.');
    }

    public function edit(CustomerAddress $address): View
    {
        $address = $this->ownedAddress($address);
        $details = CustomerStreetAddress::split($address->street_address);
        $address->load('village.district.regency');
        $village = $address->village;
        $district = $village->district;
        $regency = $district->regency;

        return view('customer.addresses.form', [
            'address' => $address,
            'formValues' => array_merge($address->only([
                'label', 'recipient_name', 'recipient_phone', 'village_code', 'postal_code', 'landmark', 'latitude', 'longitude',
            ]), $details, [
                'province_code' => $regency->province_code,
                'regency_code' => $regency->code,
                'district_code' => $district->code,
            ]),
            'manualVillageName' => $details['manual_village_name'],
        ]);
    }

    public function update(SaveCustomerAddressRequest $request, CustomerAddress $address): RedirectResponse
    {
        $data = $this->addressData($request->validated());
        unset($data['make_primary']);

        /** @var Customer $customer */
        $customer = auth('customer')->user();

        DB::transaction(function () use ($customer, $address, $data): void {
            Customer::query()->lockForUpdate()->findOrFail($customer->id);
            $ownedAddress = $customer->addresses()->lockForUpdate()->findOrFail($address->id);
            $data['is_primary'] = $ownedAddress->is_primary;
            $ownedAddress->update($data);
        });

        return redirect()->route('customer.addresses.index')->with('status', 'Alamat berhasil diperbarui.');
    }

    public function makePrimary(CustomerAddress $address): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        DB::transaction(function () use ($customer, $address): void {
            $lockedCustomer = Customer::query()->lockForUpdate()->findOrFail($customer->id);
            $ownedAddress = $lockedCustomer->addresses()->lockForUpdate()->findOrFail($address->id);

            $lockedCustomer->addresses()->update(['is_primary' => false]);
            $ownedAddress->update(['is_primary' => true]);
        });

        return redirect()->route('customer.addresses.index')->with('status', 'Alamat utama berhasil diubah.');
    }

    public function destroy(CustomerAddress $address): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        DB::transaction(function () use ($customer, $address): void {
            $lockedCustomer = Customer::query()->lockForUpdate()->findOrFail($customer->id);
            $ownedAddress = $lockedCustomer->addresses()->lockForUpdate()->findOrFail($address->id);

            if ($ownedAddress->is_primary) {
                throw ValidationException::withMessages([
                    'address' => 'Jadikan alamat lain sebagai alamat utama sebelum menghapus alamat ini.',
                ]);
            }

            if ($lockedCustomer->addresses()->where('is_primary', true)->count() !== 1) {
                throw ValidationException::withMessages([
                    'address' => 'Pelanggan harus memiliki tepat satu alamat utama.',
                ]);
            }

            $ownedAddress->delete();
        });

        return redirect()->route('customer.addresses.index')->with('status', 'Alamat berhasil dihapus.');
    }

    private function ownedAddress(CustomerAddress $address): CustomerAddress
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        return $customer->addresses()->findOrFail($address->id);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function addressData(array $data): array
    {
        $data['street_address'] = CustomerStreetAddress::combine(
            $data['street_address'],
            $data['manual_village_name'] ?? null,
        );

        unset(
            $data['province_code'],
            $data['regency_code'],
            $data['district_code'],
            $data['manual_village_name'],
        );

        return $data;
    }
}
