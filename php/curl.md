# php CURL模拟请求


## 1. post提交，非常规格式

```
$ch = curl_init();
$options =  array(
    CURLOPT_URL => 'url地址',
    CURLOPT_POST => true,//是否为POST提交
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => implode("\n", $url),
    CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
);
curl_setopt_array($ch, $options);
$result = curl_exec($ch);
curl_close($ch);
```


## 2. post提交，常规格式

```
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'url地址');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);//超时时间
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:80');//指定请求域名的ip与端口
curl_setopt($ch, CURLOPT_HEADER, false);
curl_exec($ch);
curl_close($ch);
```

## 3. 设置header
```
$headers = array(
    "POST ".$page." HTTP/1.0",
    "Content-type: text/xml;charset=\"utf-8\"",
    "Accept: text/xml",
    "Cache-Control: no-cache",
    "Pragma: no-cache",
    "SOAPAction: \"run\"",
    "Content-length: ".strlen($xml_data),
    "Authorization: Basic " . base64_encode($credentials)
);

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
```

## 4.设置浏览器UA

```
 curl_setopt($ch, CURLOPT_USERAGENT, 'UA信息');
```
