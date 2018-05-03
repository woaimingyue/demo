<?php
header("Content-type: text/html; charset=utf-8");
echo '<pre>';

class Conf
{
    private $file;
    private $xml;
    private $lastmatch;

    function __construct($file)
    {
        $this->file = $file;
        $this->xml = simplexml_load_file($file);
    }

    function write()
    {
        return file_put_contents($this->file, $this->xml->asXML());
    }

    function get($str)
    {
        $matches = $this->xml->xpath("/conf/item[@name=\"$str\"]");
        if (count($matches)) {
            $this->lastmatch = $matches[0];
            return (string)$matches[0];
        }
        return null;
    }

    function set($key, $value)
    {
        if (!is_null($this->get($key))) {
            $this->lastmatch[0] = $value;
            return;
        }
        $conf = $this->xml->conf;
        var_dump($conf);
        die;
        $this->xml->addChild('item', $value)->addAttribute('name', $key);
    }
}

/*工厂模式*/

class Payment
{
    public function __construct($method, $total)
    {
        $payObject = $this->getType($method);
        return $this->pay($payObject, $total);
    }

    private function pay($payObject = '', $totlal = 0)
    {
        return (new $payObject)->pay($totlal);
    }

    public function getType($pay_type)
    {
        $classname = ucfirst($pay_type);
        if (class_exists($classname)) {
            return $classname;
        }
    }
}

class Wxpay
{
    public function pay($total)
    {
        if ($total) {
            echo 'pay success!';
        } else {
            echo 'pay fail!';
        }
    }
}

class Zfbpay
{
    public function pay($total)
    {
        if ($total) {
            echo 'pay success!';
        } else {
            echo 'pay fail!';
        }
    }
}

/*
$price = rand(0,1);
$method = 'zfbpay';
$result = new Payment($method, $price);
*/

/*单例模式*/

class Mysql
{
    private static $conn;

    private function __construct()
    {
        self::$conn = mysqli_connect('localhost', 'root', '');
    }

    public static function get_instance()
    {
        if (!(self::$conn instanceof self)) {
            self::$conn = new self();
        }
        return self::$conn;
    }

    public function __clone()
    {
        // TODO: Implement __clone() method.
        trigger_error('Clone is not allow');
    }

    public function __wakeup()
    {
        // TODO: Implement __wakeup() method.
        trigger_error('Wakeup is not allow');
    }
}

/*原型模式*/

interface Yuanxing
{
    public function copy();
}

class Consruct implements Yuanxing
{
    private $name;
    static $age;

    public function __construct($object)
    {
        $this->name = $object;
    }

    public function copy()
    {
        return clone $this;
    }
}

class Demo
{
}

$demo = new Demo();
$object1 = new Consruct($demo);
$result = $object1->copy();

/*适配器模式*/

interface Database
{
    public function connect();

    public function query();

    public function close();
}

class Connect
{
    private static $conarr = [];

    public static function set($alias, $object)
    {
        self::$conarr[$alias] = $object;
    }

    public static function get($alias)
    {
        return self::$conarr[$alias];
    }
}

class Mysqls implements Database
{
    public function connect()
    {

    }

    public function query()
    {

    }

    public function close()
    {

    }
}

class Pdos implements Database
{
    public function connect()
    {

    }

    public function query()
    {

    }

    public function close()
    {

    }
}

/*桥接模式*/

interface Formats
{
    public function format();
}

class Plian implements Formats
{
    public function format()
    {
        return 'text';
    }
}

class Xml implements Formats
{
    public function format()
    {
        return 'xml';
    }
}

abstract class Service
{
    public $ident;

    public function __construct($object)
    {
        $this->ident = $object;
    }

    public function setIdent($object)
    {
        $this->ident = $object;
    }

    abstract public function get();
}

class Hellow extends Service
{
    public function get()
    {
        return $this->ident->format();
    }
}

$hellow = new Hellow(new Plian());
$res['one'] = $hellow->get();

$hellow->setIdent(new Xml());
$res['two'] = $hellow->get();

/*示例 吃饭*/

abstract class Food
{
    abstract public function cooking();
}

class MakeFood extends Food
{
    private $name;
//    private $unit;
//    private $num;
//    private $action;

//    private $baocai;
//    private $lajiao;
//    private $pork;

    public function __construct($food_name)
    {
        $this->name = $food_name;
    }

    public function cooking()
    {
        return '一碗' . $this->getStuff() . '炒好了!<br>';
    }

    public function getStuff()
    {
        return $this->name;
    }
}

abstract class Meal
{
    abstract public function steaming();
}

class MakeMeal extends Meal
{
    private $name;

    public function __construct($food_name)
    {
        $this->name = $food_name;
    }

    public function steaming()
    {
        return '一碗' . $this->getStuff() . '煮好了!<br>';
    }

    public function getStuff()
    {
        return $this->name;
    }
}

class SendFood
{
    public $cai;
    public $fan;

    public function __construct($cai, $fan)
    {
        $this->cai = $cai;
        $this->fan = $fan;
    }

    public function sending()
    {
        return rand(1, 80) . '号' . $this->cai . $this->fan;
    }
}

$cai = (new MakeFood('包菜炒肉'))->cooking();
$fan = (new MakeMeal('白米饭'))->steaming();
$res1['one'] = (new SendFood($cai, $fan))->sending();

/*组合模式*/
interface Render
{
    public function render();
}

class Makegroup implements Render {
    public $elements;

    public function render()
    {
        $form = '<form>';
        foreach ($this->elements as $element) {
            $form .= (new Elements())->render($element);
        }
        $form .= '</form>';
        echo $form;
    }

    public function addElement($element)
    {
        $this->elements[] = $element;
    }
}

class Elements {
    private $types = ['text', 'password', 'select', 'button', 'submit'];

    public function render($type)
    {
        if (in_array($type, $this->types)) {
            switch ($type) {
                case 'text':
                    $text = '<input type="text" name="name" />';
                    break;
                case 'password':
                    $text = '<input type="password" name="password" />';
                    break;
                default:
                    $text = '';
                    break;
            }

            return $text;
        }
    }
}

$elementing = new Makegroup();
$elementing->addElement('text');
$elementing->addElement('password');
$elementing->addElement('submit');

//$elementing->render();

/*代理模式*/
interface Image { public function getWithd();}

//真是对象
class Makeimage implements Image {
    public function getWithd()
    {
        return '100x70';
    }
}
//代理对象
class Daili implements Image {

    private $img;

    public function __construct()
    {
        $this->img = new Makeimage();
    }

    public function getWithd()
    {
        return $this->img->getWithd();
    }
}

$daili = new Daili();
$result = $daili->getWithd();

/*注册器问题*/
class Register {
    protected static $objects;

    public function set($alia, $object)
    {
        if(!isset(self::$objects[$alia])){
            self::$objects[$alia] = $object;
        }
    }

    public function get($alia)
    {
        return self::$objects[$alia];
    }

    public function _unset($alia)
    {
        unset(self::$objects[$alia]);
    }
}

$register = new Register();
$register->set('mysql', Mysql::get_instance());

$mysql = $register->get('mysql');

/*观察者模式*/

//观察基类
abstract class Look {
    protected $rongqi;

    function add($user)
    {
        $this->rongqi[] = $user;
    }

    function notice()
    {
        foreach ($this->rongqi as $user) {
            $user->update();
        }
    }
}

class Looking extends look {
    function trigger()
    {
        echo 'event happend!';
        $this->notice();
    }
}

class User1 {
    private $age = 10;
    function update()
    {
        echo 'my age is'.++$this->age;
    }
}
class User2 {
    private $age = 20;
    function update()
    {
        echo 'my age is'.++$this->age;
    }
}
class User3 {
    private $age = 17;
    function update()
    {
        echo 'my age is'.++$this->age;
    }
}

$looking = new Looking();
$looking->add(new User1());
$looking->add(new User2());
$looking->add(new User3());

//$looking->trigger();

/*访问者模式*/
class Unit {
    public function accept($visitor)
    {
        $method = 'visit'.get_class($visitor);

        if(method_exists($visitor, $method)) {
           return $visitor->$method();
        }
    }
}

class User extends Unit{
    function getName()
    {
        return 'my name is xiaosan';
    }
}

class Getuserphone {
    function visitGetuserphone()
    {
        return 'my phone is 110';
    }
}

/*策略模式*/
interface Show { public function showStyle();}

class Showgirl implements Show {
    public function showStyle()
    {
        return 'my name is lili';
    }
}

class Showmen implements Show {
    public function showStyle()
    {
        return 'my name is kangkang';
    }
}

class Pingtai {
    private $object;
    public function __construct($object)
    {
        $this->object = $object;
    }

    public function showTime()
    {
        return $this->object->showStyle();
    }
}

$result = new Pingtai(new Showmen());

