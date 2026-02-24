<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        if (Page::where('slug', 'a-propos')->exists()) {
            return;
        }

        Page::create([
            'title' => 'À propos',
            'slug' => 'a-propos',
            'content' => '<h2>Mission</h2>'
                .'<p>L\'Association des Propriétaires de Cinémas du Québec (APCQ) existe depuis plus de 80 ans et regroupe près de 80 % des écrans de cinéma du Québec et 85 % du box-office à travers 40 villes de la province. Les cinémas sont les principaux diffuseurs du cinéma québécois, un diffuseur culturel primordial pour le rayonnement du cinéma d\'ici.</p>'
                .'<h2>Au temps du noir et blanc</h2>'
                .'<p>Fondée le 1er juin 1932, l\'Association des propriétaires de cinémas et ciné-parcs du Québec s\'appelait, à l\'époque, la « Quebec Allied Theatrical Industries Inc. ». C\'est en 1964 qu\'elle prend sa vraie couleur : son appellation devient celle que l\'on a connue jusqu\'en 2011, année où elle devient l\'APCQ.</p>'
                .'<h2>Vision</h2>'
                .'<p>L\'APCQ aspire à être reconnue comme l\'interlocuteur incontournable auprès du public, des partenaires de l\'industrie du cinéma et des instances gouvernementales.</p><ul><li>Assure la promotion du secteur de l\'exploitation de cinémas.</li><li>Assure à ses membres une représentation constante dans la société.</li><li>Offre des services à ses membres, notamment en termes de prévention, formation et séminaires.</li><li>Représente ses membres auprès de la communauté.</li><li>Collecte, reçoit et distribue à ses membres toute information pertinente.</li></ul>',
            'is_published' => true,
            'sort_order' => 0,
        ]);
    }
}
