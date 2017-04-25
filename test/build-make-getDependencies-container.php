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
	//�����б�
	foreach ($parameters as $parameter) {
	    //��ȡ��������
	    $dependency = $parameter->getClass();
	    if (is_null($dependency)) {
	        //�Ǳ���,��Ĭ��ֵ������Ĭ��ֵ
	        $dependencies[] = resolveNonClass($parameter);
	    } else {
	        //��һ����,�ݹ����
	        $dependencies[] = build($dependency->name);
	    }
	}

	return $dependencies;
}


function resolveNonClass($parameter)
{
	// ��Ĭ��ֵ�򷵻�Ĭ��ֵ
	if ($parameter->isDefaultValueAvailable()) {
	    return $parameter->getDefaultValue();
	}
	
	throw new Exception('������Ĭ��ֵ');
}


function build($className)
{
	//��������
	if ($className instanceof Closure) {
	    //ִ�бհ�����
	    return $className($this);
	}
	//��ȡ����Ϣ
	$reflector = new ReflectionClass($className);
	// ������Ƿ��ʵ����, �ų�������abstract�Ͷ���ӿ�interface
	if ( ! $reflector->isInstantiable()) {
	    throw new Exception("$className ����ʵ����.");
	}
	//��ȡ��Ĺ��캯��
	$constructor = $reflector->getConstructor();
	//���޹��캯����ֱ��ʵ����������
	if (is_null($constructor)) {
	    return new $className;
	}
	//ȡ���캯������,ͨ�� ReflectionParameter ���鷵�ز����б�
	$parameters = $constructor->getParameters();
	//�ݹ�������캯���Ĳ���
	$dependencies = getDependencies($parameters);

	//����һ�������ʵ���������Ĳ��������ݵ���Ĺ��캯����
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
    //��ȡ��������
    $dependency = $parameter->getClass();
    var_dump($dependency);
    /**
    object(ReflectionClass)#4 (1) {
	  ["name"]=>
	  string(1) "a"
	}

    */
    
    if (is_null($dependency)) {
        //�Ǳ���,��Ĭ��ֵ������Ĭ��ֵ
        $dependencies[] = resolveNonClass($parameter);
    } else {
        //��һ����,�ݹ����
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
