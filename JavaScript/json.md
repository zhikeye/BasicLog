# JavaScript 字符串格式的json转换成JSON对象

## eval
```
json = eval('('+str+')');
```

## 利用 new Function 形式

```
function strToJson(str){
var json = (new Function("return " + str))();
return json;
}
```
