# Guzzler Basics

To initiate:

    use cvweiss\Guzzler;
    $guzzler = new Guzzler();

To perform a call:
   
    $params = []; // Parameters to share with the success and fail function. Not required, defaults to []
    $headers = []; // Headers to pass, such as Content-Type or If-None-Match. Not required, defautls to []
    $requestType = "GET"; // The http request type. Not required, defaults to "GET"
    $guzzler->call("https://example.org", "success", "fail", $params, $headers, $requestType);

To perform a call you will need two functions: one for successful requests, and another for failed requests.

    function success(&$guzzler, &$params, $content)
    {
        // Do stuff with the successful return
    }
    
    function fail(&$guzzler, &$params, $exception)
    {
        // Do stuff with the successful return
    }
    
To allow guzzler to iterate existing requests:

    $guzzler->tick();
    
To finish all guzzler calls:

   $guzzler->finish();
