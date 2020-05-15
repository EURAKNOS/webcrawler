<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1d8a0a8efd145837b0c7ebd9d4db8126
{
    public static $prefixLengthsPsr4 = array (
        'w' => 
        array (
            'wapmorgan\\Mp3Info\\' => 18,
            'wapmorgan\\MediaFile\\' => 20,
            'wapmorgan\\FileTypeDetector\\' => 27,
            'wapmorgan\\BinaryStream\\' => 23,
        ),
        'S' => 
        array (
            'Symfony\\Component\\Process\\' => 26,
            'Socket\\Raw\\' => 11,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
        'N' => 
        array (
            'Nesk\\Rialto\\' => 12,
            'Nesk\\Puphpeteer\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'wapmorgan\\Mp3Info\\' => 
        array (
            0 => __DIR__ . '/..' . '/wapmorgan/mp3info/src',
        ),
        'wapmorgan\\MediaFile\\' => 
        array (
            0 => __DIR__ . '/..' . '/wapmorgan/media-file/src',
        ),
        'wapmorgan\\FileTypeDetector\\' => 
        array (
            0 => __DIR__ . '/..' . '/wapmorgan/file-type-detector/src',
        ),
        'wapmorgan\\BinaryStream\\' => 
        array (
            0 => __DIR__ . '/..' . '/wapmorgan/binary-stream/src',
        ),
        'Symfony\\Component\\Process\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/process',
        ),
        'Socket\\Raw\\' => 
        array (
            0 => __DIR__ . '/..' . '/clue/socket-raw/src',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Nesk\\Rialto\\' => 
        array (
            0 => __DIR__ . '/..' . '/nesk/rialto/src',
        ),
        'Nesk\\Puphpeteer\\' => 
        array (
            0 => __DIR__ . '/..' . '/nesk/puphpeteer/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'v' => 
        array (
            'vierbergenlars\\SemVer\\' => 
            array (
                0 => __DIR__ . '/..' . '/vierbergenlars/php-semver/src',
            ),
            'vierbergenlars\\LibJs\\' => 
            array (
                0 => __DIR__ . '/..' . '/vierbergenlars/php-semver/src',
            ),
        ),
        'S' => 
        array (
            'Sunra\\PhpSimple\\HtmlDomParser' => 
            array (
                0 => __DIR__ . '/..' . '/sunra/php-simple-html-dom-parser/Src',
            ),
        ),
    );

    public static $classMap = array (
        'Flac' => __DIR__ . '/..' . '/bluemoehre/flac-php/flac.class.php',
        'vierbergenlars\\SemVer\\Internal\\Comparator' => __DIR__ . '/..' . '/vierbergenlars/php-semver/src/vierbergenlars/SemVer/internal.php',
        'vierbergenlars\\SemVer\\Internal\\Exports' => __DIR__ . '/..' . '/vierbergenlars/php-semver/src/vierbergenlars/SemVer/internal.php',
        'vierbergenlars\\SemVer\\Internal\\G' => __DIR__ . '/..' . '/vierbergenlars/php-semver/src/vierbergenlars/SemVer/internal.php',
        'vierbergenlars\\SemVer\\Internal\\Range' => __DIR__ . '/..' . '/vierbergenlars/php-semver/src/vierbergenlars/SemVer/internal.php',
        'vierbergenlars\\SemVer\\Internal\\SemVer' => __DIR__ . '/..' . '/vierbergenlars/php-semver/src/vierbergenlars/SemVer/internal.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit1d8a0a8efd145837b0c7ebd9d4db8126::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit1d8a0a8efd145837b0c7ebd9d4db8126::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit1d8a0a8efd145837b0c7ebd9d4db8126::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit1d8a0a8efd145837b0c7ebd9d4db8126::$classMap;

        }, null, ClassLoader::class);
    }
}
