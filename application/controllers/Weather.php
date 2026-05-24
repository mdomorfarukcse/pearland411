<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Weather widget proxy.
 *
 * Forwards a fixed Open-Meteo forecast query for Pearland, TX and
 * returns the upstream JSON. Avoids client-side CORS / regional
 * blocking issues that the homepage widget was running into.
 *
 * Previously this lived as a PHP block embedded at the top of
 * views/index.php with CORS=* and CURLOPT_SSL_VERIFYPEER=false.
 * Both fixed here.
 */
class Weather extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->driver('cache', array('adapter' => 'file', 'backup' => 'dummy'));
    }

    public function proxy()
    {
        // CORS: scope to our own origin only. Same-origin browsers
        // ignore the header; cross-origin browsers that aren't us
        // get denied.
        $this->output->set_header('Access-Control-Allow-Origin: ' . rtrim(base_url(), '/'));
        $this->output->set_header('Access-Control-Allow-Methods: GET, OPTIONS');
        $this->output->set_header('Access-Control-Allow-Headers: Content-Type');
        $this->output->set_content_type('application/json');

        if ($this->input->method() === 'options') {
            $this->output->set_status_header(204);
            return;
        }

        // Response is short and changes hourly at most — cache 15 min.
        $cached = $this->cache->get('weather_pearland_v1');
        if ($cached) {
            $this->output->set_output($cached);
            return;
        }

        $api_url = 'https://api.open-meteo.com/v1/forecast?' . http_build_query(array(
            'latitude'         => 29.5636,
            'longitude'        => -95.2861,
            'current'          => 'temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m,wind_direction_10m',
            'daily'            => 'temperature_2m_max,temperature_2m_min,weather_code',
            'temperature_unit' => 'fahrenheit',
            'wind_speed_unit'  => 'mph',
            'timezone'         => 'America/Chicago',
            'forecast_days'    => 7,
        ));

        $response = $this->fetch($api_url);
        if ($response === null) {
            $this->output->set_status_header(502)->set_output(json_encode(array(
                'error'   => true,
                'message' => 'Weather service temporarily unavailable',
            )));
            return;
        }

        $this->cache->save('weather_pearland_v1', $response, 900);
        $this->output->set_output($response);
    }

    private function fetch($url)
    {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => 10,
                // TLS verification ON — the old code disabled it.
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_USERAGENT      => 'pearland411-weather/1.0',
                CURLOPT_HTTPHEADER     => array('Accept: application/json'),
            ));
            $body = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return ($body !== false && $code === 200) ? $body : null;
        }

        // Stream wrapper fallback. TLS verification still on.
        $ctx = stream_context_create(array(
            'http' => array(
                'method'  => 'GET',
                'header'  => "Accept: application/json\r\nUser-Agent: pearland411-weather/1.0\r\n",
                'timeout' => 10,
            ),
            'ssl' => array(
                'verify_peer'      => true,
                'verify_peer_name' => true,
            ),
        ));
        $body = @file_get_contents($url, false, $ctx);
        return $body !== false ? $body : null;
    }
}
