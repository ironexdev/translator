<?php

namespace Ironex;

use Gettext\Translation as Translation;
use Gettext\Translations as Translations;
use Ironex\Exception\TranslationNotFoundIronException;
use Ironex\Exception\TranslationsFileNotFoundIronException;

interface TranslatorInterface
{
    /**
     * @return int
     */
    public function getPluralFormCount(): int;

    /**
     * @return array
     * @throws TranslationsFileNotFoundIronException
     */
    public function getCompleteTranslations(): array;

    /**
     * @return array
     * @throws TranslationsFileNotFoundIronException
     */
    public function getIncompleteTranslations(): array;

    /**
     * @param string $msgid
     * @param string $msgctx
     * @return Translation|null
     * @throws TranslationsFileNotFoundIronException
     */
    public function getTranslation(string $msgid, string $msgctx = ""): ?Translation;

    /**
     * @throws TranslationsFileNotFoundIronException
     */
    public function getTranslations(): Translations;

    /**
     * @param $value
     * @param string|null $translationStatus
     * @return array
     */
    public function searchTranslations($value, string $translationStatus = null): array;

    /**
     * @return void
     * @throws TranslationsFileNotFoundIronException
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
     * @throws TranslationsFileNotFoundIronException
     */
    public function updateTranslation(string $msgctx, string $msgid, string $msgTranslation, array $msgPluralTranslations): void;
}