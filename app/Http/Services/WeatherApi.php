<?php

namespace App\Http\Services;

use App\Http\Services\Api; 

class WeatherApi extends Api
{
    public function getCurrentWeather($city, $countryCode, $name = null)
    {
        $url = 'https://api.openweathermap.org/data/2.5/weather';
        $param = array(
            'q' => $city.','.$countryCode,
            'appid' => '1e59b22a58ea899ada4664f8fcfe00fd',
            'units' => 'metric'
        );
        $result = $this->get($url,$param);
        return $result;

    }

    public function getForecastWeather($city, $countryCode)
    {
        $url = 'https://api.openweathermap.org/data/2.5/forecast';
        $param = array(
            'q' => $city.','.$countryCode,
            'appid' => '1e59b22a58ea899ada4664f8fcfe00fd',
            'units' => 'metric',
            'cnt' => 5
        );
        $result = $this->get($url,$param);
        return $result;
    }
}
