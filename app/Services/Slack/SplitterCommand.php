<?php

namespace App\Services\Slack;

final readonly class SplitterCommand
{
    private const MENTION = '/<@([UW][A-Z0-9]+)(?:\|[^>]*)?>/';

    private const AMOUNT = '/(?<!\S)(\d+(?:[.,]\d{1,2})?)\s*(?:zł|zl|pln)?(?!\S)/iu';

    public function __construct(
        public string $debtorSlackId,
        public float $amount,
        public string $place,
    ) {}

    public static function fromText(string $text): self
    {
        preg_match_all(self::MENTION, $text, $mentions);

        if (count($mentions[1]) !== 1) {
            throw new InvalidSplitterCommand('Oznacz dokładnie jedną osobę, np. @Marcin.');
        }

        $rest = preg_replace(self::MENTION, ' ', $text);

        if (! preg_match(self::AMOUNT, $rest, $match, PREG_OFFSET_CAPTURE)) {
            throw new InvalidSplitterCommand('Podaj kwotę, np. 15zl albo 15,50.');
        }

        $amount = (float) str_replace(',', '.', $match[1][0]);

        if ($amount <= 0) {
            throw new InvalidSplitterCommand('Kwota musi być większa od zera.');
        }

        $rest = substr_replace($rest, ' ', $match[0][1], strlen($match[0][0]));
        $place = trim(preg_replace('/\s+/u', ' ', $rest));

        if ($place === '') {
            throw new InvalidSplitterCommand('Podaj miejsce, np. Lidl.');
        }

        return new self($mentions[1][0], $amount, $place);
    }
}
