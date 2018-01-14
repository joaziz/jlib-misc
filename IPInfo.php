<?php

namespace Jlib\Misc;
/**
 * Created by PhpStorm.
 * User: joe
 * Date: 15/01/18
 * Time: 12:45 ص
 */
/**
 * Class IPInfo
 * @package Jlib\Misc
 */
class IPInfo
{
    /**
     * @var
     */
    private $ip;
    private $supportTypes = ["country", "countrycode", "state", "region", "city", "location", "address"];
    private $continents = ["AF" => "Africa", "AN" => "Antarctica", "AS" => "Asia", "EU" => "Europe", "OC" => "Australia (Oceania)", "NA" => "North America", "SA" => "South America"];
    private $rowData;
    private $hasData = false;

    public function __construct($ip = NULL, $deepDetect = TRUE)
    {
        $this->detectIP($ip, $deepDetect);
        $this->featchData();
    }

    public function detectIP($ip = NULL, $deepDetect = TRUE)
    {
        if (!is_null($ip)) {
            if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE)
                throw new \Exception("you entered wrong ip [$ip]");
            $this->ip = $ip;
        } else {
            if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
                $this->ip = $_SERVER["REMOTE_ADDR"];
                if ($deepDetect) {
                    if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                        $this->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                        $this->ip = $_SERVER['HTTP_CLIENT_IP'];
                }
            }
        }

        return $this->ip;
    }

    /**
     * @return mixed
     */
    public function getRowData()
    {
        return new class ($this->rowData) implements \JsonSerializable
        {
            public function __construct($data)
            {
                $this->data = $data;
            }

            /**
             * Specify data which should be serialized to JSON
             * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
             * @return mixed data which can be serialized by <b>json_encode</b>,
             * which is a value of any type other than a resource.
             * @since 5.4.0
             */
            function jsonSerialize()
            {
                return $this->data;
            }
        };

    }

    private function getLocation()
    {
        return array(
            "ip" => $this->ip,
            "city" => @$this->rowData->geoplugin_city,
            "state" => @$this->rowData->geoplugin_regionName,
            "country" => @$this->rowData->geoplugin_countryName,
            "country_code" => @$this->rowData->geoplugin_countryCode,
            "continent" => @$this->continents[strtoupper($this->rowData->geoplugin_continentCode)],
            "continent_code" => @$this->rowData->geoplugin_continentCode
        );
    }

    private function getCountry()
    {
        return @$this->rowData->geoplugin_countryName;
    }

    private function getCity()
    {
        return @$this->rowData->geoplugin_city;
    }

    private function getState()
    {
        return @$this->rowData->geoplugin_regionName;
    }

    private function getRegion()
    {
        return $this->getState();
    }

    private function getCountrycode()
    {
        return @$this->rowData->geoplugin_countryCode;
    }

    private function getAddress()
    {
        $address = array($this->rowData->geoplugin_countryName);
        if (@strlen($this->rowData->geoplugin_regionName) >= 1)
            $address[] = $this->rowData->geoplugin_regionName;
        if (@strlen($this->rowData->geoplugin_city) >= 1)
            $address[] = $this->rowData->geoplugin_city;
        return implode(", ", array_reverse($address));
    }

    /**
     * @param string $purpose
     * @return null
     */
    public function get($purpose = "location")
    {
        if (!$this->hasData) return null;

        $output = null;
        $purpose = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
        if (in_array($purpose, $this->supportTypes))
            if (@strlen(trim($this->rowData->geoplugin_countryCode)) == 2)
                $output = $this->{"get" . ucfirst($purpose)}();

        return $output;


    }

    /**
     * @return mixed
     */
    private function featchData()
    {
        $this->rowData = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $this->ip));

        if ($this->rowData)
            $this->hasData = true;

        return $this->rowData;


    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->rowData);
    }
}