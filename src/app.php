<?php

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Soap service model
 */
class SoapHttpService
{
    /**
     * This method forwards the request and returns the http response
     *
     * @param string $Url
     * @param string $Method
     * @param string $Body
     * @param SoapHttpHeader[] $Headers
     * @return SoapHttpResponse
     */
    public function SoapHttpRequest($Url, $Method, $Body, $Headers)
    {
        // forward request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        if (in_array(strtoupper($Method),array("GET", "POST", "PUT", "DELETE"))) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($Method));
        }
        if ($Body && (strtoupper($Method) == "POST" || strtoupper($Method) == "PUT")) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $Body);
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        if ($Headers && is_array($Headers->item)) {
            $headers = array_map(function($item) {
                return $item->Key . ": " . $item->Value;
            }, $Headers->item);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        // get response
        $output = curl_exec($ch);
        $output = utf8_encode($output);
        curl_close($ch);

        // xml safety
        $doc = new DOMDocument();
        $outputXml = $doc->saveXML($doc->createTextNode($output));
        $outputXml = htmlspecialchars($outputXml);

        // build response
        $response = new SoapHttpResponse();
        $response->RequestUrl = $Url;
        $response->RequestMethod = $Method;
        $response->RequestBody = $Body;
        $response->RequestHeaders = $Headers;
        $response->ResponseText = $output;
        $response->ResponseJson = json_encode(array($output));
        return $response;
    }
}

/**
 * Soap response model
 */
class SoapHttpResponse
{
    /**
     * @var string
     * */
    public $RequestUrl;

    /**
     * @var string
     * */
    public $RequestMethod;

    /**
     * @var string
     * */
    public $RequestBody;

    /**
     * @var SoapHttpHeader[] $Headers
     * */
    public $RequestHeaders;

    /**
     * @var string
     * */
    public $ResponseText;

    /**
     * @var string
     * */
    public $ResponseJson;
}

/**
 * Soap request header model
 */
class SoapHttpHeader
{
    /**
     * @var string
     * */
    public $Key;

    /**
     * @var string
     * */
    public $Value;

    /**
     * constructor method
     *
     * @param string $Key
     * @param string $Value
     */
    public function __construct($Key, $Value) {
        $this->Key = $Key;
        $this->Value = $Value;
    }
}

/**
 * AutoDiscover class for WSDL generation
 */
class SoapHttpAutoDiscover
{
    /**
     * @var Zend\Soap\AutoDiscover
     */
    protected $autodiscover;

    /**
     * constructor method
     */
    public function __construct($uri) {
        $strategy = new \Zend\Soap\Wsdl\ComplexTypeStrategy\ArrayOfTypeSequence();
        $this->autodiscover = new Zend\Soap\AutoDiscover($strategy);
        $this->autodiscover->setUri($uri);
        $this->autodiscover->setServiceName('SoapHttp');
        $this->autodiscover->setClass("SoapHttpService");
    }

    /**
     * @return string
     */
    public function toXml() {
        return $this->autodiscover->toXml();
    }
}

/**
 * Custom actions for each service request
 */
$app->before(function () use ($app)
{
    $app['base_url'] = $app['request']->getScheme().'://'.$app['request']->getHttpHost().$app['request']->getBaseUrl();
    $app['wsdl'] = dirname(__FILE__) . "/../cache/SoapHttp.wsdl";

    // Debug: don't cache WSDL file whilst in development
    // & re-create wsdl file on each request
    if ($app['debug']) {
        ini_set("soap.wsdl_cache_enabled","0");
        $wsdl = new SoapHttpAutoDiscover($app['base_url']);
        file_put_contents($app['wsdl'], $wsdl->toXml());
    }
});

/**
 * This method runs the soap server
 */
$app->match('/', function () use ($app)
{
    $server = new SoapServer($app['wsdl']);
    $server->setClass("SoapHttpService");
    $request = $app['request']->getContent();
    $response = $server->handle($request);
    return $response;
});

/**
 * This method shows the WSDL
 */
$app->get('/wsdl', function () use ($app)
{
    $wsdl = new SoapHttpAutoDiscover($app['base_url']);
    $header = array('Content-Type' => 'application/xml');
    $response = new Response($wsdl->toXml(), 200, $header);
    return $response;
});

/**
 * This method demonstrates a GET request
 */
$app->get('/test/get', function () use ($app)
{
    $client = new Zend\Soap\Client($app['wsdl']);
    $response = $client->SoapHttpRequest(
        "http://www.google.ch/"
    );
    return $response->ResponseText;
});

/**
 * This method demonstrates a POST request with headers
 * Warning: be careful with encoding headers. This will be a problem for soap & php:
 * Accept-Encoding: gzip, deflate
 */
$app->get('/test/post', function () use ($app)
{
    $client = new Zend\Soap\Client($app['wsdl']);
    $response = $client->SoapHttpRequest(
        "http://fahrplan.sbb.ch/bin/query.exe/dn",
        "POST",
        http_build_query(array("S" => "Bern", "Z" => "Luzern")),
        array(
            new SoapHttpHeader("Accept", "application/html"),
            new SoapHttpHeader("Cache-Control", "no-cache"),
        )
    );
    return json_decode($response->ResponseJson)[0];
});
