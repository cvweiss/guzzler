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
        // Do stuff with the failed return
    }
    
To allow guzzler to iterate existing requests:

    $guzzler->tick();
    
To finish all guzzler calls:

    $guzzler->finish();

# Guzzler Advanced

Guzzler utilizes Guzzle's many powerful tools and provides a simple wrapper for performing calls. 

You can modify the number of concurrent requests as well as the maximum time Guzzler will wait between calls. The wait takes into consideration the time it took for a call to come back and be processed.

    $guzzler = new Guzzler($concurrentRequests, $utimeSleep);
    
Guzzler also makes it easier to pass parameters to the functions that are called on fail or success.

    $params = ['data' = ['id' = 123, 'foo' = 'bar']];
    $guzzler->call("https://example.org", "success", "fail", $params);
    
    function success(&$guzzler, &$params, $content)
    {
        $data = $params['data'];
        
        // Do stuff with $content
    }
    
Guzzler also allows you to make additional guzzler calls within the functions.

    $guzzler->call("https://example.org", "success", "fail");
    
    function success(&$guzzler, &$params, $content)
    {
        // Do stuff with $content
        
        $guzzler->call("https://example.org/example.html", "success", "fail");
    }
    
Guzzler makes it easy to POST with a body included:

    $body = "[123, 456, 789]";
    $guzzler->call("https://example.org", "success", "fail", $params, $headers, "POST", $body);
    
