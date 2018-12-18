<?php
//yii/base/object实现过程
  /**
  * $name:属性名
  * getter 函数名
  *//
  public function __get($name){
    $getter = 'get'.$name;
    if(method_exists($this,$getter)){
      return $this->$getter();//调用该函数
    }elseif (method_exists($this,'set'.$name)){
      throw new InvalidCallException('Getting write-only property:'
      .get_class($this).'::'.$name);
    }else{
      throw new UnknownPropertyException('Getting unknown property:'.get_class($this).'::'.$name);
    }
  }

  public function __set($name,$value){
    $setter = 'set'.$name;
    if(method_exists($this,$setter)){
      return $this->$setter($value);
    }elseif(method_exists($this,'get'.$name)){
      throw new InvalidCallException('Setiing read-only property:'.get_class($this).'::'.$name);
    }else{
      throw new UnknownPropertyException('Setting unkonwn property :'.get_class($this).'::'.$name);
    }
  }


  //实践
  class Post extends yii/base/Object{
    private $_title;
    public funciton getTitle(){
      return $this->_title;
    }

    public function setTitle($value){
      $this->_title = trim($value);
    }
  }

  //yii 全局统一的配置方式

  $config = yii\helper\ArrayHelper::merge(
    require(__DIR__.'/../../common/config/main.php'),
    require(__DIR__.'/../../common/config/main-local.php'),
    require(__DIR__.'/../config/main.php'),
    require(__DIR__.'/../config/main-local.php')

  );

  $application = new yii\web\Application($config);

  public function __construct($config = []){
    if(!empty($config)){
      Yii::configure($this,$config);
    }
    $this->init();
  }

  //cinfigure实现鬼过程
  public static function configure($object,$properties){
    foreach ($properties as $name => $value) {
      // code...
      $object->$name = $value;
    }
    return $object;
  }
/**
*$name :事件名；$sender:事件发布者，调用trigger()的对象或者类
*$handled //是否终止事件的后续处理
*$data //事件相关数据
*/
  class Event extends Object{
    public $name;
    public $sender;
    public $handled = false;
    public $data;

    private static $_event = [];//这个就是handler数组
    //绑定事件的handler就是一个将anlder写入_event[]
    public static function on($class,$name,$handler,$data=null,$append=true){
      //绑定事件的handler
    }

    public static function off($class,$name,$handler=null){
      //用于取消事件的绑定
    }

    public static function hasHandlers($class,$name){
      //用于判断是有相应的handler与事件对相应
    }

    public static function trigger($class,$name,$even = null){
      //用于触发事件
    }
  }

  public function on($name,$handler,$data = null,$append=true){
    $this->ensureBehaviors();
    if($append|| empty($this->_events[$name])){
      $this->_events[$name][] = [$handler,$data];
    }else{
      array_unshift($this->_events[$name],[$handler,$data]);
    }
  }

  public function  off($name,$handler=null){
    $this->ensureBehaviors();
    if(empty($this->_events[$name])){
      return false;
    }

    //$hanlder ===null就是解除所有的hanlder
    if($handler ===null){
      unset($this->_events[$name]);
      return true;
    }else{
      $removed = false;
      //遍历所有的handler
      foreach ($this->_events[$name] as $i => $event) {
        // code...
        if($event[0] === $handler){
          unset($this->_events[$name][$i]);
          $removed = true;
        }
      }
      if($removed){
        $this->_events[$name] = array_values($this->_events[$name]);
      }
      return $removed;
    }
  }

  public function trigger($name,Event $event=null){
    $this->ensureBehaviors();
    if(!empty($this->_events[$name])){
      if($event === null){
        $event = new Event;
      }
      if($event->sender =null){
        $event->sender = $this;
      }
      $event->handled = false;
      $event->name = $name;

      //遍历handler数组，并且依次调用
      foreach ($this->_events[$name] as $handler) {
        // code...
        $event->data = $handler[1];
        //使用php的call_user_func()调用handler
        call_user_func($handler[0],$event);
        //如果在某一个handler里面设置￥event->handled为true,就不会调用后续的hanlder
        if($event->handled){

          return ;
        }
      }
    }
    Event::trigger($this,$name,$event);

  }
//实践：
abstract class Application extends Module{
  //定义了两个事件
  const EVENT_BEFORE_REQUEST = 'beforeRequest';
  const EVENT_AFTER_REQUEST = 'afterRequest';

  public funciton run(){
    try{
      $this->state = self::STATE_BEFORE_REQUEST;
      //先触发EVENT_BEFORE_REQUEST;
      $this->trigger(self::EVENT_BEFORE_REQUEST);

      $this->state = self::STATE_HANDLING_REQUEST;
      //处理请求
      $response = $this->handleRequest($this->getRequest());
      $this->state = self::STATE_AFTER_REQUEST;

      $this->trigger(self::EVENT_AFTER_REQUEST);

      $this->state = self::STATE_SENDING_RESPONSE;

      $response->send();

      $this->state = self::STATE_END;

      return $response->exitStatus;
    }catch(ExitException $e){
      $this->end($e->statusCode,isset($response)?$response:null);
      return $e->statusCode;
    }
  }
}

class MsgEvent extends yii\base\Event{
  public $dateTime;
  public $author;
  public $content;
}

//在发微薄的时候，准备好传递给hanlder的数据
$event = new MsgEvent;
$event->title = $title;
$event->author = $author;
//触发事件
$msg->trigger(Msg::EVENT_NEW_MESSAGE,$event);


//step1:定义一个将绑定行为的类
class MyClass extends yii\base\Component{
  //空的
}
//step2:
class MyBehavior extends yii\base\Behavior{
  //行为的属性
  public $property1 = 'This is property in MyBehaviors.'

  //行为的一个方法
  public function  method1(){
    return 'Method in MyBehavior is called';

  }
}

$myClass = new MyCalss();
$myBehavior = new MyBehavior();

//step3:将行为u绑定到类
$myClass->attachBehavior('myBehavior',$myBehavior);

//step4:
echo $myClass->property1;
echo $myClass->method1();


class Behavior extends Object{
  //指向行为本身绑定的Component对象
  public $owner;
  //Behavior基类本身没有任何用处用，主要是子类使用，重载这个函数返回一个数组表
    // 示行为所关联的事件
  public function events(){
    return [];
  }

  public function attach($owner){
    //绑定行为到￥owner
  }

  //解除绑定
  public function detach(){

  }

}


namespace app\Component;

use yii\db\ActiveRecord;
use yii\base\Behavior;

class MyBehavior extends Behavior{
  //重载events,来允许事件触发的时候调用行为的一些方法
  public function events(){
    return [ActiveRecord::EVENT_BEFORE_VALIDATE =>'beforeValidate'];
  }

  //上面绑定的handler是行为的成员函数，也就是这个里面的。
  public function beforeValidate(){}
}



namespace app\Component;

use yii\base\Behavior;

class MyBehavior extends Behavior{
  public $prop1;

  private $_prop2;
  private $_prop3;
  private $_prop4;
  //属性可读
  public function getProp2(){
    return $this->_prop2;
  }
  // 属性可写
  public function setProp3($value){
    $this->_prop3 = $value;
  }
//公共方法
  public function foo(){
    //
  }
//不会被其他类继承
protected function bar(){}

}
// 绑定的Component也就拥有了 prop1 prop2 这两个属性和方法 foo() ，因为他们都是 public 的。
// 而 private 的 $_prop4 和 protected 的 bar 就得不到了



?>
