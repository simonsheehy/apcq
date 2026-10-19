<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Background colors offered for the training cards of the portal, taken from the portal design.
 */
enum TrainingCardColor: string implements HasLabel
{
    case Cyan = 'cyan';
    case Yellow = 'yellow';
    case Mist = 'mist';
    case Sky = 'sky';
    case Coral = 'coral';
    case Lime = 'lime';

    /**
     * Colors used, in turn, for trainings without a chosen color.
     */
    private const FALLBACK_SEQUENCE = [self::Cyan, self::Yellow, self::Mist, self::Mist, self::Cyan, self::Yellow];

    public function getLabel(): string
    {
        return match ($this) {
            self::Cyan => 'Turquoise',
            self::Yellow => 'Jaune',
            self::Mist => 'Gris pâle',
            self::Sky => 'Bleu',
            self::Coral => 'Corail',
            self::Lime => 'Vert lime',
        };
    }

    public function hex(): string
    {
        return match ($this) {
            self::Cyan => '#7fdde9',
            self::Yellow => '#ffd200',
            self::Mist => '#eeeeee',
            self::Sky => '#29b5e8',
            self::Coral => '#f2665c',
            self::Lime => '#c8d62e',
        };
    }

    public static function fallbackForPosition(int $position): self
    {
        return self::FALLBACK_SEQUENCE[$position % count(self::FALLBACK_SEQUENCE)];
    }

    /**
     * Option labels with a color swatch, for the administration form.
     *
     * @return array<string, string>
     */
    public static function swatchOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $color): array => [
                $color->value => '<span style="display:inline-flex;align-items:center;gap:.5rem">'
                    .'<span style="display:inline-block;width:1rem;height:1rem;border-radius:.25rem;border:1px solid rgba(0,0,0,.15);background:'.$color->hex().'"></span>'
                    .e($color->getLabel()).'</span>',
            ])
            ->all();
    }
}
