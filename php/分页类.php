<?php
class Pages
{
    /**
     * @var int 总数量
     */
    public $count = 0;
    /**
     * @var int 每页的数量
     */
    public $pageSize = 20;
    /**
     * @var int 当前页数
     */
    public $curPage = 1;
    /**
     * @var string  链接
     */
    public $url = '';
    /**
     * @var string 第一页的链接
     */
    public $firstUrl = '';
    /**
     * @var string 分页的Html模板
     */
    public $html = '<a href="{url}">{page}</a>';
    /**
     * @var string 当前分页的html模板
     */
    public $curHtml = '<a class="cur" href="#">{page}</a>';
    /**
     * 显示分页html
     * @return string
     */
    public function show(){
        if ($this->count == 0) {
            return '';
        }
        $maxPage = ceil($this->count / $this->pageSize);
        $maxNum = 5;
        $preHtml = [];
        $nextHtml = [];
        $firstHtml = str_replace(['{url}','{page}'],[$this->firstUrl,'首页'],$this->html);
        $endUrl = str_replace('{page}',$maxPage,$this->url);
        $endHtml = str_replace(['{url}','{page}'],[$endUrl,'末页'],$this->html);
        $curHtml = str_replace('{page}',$this->curPage,$this->curHtml);
        for ($i = 1; $i <= $maxNum; $i++) {
            $pre_page = $this->curPage - $i;
            if ($pre_page > 0) {
                if ($pre_page == 1) {
                    $preHtml[] = str_replace(['{url}','{page}'],[$this->firstUrl,1],$this->html);
                } else {
                    $url = str_replace('{page}',$pre_page,$this->url);
                    $preHtml[] = str_replace(['{url}','{page}'],[$url,$pre_page],$this->html);
                }
            }
            $next_page = $this->curPage + $i;
            if ($next_page <= $maxPage) {
                $url = str_replace('{page}',$next_page,$this->url);
                $nextHtml[] = str_replace(['{url}','{page}'],[$url,$next_page],$this->html);
            }
        }
        $preHtml = array_reverse($preHtml);
        $preHtml = implode('',$preHtml);
        $nextHtml = implode('',$nextHtml);
        return $firstHtml.$preHtml.$curHtml.$nextHtml.$endHtml;
    }
}
