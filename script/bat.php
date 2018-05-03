<?php
/**
 * @author Diven 702814242@qq.com
 */

//数据库操作
function db($sql)
{
    static $linkNum;
        
    if (is_null($linkNum)) {        
        //链接数据库
        $linkNum = new PDO(
                'mysql:dbname=demo;host=localhost', 
                'root', 
                '', 
                [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'']
        );
    }

    // 预处理
    $PDOStatement = $linkNum->prepare($sql);
    // 执行查询
    $PDOStatement->execute();
    
    return $PDOStatement;
}

//获取微信请求token
function getToken()
{
    $time = time();
    $sql = "select access_token from weixin_token where is_deleted = 0 and expire_time > {$time} order by id desc limit 1";
    $token = db($sql)->fetch(PDO::FETCH_COLUMN);
    
    if (empty($token)) {
        $sql = "select * from weixin_system_queue where is_done = 1 order by id asc limit 1";
        $result = db($sql)->fetch(PDO::FETCH_ASSOC);
        if(empty($result))
        {
            exit("empty queue");
        }
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$result['weixin_appid']}&secret={$result['weixin_secret']}";
        $token = file_get_contents($url);
        $expireTime = $time + 7000;
        $sql = "insert into weixin_token (access_token,expire_time) values ('{$token}', '{$expireTime}')";
        db($sql);
    }
    
    $token = json_decode($token, true);
    $token = isset($token['access_token']) ? $token['access_token'] : '';
    
    return $token;
}
//获取微信用户详情
function getUrl($openid)
{
    $token = getToken();
    return "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$token}&openid={$openid}&lang=zh_CN";
}

//获取openid列表
function getOpenidList( & $next = '')
{
    $token = getToken();
    $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token={$token}&next_openid={$next}";
    
    $data = file_get_contents($url);
    
    $result = json_decode($data, true);
    
    //如果没有返回正确的数据，说明要删除token缓存
    if (!isset($result['next_openid'])) {
        //删除access_token
        $sql = "update weixin_token set is_deleted = 1";
        db($sql);
        
        //递归调用
        return getOpenidList($next);
    }
    
    $next = $result['next_openid'];
    
    return $result;
}

//获取请求参数
function getRequestParam($url, $name = 'openid')
{
    $res = parse_url($url);
    $res = explode('&', $res['query']);
    $data = [];
    foreach ($res as $val) {
        list($key, $value) = explode('=', $val);
        $data[$key] = $value;
    }
    
    return isset($data[$name]) ? $data[$name] : null;
}


function post($url, $data)
{
    $ch = curl_init();

    $queryString = is_array($data) ? json_encode($data) : $data;
     
    //避免data数据过长问题
    //curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-type: application/json"]);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
    //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

    //设置header
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    //要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    //设置远程服务响应超时关闭时间
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    //设置请求链接超时关闭
    //用来告诉PHP脚本在成功连接服务器前等待多久（连接成功之后就会开始缓冲输出），
    //这个参数是为了应对目标服务器的过载，下线，或者崩溃等可能状况
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
     
    //是否检验证书
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
     
    //发送请求
    $res = curl_exec($ch);

    // 返回结果
    if ($res) {
         
        curl_close($ch);
         
    }else {

        $errno = curl_errno($ch);
        $error = curl_error($ch);
         
        curl_close($ch);
    }

    return $res;
}

//获取标签列表
function getTagList()
{
    $token = getToken();
    $url = "https://api.weixin.qq.com/cgi-bin/tags/get?access_token={$token}";

    $res = file_get_contents($url);
    $res = json_decode($res, true);

    return isset($res['tags']) ? $res['tags'] : [];
}

//获取对应标签下的用户列表
function getTagUserList()
{
    $tagList = getTagList();

    $token = getToken();
    $url = "https://api.weixin.qq.com/cgi-bin/tags/get";
    $result = [];

    foreach ($tagList as $info) {
         
        $data = [
        "access_token" => $token,
        "tagid" => $info['id'],
        "next_openid" => ""//第一个拉取的OPENID，不填默认从头开始拉取
        ];

        $result[] = json_decode(post($url, $data), true);
    }

    return $result;
}

//开始时间
$startTime = microtime(true);

// $argv = [0,'add'];
//添加openid计划任务
if (isset($argv[1]) && $argv[1] === 'add') 
{
    //exit('Do not perform this operation');
    $next = '';
    $step = 0;
    $stepTotal = 0;
    
    do {
        
        $openidList = getOpenidList($next);
        
        if (empty($openidList['data']['openid'])) break;
        
        $total = $openidList['total'];
        $count = $openidList['count'];
        $data = $openidList['data']['openid'];
        
        //每次批量插入数据的条目数
        $rows = 1000;
        
        //分页插入数据库
        for ($i = 0; $i < $count/$rows; $i++) {
            $openids = array_slice($data, $i*$rows, $rows);
            $sql = [];
            foreach ($openids as $openid) {
                $sql[] = "insert into weixin_openid (openid) values ('{$openid}')";
            }
            $sql = implode(";", $sql);
            db($sql);
        }
        
        $step++;
        
        echo "[{$step}]";
        
        $stepTotal = $step*$count;
        
    }while ($stepTotal < $total && $step < 730);
    
    $endTime = microtime(true);
    $runTime = bcsub($endTime, $startTime, 6);
    
    exit("runing [{$runTime}] add openid success");
}

//重置一些没有成功拉取的数据
if (isset($argv[1]) && $argv[1] === 'update')
{
    
    $step = 0;
    $totalTime = 0;
    $seconds = 300;
    
    do {

        $step++;
        
        $sql = "UPDATE `weixin_openid` SET is_done=0 WHERE is_done=1 AND update_time<UNIX_TIMESTAMP()-600 AND update_time > 0";
        $num = db($sql)->rowCount();
        
        echo "[{$step},{$num}]";
        
        //sleep($seconds);
        
        $totalTime += $seconds;

    }while ($step <= 12);
    
    exit("runing {$totalTime} update openid is_done = 0 success");
}


//最小id
$minId = 1;
//最大id
$maxId = 7300000;
//每次查询条目数也是并发数
$rows = 100;
//循环步数
$step = 0;

$sql = "select count(*) as num from weixin_openid where is_done = 0 and id >={$minId} and id <={$maxId}";
$count = db($sql)->fetch(PDO::FETCH_COLUMN);

while (1) {
    
    $step++;
    
    $sql = "select * from weixin_openid where is_done = 0 and id >={$minId} and id <={$maxId} order by id desc limit {$rows}";
    $result = db($sql)->fetchAll(PDO::FETCH_ASSOC);
    
    //死循环直到查询数据为空
    if (empty($result)) {
        
        $endTime = microtime(true);
        $runTime = bcsub($endTime, $startTime, 6);
        
        exit("runing [{$runTime}] insert weixin users success");
    }
    
    // 创建批处理cURL句柄
    $mh = curl_multi_init();

    $ch = [];
    $ids = [];
    $openidToId = [];
    foreach($result as $key => $info) {
        
        //对应的数据id
        $ids[] = $info['id'];
        //对应的openid和id关联数据
        $openidToId[$info['openid']] = $info['id'];
        //微信url请求
        $url = getUrl($info['openid']);
        
        $ch[$key] = curl_init();
       
        curl_setopt($ch[$key], CURLOPT_URL, $url);
        curl_setopt($ch[$key], CURLOPT_HEADER, 0);
        curl_setopt($ch[$key], CURLOPT_TIMEOUT, 30);
        curl_setopt($ch[$key], CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch[$key], CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch[$key], CURLOPT_SSL_VERIFYHOST, false);
       
        curl_multi_add_handle($mh, $ch[$key]);          
    }
    
    $time = time();
    //更新状态
    $sql = "update weixin_openid set is_done=1,update_time={$time} where id in (".implode(",", $ids).")";
    db($sql);       
    
    $active = null;
    
    do {
        $mrc = curl_multi_exec($mh, $active);
    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    
    while ($active && $mrc == CURLM_OK)
    {
        // add this line
        while (curl_multi_exec($mh, $active) === CURLM_CALL_MULTI_PERFORM);
    
        if (curl_multi_select($mh) != -1)
        {
            do {
                $mrc = curl_multi_exec($mh, $active);
                if ($mrc == CURLM_OK)
                {
                    while($read = curl_multi_info_read($mh))
                    {
                        $request = curl_getinfo($read['handle']);
                        //获取到请求中的openid
                        $openid = getRequestParam($request['url']);
                        //获取对应openid的主键id
                        $id = $openidToId[$openid];
                        
                        //如果请求成功
                        if (curl_error($read['handle']) == "") {
                            
                            $weixin = (string) curl_multi_getcontent($read['handle']);
                            $weixin = json_decode($weixin, true);

                            //如果出现错误编码，通常是access_token过期
                            if (isset($weixin['errcode'])) {
                                
                                echo '-';
                                
                                //删除access_token
                               $sql = "update weixin_token set is_deleted = 1";
                               db($sql);
                               
                               //更新状态为未处理
                               $sql = "update weixin_openid set is_done=0 where id={$id} and is_done=1";
                               db($sql);
                               
                            }else if (!empty($weixin['subscribe_time'])) {
                                $sql = "select count(*) as num from weixin_users where openid='{$weixin['openid']}'";
                                $infoCount = db($sql)->fetch(PDO::FETCH_COLUMN);

                                $weixin['tagid_list'] = implode(',', $weixin['tagid_list']);
                                
                                $filed = array('openid','nickname', 'sex', 'language', 'city', 'province', 'country',
                                        'headimgurl','remark', 'subscribe', 'subscribe_time', 'groupid', 'tagid_list');
                                foreach ($weixin as $k => $v) {
                                    if (in_array($k, $filed)) {
                                        if (empty($weixin[$k])) $weixin[$k] = '';
                                        $weixin[$k] = addslashes($weixin[$k]);
                                    }
                                }

                                echo '+';
                                
                                try {
                                    
                                    if(intval($infoCount) > 0)
                                    {
                                        //更新数据
                                        $sql = "update weixin_users set openid='{$weixin['openid']}',nickname='{$weixin['nickname']}',sex='{$weixin['sex']}',language='{$weixin['language']}',city='{$weixin['city']}',province='{$weixin['province']}',country='{$weixin['country']}',headimgurl='{$weixin['headimgurl']}',remark='{$weixin['remark']}',subscribe='{$weixin['subscribe']}',subscribe_time='{$weixin['subscribe_time']}',groupid='{$weixin['groupid']}',tagid_list='{$weixin['tagid_list']}' where openid='{$weixin['openid']}'";
                                        db($sql);
                                    }else{
                                        //插入数据库
                                        $sql = "insert into weixin_users (`openid`,`nickname`,`sex`,`language`,`city`,`province`,`country`,`headimgurl`,`remark`,`subscribe`,`subscribe_time`,`groupid`,`tagid_list`) values ('{$weixin['openid']}','{$weixin['nickname']}','{$weixin['sex']}','{$weixin['language']}','{$weixin['city']}','{$weixin['province']}','{$weixin['country']}','{$weixin['headimgurl']}','{$weixin['remark']}','{$weixin['subscribe']}','{$weixin['subscribe_time']}','{$weixin['groupid']}','{$weixin['tagid_list']}')";
                                        db($sql);
                                    }
                                    
                                    //更新对应的处理状态为已完成
                                    $sql = "update weixin_openid set is_done=2 where id={$id} and is_done=1";
                                    db($sql);
                                    
                                }catch (Exception $e) {
                                    echo 'E';
                                } 
                                       
                            }else {
                                echo '1';
                            }
                                                            
                        }else {

                            echo '0';
                        }
                    }
                }
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
    }
    
    //close the handles
    foreach($ch as $val) {
       curl_multi_remove_handle($mh, $val);
    }
    curl_multi_close($mh);
    
    echo "[{$step}]";
}

$endTime = microtime(true);
$runTime = bcsub($endTime, $startTime, 6);

exit("runing [{$runTime}] insert weixin users success");
