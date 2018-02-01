<?php
namespace Jarzon\Container;

trait Localization {
    /**
     * @return \Jarzon\Localization
     */
    public function getLocalizationService()
    {
        $obj = 'localizationService';

        $this->setDefaultParameter($obj, '\Jarzon\Localization');

        return $this->init($obj, $this->getView(), $this->options);
    }
}