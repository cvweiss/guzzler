# Guzzler

Guzzler utilizes Guzzle's many powerful tools and provides a simple wrapper for performing asynchronous requests in PHP. Guzzle uses curl as the backend for making all requests.

# Guzzler Basics

To initiate:

    use cvweiss\Guzzler;
    $guzzler = new Guzzler();

To perform a call:
   
    $params = []; // Parameters to share with the success and fail function. Not required, defaults to []
    $headers = []; // Headers to pass, such as Content-Type or If-None-Match. Not required, defaults to []
    $requestType = "GET"; // The http request type. Not required, defaults to "GET"
    $guzzler->call("https://example.org", "success", "fail", $params, $headers, $requestType);

To perform a call you will need two functions: one for successful requests, and another for failed requests. As seen in the example above, you must pass the function names as strings.

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
    
Guzzler makes it easy to retrieve the headers from the request. Keep in mind, all headers keys are lowercased.

    function success(&$guzzler, &$params, $content)
    {
        $headers = $guzzler->getLastHeaders(); // Retrievess headers for this request
        $etag = $headers['etag'][0]; // Each header is an array (thanks curl). Use [0] to get the value of the header in most cases.
    }
    
Guzzler makes it very easy to add the headers as well.

    $headers = [];
    $headers['User-Agent'] = "My Application";
    $headers['If-None-Match'] = '"09f8b2541e00231360e70eb9d4d6e6504a298f9c8336277c704509a8"'; // ETag example
    $guzzler->call("https://example.org", "success", "fail", $params, $headers);

Guzzler will verify that the functions specified to be called actually exist, so if you typo you'll get an immediate notification before the request is actually made.

    $guzzler->call("https://example.org", "scucess", "fail");
    function success( ... ) { ... } 
    
    IllegalArgumentException returned since you have not defined the function 'scucess'
    
# Guzzler Example

Now that yo have fully read the manual, here is a complete example:

    <?php
    
    use cvweiss\Guzzler;
    
    $data = [1, 2, 3, 4, 5, 6, 7, 8, 9];
    
    $guzzler = new Guzzler(1, 1000000); // One concurrent request, waiting up to one second on ticks
    
    foreach ($data as $id) {
        $guzzler->call("https://example.org?page=" . $page", "success", "fail", $params, $headers, "GET"); // Preps the request
        $guzzler->tick(); // Allows the above request to be started and allows previous requests to be processed and completed.
    }
    $guzzler->finish(); // Waits for all requests to be completed.
    
    // This function is called when the request is successful
    function success(&$guzzler, &$params, $content)
    {
        echo $content . "\n";
    }
    
    // This function is called when the request has failed
    function success(&$guzzler, &$params, $exception)
    {
        echo $exception->getMessage() . "\n";
    }    
