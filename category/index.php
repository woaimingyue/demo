<?php

//获取所有分类信息
$sql = "select * from yx_goods_cate";
$categories = db($sql, true)->fetchAll(PDO::FETCH_ASSOC);

$pushArr = Array(
        'cate_id' => '1008033',
        'cate_name' => '食品保鲜',
        'url' => 'http://you.163.com/item/list?categoryId=1005001&subCategoryId=1008011',
        'parent_cate_id' => '1008011',
        'update_time' => '0',
);

array_push($categories, $pushArr);


$tree = array();
//第一步，将分类id作为数组key,并创建children单元
foreach ($categories as $category) {
    $tree[$category['cate_id']] = $category;
    $tree[$category['cate_id']]['children'] = array();
}

//第二部，利用引用，将每个分类添加到父类children数组中，这样一次遍历即可形成树形结构。
foreach ($tree as $k => $item) {
    if ($item['parent_cate_id'] != 0) {
        $tree[$item['parent_cate_id']]['children'][] = &$tree[$k];
    }
}
print_r($tree);


//无限极分类
// function catcate($cates=[], $parent_id=0)
// {

//     static $searialCate = [];
//     foreach ($cates as $key => $value) {
//         if($value['parent_cate_id'] == $parent_id)
//         {
//             //echo 1;
//             //$searialCate[$value['cate_id']][] = catcate($cates, $value['cate_id']);
//             $searialCate[$value['cate_id']][] = $value;
//             //catcate($searialCate[$value['cate_id']], $value['cate_id']);
//         }
//     }
//     return $searialCate;
// }

//数据库操作
function db($sql, $is_select = false)
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
    $res = $PDOStatement->execute();
    if ($PDOStatement->errorCode() > 0) {
        $res['errorcode'] = $PDOStatement->errorCode();
        $res['errorinfo'] = $PDOStatement->errorInfo();
    }
    //如果是查询则返回pdo对象 否则返回sql执行结果
    if ($is_select && !isset($res['errorcode'])) {
        return $PDOStatement;
    }
    return $res;
}