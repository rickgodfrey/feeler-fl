<?php
/**
 * Created by PhpStorm.
 * User: rickguo
 * Date: 2019-06-05
 * Time: 19:02
 */

namespace Feeler\Fl;

class Distance{
    protected $location1Lat;
    protected $location1Lng;
    protected $location2Lat;
    protected $location2Lng;

    /**
     * @param mixed $location1Lat
     */
    public function setLocation1Lat($location1Lat): void
    {
        $this->location1Lat = $location1Lat;
    }

    /**
     * @param mixed $location1Lng
     */
    public function setLocation1Lng($location1Lng): void
    {
        $this->location1Lng = $location1Lng;
    }

    /**
     * @param mixed $location2Lat
     */
    public function setLocation2Lat($location2Lat): void
    {
        $this->location2Lat = $location2Lat;
    }

    /**
     * @param mixed $location2Lng
     */
    public function setLocation2Lng($location2Lng): void
    {
        $this->location2Lng = $location2Lng;
    }

    public function setLocation1Data($location1Lat, $location1Lng){
        $this->location1Lat = $location1Lat;
        $this->location1Lng = $location1Lng;
    }

    public function setLocation2Data($location2Lat, $location2Lng){
        $this->location2Lat = $location2Lat;
        $this->location2Lng = $location2Lng;
    }

    public function calcDistance() {
        if(is_null($this->location1Lat) || is_null($this->location1Lng) || is_null($this->location2Lat) || is_null($this->location2Lng)){
            return false;
        }

        // Convert angle to radians
        $radLat1 = deg2rad($this->location1Lat);
        $radLat2 = deg2rad($this->location2Lat);
        $radLng1 = deg2rad($this->location1Lng);
        $radLng2 = deg2rad($this->location2Lng);
        $radLatDifference = $radLat1 - $radLat2;
        $radLngDifference = $radLng1 - $radLng2;
        $meters = asin(sqrt(pow(sin($radLatDifference), 2) * 2 + cos($radLat1) * cos($radLat2) * pow(sin($radLngDifference), 2))) * 6378.137 * 1000;

        return $meters;
    }
}