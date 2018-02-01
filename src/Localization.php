<?php
namespace Jarzon;

class Localization
{
    protected $view;

    public $language = 'en';
    static public $messagesLanguage = '';
    public $messages = [];

    protected $options = [];

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

        $this->view->registerFunction('_', function(string $message) {
            return $this->translate($message);
        });

        $this->view->registerFunction('currencyFormat', function(string $message) {
            return $this->currency($message);
        });
    }

    function translate(string $message) : string
    {
        return (isset($this->messages[$message]))? $this->messages[$message][self::$messagesLanguage]: $message;
    }

    function currency(float $price) : string
    {
        return number_format($price, 2, ',', ' ') . ' $';
    }

    function getLanguage() : string
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
}