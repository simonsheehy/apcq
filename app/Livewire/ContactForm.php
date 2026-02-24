<?php

namespace App\Livewire;

use Livewire\Component;

class ContactForm extends Component
{
    public string $prenom = '';

    public string $nom = '';

    public string $courriel = '';

    public ?string $telephone = null;

    public ?string $entreprise = null;

    public string $sujet = '';

    public string $message = '';

    public function submit(): void
    {
        $this->validate([
            'prenom' => ['required', 'string', 'max:255'],
            'nom' => ['required', 'string', 'max:255'],
            'courriel' => ['required', 'email'],
            'telephone' => ['nullable', 'string', 'max:255'],
            'entreprise' => ['nullable', 'string', 'max:255'],
            'sujet' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        // TODO: Envoyer l'email ou stocker en base
        $this->reset(['prenom', 'nom', 'courriel', 'telephone', 'entreprise', 'sujet', 'message']);

        session()->flash('contact-success', 'Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.');
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
