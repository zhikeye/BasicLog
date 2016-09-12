# c++ explicit 关键字

c++提供了关键字explicit，可以阻止不应该允许的经过转换构造函数进行的隐式转换的发生。声明为explicit的构造函数不能在隐式转换中使用。

C++中， 一个参数的构造函数(或者除了第一个参数外其余参数都有默认值的多参构造函数)， 承担了两个角色。一是个构造器，二是个默认且隐含的类型转换操作符。

所以，有时候在我们写下如 AAA = XXX， 这样的代码， 且恰好XXX的类型正好是AAA单参数构造器的参数类型， 这时候编译器就自动调用这个构造器， 创建一个AAA的对象。

```
class Test1
{
public:
	Test1(int n) { num = n; } //普通构造函数
private:
	int num;
};

class Test2
{
public:
	explicit Test2(int n) { num = n; } //explicit(显式)构造函数
private:
	int num;
};

int main()
{
	Test1 t1 = 12; //隐式调用其构造函数, 成功
	Test2 t2 = 12; //编译错误,不能隐式调用其构造函数
	Test2 t3(12); //显示调用成功
	return 0;
}
```

## 隐式转换的场景

等于号与构造函数
