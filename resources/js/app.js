import Alpine from 'alpinejs';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

window.Alpine = Alpine;

Alpine.data('regionAddressForm', (config) => ({
    urls: config.urls,
    initial: config.initial,
    provinces: [],
    regencies: [],
    districts: [],
    villages: [],
    provinceCode: config.initial.province_code,
    regencyCode: config.initial.regency_code,
    districtCode: config.initial.district_code,
    villageCode: config.initial.village_code,
    postalCode: config.initial.postal_code ?? '',
    manualVillageName: config.initial.manual_village_name ?? '',
    latitude: config.initial.latitude ?? '',
    longitude: config.initial.longitude ?? '',
    provinceSearch: '',
    regencySearch: '',
    districtSearch: '',
    villageSearch: '',
    isOtherVillage: false,
    needsManualPostalCode: false,
    showMap: false,
    regionError: '',
    map: null,
    marker: null,

    async init() {
        const initial = this.initial;
        this.provinceCode = '';
        this.regencyCode = '';
        this.districtCode = '';
        this.villageCode = '';
        await this.loadOptions(this.urls.provinces, 'provinces');
        this.provinceCode = initial.province_code;
        await this.$nextTick();

        if (this.provinceCode) {
            await this.loadOptions(this.urlFor(this.urls.regencies, this.provinceCode), 'regencies');
        }
        this.regencyCode = initial.regency_code;
        await this.$nextTick();
        if (this.regencyCode) {
            await this.loadOptions(this.urlFor(this.urls.districts, this.regencyCode), 'districts');
        }
        this.districtCode = initial.district_code;
        await this.$nextTick();
        if (this.districtCode) {
            await this.loadOptions(this.urlFor(this.urls.villages, this.districtCode), 'villages');
        }
        this.villageCode = initial.village_code;
        await this.$nextTick();

        const selectedVillage = this.villages.find((village) => village.code === this.villageCode);
        this.isOtherVillage = Boolean(selectedVillage?.code.endsWith('LNY'));
        this.needsManualPostalCode = Boolean(this.isOtherVillage || !selectedVillage?.postal_code);
        if (!this.postalCode && selectedVillage?.postal_code) {
            this.postalCode = selectedVillage.postal_code;
        }
    },

    async loadOptions(url, property) {
        this.regionError = '';
        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!response.ok) {
                throw new Error('Daftar wilayah tidak dapat dimuat. Silakan coba lagi.');
            }
            this[property] = await response.json();
        } catch (error) {
            this.regionError = error instanceof Error ? error.message : 'Daftar wilayah tidak dapat dimuat.';
            this[property] = [];
        }
    },

    urlFor(template, code) {
        return template.replace('__CODE__', encodeURIComponent(code));
    },

    filtered(items, search, selectedCode) {
        const term = search.trim().toLocaleLowerCase('id');
        return term
            ? items.filter((item) => item.code === selectedCode || item.name.toLocaleLowerCase('id').includes(term))
            : items;
    },

    async changeProvince() {
        this.regencyCode = '';
        this.districtCode = '';
        this.villageCode = '';
        this.regencies = [];
        this.districts = [];
        this.villages = [];
        this.regencySearch = '';
        this.districtSearch = '';
        this.villageSearch = '';
        this.clearVillageData();
        if (this.provinceCode) {
            await this.loadOptions(this.urlFor(this.urls.regencies, this.provinceCode), 'regencies');
        }
    },

    async changeRegency() {
        this.districtCode = '';
        this.villageCode = '';
        this.districts = [];
        this.villages = [];
        this.districtSearch = '';
        this.villageSearch = '';
        this.clearVillageData();
        if (this.regencyCode) {
            await this.loadOptions(this.urlFor(this.urls.districts, this.regencyCode), 'districts');
        }
    },

    async changeDistrict() {
        this.villageCode = '';
        this.villages = [];
        this.villageSearch = '';
        this.clearVillageData();
        if (this.districtCode) {
            await this.loadOptions(this.urlFor(this.urls.villages, this.districtCode), 'villages');
        }
    },

    changeVillage() {
        const village = this.villages.find((item) => item.code === this.villageCode);
        this.isOtherVillage = Boolean(village?.code.endsWith('LNY'));
        this.needsManualPostalCode = Boolean(this.isOtherVillage || !village?.postal_code);
        this.postalCode = village?.postal_code ?? '';
        this.manualVillageName = '';
    },

    clearVillageData() {
        this.isOtherVillage = false;
        this.needsManualPostalCode = false;
        this.postalCode = '';
        this.manualVillageName = '';
    },

    toggleMap() {
        this.showMap = !this.showMap;
        if (this.showMap) {
            this.$nextTick(() => this.openMap());
        }
    },

    openMap() {
        if (this.map) {
            this.map.invalidateSize();
            return;
        }

        const hasCoordinates = this.latitude !== '' && this.longitude !== '';
        const center = hasCoordinates
            ? [Number(this.latitude), Number(this.longitude)]
            : [-2.5, 118];
        this.map = L.map(this.$refs.map).setView(center, hasCoordinates ? 15 : 4);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(this.map);
        this.map.on('click', (event) => this.setPin(event.latlng));

        if (hasCoordinates) {
            this.setPin(L.latLng(center[0], center[1]));
        }
    },

    setPin(latlng) {
        if (!this.marker) {
            this.marker = L.marker(latlng, { draggable: true }).addTo(this.map);
            this.marker.on('dragend', () => this.updateCoordinates(this.marker.getLatLng()));
        } else {
            this.marker.setLatLng(latlng);
        }
        this.updateCoordinates(latlng);
    },

    updateCoordinates(latlng) {
        this.latitude = Number(latlng.lat).toFixed(7);
        this.longitude = Number(latlng.lng).toFixed(7);
    },

    clearPin() {
        this.latitude = '';
        this.longitude = '';
        if (this.marker) {
            this.marker.remove();
            this.marker = null;
        }
    },
}));

Alpine.start();
