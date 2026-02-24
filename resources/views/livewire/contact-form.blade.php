<div>
    @if(session('contact-success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
            {{ session('contact-success') }}
        </div>
    @endif

    <form wire:submit="submit" class="space-y-4">
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label for="prenom" class="block text-sm font-medium text-stone-700 mb-1">Prénom <span class="text-apcq">*</span></label>
                <input type="text" id="prenom" wire:model="prenom"
                       class="w-full rounded-lg border border-stone-300 shadow-sm focus:border-apcq focus:ring-1 focus:ring-apcq"
                       required>
                @error('prenom')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="nom" class="block text-sm font-medium text-stone-700 mb-1">Nom <span class="text-apcq">*</span></label>
                <input type="text" id="nom" wire:model="nom"
                       class="w-full rounded-lg border border-stone-300 shadow-sm focus:border-apcq focus:ring-1 focus:ring-apcq"
                       required>
                @error('nom')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="courriel" class="block text-sm font-medium text-stone-700 mb-1">Courriel <span class="text-apcq">*</span></label>
            <input type="email" id="courriel" wire:model="courriel"
                   class="w-full rounded-lg border border-stone-300 shadow-sm focus:border-apcq focus:ring-1 focus:ring-apcq"
                   required>
            @error('courriel')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="telephone" class="block text-sm font-medium text-stone-700 mb-1">Téléphone</label>
            <input type="tel" id="telephone" wire:model="telephone"
                   class="w-full rounded-lg border border-stone-300 shadow-sm focus:border-apcq focus:ring-1 focus:ring-apcq">
            @error('telephone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="entreprise" class="block text-sm font-medium text-stone-700 mb-1">Entreprise</label>
            <input type="text" id="entreprise" wire:model="entreprise"
                   class="w-full rounded-lg border border-stone-300 shadow-sm focus:border-apcq focus:ring-1 focus:ring-apcq">
            @error('entreprise')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="sujet" class="block text-sm font-medium text-stone-700 mb-1">Sujet <span class="text-apcq">*</span></label>
            <input type="text" id="sujet" wire:model="sujet"
                   class="w-full rounded-lg border border-stone-300 shadow-sm focus:border-apcq focus:ring-1 focus:ring-apcq"
                   required>
            @error('sujet')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="message" class="block text-sm font-medium text-stone-700 mb-1">Message <span class="text-apcq">*</span></label>
            <textarea id="message" wire:model="message" rows="5"
                      class="w-full rounded-lg border border-stone-300 shadow-sm focus:border-apcq focus:ring-1 focus:ring-apcq"
                      required></textarea>
            @error('message')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full sm:w-auto px-6 py-3 bg-apcq text-white font-medium rounded-lg hover:bg-apcq-dark transition">
            Envoyer
        </button>
    </form>
</div>
