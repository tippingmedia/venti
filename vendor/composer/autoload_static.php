<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0fccd79e9a31c51a38d528e117645914
{
    public static $prefixLengthsPsr4 = array (
        't' => 
        array (
            'tippingmedia\\venti\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'tippingmedia\\venti\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'R' => 
        array (
            'Recurr' => 
            array (
                0 => __DIR__ . '/..' . '/simshaun/recurr/src',
            ),
        ),
        'D' => 
        array (
            'Doctrine\\Common\\Collections\\' => 
            array (
                0 => __DIR__ . '/..' . '/doctrine/collections/lib',
            ),
        ),
    );

    public static $classMap = array (
        'Mexitek\\PHPColors\\Color' => __DIR__ . '/..' . '/mexitek/phpcolors/src/Mexitek/PHPColors/Color.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0fccd79e9a31c51a38d528e117645914::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0fccd79e9a31c51a38d528e117645914::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit0fccd79e9a31c51a38d528e117645914::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit0fccd79e9a31c51a38d528e117645914::$classMap;

        }, null, ClassLoader::class);
    }
}