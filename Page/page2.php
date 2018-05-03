<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/15
 * Time: 10:12
 */

class page2 {
    private $total_pages;
    private $list_row;
    private $current;
    private $url;
    private $showpage=7;
    private $showtop=2;
    private $interval = array();

    function __construct($total, $list_row=5, $param='') {
        header('Content-type:text/html; charset=utf-8');
        $this->total_pages = ceil($total/$list_row);
        $this->list_row = $list_row;
        $this->current = isset($_GET['page']) ? $_GET['page'] : 1;
        $this->url = $this->getUrl($param);
        $this->showpage = $this->showpage > $this->total_pages ? $this->total_pages : $this->showpage;

        $fgd = $this->showpage-$this->showtop;
        if ($fgd%2 == 0) {
            $left = floor($fgd/2);
            $right = $left - 1;
        }else {
            $left = $right = floor($fgd/2);
        }
        $this->interval = array($left, $right);
        print_r($this->interval);
        echo '<br>';
    }

    function show() {
        $html = '';
        $html .= $this->getPrev();
        $html .= $this->getPageList();
        $html .= $this->getNext();
        echo $html;
    }

    function  getTop() {
        $list = '';
        for ($i=1; $i<=$this->showtop; $i++) {
            $url = $this->url.'page='.$i;
            $list .= "<a href='$url'>{$i}</a>";
        }
        return $list;
    }

    function getPrev() {
        if ($this->current == 1) {
            return "<a href='javascript:;'>上一页</a>";
        }else {
            $url = $this->url.'page='.($this->current-1);
            return "<a href='$url'>上一页</a>";
        }
    }

    function getPageList() {
        $list = '';

        if ($this->current <= $this->showpage && $this->current <= $this->showpage-$this->showtop) {
            $this->log('a');
            for ($i=1; $i<=$this->showpage; $i++) {
                $url = $this->url.'page='.$i;
                $list .= "<a href='$url'>{$i}</a>";
            }
            if($this->total_pages > $this->showpage) {
                $list .= '...';
            }
        }else if ($this->current > $this->showpage-$this->showtop && $this->current < ($this->total_pages-$this->interval[1])) {
            $this->log('b');
            $list = '';
            $list .= $this->getTop();
            $list .= '...';
            for ($i=$this->current-$this->interval[0]; $i<=$this->current+$this->interval[1]; $i++) {
                $url = $this->url.'page='.$i;
                $list .= "<a href='$url'>{$i}</a>";
            }
            $list .= '...';
        }else if ($this->current >= $this->total_pages-$this->interval[1]) {
            $this->log('c');
            $list = '';
            $list .= $this->getTop();
            if($this->total_pages > $this->showpage) {
                $list .= '...';
            }
            for ($i=$this->total_pages-($this->showpage-$this->showtop)+1; $i<=$this->total_pages; $i++) {
                $url = $this->url.'page='.$i;
                $list .= "<a href='$url'>{$i}</a>";
            }
        }

        return $list;
    }

    function getNext() {
        if ($this->current == $this->total_pages) {
            return "<a href='javascript:;'>下一页</a>";
        }else {
            $url = $this->url.'page='.($this->current+1);
            return "<a href='$url'>下一页</a>";
        }
    }

    function getUrl($param)
    {
        $url = $_SERVER["REQUEST_URI"] . (strpos($_SERVER["REQUEST_URI"], '?') ? '' : "?") . $param;
        $parse = parse_url($url);
        if (isset($parse["query"])) {
            parse_str($parse['query'], $params);
            unset($params["page"]);
            $url = $parse['path'] . '?' . http_build_query($params);
        }
        return $url;
    }

    function log($tag) {
        echo "【{$tag}】";
    }
}


$objPage = new Page2(10, 2);
$objPage->show();