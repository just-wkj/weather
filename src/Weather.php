<?php


namespace Justwkj\Weather;


use GuzzleHttp\Client;
use Justwkj\Weather\Exceptions\HttpException;
use Justwkj\Weather\Exceptions\InvalidArgumentException;

class Weather {
    protected $key;
    protected $guzzleOptions = [];

    public function __construct(string $key) {
        $this->key = $key;
    }

    public function getHttpClient() {
        return new Client($this->guzzleOptions);
    }

    public function setGuzzleOptions(array $options) {
        $this->guzzleOptions = $options;
    }

    public function getWeather($city, $type = 'live', $format = 'json') {
        $url = 'https://restapi.amap.com/v3/weather/weatherInfo';

        if (!\in_array(\strtolower($format), ['xml', 'json'])) {
            throw new InvalidArgumentException('Invalid response format: ' . $format);
        }

        $types = [
            'live'     => 'base',
            'forecast' => 'all',
        ];
        if (!\array_key_exists(\strtolower($type), $types)) {
            throw new InvalidArgumentException('Invalid type value(base/all): ' . $type);
        }

        $query = array_filter([
            'key'        => $this->key,
            'city'       => $city,
            'output'     => \strtolower($format),
            'extensions' => \strtolower($type),
        ]);

        try {
            $response = $this->getHttpClient()->get($url, [
                'query' => $query,
            ])->getBody()->getContents();

            return 'json' === $format ? \json_decode($response, true) : $response;
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getLiveWeather($city, $format = 'json') {
        return $this->getWeather($city, 'live', $format);
    }

    public function getForecastWeather($city, $format = 'json') {
        return $this->getWeather($city, 'forecast', $format);
    }
}
