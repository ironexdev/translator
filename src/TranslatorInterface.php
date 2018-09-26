<?php

namespace Ironex;

use Gettext\Translation as GtxtTranslation;
use Gettext\Translations as GtxtTranslations;
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
     * @return GtxtTranslation|null
     * @throws TranslationsFileNotFoundIronException
     */
    public function getTranslation(string $msgid, string $msgctx = ""): ?GtxtTranslation;

    /**
     * @throws TranslationsFileNotFoundIronException
     */
    public function getTranslations(): GtxtTranslations;

    /**
     * @param $value
     * @return array
     * @throws TranslationsFileNotFoundIronException
     */
    public function searchTranslations($value): array;

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