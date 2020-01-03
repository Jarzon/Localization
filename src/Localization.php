<?php
namespace Jarzon;

use Prim\View;

class Localization
{
    protected View $view;

    public string $language = 'en';
    static public string $messagesLanguage = '';
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

        $this->view->registerFunction('currencyFormat', function(string $message) {
            return $this->currency($message);
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
        return (isset($this->messages[$msg]))? $this->messages[$msg][self::$messagesLanguage]: $msg;
    }

    function currency(float $price): string
    {
        return number_format($price, 2, ',', ' ') . ' $';
    }

    function getLanguage(): string
    {
        return $this->language;
    }

    function setLanguage(string $language): void
    {
        $this->language = $language;
        $this->setMessagesLanguage();
    }

    function setMessagesLanguage(): void
    {
        self::$messagesLanguage = array_search($this->language, $this->messages['languages']);
    }

    function fetchTranslation(): void
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
                    $content = '';

                    foreach ($this->messages as $index => $array) {
                        $messages = [];
                        foreach ($array as $message) {
                            $messages[] = "'".str_replace("'", "\'", $message)."'";
                        }

                        $content .= "'".str_replace("'", "\'", $index)."' => [" . implode(',', $messages) . "],";
                    }


                    file_put_contents($cache, "<?php return [$content]; ?>");
                }
            }
        }
    }

    public function getClientLanguage($lang = null): string
    {
        $lang = $lang ?? $this->language;

        if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $userLang = \Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            $lang = \Locale::lookup($this->messages['languages'], $userLang, true, $lang);
        }

        return $lang;
    }
}
