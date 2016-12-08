<?php

namespace Bionicmaster\FacturaCom;

use Validator;
use Bionicmaster\FacturaCom\Exceptions\FacturaComException;

class Facturas
{

    const API_VERSION = 'v1';
    const API_HOST = 'https://factura.com/api';

    /** @var  string Method used for the request */
    protected $method;

    /** @var  array Resource used for the parameters */
    protected $resource;

    /** @var int How long to wait for a response from the API */
    protected $timeout = 30;

    /** @var int how long to wait while connecting to the API */
    protected $connectionTimeout = 5;

    /** @var array Store proxy connection details */
    protected $proxy = [];

    /**
     * Create a new Facturas Instance
     */
    public function __construct()
    {
        $this->parameters = config('facturacom');
    }

    /**
     * Gets a specific customer by id
     * @param $rfc
     * @return array|string
     * @throws FacturaComException
     */
    public function getCustomer($rfc = null)
    {
        $path = 'clients';
        $rules = ['rfc' => 'rfc'];

        if (null !== $rfc) {
            if ($this->validate(['rfc', $rfc], $rules)) {
                $path .= '/' . $rfc;
            }

        }

        return $this->get($path);
    }

    /**
     * Creates a new Customer
     * @param array $fields
     * @return array|string
     * @throws FacturaComException
     */
    public function createCustomer(array $fields)
    {
        $path = 'clients/create';
        $rules = [
            'nombre' => 'required|alpha_dash',
            'apellidos' => 'required|alpha_dash',
            'email' => 'required|email',
            'razons' => 'required|alpha_dash',
            'rfc' => 'required|rfc',
            'calle' => 'required',
            'numero_exterior' => 'required|integer',
            'numero_interior' => 'alpha_dash',
            'codpos' => 'required|zip',
            'colonia' => 'alpha_dash',
            'estado' => 'required|alpha_dash',
            'ciudad' => 'required|alpha_dash',
            'delegacion' => 'alpha_dash'
        ];

        $this->validate($fields, $rules);
        return $this->post($path, $fields);
    }

    /**
     * Updates an existing customer
     *
     * @param string $uuid
     * @param array $fields
     * @return array|st ring
     */
    public function updateCustomer($uuid, array $fields)
    {
        $path = 'clients/' . $uuid . '/update';
        $rules = [
            'nombre' => 'required|string',
            'apellidos' => 'required|string',
            'email' => 'required|email',
            'razons' => 'required|string',
            'rfc' => 'required|rfc',
            'calle' => 'required',
            'numero_exterior' => 'required|integer',
            'numero_interior' => 'string',
            'codpos' => 'required|zip',
            'colonia' => 'string',
            'estado' => 'required|string',
            'ciudad' => 'required|string',
            'delegacion' => 'string'
        ];

        $this->validate($fields, $rules);
        return $this->post($path, $fields);
    }

    /**
     * Gets the Invoices based on Criteria
     *
     * @param array $args
     * @return array|string
     */
    public function getInvoices(array $args = [])
    {
        $params = '';
        $rules = [
            'month' => 'date_format:m',
            'year' => 'date_format:Y',
            'rfc' => 'rfc'
        ];

        if ($args) {
            $this->validate($args, $rules);
            $params = '?' . http_build_query($args);
        }
        $path = 'invoices' . $params;

        return $this->get($path);
    }

    /**
     * Sends invoice to the customer by Email
     *
     * @param string $uuid
     * @return array|string
     */
    public function sendInvoice($uuid)
    {
        $path = 'invoice/' . $uuid . '/email';

        return $this->get($path);
    }

    /**
     * Creates the Invoice with the provided values
     *
     * @param array $fields
     * @return array|string
     */
    public function createInvoice(array $fields)
    {
        $path = 'invoice/create';
        $rules = [
            'rfc' => 'rfc',
            'numerocuenta' => 'digits:4',
            'formapago' => 'string',
            'metodopago' => 'string',
            'currencie' => 'string',
            'iva' => 'boolean',
            'num_order' => 'string',
            'seriefactura' => 'string',
            'descuento' => 'numeric',
            'fecha_cfdi' => 'string',
            'send_email' => 'integer',
            'invoice_comments' => 'string'
        ];

        $this->validate($fields, $rules);
        return $this->post($path, $fields);
    }

    public
    function cancelInvoice(
        $uuid
    ) {
        $path = 'invoice/' . $uuid . '/cancel';

        return $this->get($path);
    }

    /**
     * Gets the Files generated from the public site
     *
     * @param $uuid
     * @return \stdClass
     */
    public
    function getFiles(
        $uuid
    ) {
        $d = new \stdClass();
        $pdf = 'publica/invoice' . $uuid . '/pdf';
        $xml = 'publica/invoice' . $uuid . '/xml';
        $d->pdf = $this->setApiPath($pdf, false);
        $d->xml = $this->setApiPath($xml, false);

        return $d;
    }

    /**
     * Set the connection and response timeouts.
     *
     * @param int $connectionTimeout
     * @param int $timeout
     * @return $this
     */
    public
    function setTimeouts(
        $connectionTimeout,
        $timeout
    ) {
        $this->connectionTimeout = (int)$connectionTimeout;
        $this->timeout = (int)$timeout;

        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public
    function setDecodeJsonAsArray(
        $value
    ) {
        $this->decodeJsonAsArray = (bool)$value;

        return $this;
    }

    /**
     * @param string $userAgent
     * @return $this
     */
    public
    function setUserAgent(
        $userAgent
    ) {
        $this->userAgent = (string)$userAgent;

        return $this;
    }

    /**
     * @param array $proxy
     * @return $this
     */
    public
    function setProxy(
        array $proxy
    ) {
        $this->proxy = $proxy;

        return $this;
    }

    /**
     * Creates headers with the right keys to be sent
     * @return array
     */
    public
    function toHeader()
    {
        $out = [];
        foreach ($this->parameters['facturacom'] as $k => $v) {
            $out [] = $k . ': ' . $v;
        }

        return $out;
    }

    /**
     * Make GET requests to the API.
     *
     * @param string $path
     * @param array $parameters
     * @return string|array
     */
    protected
    function get(
        $path,
        array $parameters = []
    ) {
        return $this->http('GET', $path, $parameters);
    }

    /**
     * @param $method
     * @param $path
     * @param array $parameters
     * @param array $headers
     * @return array|string
     */
    protected
    function http(
        $method,
        $path,
        array $parameters = [],
        $headers = []
    ) {

        $this->method = $method;
        $apiPath = $this->setApiPath($path);
        $authorization = $this->toHeader();

        return $this->request($apiPath, $method, $authorization, $parameters, $headers);
    }

    /**
     * Make POST requests to the API.
     *
     * @param string $path
     * @param array $parameters
     *
     * @param array $headers
     * @return string|array
     */
    protected
    function post(
        $path,
        array $parameters = [],
        array $headers = []
    ) {
        return $this->http('POST', $path, $parameters, $headers);
    }

    /**
     * Make DELETE requests to the API.
     *
     * @param string $path
     * @param array $parameters
     *
     * @return string|array
     */
    protected
    function delete(
        $path,
        array $parameters = []
    ) {
        return $this->http('DELETE', $path, $parameters);
    }

    /**
     * Make PUT requests to the API.
     *
     * @param string $path
     * @param array $parameters
     *
     * @return string|array
     */
    protected
    function put(
        $path,
        array $parameters = [],
        array $headers = []
    ) {
        return $this->http('PUT', $path, $parameters, $headers);
    }

    /**
     * @param $url
     * @param $method
     * @param $authorization
     * @param $postfields
     * @param array $headers
     * @return string|array
     * @throws FacturaComException
     */
    protected
    function request(
        $url,
        $method,
        $authorization,
        $postfields,
        $headers = []
    ) {
        $options = [
            CURLOPT_VERBOSE => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => array_merge(['Content-Type: application/json'], $authorization, $headers),
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_URL => $url,
        ];

        if (!empty($this->proxy)) {
            $options[CURLOPT_PROXY] = $this->proxy['CURLOPT_PROXY'];
            $options[CURLOPT_PROXYUSERPWD] = $this->proxy['CURLOPT_PROXYUSERPWD'];
            $options[CURLOPT_PROXYPORT] = $this->proxy['CURLOPT_PROXYPORT'];
            $options[CURLOPT_PROXYAUTH] = CURLAUTH_BASIC;
            $options[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP;
        }

        switch ($method) {
            case 'GET':
                break;
            case 'POST':
                $options[CURLOPT_POST] = true;
                if (isset($postfields['raw'])) {
                    $options[CURLOPT_POSTFIELDS] = $postfields['raw'];
                } else {
                    $options[CURLOPT_POSTFIELDS] = $this->buildHttpQuery($postfields);
                }
                break;
            case 'DELETE':
                $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;
            case 'PUT':
                $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
                if (isset($postfields['raw'])) {
                    $options[CURLOPT_POSTFIELDS] = $postfields['raw'];
                } else {
                    $options[CURLOPT_POSTFIELDS] = $this->buildHttpQuery($postfields);
                }
                break;
        }

        $curlHandle = curl_init();
        curl_setopt_array($curlHandle, $options);
        $response = curl_exec($curlHandle);

        if (curl_errno($curlHandle) > 0) {
            throw new FacturaComException(curl_error($curlHandle), curl_errno($curlHandle), null, null);
        }

        $response = json_decode($response);

        if (is_null($response)) {
            throw new FacturaComException(FacturaComException::NOT_FOUND, 404, null, null);
        }
        if ($response->status === 'error') {
            switch ($response->message) {
                case 'Authentication Account no Exists':
                    $error = FacturaComException::NOT_AUTHORIZED;
                    $errno = 401;
                    break;
                default:
                    $error = FacturaComException::NOT_FOUND;
                    $errno = 404;
            }
            throw new FacturaComException($error, $errno, null, null);
        }


        return $response;
    }

    /**
     * @param array $params
     * @return string
     */
    protected
    function buildHttpQuery(
        array $params
    ) {
        if (!$params) {
            return '';
        }

        // Urlencode both keys and values
        $keys = $this->urlencodeRfc3986(array_keys($params));
        $values = $this->urlencodeRfc3986(array_values($params));
        $params = array_combine($keys, $values);

        // Ref: Spec: 9.1.1 (1)
        uksort($params, 'strcmp');

        $pairs = [];
        foreach ($params as $parameter => $value) {
            if (is_array($value)) {
                // Ref: Spec: 9.1.1 (1)
                sort($value, SORT_STRING);
                foreach ($value as $duplicateValue) {
                    $pairs[] = $parameter . '=' . $duplicateValue;
                }
            } else {
                $pairs[] = $parameter . '=' . $value;
            }
        }
        return json_encode(implode('&', $pairs));
    }

    /**
     * @param $input
     * @return array|mixed|string
     */
    protected
    function urlencodeRfc3986(
        $input
    ) {
        $output = '';
        if (is_array($input)) {
            $output = array_map([__NAMESPACE__ . '\Util', 'urlencodeRfc3986'], $input);
        } elseif (is_scalar($input)) {
            $output = rawurlencode($input);
        }

        return $output;
    }

    /**
     * @param string $string
     * @return string
     */
    protected
    function urldecodeRfc3986(
        $string
    ) {
        return urldecode($string);
    }

    protected
    function setApiPath(
        $path,
        $versioned = true
    ) {
        return self::API_HOST . '/' . ($versioned ? self::API_VERSION : '') . '/' . $path;
    }

    protected
    function validate(
        array $fields,
        array $rules
    ) {
        $validator = Validator::make($fields, $rules);

        if ($validator->fails()) {

            throw new FacturaComException(FacturaComException::BAD_REQUEST, 500, null, null);

        }

        return true;
    }

}
