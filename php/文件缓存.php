<?php
class FileCache
{
    /**
     * @var string 缓存保存目录
     */
    public $cachePath = '@runtime/fileCache';

    /**
     * @var string 缓存文件后缀
     */
    public $cacheFileSuffix = '.cache';

    /**
     * @var string 缓存字段前缀
     */
    public $keyPrefix = '';

    /**
     * 设置缓存
     * @param $key
     * @param $val
     * @param int $duration 缓存时间, <=0:为1年
     * @return bool
     */
    public function set($key,$val,$duration=-1)
    {
        $cacheFile = $this->getSavePath($key);
        if (!is_dir(dirname($cacheFile))) {
            mkdir(dirname($cacheFile),0755,true);
        }
        if (@file_put_contents($cacheFile, $val, LOCK_EX) !== false) {
            if ($duration <= 0) {
                $duration = 31536000; // 1 year
            }
            return @touch($cacheFile, $duration + time());
        } else {
            $error = error_get_last();
            \Yii::warning("Unable to write cache file '{$cacheFile}': {$error['message']}", __METHOD__);
            return false;
        }
    }

    /**
     * 获取缓存
     * @param $key
     * @return bool|string
     */
    public function get($key)
    {
        $cacheFile = $this->getSavePath($key);
        if (file_exists($cacheFile)) {
            if (filemtime($cacheFile) > time()) {
                $fp = @fopen($cacheFile, 'r');
                if ($fp !== false) {
                    @flock($fp, LOCK_SH);
                    $cacheValue = @stream_get_contents($fp);
                    @flock($fp, LOCK_UN);
                    @fclose($fp);
                    return $cacheValue;
                }
            }
        }
        return false;
    }

    /**
     * 删除缓存
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        $cacheFile = $this->getSavePath($key);
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
        return true;
    }

    /**
     * 获取缓存保存的全路径
     * @param $key
     * @return string
     */
    protected function getSavePath($key)
    {
        $str = md5($this->keyPrefix.$key);
        return \Yii::getAlias($this->cachePath).'/'.substr($str, 0,1).'/'.substr($str, 1,2).'/'.substr($str, 2).$this->cacheFileSuffix;

    }

}
