<?php
function openCURLRequest()
{
    $curl_options = array(
        CURLOPT_RETURNTRANSFER => true,     /* return web page */
        CURLOPT_HEADER         => true,     /* don't return headers */
        CURLOPT_FOLLOWLOCATION => true,     /* follow redirects */
        CURLOPT_ENCODING       => "",       /* handle all encodings */
        CURLOPT_AUTOREFERER    => true,     /* set referer on redirect */
        CURLOPT_CONNECTTIMEOUT => 120,      /* timeout on connect */
        CURLOPT_TIMEOUT        => 120,      /* timeout on response */
        CURLOPT_MAXREDIRS      => 10,       /* stop after 10 redirects */
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        //CURLOPT_COOKIEFILE     => dirname(__FILE__)."/cookie.txt",
        CURLOPT_COOKIEJAR      => dirname(__FILE__)."/cookie.txt",
        //were going to be acting as a Firefox client to eliminate any possible issues
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20100101 Firefox/31.0'
    );

    $ch = curl_init();
    curl_setopt_array($ch, $curl_options);
    
    //echo "cURL cookie file: \r\n";
    //echo dirname(__FILE__)."/cookie.txt";
    //echo file_get_contents(dirname(__FILE__)."/cookie.txt");
    
    return $ch;
}

function CURLPost($ch, $url, $data)
{
    //Setup Additional Headers 
    curl_setopt($ch, CURLOPT_URL, $url);
    //spoofing firefox
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Connection: keep-alive',
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.5',
        'Accept-Encoding: gzip, deflate'
    ));
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    
    //uncomment to get headers for debugging
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);

    $result = curl_exec($ch);
    
    //echo curl_getinfo($ch, CURLINFO_HEADER_OUT );
    
    return $result;
}

function extractCookies($response)
{
    /*
     * Extract the sessionID cookie from the headers
     */
    //Get the string of cookies from headers
    $cookieMatches;
    preg_match_all('/^Set-Cookie:(.*)/im', $response, $cookieMatches);
    //var_dump($cookieMatches);
    
    $cookies = array();
    for($l = 0; $l < count($cookieMatches[1]); $l++) {
        $cookieString = $cookieMatches[1][$l];

        //extract the cookie kes and values
        $rawCookies;
        preg_match_all('/(\s?([^;]+)=([^;]+);?)/i', $cookieString, $rawCookies);

        //make an associative array of the cookies
        for($c = 0; $c < count($rawCookies[2]); $c++)
        {
            $cookies[$rawCookies[2][$c]] = $rawCookies[3][$c];
        }
    }
    
    return $cookies;
}
?>