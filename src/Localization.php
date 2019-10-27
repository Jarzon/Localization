<?php
namespace Jarzon;

class Localization
{
    protected $view;

    public $language = 'en';
    static public $messagesLanguage = '';
    public $messages = [];

    protected $options = [];

    /** @var $view \Prim\View; */
    public function __construct($view, array $options = [])
    {
        $this->view = $view;

        $this->options = $options += [
            'root' => ''
        ];

        $this->buildLocalization();
    }

    function buildLocalization() {
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

    function getTranslation(string $msg)
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

    function setLanguage(string $language)
    {
        $this->language = $language;
        $this->setMessagesLanguage();
    }

    function setMessagesLanguage()
    {
        self::$messagesLanguage = array_search($this->language, $this->messages['languages']);
    }

    function fetchTranslation()
    {
        $appDir = "{$this->options['root']}app";

        $cache = "$appDir/cache/messages.cache";

        if($this->options['env'] !== 'dev' && file_exists($cache)) {
            $this->messages = include($cache);
        }
        else {
            $file = "$appDir/config/messages.json";

            if (file_exists($file)) {
                $this->messages = json_decode(file_get_contents($file), true);

                if($this->options['env'] !== 'dev') {
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

    function getClientLanguage($lang = null) {
        $lang = $lang ?? $this->language;

        if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $lang = \Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            $lang = substr($lang, 0, 2);
        }

        return $lang;
    }
}
