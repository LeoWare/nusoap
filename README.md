[comment]: <> (Version $Id: $)

<h1 align="center">New NuSOAP</h1>

<p align="center">
NuSOAP is a rewrite of SOAPx4, provided by NuSphere and Dietrich Ayala. It is a set of PHP classes - no PHP extensions required - that allow developers to create and consume web services based on SOAP 1.1, WSDL 1.1 and HTTP 1.0/1.1.
</p>

<p align="center">
💻 <a href="https://github.com/LeoWare">LeoWare</a>
</p>

<p align="center">
  All credits belongs to official authors, take a look at <a href="https://sourceforge.net/projects/nusoap/">sourceforge.net/projects/nusoap/</a>
</p>

<p align="center">
    <a href="https://packagist.org/packages/neutrondev/new-nusoap"><img src="https://img.shields.io/packagist/l/neutrondev/new-nusoap.svg?style=flat-square"></a>
    <a href="https://packagist.org/packages/neutrondev/new-nusoap"><img src="https://img.shields.io/packagist/dt/neutrondev/new-nusoap.svg?style=flat-square"></a>
    <a href="https://packagist.org/packages/neutrondev/new-nusoap"><img src="https://img.shields.io/packagist/v/neutrondev/new-nusoap.svg?style=flat-square"></a>
</p>

-----

## Info

- Supported PHP: 7.4 - 8.0
- Latest version: [0.10](https://github.com/LeoWare/nusoap/releases/tag/v0.10)
- Official project: https://sourceforge.net/projects/nusoap/

## Installation

To install this library use [Composer](https://getcomposer.org/).

```
composer require neutrondev/new-nusoap
```

## Usage

```php
// Config
$client = new nusoap_client('example.com/api/v1', 'wsdl');
$client->soap_defencoding = 'UTF-8';
$client->decode_utf8 = FALSE;

// Calls
$result = $client->call($action, $data);
```