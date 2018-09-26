<?php

namespace Ironex;

use Gettext\Translator as GtxtTranslator;
use Gettext\Translation as GtxtTranslation;
use Gettext\Translations as GtxtTranslations;
use Ironex\Example\Language;
use Ironex\Exception\TranslationNotFoundIronException;
use Ironex\Exception\TranslationsFileNotFoundIronException;

class Translator implements TranslatorInterface
{
    const MO_EXTENSION = "mo";
    const PO_EXTENSION = "po";

    /**
     * @var Language
     */
    private $currentLanguage;

    /**
     * @var Language
     */
    private $defaultLanguage;

    /**
     * @var bool
     */
    private $isTranslationEnvironment;

    /**
     * @var LanguageInterface[]
     */
    private $languages;

    /**
     * @var array
     */
    private $pluralForm;

    /**
     * @var GtxtTranslations
     */
    private $translations;

    /**
     * @var GtxtTranslations
     */
    private $translationsWithIndexes;

    /**
     * @var string
     */
    private $translationsDirectory;

    /**
     * @var GtxtTranslator
     */
    private $translator;

    /**
     * Translator constructor.
     * @param LanguageInterface $currentLanguage
     * @param LanguageInterface $defaultLanguage
     * @param array $languages
     * @param string $translationsDirectory
     * @param bool $isTranslationEnvironment
     */
    public function __construct(LanguageInterface $currentLanguage, LanguageInterface $defaultLanguage, array $languages, string $translationsDirectory, bool $isTranslationEnvironment = true)
    {
        $this->currentLanguage = $currentLanguage;
        $this->defaultLanguage = $defaultLanguage;
        $this->isTranslationEnvironment = $isTranslationEnvironment;
        $this->languages = $languages;
        $this->translationsDirectory = $translationsDirectory;
        $this->translator = new GtxtTranslator();

        $this->translations = $this->loadTranslationsFromFile($currentLanguage);
        $this->translator->loadTranslations($this->translations);

        $this->pluralForm = $this->translations->getPluralForms();
    }

    /**
     * @return int
     */
    public function getPluralFormCount(): int
    {
        return $this->pluralForm[0] - 1;
    }

    /**
     * @return array
     * @throws TranslationsFileNotFoundIronException
     */
    public function getCompleteTranslations(): array
    {
        $translations = $this->getTranslations();
        $pluralFormCount = $this->getPluralFormCount();

        foreach($translations as $id => $translation)
        {
            /** @var GtxtTranslation $translation */
            if(!$translation->getTranslation() || count($translation->getPluralTranslations()) !== $pluralFormCount)
            {
                unset($translations[$id]);
            }
        }

        return array_values($translations->getArrayCopy());
    }

    /**
     * @return array
     * @throws TranslationsFileNotFoundIronException
     */
    public function getIncompleteTranslations(): array
    {
        $translations = $this->getTranslations();
        $pluralFormCount = $this->getPluralFormCount();

        foreach($translations as $id => $translation)
        {
            /** @var GtxtTranslation $translation */
            if($translation->getTranslation() && count($translation->getPluralTranslations()) === $pluralFormCount)
            {
                unset($translations[$id]);
            }
        }

        return array_values($translations->getArrayCopy());
    }

    /**
     * @param string $msgid
     * @param string $msgctx
     * @return GtxtTranslation|null
     * @throws TranslationsFileNotFoundIronException
     */
    public function getTranslation(string $msgid, string $msgctx = ""): ?GtxtTranslation
    {
        if(!$this->translationsWithIndexes)
        {
            $this->translationsWithIndexes = $this->loadTranslationsWithIndexesFromFile($this->currentLanguage);
        }

        return $this->translationsWithIndexes->find($msgctx, $msgid) ?: null;
    }

    /**
     * @throws TranslationsFileNotFoundIronException
     */
    public function getTranslations(): GtxtTranslations
    {
        if(!$this->translationsWithIndexes)
        {
            $this->translationsWithIndexes = $this->loadTranslationsWithIndexesFromFile($this->currentLanguage);
        }

        return $this->translationsWithIndexes;
    }

    /**
     * @param $value
     * @return array
     * @throws TranslationsFileNotFoundIronException
     */
    public function searchTranslations($value): array
    {
        $translations = $this->getTranslations();

        $input = preg_quote($value, "~");

        $result = array_filter($translations->getArrayCopy(), function($translation) use($input)  {

            /** @var GtxtTranslation $translation */
            return preg_grep("~" . $input . "~", [
                "original" => $translation->getOriginal() ?? "",
                "translation" => $translation->getTranslation() ?? "",
                "plural" => $translation->getPlural() ?? ""
            ]);
        });

        return array_values($result);
    }

    /**
     * @return void
     * @throws TranslationsFileNotFoundIronException
     */
    public function synchronizeTranslationFiles(): void
    {
        try
        {
            $defaultTranslations = $this->getTranslations();
        }
        catch (TranslationsFileNotFoundIronException $e)
        {
            $this->createTranslationsFile(new GtxtTranslations(), $this->defaultLanguage);
            $defaultTranslations = $this->getTranslations();
        }

        foreach($this->languages as $language)
        {
            if($language->getLocale() === $this->defaultLanguage->getLocale())
            {
                continue;
            }

            try
            {
                $translations = $this->loadTranslationsWithIndexesFromFile($language);
            }
            catch (TranslationsFileNotFoundIronException $e)
            {
                $this->createTranslationsFile(new GtxtTranslations(), $language);
                $translations = $this->loadTranslationsWithIndexesFromFile($language);
            }

            /** @var GtxtTranslation $defaultTranslation */
            foreach($defaultTranslations as $defaultTranslation)
            {
                if(!$translations->find($defaultTranslation->getContext(), $defaultTranslation->getOriginal()))
                {
                    $translations[] = $this->createTranslation($defaultTranslation->getOriginal(), $defaultTranslation->getContext());
                }
            }

            $this->saveTranslationsToFile($translations, $language);
        }
    }

    /**
     * @param string $msgid
     * @param int $countable
     * @param string $msgctx
     * @param LanguageInterface $language
     * @return string
     */
    public function translate(string $msgid, int $countable = 1, string $msgctx = "", LanguageInterface $language = null): string
    {
        $currentLanguage = $language ?: $this->currentLanguage;
        $translation = $this->translations->find($msgctx, $msgid);

        if ($this->isTranslationEnvironment && $currentLanguage->getLocale() === $this->defaultLanguage->getLocale() && !$translation)
        {
            $this->translations[] = $this->createTranslation($msgid, $msgctx);
            $this->saveTranslationsToFile($this->translations, $currentLanguage);
        }

        if (!$translation || $countable === 1)
        {
            return $this->translator->pgettext($msgctx, $msgid);
        }
        else
        {
            $pluralTranslation = $this->translator->npgettext($msgctx, $msgid, $translation->getPlural(), $countable);

            if($pluralTranslation)
            {
               return strtr($pluralTranslation, ["{{countable}}" => $countable]);
            }
            else
            {
                return $this->translator->pgettext($msgctx, $msgid);
            }
        }
    }

    /**
     * @param string $msgctx
     * @param string $msgid
     * @param string $msgTranslation
     * @param array $msgPluralTranslations
     * @throws TranslationNotFoundIronException
     * @throws TranslationsFileNotFoundIronException
     */
    public function updateTranslation(string $msgctx, string $msgid, string $msgTranslation, array $msgPluralTranslations): void
    {
        if(!$this->translationsWithIndexes)
        {
            $this->translationsWithIndexes = $this->getTranslations();
        }

        $translation = $this->translationsWithIndexes->find($msgctx, $msgid);

        if (!$translation)
        {
            throw new TranslationNotFoundIronException();
        }

        if ($this->getPluralFormCount() !== count($msgPluralTranslations))
        {
            trigger_error("Plural form count (msgid: " . $msgid . ") does not match plural forms definition in " . __CLASS__, E_USER_ERROR);
        }

        $translation->setTranslation($msgTranslation)
                    ->setPlural($msgPluralTranslations[0])
                    ->setPluralTranslations($msgPluralTranslations);

        $this->saveTranslationsToFile($this->translationsWithIndexes, $this->currentLanguage);
    }

    /**
     * @param string $msgid
     * @param string $msgctx
     * @return GtxtTranslation
     */
    private function createTranslation(string $msgid, string $msgctx = ""): GtxtTranslation
    {
        return new GtxtTranslation($msgctx, $msgid);
    }

    /**
     * @param GtxtTranslations $translations
     * @param LanguageInterface $language
     */
    private function createTranslationsFile(GtxtTranslations $translations, LanguageInterface $language): void
    {
        $translations->setLanguage($language->getLocale());

        $this->saveTranslationsToFile($translations, $language);
    }

    /**
     * @param LanguageInterface $language
     * @param string $extension
     * @return string
     */
    private function getTranslationFile(LanguageInterface $language, string $extension): string
    {
        return $this->translationsDirectory . DIRECTORY_SEPARATOR . $language->getLocale() . "." . $extension;
    }

    /**
     * @param LanguageInterface $language
     * @return GtxtTranslations
     */
    private function loadTranslationsFromFile(LanguageInterface $language): GtxtTranslations
    {
        $translationFile = $this->getTranslationFile($language, static::MO_EXTENSION);

        if($this->translationFileExists($translationFile))
        {
            $translations = GtxtTranslations::fromMoFile($translationFile);
        }
        else
        {
            $translations = new GtxtTranslations();
            $this->createTranslationsFile($translations, $language);
        }

        return $translations;
    }

    /**
     * @param LanguageInterface $language
     * @return GtxtTranslations
     * @throws TranslationsFileNotFoundIronException
     */
    private function loadTranslationsWithIndexesFromFile(LanguageInterface $language): GtxtTranslations
    {
        $translationFile = $this->getTranslationFile($language, static::PO_EXTENSION);

        if(!$this->translationFileExists($translationFile))
        {
            throw new TranslationsFileNotFoundIronException();
        }

        return GtxtTranslations::fromPoFile($translationFile);
    }

    /**
     * @param GtxtTranslations $translations
     * @param LanguageInterface $language
     */
    private function saveTranslationsToFile(GtxtTranslations $translations, LanguageInterface $language): void
    {
        $translations->toPoFile($this->getTranslationFile($language, static::PO_EXTENSION));
        $translations->toMoFile($this->getTranslationFile($language, static::MO_EXTENSION));
    }

    /**
     * @param string $translationFile
     * @return bool
     */
    private function translationFileExists(string $translationFile): bool
    {
        return file_exists($translationFile);
    }
}