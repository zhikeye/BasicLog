# php Json中文转码

在使用json_encode的时候，遇到中文会转码，只需要加上JSON_UNESCAPED_UNICODE即可

```
json_encode($arr,JSON_UNESCAPED_UNICODE);
```
