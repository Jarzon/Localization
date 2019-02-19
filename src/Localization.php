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
        $file = "{$this->options['root']}app/config/messages.json";

        // Check if we have a translation file for that language
        if (file_exists($file)) {
            // TODO: Cache the file
            $this->messages = json_decode(file_get_contents($file), true);
        }
    }

    function getClientLanguage() {
        $lang = $this->language;

        if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $lang = \Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            $lang = substr($lang, 0, 2);
        }

        return $lang;
    }
}