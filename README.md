# Laravel Param Pos Kütüphanesi

[![Latest Version on Packagist](https://img.shields.io/packagist/v/fatihozpolat/laravel-param-pos.svg?style=flat-square)](https://packagist.org/packages/fatihozpolat/laravel-param-pos)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/fatihozpolat/laravel-param-pos/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/fatihozpolat/laravel-param-pos/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/fatihozpolat/laravel-param-pos/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/fatihozpolat/laravel-param-pos/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/fatihozpolat/laravel-param-pos.svg?style=flat-square)](https://packagist.org/packages/fatihozpolat/laravel-param-pos)

Kişisel kullanımlar için, https://dev.param.com.tr/tr adresi baz alınarak geliştirilmiş Laravel Param Pos
kütüphanesidir.

API kısmındaki servislerin 99% unu içerir. Pazaryeri kodlara dahil değildir. Daha sonra gerekliliğe göre eklenebilir.

## Kurulum

composer ile paketi yükleyebilirsiniz:

```bash
composer require fatihozpolat/laravel-param-pos
```

Ayar dosyasını yayınlamak için:

```bash
php artisan vendor:publish --tag="param-pos-config"
```

Ayar dosyası yayınlandıktan sonra bu şekilde görünecektir:

```php
return [
    'client_code' => env('PARAM_CLIENT_CODE'),
    'client_username' => env('PARAM_CLIENT_USERNAME'),
    'client_password' => env('PARAM_CLIENT_PASSWORD'),
    'guid' => env('PARAM_GUID'),
    'test' => env('PARAM_TEST', true),
];
```

## Kullanım

Birçok param api soap servisini Param:: ile kullanabilirsiniz.
Örneğin KS_Kart_Ekle servisini kullanmak için;

```php
use FatihOzpolat\Param\Facades\Param;

Param::ks_kart_ekle('Test Kişi', '4446763125813623', '12', '26', 'Test Ziraat Kartı', 'testislemid');
````

bu işlem size dökümanda belirtildiği gibi ama array olarak dönecektir.

## Changelog

Daha fazla bilgi için [CHANGELOG](CHANGELOG.md) a bakabilirsiniz.

## Credits

- [Fatih Özpolat](https://github.com/fatihozpolat)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
