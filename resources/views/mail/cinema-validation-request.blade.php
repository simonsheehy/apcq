<x-mail::message>
# Mise à jour des informations de votre cinéma

Bonjour{{ filled($cinema->primary_contact_name) ? ' '.$cinema->primary_contact_name : '' }},

L’Association des propriétaires de cinémas du Québec vous invite à vérifier et mettre à jour les informations de **{{ $cinema->name }}** : contacts, cinéma, salles et projecteurs.

Le formulaire est accessible via un lien unique, sans mot de passe :

<x-mail::button :url="$cinema->validationUrl()">
Accéder au formulaire
</x-mail::button>

Si le bouton ne fonctionne pas, copiez cette adresse dans votre navigateur :

{{ $cinema->validationUrl() }}

Merci de votre collaboration,<br>
L’équipe de l’APCQ
</x-mail::message>
