<?php
spl_autoload_register('autoload');
//include_once __DIR__ . DIRECTORY_SEPARATOR . 'class1.php';
//spl_autoload_register(array('design\Class1', 'autoload'));
//将实现自动导入的函数，以字符串的形式传入该函数中，即可解决重复导入文件导致的错误问题。
function autoload($class = '')
{
    $file = dirname(__DIR__). DIRECTORY_SEPARATOR . $class . '.php';
    include_once $file;
}

\design\library2\Demo1::test();
\design\library2\Demo2::test();

\design\library1\Test1::test();
\design\library1\Test2::test();