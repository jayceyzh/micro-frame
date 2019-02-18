<?php
namespace mpf\ext;
/**
 * Page
 * 分页类
 * Page::show() 输出分页内容
 * Page::fetch() 获取分页内容
 * Page::setCount($count) 设置记录总数
 * Page::getParams() 获取sql查询所需要的起始位置和偏移
 * Page::debug() 调试新增模版，打印传递到模板的数据信息
 */
class Page{
    private $limit;
    private $tpl;
    private $count;
    private $page;
    private $pageParam;
    private $beforeHalf;
    private $afterHalf;
    /**
     * Page::__construct()
     * 
     * @param integer $limit 每页的记录数量
     * @param integer $beforeHalf 当前分页前面显示几个分页数码
     * @param integer $afterHalf 当前分页后面显示几个分页数码
     * @param string $tpl 分页的模板
     * @param string $pageParam 分页在$_GET中的key
     * @return
     */
    public function __construct($limit=10,$beforeHalf = 3,$afterHalf=3,$tpl = '',$pageParam = 'page'){
        if( $limit == 0 ){
            throw new \Exception('limit不能为0！');
        }
        $this->limit = $limit;
        if( empty($tpl) ){
            $this->tpl = __DIR__ . '/page.tpl.php';
        }else{
            $this->tpl = $tpl;
        }
        if( isset($_GET[$pageParam]) && is_numeric($_GET[$pageParam]) ){
            $page = (int)$_GET[$pageParam];
            if( $page < 0 ){
                $this->page = 1;
            }else{
                $this->page = $page;
            }
        }else{
            $this->page = 1;
        }
        $this->pageParam = $pageParam;
        $this->beforeHalf = $beforeHalf;
        $this->afterHalf = $afterHalf;
    }
    /**
     * Page::show()
     * 显示分页
     * @return
     */
    public function show(){
        echo $this->fetch();
    }
    /**
     * Page::fetch()
     * 获取分页的HTML内容
     * @return
     */
    public function fetch(){
        $page = $this->getTplData();
        ob_start();
        include $this->tpl;
        $pageHtml = ob_get_contents();
        ob_clean();
        return $pageHtml;
    }
    /**
     * Page::setCount($count)
     * 设置记录的总数
     * @param mixed $count
     * @return
     */
    public function setCount($count){
        $this->count = $count;
    }
    /**
     * Page::getParams()
     * 获取sql查询所需要的起始位置和偏移
     * @return
     */
    public function getParams(){
        return [($this->page-1) * $this->limit,$this->limit];
    }
    /**
     * Page::debug()
     * 调试新增模版，打印传递到模板的数据信息
     * @return
     */
    public function debug(){
        echo "使用的分页模版：" . $this->tpl . ';<br/>' . PHP_EOL;
        echo "<pre>" . PHP_EOL;
        var_dump($this->getTplData());
        echo "</pre>" . PHP_EOL;
    }
    /**
     * Page::getUri()
     * 获取uri
     * @return
     */
    private function getUri()
    {
        $request_uri = $_SERVER["REQUEST_URI"];
        $url = strstr($request_uri, '?') ? $request_uri : $request_uri . '?';

        $arr = parse_url($url);

        if (isset($arr["query"])) {
            parse_str($arr["query"], $arrs);
            unset($arrs["page"]);
            $url = $arr["path"] . '?' . http_build_query($arrs);
        }

        if (strstr($url, '?')) {
            if (substr($url, -1) != '?') $url = $url . '&';
        } else {
            $url = $url . '?';
        }

        return $url;
    }
    /**
     * Page::getPages()
     * 分页核心算法,计算分页相关数据信息
     * @return [$start,$end,$last,$next,$pageNum]
     */
    private function getPages(){
        $pageNum = ceil( $this->count / $this->limit );
        $pageNum = (int)round($pageNum);
        if( $pageNum == 0 ){
            return [0,0,0,0,0];
        }
        if( $this->page <= $this->beforeHalf ){
            $start = 1;
            $end = $start + $this->beforeHalf + $this->afterHalf;
            $end = ($end > $pageNum) ? $pageNum : $end;
        }elseif( $this->page+$this->afterHalf >= $pageNum ){
            $end = $pageNum;
            $start = $end - $this->beforeHalf - $this->afterHalf;
            $start = ( $start > 0 ) ? $start : 1;
        }else{
            $start = $this->page - $this->beforeHalf;
            $end = $this->page + $this->afterHalf;
        }
        if( $this->page < $pageNum ){
            $next = $this->page+1;
        }else{
            $next = $pageNum;
        }
        if( $this->page == 1 ){
            $last = 1;
        }else{
            $last = $this->page-1;
        }
        return [$start,$end,$last,$next,$pageNum];
    }
    /**
     * Page::getTplData()
     * 获取分页模板的参数
     * @return
     */
    private function getTplData(){
        if( $this->count == null ){
            throw new \Exception('还没有设置记录的总数！');
        }
        list($start,$end,$last,$next,$pageNum) = $this->getPages();
        $page = [
            'uri'=>$this->getUri(),
            'page'=>$this->page,
            'pageParam'=>$this->pageParam,
            'count'=>$this->count,
            'limit'=>$this->limit,
            'start'=>$start,
            'end'=>$end,
            'last'=>$last,
            'next'=>$next,
            'pageNum'=>$pageNum,
            '以下是分页的参数说明'=>[
                'uri'=>'uri地址',
                'page'=>'当前页',
                'pageParam'=>'分页中的get参数',
                'count'=>'记录总数',
                'limit'=>'每页的记录数',
                'start'=>'开始的分页',
                'end'=>'结束的分页',
                'last'=>'上一页',
                'next'=>'下一页',
                'pageNum'=>'总页数'
            ]
        ];
        $page = (object)$page;
        return $page;
    }
}
