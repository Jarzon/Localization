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

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es; jp';

        $this->assertEquals('fr', $local->getClientLanguage('fr'));

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en; es jp';

        $this->assertEquals('en', $local->getClientLanguage('fr'));

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = '*';

        $this->assertEquals('fr', $local->getClientLanguage('fr'));
    }

    /**
     * @depends testGetLanguage
     */
    public function testGeneratePHP(Localization $local)
    {
        $this->assertEquals("array (
  'languages' => 
  array (
    0 => 'en',
    1 => 'fr',
  ),
  'test' => 
  array (
    0 => 'Translated test',
    1 => 'Test traduit',
  ),
  'testing \' \"' => 
  array (
    0 => 'testing \' \"',
    1 => 'teste \' \"',
  ),
)", $local->generatePHP());
    }
}
