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
$plugin = new CaparicaGuzzlePlugin(
    $client,
    $requestSigner,
    $includeRequestPathInSignature = true,
    $includeRequestMethodInSignature = true
);

// 4) Register the plugin with guzzle
$guzzle = new Client;
$guzzle->addSubscriber($plugin)

// now you can do your requests, they will automatically be signed
```

Typically this configuration is done in a DI Container, for symfony you could
add the following to your services.yml file

```yml
parameters:
    client.code: client-id
    client.secret: client-secret

    caparica.signature.includes.path: true
    caparica.signature.includes.method: true

services:
    caparica.client:
        class: Caparica\Client\BasicClient
        calls:
            - [setCode,   [%client.code%]]
            - [setsecret, [%client.secret%]]

    caparica.request.signer:
        class: Caparica\Crypto\RequestSigner

    caparica.guzzle.plugin:
        class: Francodacosta\CaparicaBundle\Guzzle\Plugin\CaparicaGuzzlePlugin
        arguments:
            - @caparica.client
            - @caparica.request.signer
            - %caparica.signature.includes.path%
            - %caparica.signature.includes.method%

    caparica.guzzle:
        class: Guzzle\Service\Client
        calls:
            - [addSubscriber, [@caparica.guzzle.plugin]]

```

and on your controller ``` $caparicaGuzzle = $this->get('caparica.guzzle'); ``` will give you access to Guzzle with the Caparica plugin configured
