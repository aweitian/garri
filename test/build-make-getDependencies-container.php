<?php
class a
{
	public function __construct(b $b)
	{
		$b->test();
	}	
	public function test()
	{
		echo 'a->test()'."\n";	
	}
	
}
class b
{
	private $c;
	public function __construct($c=2)
	{
		$this->c = $c;
	}
	public function test()
	{
		echo 'b->test():'.$this->c."\n";	
	}	
	
}
class c
{
	public function __construct(a $a)
	{
		$a->test();
	}	
}



function getDependencies($parameters)
{
	$dependencies = [];
	//参数列表
	foreach ($parameters as $parameter) {
	    //获取参数类型
	    $dependency = $parameter->getClass();
	    if (is_null($dependency)) {
	        //是变量,有默认值则设置默认值
	        $dependencies[] = resolveNonClass($parameter);
	    } else {
	        //是一个类,递归解析
	        $dependencies[] = build($dependency->name);
	    }
	}

	return $dependencies;
}


function resolveNonClass($parameter)
{
	// 有默认值则返回默认值
	if ($parameter->isDefaultValueAvailable()) {
	    return $parameter->getDefaultValue();
	}
	
	throw new Exception('参数无默认值');
}


function build($className)
{
	//匿名函数
	if ($className instanceof Closure) {
	    //执行闭包函数
	    return $className($this);
	}
	//获取类信息
	$reflector = new ReflectionClass($className);
	// 检查类是否可实例化, 排除抽象类abstract和对象接口interface
	if ( ! $reflector->isInstantiable()) {
	    throw new Exception("$className 不能实例化.");
	}
	//获取类的构造函数
	$constructor = $reflector->getConstructor();
	//若无构造函数，直接实例化并返回
	if (is_null($constructor)) {
	    return new $className;
	}
	//取构造函数参数,通过 ReflectionParameter 数组返回参数列表
	$parameters = $constructor->getParameters();
	//递归解析构造函数的参数
	$dependencies = getDependencies($parameters);

	//创建一个类的新实例，给出的参数将传递到类的构造函数。
	return $reflector->newInstanceArgs($dependencies);
}

$reflector = new ReflectionClass('c');
$constructor = $reflector->getConstructor();
$parameters = $constructor->getParameters();
//var_dump($parameters);
/*
array(1) {
  [0]=>
  &object(ReflectionParameter)#3 (1) {
    ["name"]=>
    string(1) "a"
  }
}
*/
$dependencies = [];
foreach ($parameters as $parameter) 
{
    //获取参数类型
    $dependency = $parameter->getClass();
    var_dump($dependency);
    /**
    object(ReflectionClass)#4 (1) {
	  ["name"]=>
	  string(1) "a"
	}

    */
    
    if (is_null($dependency)) {
        //是变量,有默认值则设置默认值
        $dependencies[] = resolveNonClass($parameter);
    } else {
        //是一个类,递归解析
        $dependencies[] = build($dependency->name);
    }
}
var_dump($dependencies);
/*
array(1) {
  [0]=>
  object(a)#8 (0) {
  }
}

*/
var_dump(build('c'));
/*
object(c)#5 (0) {
}
*/
