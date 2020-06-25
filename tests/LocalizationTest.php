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

        $this->assertEquals('fr', $local->getLanguage());

        return $local;
    }

    /**
     * @depends testGetLanguage
     */
    public function testTranslate(Localization $local)
    {
        $local->setLanguage('en_CA');
        $this->assertEquals('Translated test', $local->translate('test'));
    }

    /**
     * @depends testGetLanguage
     */
    public function testSetLanguage(Localization $local)
    {
        $local->setLanguage('fr_CA');
        $this->assertEquals('Test traduit', $local->translate('test'));
    }

    /**
     * @depends testGetLanguage
     */
    public function testGetClientLanguage(Localization $local)
    {
        $this->assertEquals('fr_CA', $local->getClientLanguage());

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es; jp';

        $this->assertEquals('en_CA', $local->getClientLanguage('en_CA'));

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en; es jp';

        $this->assertEquals('fr_CA', $local->getClientLanguage('fr_CA'));

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = '*';

        $this->assertEquals('fr_CA', $local->getClientLanguage('fr_CA'));
    }

    /**
     * @depends testGetLanguage
     */
    public function testGeneratePHP(Localization $local)
    {
        $this->assertEquals("array (
  'languages' => 
  array (
    0 => 'en_CA',
    1 => 'fr_CA',
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
