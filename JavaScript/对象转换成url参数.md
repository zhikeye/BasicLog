# javascript 对象转换成url请求参数

```
var urlEncodeForObj = function (param) {
	var _p = function(param, key, encode){
		  if(param==null) return '';
		  var paramStr = new Array();
		  var t = typeof (param);
		  if (t == 'string' || t == 'number' || t == 'boolean') {
		    paramStr.push(key + '=' + ((encode==null||encode) ? encodeURIComponent(param) : param));
		  } else {
		    for (var i in param) {
		      var k = key == null ? i : key + (param instanceof Array ? '[' + i + ']' : '.' + i);
		      paramStr = paramStr.concat(_p(param[i], k, encode));
		    }
		  }
		  return paramStr;
	};
	var s = _p(param);
	return s.join('&');
}
```