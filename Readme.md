# Caparica Guzzle Plugin
A [Guzzle](https://github.com/guzzle/guzzle) plugin to make signed requests to a [Caparica](https://github.com/francodacosta/caparica) enabled API

# Installation
the easiest way is to install it via composer

```composer.phar require francodacosta/caparica-guzzle-plugin```


# How to use it
```php
use Caparica\Client\BasicClient as CaparicaClient;
use Caparica\Crypto\RequestSigner;
use Guzzle\Service\Client;

// 1) create the Caparica client with your client id and password
$client = new CaparicaClient;
$client->setCode('client code');
$client->setSecret('client Secret');

// 2) Instantiate a request signer
$requestSigner = new RequestSigner;

// 3) Set up the plugin
$plugin = new CaparicaGuzzlePlugin($client, $requestSigner, $includeRequestPathInSignature = true, $includeRequestMethodInSignature = true);

// 4) Register the plugin with guzzle
$guzzle = new Client;
$guzzle->addSubscriber($plugin)

// now you can do your requests, they will automaticaly be signed
