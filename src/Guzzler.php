<?php

namespace cvweiss;

class Guzzler
{
    private $curl;
    private $handler;
    private $client;
    private $concurrent = 0;
    private $maxConcurrent;
    private $usleep;
    private $lastHeaders = [];

    public function __construct($maxConcurrent = 10, $usleep = 100000, $userAgent = 'cvweiss/guzzler/', $curlOptions = [])
    {
	$curlOptions = $curlOptions == [] ? [CURLOPT_FRESH_CONNECT => false] : $curlOptions;

        $this->curl = new \GuzzleHttp\Handler\CurlMultiHandler();
        $this->handler = \GuzzleHttp\HandlerStack::create($this->curl);
        $this->client = new \GuzzleHttp\Client(['curl' => $curlOptions, 'connect_timeout' => 10, 'timeout' => 60, 'handler' => $this->handler, 'User-Agent' => $userAgent]);
        $this->maxConcurrent = max($maxConcurrent, 1);
        $this->usleep = max(0, min(1000000, (int) $usleep));
    }

    public function isSetDefault($arr, $key, $default)
    {
        return isset($arr[$key]) ? $arr[$key] : $default;
    }

    public function tick()
    {
        $ms = microtime();
        do {
            $this->curl->tick();
        } while ($this->concurrent >= $this->maxConcurrent);
        return max(0, microtime() - $ms);
    }

    public function finish()
    {
        $ms = microtime();
        $this->curl->execute();
        return max(0, microtime() - $ms);
    }

    public function inc()
    {
        $this->concurrent++;
    }

    public function dec()
    {
        $this->concurrent--;
    }

    public function call($uri, $fulfilled, $rejected, $params = [], $setup = [], $callType = 'GET', $body = null)
    {
        $this->verifyCallable($fulfilled);
        $this->verifyCallable($rejected);

        $guzzler = $this;
        $request = new \GuzzleHttp\Psr7\Request($callType, $uri, $setup, $body);
        $this->client->sendAsync($request)->then(
            function($response) use (&$guzzler, $fulfilled, &$params) {
                $guzzler->dec();
                $content = (string) $response->getBody();
                $this->lastHeaders = array_change_key_case($response->getHeaders());
                $fulfilled($guzzler, $params, $content);
            },
            function($connectionException) use (&$guzzler, &$rejected, &$params) {
                $guzzler->dec();
                $response = $connectionException->getResponse();
                $this->lastHeaders = $response == null ? [] : array_change_key_case($response->getHeaders());
                $params['content'] = method_exists($response, "getBody") ? (string) $response->getBody() : "";
                $rejected($guzzler, $params, $connectionException);
            });
        $this->inc();
        $ms = $this->tick();
        $sleep = min(1000000, max(0, $this->usleep - $ms));
        usleep($sleep);
    }

    public function verifyCallable($callable)
    {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException(print_r($callable, true) . " is not a callable function");
        }
    }
    
    public function getLastHeaders()
    {
        return $this->lastHeaders;
    }
}
