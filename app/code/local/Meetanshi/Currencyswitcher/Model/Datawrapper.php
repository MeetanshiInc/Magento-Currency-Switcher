<?php

class Meetanshi_Currencyswitcher_Model_Datawrapper
{
    const GEOIPCURRENCY_ENABLE = 'currencyswitcher/general/enable';

    private $flags;
    private $filehandle;
    private $memory_buffer;
    private $databaseType;
    private $databaseSegments;
    private $record_length;
    private $shmid;

    private $GEOIP_COUNTRY_CODES = array(
        "", "AP", "EU", "AD", "AE", "AF", "AG", "AI", "AL", "AM", "CW",
        "AO", "AQ", "AR", "AS", "AT", "AU", "AW", "AZ", "BA", "BB",
        "BD", "BE", "BF", "BG", "BH", "BI", "BJ", "BM", "BN", "BO",
        "BR", "BS", "BT", "BV", "BW", "BY", "BZ", "CA", "CC", "CD",
        "CF", "CG", "CH", "CI", "CK", "CL", "CM", "CN", "CO", "CR",
        "CU", "CV", "CX", "CY", "CZ", "DE", "DJ", "DK", "DM", "DO",
        "DZ", "EC", "EE", "EG", "EH", "ER", "ES", "ET", "FI", "FJ",
        "FK", "FM", "FO", "FR", "SX", "GA", "GB", "GD", "GE", "GF",
        "GH", "GI", "GL", "GM", "GN", "GP", "GQ", "GR", "GS", "GT",
        "GU", "GW", "GY", "HK", "HM", "HN", "HR", "HT", "HU", "ID",
        "IE", "IL", "IN", "IO", "IQ", "IR", "IS", "IT", "JM", "JO",
        "JP", "KE", "KG", "KH", "KI", "KM", "KN", "KP", "KR", "KW",
        "KY", "KZ", "LA", "LB", "LC", "LI", "LK", "LR", "LS", "LT",
        "LU", "LV", "LY", "MA", "MC", "MD", "MG", "MH", "MK", "ML",
        "MM", "MN", "MO", "MP", "MQ", "MR", "MS", "MT", "MU", "MV",
        "MW", "MX", "MY", "MZ", "NA", "NC", "NE", "NF", "NG", "NI",
        "NL", "NO", "NP", "NR", "NU", "NZ", "OM", "PA", "PE", "PF",
        "PG", "PH", "PK", "PL", "PM", "PN", "PR", "PS", "PT", "PW",
        "PY", "QA", "RE", "RO", "RU", "RW", "SA", "SB", "SC", "SD",
        "SE", "SG", "SH", "SI", "SJ", "SK", "SL", "SM", "SN", "SO",
        "SR", "ST", "SV", "SY", "SZ", "TC", "TD", "TF", "TG", "TH",
        "TJ", "TK", "TM", "TN", "TO", "TL", "TR", "TT", "TV", "TW",
        "TZ", "UA", "UG", "UM", "US", "UY", "UZ", "VA", "VC", "VE",
        "VG", "VI", "VN", "VU", "WF", "WS", "YE", "YT", "RS", "ZA",
        "ZM", "ME", "ZW", "A1", "A2", "O1", "AX", "GG", "IM", "JE",
        "BL", "MF", "BQ");

    private $GEOIP_COUNTRY_BEGIN = 16776960;
    private $GEOIP_STATE_BEGIN_REV0 = 16700000;
    private $GEOIP_STATE_BEGIN_REV1 = 16000000;
    private $GEOIP_MEMORY_CACHE = 1;
    private $GEOIP_SHARED_MEMORY = 2;
    private $STRUCTURE_INFO_MAX_SIZE = 20;
    private $GEOIP_COUNTRY_EDITION = 106;
    private $GEOIP_PROXY_EDITION = 8;
    private $GEOIP_ASNUM_EDITION = 9;
    private $GEOIP_NETSPEED_EDITION = 10;
    private $GEOIP_REGION_EDITION_REV0 = 112;
    private $GEOIP_REGION_EDITION_REV1 = 3;
    private $GEOIP_CITY_EDITION_REV0 = 111;
    private $GEOIP_CITY_EDITION_REV1 = 2;
    private $GEOIP_ORG_EDITION = 110;
    private $GEOIP_ISP_EDITION = 4;
    private $SEGMENT_RECORD_LENGTH = 3;
    private $STANDARD_RECORD_LENGTH = 3;
    private $ORG_RECORD_LENGTH = 4;
    private $GEOIP_SHM_KEY = 0x4f415401;
    private $GEOIP_DOMAIN_EDITION = 11;
    private $GEOIP_COUNTRY_EDITION_V6 = 12;
    private $GEOIP_LOCATIONA_EDITION = 13;
    private $GEOIP_ACCURACYRADIUS_EDITION = 14;
    private $GEOIP_CITY_EDITION_REV1_V6 = 30;
    private $GEOIP_CITY_EDITION_REV0_V6 = 31;
    private $GEOIP_NETSPEED_EDITION_REV1 = 32;
    private $GEOIP_NETSPEED_EDITION_REV1_V6 = 33;
    private $GEOIP_USERTYPE_EDITION = 28;
    private $GEOIP_USERTYPE_EDITION_V6 = 29;
    private $GEOIP_ASNUM_EDITION_V6 = 21;
    private $GEOIP_ISP_EDITION_V6 = 22;
    private $GEOIP_ORG_EDITION_V6 = 23;
    private $GEOIP_DOMAIN_EDITION_V6 = 24;


    public function isActive()
    {
        return (bool)Mage::getStoreConfig(self::GEOIPCURRENCY_ENABLE);
    }

    public function getCountryByIp($ipAddress)
    {
        try {
            if ($this->isActive()) {
                if ($this->geoip_open('media/currencyswitcher/GeoIP.dat', 0)) {
                    $country = $this->geoip_country_code_by_addr($ipAddress);
                    $this->geoip_close();
                    return $country;
                } else {
                    return null;
                }
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, "Currencyswitcher.log");
        }
    }

    public function geoip_open($filename, $flags)
    {
        $this->flags = $flags;
        if ($this->flags & $this->GEOIP_SHARED_MEMORY) {
            $this->shmid = @shmop_open($this->GEOIP_SHM_KEY, "a", 0, 0);
        } else {
            if (file_exists($filename) && $this->filehandle = fopen($filename, "rb")) {
                if ($this->flags & $this->GEOIP_MEMORY_CACHE) {
                    $s_array = fstat($this->filehandle);
                    $this->memory_buffer = fread($this->filehandle, $s_array['size']);
                }
            } else {
                return false;
            }
        }

        $this->_setup_segments();
        return true;
    }

    public function geoip_close()
    {
        if ($this->flags & $this->GEOIP_SHARED_MEMORY) {
            return true;
        }

        return fclose($this->filehandle);
    }

    public function geoip_country_code_by_addr($addr)
    {
        $country_id = $this->geoip_country_id_by_addr($addr);
        return $country_id !== false ? $this->GEOIP_COUNTRY_CODES[$country_id] : false;
    }

    public function geoip_country_id_by_addr($addr)
    {
        $ipnum = ip2long($addr);
        return $this->_geoip_seek_country($ipnum) - $this->GEOIP_COUNTRY_BEGIN;
    }

    private function _geoip_seek_country($ipnum)
    {
        $offset = 0;
        for ($depth = 31; $depth >= 0; --$depth) {
            if ($this->flags & $this->GEOIP_MEMORY_CACHE) {
                $enc = mb_internal_encoding();
                mb_internal_encoding('ISO-8859-1');

                $buf = substr(
                    $this->memory_buffer,
                    2 * $this->record_length * $offset,
                    2 * $this->record_length
                );

                mb_internal_encoding($enc);
            } elseif ($this->flags & $this->GEOIP_SHARED_MEMORY) {
                $buf = @shmop_read(
                    $this->shmid,
                    2 * $this->record_length * $offset,
                    2 * $this->record_length
                );
            } else {
                fseek($this->filehandle, 2 * $this->record_length * $offset, SEEK_SET) == 0;
                $buf = fread($this->filehandle, 2 * $this->record_length);
            }

            $x = array(0, 0);
            for ($i = 0; $i < 2; ++$i) {
                for ($j = 0; $j < $this->record_length; ++$j) {
                    $x[$i] += ord($buf[$this->record_length * $i + $j]) << ($j * 8);
                }
            }

            if ($ipnum & (1 << $depth)) {
                if ($x[1] >= $this->databaseSegments) {
                    return $x[1];
                }

                $offset = $x[1];
            } else {
                if ($x[0] >= $this->databaseSegments) {
                    return $x[0];
                }

                $offset = $x[0];
            }
        }

        trigger_error("error traversing database - perhaps it is corrupt?", E_USER_ERROR);
        return false;
    }

    private function _setup_segments()
    {
        $this->databaseType = $this->GEOIP_COUNTRY_EDITION;
        $this->record_length = $this->STANDARD_RECORD_LENGTH;
        if ($this->flags & $this->GEOIP_SHARED_MEMORY) {
            $offset = @shmop_size($this->shmid) - 3;
            for ($i = 0; $i < $this->STRUCTURE_INFO_MAX_SIZE; $i++) {
                $delim = @shmop_read($this->shmid, $offset, 3);
                $offset += 3;
                if ($delim == (chr(255) . chr(255) . chr(255))) {
                    $this->databaseType = ord(@shmop_read($this->shmid, $offset, 1));
                    $offset++;

                    if ($this->databaseType == $this->GEOIP_REGION_EDITION_REV0) {
                        $this->databaseSegments = $this->GEOIP_STATE_BEGIN_REV0;
                    } else if ($this->databaseType == $this->GEOIP_REGION_EDITION_REV1) {
                        $this->databaseSegments = $this->GEOIP_STATE_BEGIN_REV1;
                    } else if (($this->databaseType == $this->GEOIP_CITY_EDITION_REV0) ||
                        ($this->databaseType == $this->GEOIP_CITY_EDITION_REV1)
                        || ($this->databaseType == $this->GEOIP_ORG_EDITION)
                        || ($this->databaseType == $this->GEOIP_ORG_EDITION_V6)
                        || ($this->databaseType == $this->GEOIP_DOMAIN_EDITION)
                        || ($this->databaseType == $this->GEOIP_DOMAIN_EDITION_V6)
                        || ($this->databaseType == $this->GEOIP_ISP_EDITION)
                        || ($this->databaseType == $this->GEOIP_ISP_EDITION_V6)
                        || ($this->databaseType == $this->GEOIP_USERTYPE_EDITION)
                        || ($this->databaseType == $this->GEOIP_USERTYPE_EDITION_V6)
                        || ($this->databaseType == $this->GEOIP_LOCATIONA_EDITION)
                        || ($this->databaseType == $this->GEOIP_ACCURACYRADIUS_EDITION)
                        || ($this->databaseType == $this->GEOIP_CITY_EDITION_REV0_V6)
                        || ($this->databaseType == $this->GEOIP_CITY_EDITION_REV1_V6)
                        || ($this->databaseType == $this->GEOIP_NETSPEED_EDITION_REV1)
                        || ($this->databaseType == $this->GEOIP_NETSPEED_EDITION_REV1_V6)
                        || ($this->databaseType == $this->GEOIP_ASNUM_EDITION)
                        || ($this->databaseType == $this->GEOIP_ASNUM_EDITION_V6)) {
                        $this->databaseSegments = 0;
                        $buf = @shmop_read($this->shmid, $offset, $this->SEGMENT_RECORD_LENGTH);
                        for ($j = 0; $j < $this->SEGMENT_RECORD_LENGTH; $j++) {
                            $this->databaseSegments += (ord($buf[$j]) << ($j * 8));
                        }

                        if (($this->databaseType == $this->GEOIP_ORG_EDITION)
                            || ($this->databaseType == $this->GEOIP_ORG_EDITION_V6)
                            || ($this->databaseType == $this->GEOIP_DOMAIN_EDITION)
                            || ($this->databaseType == $this->GEOIP_DOMAIN_EDITION_V6)
                            || ($this->databaseType == $this->GEOIP_ISP_EDITION)
                            || ($this->databaseType == $this->GEOIP_ISP_EDITION_V6)) {
                            $this->record_length = $this->ORG_RECORD_LENGTH;
                        }
                    }

                    break;
                } else {
                    $offset -= 4;
                }
            }

            if (($this->databaseType == $this->GEOIP_COUNTRY_EDITION) ||
                ($this->databaseType == $this->GEOIP_COUNTRY_EDITION_V6) ||
                ($this->databaseType == $this->GEOIP_PROXY_EDITION) ||
                ($this->databaseType == $this->GEOIP_NETSPEED_EDITION)) {
                $this->databaseSegments = $this->GEOIP_COUNTRY_BEGIN;
            }
        } else {
            $filepos = ftell($this->filehandle);
            fseek($this->filehandle, -3, SEEK_END);
            for ($i = 0; $i < $this->STRUCTURE_INFO_MAX_SIZE; $i++) {
                $delim = fread($this->filehandle, 3);
                if ($delim == (chr(255) . chr(255) . chr(255))) {
                    $this->databaseType = ord(fread($this->filehandle, 1));
                    if ($this->databaseType == $this->GEOIP_REGION_EDITION_REV0) {
                        $this->databaseSegments = $this->GEOIP_STATE_BEGIN_REV0;
                    } else if ($this->databaseType == $this->GEOIP_REGION_EDITION_REV1) {
                        $this->databaseSegments = $this->GEOIP_STATE_BEGIN_REV1;
                    } else if (($this->databaseType == $this->GEOIP_CITY_EDITION_REV0)
                        || ($this->databaseType == $this->GEOIP_CITY_EDITION_REV1)
                        || ($this->databaseType == $this->GEOIP_CITY_EDITION_REV0_V6)
                        || ($this->databaseType == $this->GEOIP_CITY_EDITION_REV1_V6)
                        || ($this->databaseType == $this->GEOIP_ORG_EDITION)
                        || ($this->databaseType == $this->GEOIP_DOMAIN_EDITION)
                        || ($this->databaseType == $this->GEOIP_ISP_EDITION)
                        || ($this->databaseType == $this->GEOIP_ORG_EDITION_V6)
                        || ($this->databaseType == $this->GEOIP_DOMAIN_EDITION_V6)
                        || ($this->databaseType == $this->GEOIP_ISP_EDITION_V6)
                        || ($this->databaseType == $this->GEOIP_LOCATIONA_EDITION)
                        || ($this->databaseType == $this->GEOIP_ACCURACYRADIUS_EDITION)
                        || ($this->databaseType == $this->GEOIP_CITY_EDITION_REV0_V6)
                        || ($this->databaseType == $this->GEOIP_CITY_EDITION_REV1_V6)
                        || ($this->databaseType == $this->GEOIP_NETSPEED_EDITION_REV1)
                        || ($this->databaseType == $this->GEOIP_NETSPEED_EDITION_REV1_V6)
                        || ($this->databaseType == $this->GEOIP_USERTYPE_EDITION)
                        || ($this->databaseType == $this->GEOIP_USERTYPE_EDITION_V6)
                        || ($this->databaseType == $this->GEOIP_ASNUM_EDITION)
                        || ($this->databaseType == $this->GEOIP_ASNUM_EDITION_V6)) {
                        $this->databaseSegments = 0;
                        $buf = fread($this->filehandle, $this->SEGMENT_RECORD_LENGTH);
                        for ($j = 0; $j < $this->SEGMENT_RECORD_LENGTH; $j++) {
                            $this->databaseSegments += (ord($buf[$j]) << ($j * 8));
                        }

                        if (($this->databaseType == $this->GEOIP_ORG_EDITION)
                            || ($this->databaseType == $this->GEOIP_DOMAIN_EDITION)
                            || ($this->databaseType == $this->GEOIP_ISP_EDITION)
                            || ($this->databaseType == $this->GEOIP_ORG_EDITION_V6)
                            || ($this->databaseType == $this->GEOIP_DOMAIN_EDITION_V6)
                            || ($this->databaseType == $this->GEOIP_ISP_EDITION_V6)) {
                            $this->record_length = $this->ORG_RECORD_LENGTH;
                        }
                    }

                    break;
                } else {
                    fseek($this->filehandle, -4, SEEK_CUR);
                }
            }

            if (($this->databaseType == $this->GEOIP_COUNTRY_EDITION) ||
                ($this->databaseType == $this->GEOIP_COUNTRY_EDITION_V6) ||
                ($this->databaseType == $this->GEOIP_PROXY_EDITION) ||
                ($this->databaseType == $this->GEOIP_NETSPEED_EDITION)) {
                $this->databaseSegments = $this->GEOIP_COUNTRY_BEGIN;
            }

            fseek($this->filehandle, $filepos, SEEK_SET);
        }
    }

    public function getCurrencyByCountry($countryCode)
    {
        $mapCountry = array('' => '',
            "EU" => "EUR", "AD" => "EUR", "AE" => "AED", "AF" => "AFN", "AG" => "XCD", "AI" => "XCD",
            "AL" => "ALL", "AM" => "AMD", "CW" => "ANG", "AO" => "AOA", "AQ" => "AQD", "AR" => "ARS", "AS" => "EUR",
            "AT" => "EUR", "AU" => "AUD", "AW" => "AWG", "AZ" => "AZN", "BA" => "BAM", "BB" => "BBD",
            "BD" => "BDT", "BE" => "EUR", "BF" => "XOF", "BG" => "BGL", "BH" => "BHD", "BI" => "BIF",
            "BJ" => "XOF", "BM" => "BMD", "BN" => "BND", "BO" => "BOB", "BR" => "BRL", "BS" => "BSD",
            "BT" => "BTN", "BV" => "NOK", "BW" => "BWP", "BY" => "BYR", "BZ" => "BZD", "CA" => "CAD",
            "CC" => "AUD", "CD" => "CDF", "CF" => "XAF", "CG" => "XAF", "CH" => "CHF", "CI" => "XOF",
            "CK" => "NZD", "CL" => "CLP", "CM" => "XAF", "CN" => "CNY", "CO" => "COP", "CR" => "CRC",
            "CU" => "CUP", "CV" => "CVE", "CX" => "AUD", "CY" => "EUR", "CZ" => "CZK", "DE" => "EUR",
            "DJ" => "DJF", "DK" => "DKK", "DM" => "XCD", "DO" => "DOP", "DZ" => "DZD", "EC" => "ECS",
            "EE" => "EEK", "EG" => "EGP", "EH" => "MAD", "ER" => "ETB", "ES" => "EUR", "ET" => "ETB",
            "FI" => "EUR", "FJ" => "FJD", "FK" => "FKP", "FM" => "USD", "FO" => "DKK", "FR" => "EUR", "SX" => "ANG",
            "GA" => "XAF", "GB" => "GBP", "GD" => "XCD", "GE" => "GEL", "GF" => "EUR", "GH" => "GHS",
            "GI" => "GIP", "GL" => "DKK", "GM" => "GMD", "GN" => "GNF", "GP" => "EUR", "GQ" => "XAF",
            "GR" => "EUR", "GS" => "GBP", "GT" => "GTQ", "GU" => "USD", "GW" => "XOF", "GY" => "GYD",
            "HK" => "HKD", "HM" => "AUD", "HN" => "HNL", "HR" => "HRK", "HT" => "HTG", "HU" => "HUF",
            "ID" => "IDR", "IE" => "EUR", "IL" => "ILS", "IN" => "INR", "IO" => "USD", "IQ" => "IQD",
            "IR" => "IRR", "IS" => "ISK", "IT" => "EUR", "JM" => "JMD", "JO" => "JOD", "JP" => "JPY",
            "KE" => "KES", "KG" => "KGS", "KH" => "KHR", "KI" => "AUD", "KM" => "KMF", "KN" => "XCD",
            "KP" => "KPW", "KR" => "KRW", "KW" => "KWD", "KY" => "KYD", "KZ" => "KZT", "LA" => "LAK",
            "LB" => "LBP", "LC" => "XCD", "LI" => "CHF", "LK" => "LKR", "LR" => "LRD", "LS" => "LSL",
            "LT" => "LTL", "LU" => "EUR", "LV" => "LVL", "LY" => "LYD", "MA" => "MAD", "MC" => "EUR",
            "MD" => "MDL", "MG" => "MGF", "MH" => "USD", "MK" => "MKD", "ML" => "XOF", "MM" => "MMK",
            "MN" => "MNT", "MO" => "MOP", "MP" => "USD", "MQ" => "EUR", "MR" => "MRO", "MS" => "XCD",
            "MT" => "EUR", "MU" => "MUR", "MV" => "MVR", "MW" => "MWK", "MX" => "MXN", "MY" => "MYR",
            "MZ" => "MZN", "NA" => "NAD", "NC" => "XPF", "NE" => "XOF", "NF" => "AUD", "NG" => "NGN",
            "NI" => "NIO", "NL" => "EUR", "NO" => "NOK", "NP" => "NPR", "NR" => "AUD", "NU" => "NZD",
            "NZ" => "NZD", "OM" => "OMR", "PA" => "PAB", "PE" => "PEN", "PF" => "XPF", "PG" => "PGK",
            "PH" => "PHP", "PK" => "PKR", "PL" => "PLN", "PM" => "EUR", "PN" => "NZD", "PR" => "USD", "PS" => "ILS", "PT" => "EUR",
            "PW" => "USD", "PY" => "PYG", "QA" => "QAR", "RE" => "EUR", "RO" => "RON", "RU" => "RUB",
            "RW" => "RWF", "SA" => "SAR", "SB" => "SBD", "SC" => "SCR", "SD" => "SDD", "SE" => "SEK",
            "SG" => "SGD", "SH" => "SHP", "SI" => "EUR", "SJ" => "NOK", "SK" => "SKK", "SL" => "SLL",
            "SM" => "EUR", "SN" => "XOF", "SO" => "SOS", "SR" => "SRG", "ST" => "STD", "SV" => "SVC",
            "SY" => "SYP", "SZ" => "SZL", "TC" => "USD", "TD" => "XAF", "TF" => "EUR", "TG" => "XOF",
            "TH" => "THB", "TJ" => "TJS", "TK" => "NZD", "TM" => "TMM", "TN" => "TND", "TO" => "TOP", "TL" => "USD",
            "TR" => "TRY", "TT" => "TTD", "TV" => "AUD", "TW" => "TWD", "TZ" => "TZS", "UA" => "UAH",
            "UG" => "UGX", "UM" => "USD", "US" => "USD", "UY" => "UYU", "UZ" => "UZS", "VA" => "EUR",
            "VC" => "XCD", "VE" => "VEF", "VG" => "USD", "VI" => "USD", "VN" => "VND", "VU" => "VUV",
            "WF" => "XPF", "WS" => "EUR", "YE" => "YER", "YT" => "EUR", "RS" => "RSD",
            "ZA" => "ZAR", "ZM" => "ZMK", "ME" => "EUR", "ZW" => "ZWD",
            "AX" => "EUR", "GG" => "GBP", "IM" => "GBP",
            "JE" => "GBP", "BL" => "EUR", "MF" => "EUR", "BQ" => "USD", "SS" => "SSP"
        );

        return $mapCountry[$countryCode];
    }
}
