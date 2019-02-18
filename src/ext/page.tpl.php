<div class="page <?php if($page->count == 0){echo 'page-no-data';}?>">
    <span id="total">共<?php echo $page->count;?>条,每页显示<?php echo $page->limit;?>条</span>
    <a class='page-start' href='<?php echo $page->uri;?>page=1'>首页</a>
    <a class='page-last' href='<?php echo $page->uri;?>page=<?php echo $page->last;?>'>上一页</a>
<?php
    for( $i=$page->start;$i<=$page->end && $i!=0;$i++ ){
        if( $i == $page->page ){
            echo "    <a class='page-active' href='javascript:void(0);'>{$i}</a>" . PHP_EOL;
        }else{
            echo "    <a  href='{$page->uri}{$page->pageParam}={$i}'>{$i}</a>" . PHP_EOL;
        }
    }
?>
    <a class='page-next' href='<?php echo $page->uri;?>page=<?php echo $page->next;?>'>下一页</a>
    <a class='page-end' href='<?php echo $page->uri;?>page=<?php echo $page->pageNum; ?>'>尾页</a>
</div>
