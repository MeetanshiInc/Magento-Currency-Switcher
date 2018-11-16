<?php

class Meetanshi_Currencyswitcher_Model_Store extends Mage_Core_Model_Store
{
    public function getDefaultCurrencyCode()
    {
        try {
            $dataWrapper = Mage::getModel('currencyswitcher/Datawrapper');
            $dedaultCurrency = $this->getConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_DEFAULT);
            if ($dataWrapper->isActive()) {
                $geoCountry = $dataWrapper->getCountryByIp(Mage::helper('core/http')->getRemoteAddr($ipToLong = false));
                $geoCurrencyCode = $dataWrapper->getCurrencyByCountry($geoCountry);
                $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
                if (!in_array($geoCurrencyCode, $allowedCurrencies)) {
                    return $dedaultCurrency;
                } else {
                    return $geoCurrencyCode;
                }
            } else {
                return $dedaultCurrency;
            }
        } catch (Exception $e) {
            Mage::log("Geoip Error:" . $e->getMessage(), null, "Currencyswitcher.log");
        }
    }
}

