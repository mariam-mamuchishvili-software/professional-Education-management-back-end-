<?php

namespace Tests\Unit;

use App\Services\Passwords\PassphraseGenerator;
use PHPUnit\Framework\TestCase;

class PassphraseGeneratorTest extends TestCase
{
    public function test_generates_capitalized_words_and_a_two_digit_number_joined_by_hyphens(): void
    {
        $passphrase = (new PassphraseGenerator)->generate();

        $this->assertMatchesRegularExpression('/^([A-Z][a-z]+-){4}\d{2}$/', $passphrase);
    }

    public function test_word_count_and_separator_can_be_changed(): void
    {
        $passphrase = (new PassphraseGenerator)->generate(wordCount: 6, separator: '.');

        $this->assertMatchesRegularExpression('/^([A-Z][a-z]+\.){6}\d{2}$/', $passphrase);
    }

    public function test_generates_a_different_passphrase_each_time(): void
    {
        $generator = new PassphraseGenerator;

        $passphrases = array_map(fn (): string => $generator->generate(), range(1, 50));

        $this->assertCount(50, array_unique($passphrases));
    }
}
