<?php

namespace App\Http\Controllers\Api\Weather;

use App\Http\Controllers\Controller;
use App\Http\Services\WeatherApi as WeatherApi;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WeatherController extends Controller
{
    protected $weatherApi;

    public function __construct(WeatherApi $weatherApi)
    {
        $this->weatherApi = $weatherApi;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $getCurrentWeather = $this->weatherApi->getCurrentWeather($request->query('city'), $request->query('country_code'));
        $currentWeather = array(
            'temperature' => $getCurrentWeather['main']['temp'],
            'icon' => $getCurrentWeather['weather'][0]['icon'],
            'location' => $getCurrentWeather['name']
        );

        $getForecastWeather = $this->weatherApi->getForecastWeather($request->query('city'), $request->query('country_code'));
        $tempDetails = array();
        foreach($getForecastWeather['list'] as $data)
        {
            date_default_timezone_set('Asia/Tokyo');
            $date = date_create($data['dt_txt']);
            $details = array(
                'date' => date_format($date, 'M j, Y'),
                'time' => date_format($date, 'h:i A'),
                'temp' => $data['main']['temp'],
                'icon' => 'https://openweathermap.org/img/wn/'.$data['weather'][0]['icon'].'@2x.png',
            );
            array_push($tempDetails, $details);
        }
        $forecastWeather = array(
            "list" => $tempDetails,
        );   

        return response()->json([
            'success' => true,
            'currentWeather' => $currentWeather,
            'forecastWeather' => $forecastWeather
        ], 200);
    }
}
