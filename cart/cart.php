<?php
/*****************************************************************************/
/*                                                                           */
/* file type:      包含文件，建议后缀为.inc                                  */
/*                                                                           */
/* file name:      cart.inc                                                  */
/*                                                                           */
/* Description:    定义一个购车类                                            */
/*                                                                           */
/* Func list :     class cart                                                */
/*                                                                           */
/* author :        bigeagle                                                  */
/*                                                                           */
/*                                                                           */
/*****************************************************************************/

//定义本文件常量
define("_CART_INC_", "exists");

/*购物车类*/

class TCart
{

    var $SortCount;            //商品种类数
    var $TotalCost;            //商品总价值

    var $Id;                   //每类商品的ID（数组）
    var $Name;                 //每类商品的名称（数组）
    var $Price;                //每类商品的价格（数组）
    var $Discount;             //商品的折扣（数组）
    var $GoodPrice;           //商品的优惠价格（数组）
    var $Count;                //每类商品的件数（数组）
    var $MaxCount;            //商品限量（数组）

    //******构造函数
    function TCart()
    {
        header("Content-type: text/html; charset=utf-8"); 

        $this->SortCount = 0;

        session_start(); //初始化一个session
        $_SESSION['sId'] = '';
        $_SESSION['sName'] = '';
        $_SESSION['sPrice'] = '';
        $_SESSION['sDiscount'] = '';
        $_SESSION['sGoodPrice'] = '';
        $_SESSION['sCount'] = '';
        $_SESSION['sMaxCount'] = '';

        $this->Update();
        $this->Calculate();
    }

    //********私有，根据session的值更新类中相应数据
    function Update()
    {
        global $sId, $sName, $sPrice, $sCount, $sDiscount, $sMaxCount, $sGoodPrice;

        if (!isset($sId) or !isset($sName) or !isset($sPrice)
            or !isset($sDiscount) or !isset($sMaxCount)
            or !isset($sGoodPrice) or !isset($sCount)
        ) return;

        $this->Id = $sId;
        $this->Name = $sName;
        $this->Price = $sPrice;
        $this->Count = $sCount;
        $this->Discount = $sDiscount;
        $this->GoodPrice = $sGoodPrice;
        $this->MaxCount = $sMaxCount;

        //计算商品总数
        $this->SortCount = count($sId);

    }

    //********私有，根据新的数据计算每类商品的价值及全部商品的总价
    function Calculate()
    {
        for ($i = 0; $i < $this->SortCount; $i++) {
            /*计算每件商品的价值，如果折扣是0 ，则为优惠价格*/
            $GiftPrice = ($this->Discount[$i] == 0 ? $this->GoodPrice :
                ceil($this->Price[$i] * $this->Discount[$i]) / 100);
            $this->TotalCost += $GiftPrice * $this->Count[$i];
        }
    }

    //**************以下为接口函数

    //*** 加一件商品
    // 判断是否蓝中已有，如有，加count，否则加一个新商品
    //首先都是改session的值，然后再调用update() and calculate()来更新成员变量
    function Add($a_ID, $a_Name, $a_Price, $a_Discount,
                 $a_GoodPrice, $a_MaxCount, $a_Count)
    {
        global $sId, $sName, $sCount, $sPrice, $sDiscount,
               $sGoodPrice, $sMaxCount;

        $k = count($sId);
        for ($i = 0; $i < $k; $i++) { //先找一下是否已经加入了这种商品
            if ($sId[$i] == $a_ID) {
                $sCount[$i] += $a_Count;
                break;
            }
        }
        if ($i >= $k) { //没有则加一个新商品种类
            $sId[] = $a_ID;
            $sName[] = $a_Name;
            $sPrice[] = $a_Price;
            $sCount[] = $a_Count;
            $sGoodPrice[] = $a_GoodPrice;
            $sDiscount[] = $a_Discount;
            $sMaxCount[] = $a_MaxCount;
        }

        $this->Update(); //更新一下类的成员数据
        $this->Calculate();
    }

    //移去一件商品
    function Remove($a_ID)
    {
        global $sId, $sName, $sCount, $sPrice, $sDiscount,
               $sGoodPrice, $sMaxCount;

        $k = count($sId);
        for ($i = 0; $i < $k; $i++) {
            if ($sId[$i] == $a_ID) {
                $sCount[$i] = 0;
                break;
            }
        }

        $this->Update();
        $this->Calculate();
    }

    //改变商品的个数
    function ModifyCount($a_i, $a_Count)
    {
        global $sCount;

        $sCount[$a_i] = $a_Count;
        $this->Update();
        $this->Calculate();
    }

    /***************************
     * 清空所有的商品
     *****************************/
    function RemoveAll()
    {
        unset($_SESSION['sId']);
        unset($_SESSION['sName']);
        unset($_SESSION['sPrice']);
        unset($_SESSION['sDiscount']);
        unset($_SESSION['sGoodPrice']);
        unset($_SESSION['sCount']);
        unset($_SESSION['sMaxCount']);
        $this->SortCount = 0;
        $this->TotalCost = 0;
    }

    //是否某件商品已在蓝内，参数为此商品的ID
    function Exists($a_ID)
    {
        for ($i = 0; $i < $this->SortCount; $i++) {
            if ($this->Id[$i] == $a_ID) return TRUE;
        }
        return FALSE;
    }

    //某件商品在蓝内的位置
    function IndexOf($a_ID)
    {
        for ($i = 0; $i < $this->SortCount; $i++) {
            if ($this->Id[$i] == $id) return $i;
        }
        return 0;
    }

    //取一件商品的信息，主要的工作函数
    //返回一个关联数组，
    function Item($i)
    {
        $Result['id'] = $this->Id[$i];
        $Result['name'] = $this->Name[$i];
        $Result['price'] = $this->Price[$i];
        $Result['count'] = $this->Count[$i];
        $Result['discount'] = $this->Discount[$i];
        $Result['goodprice'] = $this->GoodPrice[$i];
        $Result['maxcount'] = $this->MaxCount[$i];
        return $Result;
    }

    //取总的商品种类数
    function CartCount()
    {
        return $this->SortCount;
    }

    //取总的商品价值
    function GetTotalCost()
    {
        return $this->TotalCost;
    }
}



$cartObject = new TCart();
$a_ID = '1';
$a_Name = '超级保温杯';
$a_Price = '119.50';
$a_Discount = '90';
$a_GoodPrice = '0';
$a_MaxCount = '10';
$a_Count = '1';

$cartObject->Add($a_ID, $a_Name, $a_Price, $a_Discount, $a_GoodPrice, $a_MaxCount, $a_Count);
$cartData = $cartObject->Item(0);
$CartCount = $cartObject->CartCount();
$GetTotalCost = $cartObject->GetTotalCost();

var_dump($cartData, $CartCount, $GetTotalCost);
?>