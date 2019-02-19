<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Mock\Localization;

class LocalizationTest extends TestCase
{
    public function testGetLanguage()
    {
        $local = new Localization();

        $local->setMessagesLanguage();

        $this->assertEquals('en', $local->getLanguage());

        return $local;
    }

    /**
     * @depends testGetLanguage
     */
    public function testTranslate(Localization $local)
    {
        $this->assertEquals('Translated test', $local->translate('test'));
    }

    /**
     * @depends testGetLanguage
     */
    public function testSetLanguage(Localization $local)
    {
        $local->setLanguage('fr');
        $this->assertEquals('Test traduit', $local->translate('test'));
    }

    /**
     * @depends testGetLanguage
     */
    public function testGetClientLanguage(Localization $local)
    {
        $this->assertEquals('fr', $local->getClientLanguage());
    }
}