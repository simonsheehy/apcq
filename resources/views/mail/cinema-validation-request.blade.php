<x-mail::message>
Bonjour à tous,

Bonne nouvelle : grâce à une subvention de la SODEC dans le cadre de la **Stratégie québécoise de l’audiovisuel 2026-2031**, l’APCQ pourra accompagner les cinémas dans le remplacement de leurs projecteurs désuets.

L’aide pourra couvrir jusqu’à **50 % des coûts**, avec la possibilité d’un **financement avantageux pour la portion restante**. Le remplacement se fera progressivement sur 7 à 10 ans, en priorisant les équipements les plus anciens, par des projecteurs de même type utilisant la technologie laser.

Pour établir les priorités, nous devons dresser un portrait précis des équipements en fonction. **Merci de remplir le formulaire pour chacun de vos écrans, même les plus récents, d’ici le 15 septembre.**

Le formulaire de **{{ $cinema->name }}** est accessible via un lien unique, sans mot de passe :

<x-mail::button :url="$cinema->validationUrl()">
Accéder au formulaire
</x-mail::button>

Si le bouton ne fonctionne pas, copiez cette adresse dans votre navigateur :

{{ $cinema->validationUrl() }}

Ces informations permettront de préparer le déploiement du programme avec la SODEC et un premier appel de projets.

Merci de votre collaboration!<br>
Christian Roy<br>
Coordination<br>
APCQ
</x-mail::message>
