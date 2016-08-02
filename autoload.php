<?php

function autoload($className)
{
    $file = dirname(__FILE__).'/model/'.$className.'.php';
    if( file_exists($file) )
        require $file;
}

spl_autoload_register('autoload');