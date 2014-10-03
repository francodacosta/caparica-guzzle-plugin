<?php
/**
 * Caparica Guzzle Plugin
 *
 * Guzzle plugin to make signed request to a Caparica enabled API
 *
 * @author    Nuno Franco da Costa <nuno@francodacosta.com>
 * @copyright 2013-2014 Nuno Franco da Costa
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/francodacosta/caparica
 */

namespace Francodacosta\Caparica\Plugins\Guzzle;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Caparica\Client\ClientInterface;
use Caparica\Crypto\SignerInterface;

/**
 * Caparica Guzzle Plugin
 */
class CaparicaGuzzlePlugin implements EventSubscriberInterface
{
    CONST PLUGIN_VERSION='0.1.0';

    /**
     * plugin configuration options
     * @type array
     */
    private $config = [
        'keys' => [
            'timestamp' => 'X-CAPARICA-TIMESTAMP',
            'signature' => 'X-CAPARICA-SIG',
            'client'    => 'X-CAPARICA-CLIENT',
            'path'      => 'X-CAPARICA-PATH',
            'method'    => 'X-CAPARICA-METHOD',
        ]
    ];


    /**
     * the caparica client
     *
     * @type ClientInterface
     */
    private $caparicaClient;

    /**
     * the caparica request Signer
     *
     * @type SignerInterface
     */
    private $requestSigner;

    /**
     * include the reuqest path when signing
     *
     * @type boolean
     */
    private $includePath = true;

    /**
     * include the reuqest method when signing
     *
     * @type boolean
     */
    private $includeMethod = true;


    /**
     * initializes the class
     *
     * @param  ClientInterface $client
     * @param  SignerInterface $requestSigner
     * @param  boolean         $includePath
     * @param  boolean         $includeMethod
     */
    public function __construct(ClientInterface $client, SignerInterface $requestSigner, $includePath = true, $includeMethod = true)
    {
        $this->setCaparicaClient($client);
        $this->setRequestSigner($requestSigner);
        $this->setIncludePath($includePath);
        $this->setIncludeMethod($includeMethod);
    }

    public function setConfig(array $config)
    {
        $this->config = array_merge_recursive($this->config, $config);
    }

    public static function getSubscribedEvents()
    {
        return array('request.before_send' => 'onBeforeSend');
    }

    public function getParamsToSign(\Guzzle\Http\Message\Request $request)
    {
            return $request->getQuery()->toArray();
    }


    private function getRequestPath(\Guzzle\Http\Message\Request $request) {
        // $ret =  str_replace('/app_dev.php', '', $request->getPath());
        $ret =  $request->getPath();

        return $ret;
    }

    public function onBeforeSend(\Guzzle\Common\Event $event)
    {

        $request        = $event['request'];
        $caparicaClient = $this->caparicaClient;
        $requestSigner  = $this->requestSigner;
        $timestamp      = date('U');
        $paramsToSign   = $this->getParamsToSign($request);

        // add request timestamp
        $request->setHeader(
            $this->config['keys']['timestamp'],
            $timestamp
        );
        $paramsToSign[$this->config['keys']['timestamp']] = $timestamp;

        // add client code
        $request->setHeader(
            $this->config['keys']['client'],
            $caparicaClient->getCode()
        );

        // add request path to signature
        if ($this->getIncludePath()) {
            $path = $this->getRequestPath($request);
            $request->setHeader(
                $this->config['keys']['path'],
                $path
            );
            $paramsToSign[$this->config['keys']['path']] = $path;
        }

        // add request method to signature
        if ($this->getIncludeMethod()) {
            $method = strtoupper($request->getMethod());
            $request->setHeader(
                $this->config['keys']['method'],
                $method
            );
            $paramsToSign[$this->config['keys']['method']] = $method;
        }

        // add the signature
        $signature = $requestSigner->sign($paramsToSign, $caparicaClient->getSecret());
        $request->setHeader(
            $this->config['keys']['signature'],
            $signature
        );

        // just to simplify unit testing
        return $request;
    }

    /**
     * Get the value of plugin configuration options
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get the value of the caparica client
     *
     * @return ClientInterface
     */
    public function getCaparicaClient()
    {
        return $this->caparicaClient;
    }

    /**
     * Set the value of the caparica client
     *
     * @param ClientInterface caparicaClient
     *
     * @return self
     */
    public function setCaparicaClient(ClientInterface $value)
    {
        $this->caparicaClient = $value;

        return $this;
    }

    /**
     * Get the value of the caparica request Signer
     *
     * @return SignerInterface
     */
    public function getRequestSigner()
    {
        return $this->requestSigner;
    }

    /**
     * Set the value of the caparica request Signer
     *
     * @param SignerInterface requestSigner
     *
     * @return self
     */
    public function setRequestSigner(SignerInterface $value)
    {
        $this->requestSigner = $value;

        return $this;
    }

    /**
     * Get the value of include the reuqest path when signing
     *
     * @return boolean
     */
    public function getIncludePath()
    {
        return $this->includePath;
    }

    /**
     * Set the value of include the reuqest path when signing
     *
     * @param boolean includePath
     *
     * @return self
     */
    public function setIncludePath($value)
    {
        $this->includePath = $value;

        return $this;
    }

    /**
     * Get the value of include the reuqest method when signing
     *
     * @return boolean
     */
    public function getIncludeMethod()
    {
        return $this->includeMethod;
    }

    /**
     * Set the value of include the reuqest method when signing
     *
     * @param boolean includeMethod
     *
     * @return self
     */
    public function setIncludeMethod($value)
    {
        $this->includeMethod = $value;

        return $this;
    }

}
