# yii2-mynote
##关于对象中属性和变量的区别
1.成员变量和属性的区别与联系在于：

1.1成员变量是一个**“内”概念**，反映的是类的结构构成。
1.2属性是一个“外”概念，反映的是类的逻辑意义。
1.3成员变量没有读写权限控制，而属性可以指定为只读或只写，或可读可写。
1.4成员变量不对读出作任何后处理，不对写入作任何预处理，而属性则可以。
1.5public成员变量可以视为一个可读可写、没有任何预处理或后处理的属性。
 而private成员变量由于外部不可见，与属性“外”的特性不相符，所以不能视为属性。
虽然大多数情况下，属性会由某个或某些成员变量来表示，
但属性与成员变量没有必然的对应关系， 
比如与非门的 output 属性，
就没有一个所谓的 $output 成员变量与之对应。

2.对于属性的实现过程，也会就是yii/base/object里面

我们知道，在读取和写入对象的一个不存在的成员变量时，
 __get() __set() 会被自动调用。 
 **Yii正是利用这点，提供对属性的支持的。**
 
 实现思路：：
** 如果访问一个对象的某个属性，**
  Yii会调用名为 get属性名() 的函数。
 如， SomeObject->Foo ， 会自动调用 SomeObject->getFoo() 。
** 如果修改某一属性，会调用相应的setter函数。**
  如， SomeObject->Foo = $someValue ，会自动调用 SomeObject->setFoo($someValue) 。
 
 实践：：
 2.1 继承自 yii\base\Object 。
2.2  声明一个用于保存该属性的私有成员变量。
2.3 提供getter或setter函数，或两者都提供，用于访问、修改上面提到的私有成员变量。 
备注：：**如果只提供了getter，那么该属性为只读属性，
只提供了setter，则为只写。**
 
 实践经验之谈：：
 2.4从理论上来讲，将 private $_title 写成 public $title ，也是可以实现对 $post->title 的读写的。
 2.5但这不是好的习惯，理由如下：

2.6失去了类的封装性。 一般而言，成员变量对外不可见是比较好的编程习惯。
 从这里你也许没看出来，但是假如有一天，你不想让用户修改标题了，你怎么改？
  怎么确保代码中没有直接修改标题？ 如果提供了setter，只要把setter删掉，那么一旦有没清理干净的对标题的写入，
  就会抛出异常。 
  而使用 public $title 的方法的话，你改成 private $title 可以排查写入的异常，
  但是读取的也被禁止了。
对于标题的写入，你想去掉空格。 
使用setter的方法，只需要像上面的代码段一样在这个地方调用 trim() 就可以了。
2.7 但如果使用 public $title 的方法，那么毫无疑问，每个写入语句都要调用 trim() 。 
 你能保证没有一处遗漏？
因此，使用 public $title 只是一时之快，看起来简单，但今后的修改是个麻烦事。 
简直可以说是恶梦。这就是软件工程的意义所在，通过一定的方法，使代码易于维护、便于修改。
 一时看着好像没必要，但实际上吃过亏的朋友或者被客户老板逼着修改上一个程序员写的代码，
 问候过他亲人的， 都会觉得这是十分必要的。
 
 2.8不足之处
 由于 __get() 和 __set() 是在遍历所有成员变量，找不到匹配的成员变量时才被调用。 
 因此，其效率天生地低于使用成员变量的形式。在一些表示数据结构、数据集合等简单情况下，且不需读写控制等，
**  可以考虑使用成员变量作为属性，这样可以提高一点效率**

此处改进：：
使用 $pro = $object->getPro() 来代替 $pro = $object->pro ， 
用 $objcect->setPro($value) 来代替 $object->pro = $value 。 
这在功能上是完全一样的效果，但是避免了使用 __get() 和 __set() ，相当于绕过了遍历的过程。

2.9值得注意的地方

就是属性的public

2.10由于自动调用 __get() __set() 的时机仅仅发生在访问不存在的成员变量时。
 因此，如果定义了成员变量 public $title 那么，就算定义了 getTitle() setTitle() ， 他们也不会被调用。
 因为 $post->title 时，会直接指向该 pulic $title ， __get() __set() 是不会被调用的。从根上就被切断了。
2.11由于PHP对于类方法不区分大小写，即大小写不敏感， $post->getTitle() 和 $post->gettitle() 是调用相同的函数。 
因此， $post->title 和 $post->Title 是同一个属性。即属性名也是不区分大小写的。
2.12由于 __get() __set() 都是public的， 无论将 getTitle() setTitle() 声明为 public, private, protected， 都没有意义，外部同样都是可以访问。所以，所有的属性都是public的。
由于 __get() __set() 都不是static的，因此，没有办法使用static 的属性。

2.13其他便利的方法使用
2.14__isset() 用于测试属性值是否不为 null ，在 isset($object->property) 时被自动调用。 注意该属性要有相应的getter。
2.15__unset() 用于将属性值设为 null ，在 unset($object->property) 时被自动调用。 注意该属性要有相应的setter。
2.16hasProperty() 用于测试是否有某个属性。即，定义了getter或setter。 如果 hasProperty() 的参数 $checkVars = true （默认为true）， 那么只要具有同名的成员变量也认为具有该属性，如前面提到的 public $title 。
2.17canGetProperty() 测试一个属性是否可读，参数 $checkVars 的意义同上。只要定义了getter，属性即可读。 同时，如果 $checkVars 为 true 。那么只要类定义了成员变量，不管是public， private 还是 protected， 都认为是可读。
2.18canSetProperty() 测试一个属性是否可写，参数 $checkVars 的意义同上。只要定义了setter，属性即可写。 同时，在 $checkVars 为 ture 。那么只要类定义了成员变量，不管是public， private 还是 protected， 都认为是可写。

3.Component
yii/base/Component  继承 yii/base/Object

3.1由于Componet还引入了**事件、行为，**因此，它并非简单继承了Object的属性实现方式，而是基于同样的机制， 
重载了 __get() __set() 等函数。但从实现机制上来讲，是一样的。这个不影响理解
3.2官方将Yii定位于一个基于组件的框架。可见组件这一概念是Yii的基础。 

3.3Component三个属性
属性（property）
事件（event）
行为（behavior）

3.4注意使用的选择
3.4.1如果开发中不需要使用event和behavior这两个特性，比如表示一些数据的类。 那么，可以不从Component继承，而从Object继承。
典型的应用场景就是如果表示用户输入的一组数据，那么，使用Object。
 而如果需要对对象的行为和能响应处理的事件进行处理，毫无疑问应当采用Component。
 3.5Yii提供了一个统一的配置对象的方式。
 
yii的配置看起来比较复杂，其实就是各种配置项的数组。
也就是使用数组针对对象来配置
实现过程也就是基于yiii/base/object

 3.6
 所有 yii\base\Object 的构建流程是：

3.6.1 构建函数以** $config 数组为参数**被自动调用。
3.6.2 构建函数调用** Yii::configure()** 对对象进行配置。
3.6.3 在最后，构造函数调用**对象的 init()**方法进行初始化。

3.7配置的过程就是
遍历￥config配置数组，讲数组的键作为属性名，讲对应的数组元素的值对对象的属性进行复制

要点如下：
继承自 yii\base\Object 。
为对象属性提供setter方法，以正确处理配置过程。
如果需要重载构造函数，请将 $config 作为该构造函数的最后一个参数，并将该参数传递给父构造函数。
重载的构造函数的最后，一定记得调用父构造函数。
如果重载了 yii\base\Object::init() 函数，注意一定要在重载函数的开头调用父类的 init() 。
 
注意事项：
如果配置的值是一个对象，只需要在setter方法上对属性进行正确的处理

Yii应用 yii\web\Application 就是依靠定义专门的setter函数，实现自动处理配置项的。
对于对象属性，其配置值 $value 是一个数组，为了使其正确配置。
 你需要在其setter函数上做出正确的处理方式。 
 
 'components' => [
    'request' => [
        // !!! insert a secret key in the following (if it is empty) -
        // this is required by cookie validation
        'cookieValidationKey' => 'v7mBbyetv4ls7t8UIqQ2IBO60jY_wf_U',
    ],
    'user' => [
        'identityClass' => 'common\models\User',
        'enableAutoLogin' => true,
    ],
    'log' => [
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets' => [
            [
                'class' => 'yii\log\FileTarget',
                'levels' => ['error', 'warning'],
            ],
        ],
    ],
    'errorHandler' => [
        'errorAction' => 'site/error',
    ],
],

所以这一个经典的配置项，也是必须要有一个setComponents()来处理里面各项属性的分配

4.事件
事件是就是在特定的时间点，触发执行预先设计的一段代码。
事件既是代码解耦的一种方式，也是设计业务流程的一种模式。

4.1事件的要素：
4.1.1这是一个什么事件？一个软件系统里，有诸多事件，发布新微博是事件，删除微博也是一种事件。
4.1.2谁触发了事件？你发的微博，就是你触发的事件。
4.1.3谁负责监听这个事件？或者谁能知道这个事件发生了？服务器上处理用户注册的模块，肯定不会收到你发出新微博的事件。
4.1.4事件怎么处理？对于发布新微博的事件，就是通知关注了你的其他用户。
4.1.5事件相关数据是什么？对于发布新微博事件，包含的数据至少要有新微博的内容，时间等。

yii中事件实在yii/base/Component中引入，yii/base/object不支持事件

yii中还有一个与事件紧密相关的yii/base/Event,封装与事件相关的有关数据，并且提供一些功能函数作为辅助

4.2事件Handler
这个就是事件处理程序，负责事件触发后怎么办的事情
从本质上讲，一个事件handler就是一段PHP代码，一个PHP函数

形式如下：
4.2.1全局函数名，只需要一个名字，如trim,不需要括号和参数
4.2.2 一个对象的方法或者一个类的静态方法。比比如$person->sayHello()
,但是需要改变形式，数组的形式---》[$person,'sayHello'],静态方法是这样的---》['namespace\to\Person','sayHello]
4.2.3匿名函数，如function($evet){}

handler形式如下
function（$event）{
	//$event就是前面提到的yii\base\Event
}
必须是这样的，才能作为事件的handler

(EVENT_A,'publicMethod')
但是事实上这是一个错误的写法
使用字符串像是提供的handler，至少能使PHP的全局函数
原因：这是因为handler的调用时通过call_user_func()来实现的，
**因此，hanlder的形式，与call_user_func()的要求是一致的**

4.3事件的绑定与解除
有了事件handler,还需要告诉Yii,这个handler是处理什么事件的。这个过程中，就是事件的绑定，就是把事件和事件handler这二者绑定在一起

当事件出发的时候，就和联动hanlder


实现：
yii\base\Component::on()就是用来绑定的，很容易猜到，yii\base\Component::off()就是用来解除的
4.3.1绑定的形式：：
$person = new Person;

$person->on(Person::EVENT_GREET,'person_say_hello');//这是字符串,这是PHP全局函数
$person->on(Person::EVENT_GREET,[$obj,'say_hello']);//使用对象￥obj的成员函数say_hello来绑定
$person->on(Person::EVENT_GREET,['app\helper\Greet','say_hello']);
$person->on(Person::EVENT_GREET,function($event){
	echo 'hello';
});

4.3.2事件的绑定不仅是在运行时绑定，也可以在配置文件中绑定
4.3.3事件的参数
//参数将会写进Event的相关数据字段，也就是属性data
$person->on(Person::EVENT_GREET,'person_say_hello','Hello World!');
//event的第三个参数是可以通过$event来访问的
function person_say_hello($event){
	echo $event->data;
}

4.3.4 yii\base\Component维护了一个handler数组，用来保存绑定的hanlder

关于数组$_event[]的结构来看，首先他是一个数组，保存了该Component的所有实践handler,该数组的下标是事件名。
数组元素是一系列[$handler,$data]的数组，事件有EVENT_NEW_POST,EVENT_UPDATE_POST,EVENT_DELETE_POST

绑定逻辑上，参数$append是否为true,  true就是要将事件hanlder放在$_event[]数组的最后面，默认的绑定方式
false:就是要放在数组的最前面，如果绑定的事件是第一个，true和false没有区别

数组的位置就是事件执行的顺序，位置很重要

4.4事件的解除
简单来说就是内部使用实现unset去除$_event[]数组中的相应元素


注意：
可能还会出现同一个handler多次绑定在一个事件上，来执行多次。但是没有办法清除其中一个，只能都清除掉


4.5事件的触发
使用yii\base\Component::trigger()


4.5.1定义事件：对于事件的定义，提倡使用const 常量的形式，可以避免写错。
trigger('Hello')和trigger('hello')这是不同的事件
原因:在hanlder数组下标中，就是事件名；
在PHP数组中，数组下标是区分大小写的；
所以触发的时候，使用类常量是可以避免的

on绑定事件的时候，可以在handler中通过$event->data进行访问，这是绑定的时候确定的数据

有很多数据是不能确定的，比如事件，需要在触发的时候提供

4.5.2多个事件handler的顺序
使用yii\base\Componnet::on()可以为各种事件绑定handler,也可以为同一事件绑定多个handler.

比如说微博发送，首先指定发送微薄的事件hanlder就是通知关注者有新的内容发出，还要通知微博中 @的所有人。

两个处理方式：
1.直接在原来的handler上增加新的代码，补充功能
2.在写一个handler,并且绑定在这个事件上。从维护的角度来说，第二个方法是合理的

//关于顺序，有个语言采用堆栈来进行顺序管理
只需要在自己定义的handler里面加上$event->handled = true;
这样的话，放在最前面，$append=false;后续所有的处理方式就不会执行
这样不合理的handler就炫耀使用off 解绑删除

需要注意的问题
1.删除匿名函数
为了正确删除，需要先把匿名函数保存成一个变量，比如$myfunction.但是一旦加上变量，就会失去匿名函数的优势。

所以只有一定不会删除的才建议使用匿名函数，否则视为无法删除的

4.6事件的级别
事件都是针对类的实例而言，也就是事件的触发，处理全部都在实例范围内。这种级别的事件功能专一，不会和其他实例发生关系。还有其他类级别的事件。
Yii，Application是一个单例，所以的代码都可以访问它。这就是一个特殊的，全局事件。本质上，它只是一个实例级别的事件

但是影响的范围不同，全局事件，会把影响扩大到整个代码所有的角落，单个类级别的事件只能影响自己

4.7类级别的事件
为了统计的所有工人的下班时间，针对数百个工人，就会有数百个worker实例，工头如何处理
它不需要绑定到具体的每个工人，只需要绑定到work,就可以知道所有的

为了类级别的事件，需要使用yii\base\Event::on()
Event::on(Worker::ClassName(),Worker::EVENT_OFF_DUTY,function($event){
	echo $event->sender.'下班了';
})

在工人下班的时候，都会触发自己的事件处理函数，打卡等。之后会触发类级别的事件
因为在Component中最后一句话就是
Event::trigger($this,$name,$event)

类级别的事件，总是在实例事件后触发。
如果不想触发最后的事件，那么可以在之前的事件处理handler中，找机会设置$event->handled = true

4.7.1类级别事件的参数
// Component中的$_event[] 数组
$_event[$eventName][] = [$handler, $data];

// Event中的$_event[] 数组
$_event[$eventName][$calssName][] = [$handler, $data];

类级别的事件触发，不会和任意的实例相关联，所以类级别事件触发时，类的实例看可能还没有。

所以类级别的事件触发，应该使用yii\base\Event::trigger(),不会触发实例级别的事件
$event->sender在实例级别事件中，$event->sender指向触发事件的实例，而在类级别的事件中，执行的是类名

if(is_object($class)){
	if($event->sender == null){
		$event->sender = $class;
	}
    get_class($class);//传入的是一个实例，则用类名替换
}else{
	$class = ltrim($class,'\\');
}

对于类级别的事件，除了触发类事件，还会触发所有祖先类的同一个事件。但是彼此之间只同一个级别，随时可以被终止

do{
	if(!empty(self::$_events[$name][$class])){
		foreach(self::$_event[$name][$class] as $handler){
			$event->data = $handler[1];
			call_user_func($handler[0],$event);
			
			if($event->handled){
				return;
			}
		}
	}
}while(($class = get_parent_class($calss))!==false);

这个处理过程的深度，时间复杂度收两个方面的影响

1.继承的祖先结构的深度
2.$_event[$name][$class][]数组元素的个数，也就是绑定的handle的数量

继承深度超过十层很少见;事件绑定上，同一个事件的绑定handler超过十几个也比较少见，所以循环运算在100~1000之间，是可以接受的


4.7.3在机制上，由于类级别事件会被类自身，类的实例，后代类，后代类实例所触发，所以越底层的类，影响范围往往超出预期，所以尽量在后代类

全局事件
接下来再讲讲全局级别事件。上面提到过，所谓的全局事件，本质上只是一个实例事件罢了。
他只是利用了Application实例在整个应用的生命周期中全局可访问的特性，来实现这个全局事件的。
当然，你也可以将他绑定在任意全局可访问的的Component上。

全局事件一个最大优势在于：在任意需要的时候，都可以触发全局事件，也可以在任意必要的时候绑定，或解除一个事件:
Yii::$app->on('bar', function ($event) {
    echo get_class($event->sender);        // 显示当前触发事件的对象的类名称
});

Yii::$app->trigger('bar', new Event(['sender' => $this]));
上面的 Yii::$app->on() 可以在任何地方调用，就可以完成事件的绑定。而 Yii::$app->trigger() 只要在绑定之后的任何时候调用就OK了。****

4.8行为

Behavior是在不修改现有类的情况下，对类的功能能进行扩充.通过将行为绑定在一个类上。可以使类具有行为本身所定义的属性和方法
就像是类本身就有这些属性和方法，而不需要一个新类去继承或者包含现有类。

Yii中的行为，其实就是yii\base\Behavior类的实例

只需要将Behavior实例你绑定到任意的yii\base\Component实例上。这个component就可以拥有该Behavior所定义的属性和方法

如果将行为和事件联系起来，就更加丰富

Behavior只能和Component绑定。所以使用行为必须继承component

4.8.1使用行为的流程

4.8.1.1从 yii\base\Component 派生自己的类，以便使用行为；
4.8.1.2从 yii\base\Behavior 派生自己的行为类，里面定义行为涉及到的属性、方法；
4.8.1.3将Component和Behavior绑定起来；attachBehavior
4.8.1.4像使用Component自身的属性和方法一样，尽情使用行为中定义的属性和方法。

4.8.2行为的要素
$owner 成员变量，用于指向行为的依附对象

events()用于表示行为为所有要响应的事件
attach()用于将行为与Component绑定起来
detach()用于将行为从Component上解除

4.8，3
$owner
通过他，行为才能依附到Component上
attach()可以给$owner赋值
而在调用attachBehavior将行为与对象进行绑定时候，Component就自动使用 $this作为参数来绑定
但是本质上，行为就是一个php类，里面的方法就是类的成员函数，所以使用行为里面的￥this去访问Component对象是不可以的

正确的使用：
yii\base\Behavior::$owner来访问

4.8.4可以通过行为来补充类在事件触发后的各种v不同反应

实践：重载yii\base\Behavior::events()方法，来表示这个行为将对类的何种事件做出何种反馈


4.8.5静态方法绑定行为

namespace app\modules;

use yii\db\ActiveRecord;
use app\Components\MyBehavior;

class User extends ActiveRecord{
	public function behaviors(){
		return [
			//匿名的行为，仅需要直接给出行为的类名称
			MyBehavior::className(),
			//名称为MyBehavior2的行为，也只是给出行为的类名称
			'myBehavior2'=>MyBehavior::className(),
			//匿名行为，给出了MyBehavior类的配置数组
			[
				'class'=>MyBehavior::className(),
				'prop1'=>'value1',
				'prop3'=>'value3'
			]
			//名为myBehavior4的行为，也是给出了MyBehavior类的配置数组
			
		]
	}
}

也可以通过静态数组来绑定

动态绑定就是使用attachBehaviors([
	'myBehavior1' => new MyBehavior,//命名行为
	MyBehavior::className(),//匿名行为
]);

对于命名的行为，可以使用yii\base\Component::getBehavior()来获得
匿名行为是无法获直接取的

但是可以获取所有绑定好的行为
$Component->getBehaviors()

4.8.6绑定的行为实现原理

只是重载一个 yii\base\Component::behaviors() 就可以这么神奇地使用行为了？
 这只是冰山的一角，实际上关系到绑定的过程，有关的方面有：

yii\base\Component::behaviors()
yii\base\Component::ensureBehaviors()
yii\base\Component::attachBehaviorInternal()
yii\base\Behavior::attach()
4个方法中，Behavior只占其一，更多的代码，是在Component中完成的。

ensureBehavior
这个方法会在Component的诸多地方调用 __get() __set() __isset() __unset() 
__call() canGetProperty() hasMethod() hasEventHandlers() on() off() 
等用到，看到这么多是不是头疼？
一点都不复杂，一句话，只要涉及到类的属性、方法、事件这个函数都会被调用到。

public funciton ensureBehaviors(){
	null便是行为没有绑定
	空数组代表没有绑定人和相位
	if($this->_behaviors ===null){
		$this->_behaviors = [];
		
		foreach($this->behaviors() as $name=>$behavior){
			$this->attachBehaviorInternal($name,$behavior);
		}
	}
}

private function attachBehaviorInternal($name,$behavior){
	//不是Behavior实例，说是只是类名、配置数组
	if(!($behavior instanceof Behavior)){
		$behavior = Yii::createObject($behavior);
	}
	
	//匿名行为
	if(is_int($name)){
		$behavior->attach($this);
		$this->_behaviors[] = $behavior;
	}else{
		//命名行为
		//如果有同名的行为，要先解除，然后将新的绑定
		if(isset($this->_behaviors[$name])){
			$this->_behaviors[$name]->detach();
		}
		$behavior->attach($this);
		$this->_behaviors[$name] = $behavior;
	}
	return $behavior;
}

//这是私有成员，在yii中所有的Internal的方法