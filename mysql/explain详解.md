# MySql explain 详解

使用explain，我们知道mysql如何使用索引来处理select语句以及连接表，可以帮助我们选择更好的索引和写出更优化的查询语句。如下示例



## explain详解

### id
它是sql语句执行的顺序。如上示例可以看出先执行外层select，再执行内层select。

### select_type
它提供了各种“列属性”引用的类型，最常见的值包括如下:
1. simple:表示简单的select，没有union和子查询。
2. primary:最外面的select，在有子查询的语句中，最外面的select查询就是primary，如上例子就是
3. union:SQL语句中带有union   
4. derived:当查询的表不是一个物理表时，那么它就叫做derived。如上示例tt表不是一个物理表

### table
很明显，它是查询所用的表

### type
表示mysql在表中找到所需要记录的方式，又称为"连接类型"或"访问类型"，从最好到最差依次如下:
1. system：表示只有一行记录(等于查询系统表)
2. const：表示表中最多只有一行匹配的记录
3. range：只检索给定范围的行，key列显示使用了哪个索引。当使用=、>、<、between操作符时，可以使用range
4. index：全表扫描，只是扫描表的时候按照索引次序进行而不是行，主要优点是避免了排序，但是依然消耗很大的开销
5. all：最坏的情况，从头到尾全表扫描

### possible_keys
表示mysql在搜索表记录时可能会使用哪个索引。注意，该字段完全独立于explain显示的表顺序，因此，possible_keys里面所包含的索引可能在实际的使用中并没有用到，即这个字段的值是null时，就表示没有索引被用到。

### key
该字段表示mysql查询实际使用的索引，当没有任何索引被用到的时候，该字段值为null。

### key_len
该字段表示mysql使用索引的长度，当key字段值为null时，索引的长度就是null。例如上述例子中，主键的长度是int类型，长度为10，这就至少需要4位来表示，所以索引的长度为4。

### ref
该列表示 使用哪个列或常数与key一起从表中选择行

### rows
表示mysql执行查询的行数，该数值越大，越不好，表明没有用好索引

### extra
该字段显示了mysql查询过程中的附加信息，常见信息如下:
1. distinct：mysql找到当前记录匹配结果的第一条记录之后，就不再搜索其他记录了
2. not exists：mysql在查询时做一个left join优化时，在当前表中找到与前一条记录符合left join条件之后，就不再搜索其他的记录了。
