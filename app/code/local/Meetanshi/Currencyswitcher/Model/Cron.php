<?php

class Meetanshi_Currencyswitcher_Model_Cron
{
    public function dataDownload()
    {
        try {
            $zipUrl = "http://geolite.maxmind.com/download/geoip/database/GeoLiteCountry/GeoIP.dat.gz";
            file_put_contents("media/currencyswitcher/GeoIP.zip", fopen($zipUrl, 'r'));
            $archiveFile = "media/currencyswitcher/GeoIP.zip";
            $destinationFile = "media/currencyswitcher/GeoIP.dat";

            $bufferSize = 4096;
            $archiveFile = gzopen($archiveFile, 'rb');
            $data = fopen($destinationFile, 'wb');
            while (!gzeof($archiveFile)) {
                fwrite($data, gzread($archiveFile, $bufferSize));
            }

            fclose($data);
            gzclose($archiveFile);
        } catch (Exception $e) {
            Mage::log("Database Download Error" . $e->getMessage(), null, "Currencyswitcher.log");
        }
    }
}
