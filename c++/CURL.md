# curl

## window

下载curl文件,

进入curl\lib目录

```
mingw32-make Makefile.m32
```

产生需要的文件

libcurl.a

libcurl.dll

## 使用

**mian**:
```
CURL *curl;    
CURLcode res;    
const char pURL[] = "http://www.baidu.com/";  
const char pParameter[] = "password=123";  
  
curl = curl_easy_init();    
string body;
if(curl) {
    curl_easy_setopt(curl, CURLOPT_URL, pURL);//url地址  
    curl_easy_setopt(curl,CURLOPT_POST,1); //设置问非0表示本次操作为post  
    curl_easy_setopt(curl,CURLOPT_POSTFIELDS,pParameter); //post参数  
    curl_easy_setopt(curl,CURLOPT_WRITEFUNCTION,write_data); //对返回的数据进行操作的函数地址  
    curl_easy_setopt(curl,CURLOPT_WRITEDATA,&body); //这是write_data的第四个参数值  
    curl_easy_setopt(curl,CURLOPT_VERBOSE,1); //打印调试信息  
    curl_easy_setopt(curl,CURLOPT_HEADER,1); //将响应头信息和相应体一起传给write_data  
    curl_easy_setopt(curl,CURLOPT_FOLLOWLOCATION,true); //表示follow服务器返回的重定向信息。  
    curl_easy_setopt(curl, CURLOPT_FORBID_REUSE, 1); //当进程处理完毕后强制关闭会话，不再缓存供重用  
    curl_easy_setopt(curl, CURLOPT_TIMEOUT, 10);  //设置访问的超时  
    curl_easy_setopt(curl,CURLOPT_COOKIEFILE,"curlposttest.txt");//包含cookie信息的文件  
    curl_easy_setopt(curl, CURLOPT_COOKIEJAR, "curlposttest.txt"); //连接结束后保存cookie信息的文件  
    curl_easy_setopt(curl, CURLOPT_USERAGENT, "libcurl-agent/1.0"); //HTTP头中User-Agent的值 
    res = curl_easy_perform(curl);  
    curl_easy_cleanup(curl);    
}
```

**write_data**:

```
long write_data(void *data, long size, long nmemb, string &content)
{
    long sizes = size * nmemb;
    string str = (string)(char *)data;
    content += str;
    return sizes;
}
```


