@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@php
    $inputClass = 'w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-base text-slate-900 shadow-sm focus:border-apcq focus:ring-1 focus:ring-apcq';
    $selectClass = $inputClass.' appearance-none pr-10 cursor-pointer';
    $labelClass = 'mb-1.5 block text-sm font-medium text-slate-700';
@endphp

<div class="bg-slate-50">
    <div class="mx-auto w-full max-w-4xl px-4 py-10 sm:px-6">
        <div class="mb-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-apcq">Espace cinéma</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $cinema->name }}</h1>
            <p class="mt-3 max-w-2xl text-slate-600">
                Vérifiez et mettez à jour vos informations. Toutes les modifications sont enregistrées automatiquement.
            </p>
        </div>

        <div class="mb-8 flex flex-wrap gap-2 text-sm">
            <a href="#informations-personnelles" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 font-medium text-slate-700 hover:border-apcq hover:text-apcq transition">Informations personnelles</a>
            <a href="#informations-cinema" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 font-medium text-slate-700 hover:border-apcq hover:text-apcq transition">Cinéma</a>
            <a href="#salles" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 font-medium text-slate-700 hover:border-apcq hover:text-apcq transition">Salles</a>
        </div>

        <div class="space-y-8">
            <section id="informations-personnelles" class="scroll-mt-28 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">Informations personnelles</h2>
                        <p class="mt-1 text-sm text-slate-500">Personne-ressource principale et contact secondaire.</p>
                    </div>
                    @if($personalInfoConfirmed && $cinema->personal_info_validated_at)
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                            Validé le {{ $cinema->personal_info_validated_at->timezone('America/Toronto')->translatedFormat('d F Y') }}
                        </span>
                    @endif
                </div>

                <div class="space-y-6">
                    <div>
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">Contact primaire</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="primary_contact_name" class="{{ $labelClass }}">Nom</label>
                                <input type="text" id="primary_contact_name" wire:model.live.debounce.500ms="personal.primary_contact_name" class="{{ $inputClass }}">
                                @error('personal.primary_contact_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="primary_contact_phone" class="{{ $labelClass }}">Téléphone</label>
                                <input type="tel" id="primary_contact_phone" wire:model.live.debounce.500ms="personal.primary_contact_phone" class="{{ $inputClass }}">
                                @error('personal.primary_contact_phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="primary_contact_email" class="{{ $labelClass }}">Courriel</label>
                                <input type="email" id="primary_contact_email" wire:model.live.debounce.500ms="personal.primary_contact_email" class="{{ $inputClass }}">
                                @error('personal.primary_contact_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">Contact secondaire</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="secondary_contact_name" class="{{ $labelClass }}">Nom</label>
                                <input type="text" id="secondary_contact_name" wire:model.live.debounce.500ms="personal.secondary_contact_name" class="{{ $inputClass }}">
                                @error('personal.secondary_contact_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="secondary_contact_phone" class="{{ $labelClass }}">Téléphone</label>
                                <input type="tel" id="secondary_contact_phone" wire:model.live.debounce.500ms="personal.secondary_contact_phone" class="{{ $inputClass }}">
                                @error('personal.secondary_contact_phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="secondary_contact_email" class="{{ $labelClass }}">Courriel</label>
                                <input type="email" id="secondary_contact_email" wire:model.live.debounce.500ms="personal.secondary_contact_email" class="{{ $inputClass }}">
                                @error('personal.secondary_contact_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <input type="checkbox" wire:model.live="personalInfoConfirmed" class="mt-1 rounded border-slate-300 text-apcq focus:ring-apcq">
                        <span>
                            <span class="block font-medium text-slate-900">Je confirme que ces informations personnelles sont exactes</span>
                            <span class="mt-0.5 block text-sm text-slate-500">Vous pouvez modifier les champs à tout moment ; l’enregistrement est automatique.</span>
                        </span>
                    </label>
                </div>
            </section>

            <section id="informations-cinema" class="scroll-mt-28 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">Informations du cinéma</h2>
                        <p class="mt-1 text-sm text-slate-500">Identification, coordonnées et exploitation.</p>
                    </div>
                    @if($cinemaInfoConfirmed && $cinema->cinema_info_validated_at)
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                            Validé le {{ $cinema->cinema_info_validated_at->timezone('America/Toronto')->translatedFormat('d F Y') }}
                        </span>
                    @endif
                </div>

                <div class="space-y-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="cinema_name" class="{{ $labelClass }}">Nom du cinéma</label>
                            <input type="text" id="cinema_name" wire:model.live.debounce.500ms="details.name" class="{{ $inputClass }}">
                            @error('details.name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="legal_company_name" class="{{ $labelClass }}">Nom de la compagnie légale</label>
                            <input type="text" id="legal_company_name" wire:model.live.debounce.500ms="details.legal_company_name" class="{{ $inputClass }}">
                            @error('details.legal_company_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="address" class="{{ $labelClass }}">Adresse</label>
                            <input type="text" id="address" wire:model.live.debounce.500ms="details.address" class="{{ $inputClass }}">
                            @error('details.address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="city" class="{{ $labelClass }}">Ville</label>
                            <input type="text" id="city" wire:model.live.debounce.500ms="details.city" class="{{ $inputClass }}">
                            @error('details.city')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="postal_code" class="{{ $labelClass }}">Code postal</label>
                            <input type="text" id="postal_code" wire:model.live.debounce.500ms="details.postal_code" class="{{ $inputClass }}">
                            @error('details.postal_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="cinema_phone" class="{{ $labelClass }}">Téléphone</label>
                            <input type="tel" id="cinema_phone" wire:model.live.debounce.500ms="details.phone" class="{{ $inputClass }}">
                            @error('details.phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="cinema_email" class="{{ $labelClass }}">Courriel (info)</label>
                            <input type="email" id="cinema_email" wire:model.live.debounce.500ms="details.email" class="{{ $inputClass }}">
                            @error('details.email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="website" class="{{ $labelClass }}">Site web</label>
                            <input type="text" id="website" wire:model.live.debounce.500ms="details.website" placeholder="https://" class="{{ $inputClass }}">
                            @error('details.website')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">Exploitation</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="pos_software" class="{{ $labelClass }}">Logiciel de caisse</label>
                                <input type="text" id="pos_software" wire:model.live.debounce.500ms="details.pos_software" class="{{ $inputClass }}">
                                @error('details.pos_software')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="edelivery" class="{{ $labelClass }}">eDelivery (CineSend, Global DCP, etc.)</label>
                                <input type="text" id="edelivery" wire:model.live.debounce.500ms="details.edelivery" class="{{ $inputClass }}">
                                @error('details.edelivery')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="cash_registers_count" class="{{ $labelClass }}">Nombre de caisses</label>
                                <input type="number" min="0" id="cash_registers_count" wire:model.live.debounce.500ms="details.cash_registers_count" class="{{ $inputClass }}">
                                @error('details.cash_registers_count')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="ticket_booths_count" class="{{ $labelClass }}">Nombre de guichets</label>
                                <input type="number" min="0" id="ticket_booths_count" wire:model.live.debounce.500ms="details.ticket_booths_count" class="{{ $inputClass }}">
                                @error('details.ticket_booths_count')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <label class="flex cursor-pointer items-center gap-3 sm:col-span-2">
                                <input type="checkbox" wire:model.live="details.alcohol_permit" class="rounded border-slate-300 text-apcq focus:ring-apcq">
                                <span class="text-sm font-medium text-slate-700">Permis d’alcool</span>
                            </label>
                        </div>
                    </div>

                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <input type="checkbox" wire:model.live="cinemaInfoConfirmed" class="mt-1 rounded border-slate-300 text-apcq focus:ring-apcq">
                        <span>
                            <span class="block font-medium text-slate-900">Je confirme que les informations du cinéma sont exactes</span>
                            <span class="mt-0.5 block text-sm text-slate-500">Les changements sont enregistrés automatiquement.</span>
                        </span>
                    </label>
                </div>
            </section>

            <section id="salles" class="scroll-mt-28 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">Salles</h2>
                        <p class="mt-1 text-sm text-slate-500">Ajoutez, modifiez ou retirez les salles et leurs projecteurs.</p>
                    </div>
                    <button type="button" wire:click="addRoom"
                            class="inline-flex items-center gap-2 rounded-lg bg-apcq px-4 py-2 text-sm font-semibold text-white hover:bg-apcq-dark transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Ajouter une salle
                    </button>
                </div>

                @if(count($rooms) === 0)
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                        <p class="text-slate-600">Aucune salle pour le moment.</p>
                        <button type="button" wire:click="addRoom" class="mt-4 text-sm font-semibold text-apcq hover:underline">
                            Ajouter la première salle
                        </button>
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach($rooms as $index => $room)
                            <article wire:key="room-{{ $room['id'] }}" class="rounded-xl border border-slate-200 p-5">
                                <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
                                    <h3 class="text-lg font-semibold text-slate-900">Salle {{ $index + 1 }}</h3>
                                    <button type="button"
                                            wire:click="removeRoom({{ $index }})"
                                            wire:confirm="Retirer cette salle ? Elle disparaîtra du formulaire."
                                            class="text-sm font-medium text-red-600 hover:text-red-700 hover:underline">
                                        Retirer
                                    </button>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <label for="room-{{ $index }}-name" class="{{ $labelClass }}">Nom de la salle</label>
                                        <input type="text" id="room-{{ $index }}-name" wire:model.live.debounce.500ms="rooms.{{ $index }}.name" class="{{ $inputClass }}">
                                        @error("rooms.{$index}.name")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="sm:col-span-2 grid gap-4 sm:grid-cols-2">

                                        <div>
                                            <label for="room-{{ $index }}-brand" class="{{ $labelClass }}">Marque du projecteur</label>
                                            <div class="relative">
                                                <select id="room-{{ $index }}-brand" wire:model.live="rooms.{{ $index }}.projector_brand" class="{{ $selectClass }}">
                                                    <option value="">Choisir…</option>
                                                    @foreach($projectorBrands as $brand)
                                                        <option value="{{ $brand->value }}">{{ $brand->getLabel() }}</option>
                                                    @endforeach
                                                </select>
                                                <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </div>
                                            @error("rooms.{$index}.projector_brand")
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div x-cloak x-show="$wire.rooms[{{ $index }}].projector_brand === 'other'">
                                            <label for="room-{{ $index }}-brand-other" class="{{ $labelClass }}">Nom de la marque</label>
                                            <input type="text" id="room-{{ $index }}-brand-other" wire:model.live.debounce.500ms="rooms.{{ $index }}.projector_brand_other" class="{{ $inputClass }}">
                                            @error("rooms.{$index}.projector_brand_other")
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                    </div>

                                    <div>
                                        <label for="room-{{ $index }}-model" class="{{ $labelClass }}">Modèle du projecteur</label>
                                        <input type="text" id="room-{{ $index }}-model" wire:model.live.debounce.500ms="rooms.{{ $index }}.projector_model" class="{{ $inputClass }}">
                                        @error("rooms.{$index}.projector_model")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="room-{{ $index }}-server-model" class="{{ $labelClass }}">Modèle du serveur</label>
                                        <input type="text" id="room-{{ $index }}-server-model" wire:model.live.debounce.500ms="rooms.{{ $index }}.server_model" class="{{ $inputClass }}">
                                        @error("rooms.{$index}.server_model")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="room-{{ $index }}-projection-type" class="{{ $labelClass }}">Type de projection</label>
                                        <div class="relative">
                                            <select id="room-{{ $index }}-projection-type" wire:model.live="rooms.{{ $index }}.projection_type" class="{{ $selectClass }}">
                                                <option value="">Choisir…</option>
                                                @foreach($projectionTypes as $type)
                                                    <option value="{{ $type->value }}">{{ $type->getLabel() }}</option>
                                                @endforeach
                                            </select>
                                            <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                        @error("rooms.{$index}.projection_type")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="room-{{ $index }}-install-year" class="{{ $labelClass }}">Année d’installation</label>
                                        <input type="number" id="room-{{ $index }}-install-year" wire:model.live.debounce.500ms="rooms.{{ $index }}.installation_year" class="{{ $inputClass }}">
                                        @error("rooms.{$index}.installation_year")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="room-{{ $index }}-screen" class="{{ $labelClass }}">Grandeur de l’écran</label>
                                        <input type="text" id="room-{{ $index }}-screen" wire:model.live.debounce.500ms="rooms.{{ $index }}.screen_size" placeholder="ex. : 12 m ou 40 pi" class="{{ $inputClass }}">
                                        @error("rooms.{$index}.screen_size")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>

    <div class="pointer-events-none fixed bottom-5 right-5 z-50">
        <div
            class="pointer-events-auto rounded-full border border-slate-200 bg-white/95 px-4 py-2 text-sm font-medium text-slate-700 shadow-lg backdrop-blur"
            x-data="{ savedAt: @js($lastSavedAt) }"
            @cinema-saved.window="savedAt = $event.detail.at"
        >
            <span wire:loading.delay>Enregistrement…</span>
            <span wire:loading.delay.remove x-text="savedAt ? 'Enregistré à ' + savedAt : 'Enregistrement automatique'"></span>
        </div>
    </div>
</div>
