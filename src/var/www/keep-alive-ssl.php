<?php

/**
 * Class SSLCertificate
 */
class SSLCertificate {

    /**
     * Holds the cert as string
     *
     * @var string
     */
    public $certData;

    /**
     * Holds the cert informations
     * @var string
     */
    public $countryName;
    public $stateOrProvinceName;
    public $localityName;
    public $organizationName;
    public $organizationalUnitName;
    public $commonName;
    public $emailAddress;
    public $passphrase;

    /**
     * Constructor to set cert informations
     *
     * @param string $countryName
     * @param string $stateOrProvinceName
     * @param string $localityName
     * @param string $organizationName
     * @param string $organizationalUnitName
     * @param string $commonName
     * @param string $emailAddress
     */
    public function __construct(
        $countryName = "DE",
        $stateOrProvinceName = "Bavaria",
        $localityName = "Kolbermoor",
        $organizationName = "TechDivision GmbH",
        $organizationalUnitName = "Appserver Team",
        $commonName = "appserver.io",
        $emailAddress = "info@appserver.io"
    )
    {
        $this->countryName = $countryName;
        $this->stateOrProvinceName = $stateOrProvinceName;
        $this->localityName = $localityName;
        $this->organizationName = $organizationName;
        $this->organizationalUnitName = $organizationalUnitName;
        $this->commonName = $commonName;
        $this->emailAddress = $emailAddress;

    }

    /**
     * Sets passphrase for cert
     *
     * @param string $passphrase
     * @return SSLCertificate
     */
    public function setPassphrase($passphrase)
    {
        $this->passphrase = $passphrase;
        return $this;
    }

    /**
     * Generates a ssl certificate
     *
     * @return SSLCertificate
     */
    public function generate()
    {
        // cert information
        $dn = array(
            "countryName" => $this->countryName,
            "stateOrProvinceName" => $this->stateOrProvinceName,
            "localityName" => $this->localityName,
            "organizationName" => $this->organizationName,
            "organizationalUnitName" => $this->organizationalUnitName,
            "commonName" => $this->commonName,
            "emailAddress" => $this->emailAddress
        );
        // generate certificate
        $privkey = openssl_pkey_new();
        $cert = openssl_csr_new($dn, $privkey);
        $cert = openssl_csr_sign($cert, null, $privkey, 365);
        // generate PEM file
        $pem = array();
        openssl_x509_export($cert, $pem[0]);
        openssl_pkey_export($privkey, $pem[1], $this->passphrase);
        $pem = implode($pem);
        // set cert data
        $this->certData = $pem;
        return $this;
    }

    /**
     * Saves the certData to a file
     *
     * @param string $filename The filename to save the certificate to.
     * @return int The function returns the number of bytes that were written to the file, or
     *              false on failure.
     */
    public function save($filename)
    {
        return file_put_contents($filename, $this->certData);
    }
}

/**
 * Class StreamSocketServer
 */
class StreamSocketServerSSL
{
    /**
     * @var string
     */
    public $scheme;

    /**
     * @var string
     */
    public $address;

    /**
     * @var string
     */
    public $port;

    /**
     * @var resource
     */
    public $context;

    /**
     * @var resource
     */
    public $socket;

    /**
     * Constructor
     *
     * @param string $scheme
     * @param string $address
     * @param string $port
     */
    public function __construct($scheme = 'ssl', $address = '0.0.0.0', $port = '9443')
    {
        // create ssl context
        $this->context = stream_context_create();
        // init properties
        $this->scheme = $scheme;
        $this->address = $address;
        $this->port = $port;
    }

    /**
     * Sets the certification filepath
     *
     * @param $certpath
     * @return bool
     */
    public function setCertpath($certpath)
    {
        return stream_context_set_option($this->context, $this->scheme, 'local_cert', $certpath);
    }

    /**
     * Sets the certification passphrase
     *
     * @param $passphrase
     * @return bool
     */
    public function setPassphrase($passphrase)
    {
        return stream_context_set_option($this->context, $this->scheme, 'passphrase', $passphrase);
    }

    /**
     * Preparse the context
     *
     * @return void
     */
    protected function prepareContext() {
        // set to allow self singed stuff
        stream_context_set_option($this->context, $this->scheme, 'allow_self_signed', true);
        // disable peer verifing
        stream_context_set_option($this->context, $this->scheme, 'verify_peer', false);
    }

    /**
     * Creats the ssl server resource
     *
     * @return void
     */
    public function create()
    {
        // prepare context
        $this->prepareContext();
        // create a new socket connection and listen to it
        $this->socket = stream_socket_server(
            $this->scheme . '://' . $this->address . ':' . $this->port,
            $errno,
            $errstr,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
            $this->context
        );
    }

    /**
     * Sets the socket into blocking mode or nod
     *
     * @param bool $blocking Flag for blocking mode
     * @return bool true on success or false on failure.
     */
    public function setBlocking($blocking = true)
    {
        // set socket in blocking mode
        return stream_set_blocking($this->socket, true);
    }

}

/**
 * Class RequestHandler
 */
class RequestHandler extends Thread
{
    /**
     * The ssl server socket resource
     *
     * @var resource
     */
    protected $socket;

    /**
     * Construct to set server socket resource
     *
     * @param resource $serverResource
     */
    public function __construct($socketResource)
    {
        // set socket server resource
        $this->socket = $socketResource;
    }

    /**
     * run thread logic
     */
    public function run()
    {
        // iterate for ever
        while (true) {
            // accept client connections
            $client = stream_socket_accept($this->socket, -1);
            // check if client is a valid resource
            if (is_resource($client)) {
                // init timeout for keepalive
                $timeout = 5;
                // set counter for keepalive
                $counter = 1;
                // set flag for connection to be open for keepalive
                $connectionOpen = true;
                // get starting time at this point
                $startTime = time();
                // init max request per keepalive session
                $maxRequests = 10;
                // start doing while keep alive connection is open
                do {
                    // init & clear buffer
                    $buffer = '';
                    // read from client
                    while ($buffer .= fread($client, 1024)) {
                        // until double line ending occurs (http)
                        if (false !== strpos($buffer, "\r\n\r\n")) {
                            // break out
                            break;
                        }
                    }
                    // decrease available requests count
                    $availableRequests = $maxRequests - $counter++;

                    // log output
                    echo '$availableRequests = ' . $availableRequests . PHP_EOL;

                    // calc time to live
                    $ttl = ($startTime + $timeout) - time();

                    // render something by using output buffering
                    ob_start();
                    echo "Hello World";
                    $contentLength = strlen($message = ob_get_clean());

                    // prepare response headers
                    $headers = array(
                        "HTTP/1.1 200 OK",
                        "Content-Type: text/html",
                        "Content-Length: $contentLength"
                    );

                    // check if this will be the last requests handled by this thread
                    if ($availableRequests > 0 && $ttl > 0) {
                        // set keep-alive headers
                        $headers[] = "Connection: keep-alive";
                        $headers[] = "Keep-Alive: max=$availableRequests, timeout=$timeout, thread={$this->getThreadId()}";
                    } else {
                        // set connection close headers
                        $headers[] = "Connection: close";
                    }

                    // prepare the response head/body
                    $response = array(
                        "head" => implode("\r\n", $headers) . "\r\n",
                        "body" => $message
                    );

                    // write the result back to the socket
                    fwrite($client, implode("\r\n", $response));

                    // check if this is the last request
                    if ($availableRequests <= 0 || $ttl <= 0) {
                        // shutdown client
                        stream_socket_shutdown($client, STREAM_SHUT_RDWR);
                        // if yes, close the socket and end the do/while
                        @fclose($client);
                        // output logging
                        echo "CLOSE CLIENT SOCKET" . PHP_EOL;
                        // set connection open flag to false
                        $connectionOpen = false;
                    }

                } while ($connectionOpen);
            }
        }
    }
}

$passphrase = 'passphrase';
$certfile = './SSLServer.pem';

// create new ssl certificate
$SSLCertificate = new SSLCertificate();
$SSLCertificate
    ->setPassphrase($passphrase)
    ->generate()
    ->save($certfile);

// init new ssl stream socket server
$SSLServer = new StreamSocketServerSSL();
$SSLServer->setPassphrase($passphrase);
$SSLServer->setCertpath($certfile);
$SSLServer->create();

// init workers array
$workers = array();
$worker = 0;

while (++$worker < 5) {
    $workers[$worker] = new RequestHandler($SSLServer->socket);
    $workers[$worker]->start();
}

echo "Waiting for requests..." . PHP_EOL;

foreach ($workers as $worker) {
    $worker->join();
}
