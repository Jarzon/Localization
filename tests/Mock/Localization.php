<?php
namespace Tests\Mock;

Class Localization extends \Jarzon\Localization {
    public function __construct()
    {
        $this->messages = [
            'languages' => ['en', 'fr'],
            'test' => ['Translated test', 'Test traduit'],
            "testing ' \"" => ["testing ' \"", 'teste \' "'],
        ];

        $this->setMessagesLanguage();
    }
}
