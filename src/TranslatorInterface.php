<?php

namespace Ironex;

use Ironex\Exception\TranslationNotFoundIronException;

interface TranslatorInterface
{
    /**
     * @return int
     */
    public function getPluralFormCount(): int;

    /**
     * @return void
     */
    public function synchronizeTranslationFiles(): void;

    /**
     * @param string $msgid
     * @param int $countable
     * @param string $msgctx
     * @param LanguageInterface $language
     * @return string
     */
    public function translate(string $msgid, int $countable = 1, string $msgctx = "", LanguageInterface $language = null): string;

    /**
     * @param string $msgctx
     * @param string $msgid
     * @param string $msgTranslation
     * @param array $msgPluralTranslations
     * @throws TranslationNotFoundIronException
     */
    public function updateTranslation(string $msgctx, string $msgid, string $msgTranslation, array $msgPluralTranslations): void;
}