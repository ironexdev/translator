<?php

namespace Ironex\Example;

use Ironex\Exception\TranslationNotFoundIronException;
use Ironex\Translator;

class IndexController
{
    /**
     * @return void
     * @throws TranslationNotFoundIronException
     */
    public function renderDefault(): void
    {
        $czech = new Language("cs-CZ");
        $english = new Language("en-US");

        $languages = [
            $czech,
            $english
        ];

        $translationsDirectory = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "translations";
        $translator = new Translator($english, $english, $languages, $translationsDirectory, true);

        $translator->synchronizeTranslationFiles();
        $penguin = $translator->translate("Penguin");
        $penguins = $translator->translate("Penguin", 2);
        // $translator->updateTranslation("", "Penguin", "Penguin", ["%s penguins", "%s penguins"]);
        // $translator->updateTranslation("", "Penguin", "Tučňák", ["%s tučňáci", "%s tučňáků", "%s tučňáků"]);

        echo $penguin . "<br><br>" . $penguins;
    }
}