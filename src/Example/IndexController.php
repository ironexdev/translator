<?php

namespace Ironex\Example;

use Ironex\Exception\TranslationsFileNotFoundIronException;
use Ironex\Translator;

class IndexController
{
    /**
     * @return void
     * @throws TranslationsFileNotFoundIronException
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
        $singular = $translator->translate("Hi, {{name}}");
        $plural = $translator->translate("Hi, {{name}}", 2);
        // $translator->updateTranslation("", "Hi, {{name}}", "Hi, {{name}}", ["Hi, {{countable}} guys"]);
        // $translator->updateTranslation("", "Hi, {{name}}", "Ahoj, {{name}}", ["Ahoj, {{countable}} lidi", "Ahoj, {{countable}}"]);
        // var_dump($translator->searchTranslations("i"));
        // var_dump($translator->getIncompleteTranslations());
        // var_dump($translator->getCompleteTranslations());

        echo strtr($singular, ["{{name}}" => "Jack"]) . "<br><br>" . $plural;
    }
}