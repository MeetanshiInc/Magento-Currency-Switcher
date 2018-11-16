<?php

class Meetanshi_Currencyswitcher_Model_Observer
{
    public function storeSwitching()
    {
        try {
            if (Mage::getModel('currencyswitcher/Datawrapper')->isActive()) {
                $allStore = Mage::app()->getStores();
                foreach ($allStore as $eachStore => $val) {
                    $country =  Mage::getModel('currencyswitcher/Datawrapper')->getCountryByIp(Mage::helper('core/http')->getRemoteAddr($ipToLong = false));
                    $storeId = Mage::app()->getStore($eachStore)->getId();

                    $countryCode = Mage::getStoreConfig('general/country/default', $storeId);
                    if ($countryCode == $country) {
                        Mage::app()->setCurrentStore($storeId);
                        break;
                    }
                }
            }
        } catch (Exception $e) {
            Mage::log("Geo Ip Observer Error:" . $e->getMessage(), null, "Currencyswitcher.log");
        }
    }
}

