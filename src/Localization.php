<?php declare(strict_types=1);
namespace Jarzon;

use Prim\View;

class Localization
{
    protected View $view;

    public string $language = 'fr_CA';
    static public int $messagesLanguage = 0;
    public array $messages = [];

    protected array $options = [];

    public function __construct(View $view, array $options = [])
    {
        $this->view = $view;

        $this->options = $options += [
            'root' => ''
        ];

        $this->buildLocalization();
    }

    function buildLocalization(): void
    {
        $this->fetchTranslation();
        $this->setMessagesLanguage();

        $this->view->registerFunction('_', function(string $message, ...$args) {
            return $this->translate($message, $args);
        });
    }

    function translate(string $message, $args = []): string
    {
        $message = $this->getTranslation($message);

        if(!empty($args)) {

            array_walk($args, function(&$msg, $key) {
                $msg = $this->getTranslation($msg);
            });

            $message = sprintf($message, ...$args);
        }

        return $message;
    }

    function getTranslation(string $msg): string
    {
        return isset($this->messages[$msg])? ($this->messages[$msg][self::$messagesLanguage] ?? $this->messages[$msg][self::$messagesLanguage -1]): $msg;
    }

    function getLanguage(): string
    {
        return explode('_', $this->language)[0];
    }

    function setLanguage(string $language): void
    {
        $this->language = $language;
        $this->setMessagesLanguage();
    }

    protected function setMessagesLanguage(): void
    {
        $index = array_search($this->language, $this->messages['languages']);
        self::$messagesLanguage = $index? $index: 0;
    }

    protected function fetchTranslation(): void
    {
        $appDir = "{$this->options['root']}app";

        $cache = "$appDir/cache/messages.cache";

        if($this->options['environment'] !== 'dev' && file_exists($cache)) {
            $this->messages = include($cache);
        }
        else {
            $file = "$appDir/config/messages.json";

            if (file_exists($file)) {
                $this->messages = json_decode(file_get_contents($file), true);

                if($this->options['environment'] !== 'dev') {
                    $content = $this->generatePHP();

                    file_put_contents($cache, "<?php return $content; ?>");
                }
            }
        }
    }

    public function generatePHP(): string
    {
        return var_export($this->messages, true);
    }

    public function getClientLanguage($lang = null): string
    {
        $lang = $lang ?? $this->language;

        if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $userLang = \Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if($userLang) {
                $lang = \Locale::lookup($this->messages['languages'], $userLang, true, $lang);
            }
        }

        return $lang;
    }
}
