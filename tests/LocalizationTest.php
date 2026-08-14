<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Localization;

class LocalizationTest extends TestCase
{
    public function testGetLanguage(): Localization
    {
        $local = new Localization();

        $local->setLanguage('fr');

        $this->assertEquals('fr', $local->getLanguage());

        return $local;
    }

    #[Depends('testGetLanguage')]
    public function testTranslate(Localization $local)
    {
        $local->setLanguage('en_CA');
        $this->assertEquals('Translated test', $local->translate('test'));
    }

    #[Depends('testGetLanguage')]
    public function testSetLanguage(Localization $local)
    {
        $local->setLanguage('fr_CA');
        $this->assertEquals('Test traduit', $local->translate('test'));
    }

    #[Depends('testGetLanguage')]
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

    #[Depends('testGetLanguage')]
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

    #[Depends('testGetLanguage')]
    public function testExtractMessages(Localization $local)
    {
        $local->messages = [
            'languages' => ['en', 'fr'],
            'test' => ['testen', 'testfr'],
            'test2' => ['test2en', 'test2fr'],
        ];
        $this->assertEquals([], $local->extractMessages([], 'fr'));

        $this->assertEquals(['test' => 'testfr'], $local->extractMessages(['test'],'fr'));
        $this->assertEquals(['test' => 'testen', 'test2' => 'test2en'], $local->extractMessages(['test', 'test2'],'en'));
    }
}
