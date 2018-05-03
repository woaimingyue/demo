<?php

class Api{

    //请求签名配置键值（发起请求者的的身份）
    protected static $_signKey = 'identity';

    //请求签名键值
    protected static $_signName = 'sign';

    //待签名的字符串
    protected static $_signStr = '';

    //未知错误编码
    protected static $_errorCode = 500;
    protected static $_errorMsg = 'system_error';

    //返回成功编码
    protected static $_successCode = 200;
    protected static $_successMsg = 'success';

    protected static $_missingParamCode = 600;

    //接口会话key
    protected static $sessionName = 'token';
    //接口会话id
    protected static $token = '';

    //页码变量
    protected static $pageParamName = 'page';
    protected static $rowsParamNmae = 'rows';
    protected static $defaultPage = 1;
    protected static $defaultRows = 20;
    protected static $maxRows = 200;

    //todo
    protected $member = array();
    protected $member_id = null;

    function __construct(&$app){
        parent::__construct($app);

        $token = $this->getParam(self::$sessionName);
        //判断必须有token参数才开启会话
        if (!empty($token) && strlen($token) == 32) {
            //接收session_id
            $_GET['sess_id'] = $token;

            //返回会话id
            kernel::single('base_session')->start();
            self::$token = kernel::single('base_session')->sess_id();
        }

        //签名验证
        try {
            $this->check_sign();
        } catch (Exception $e) {
            $this->endError($e);die;
        }

    }

    /**
     * 签名检验
     * @author diven
     * @return bool
     */
    public function check_sign()
    {
        // 检验是否要求开启签名认证
        if (!base_fun::getConfig('check_sign')) return false;

        $data = $this->getParam('',array(),''); //不做字符实体转换
        // 没有请求参数，请求受限制
        if (empty($data)) {
            self::throwException(600, self::$_signName);
        }
        // 缺少签名字段
        if (!isset($data[self::$_signName])) {
            self::throwException(600, self::$_signName);
        }
        // 缺少账号参数
        if (empty($data[self::$_signKey])) {
            self::throwException(600, self::$_signKey);
        }

        // 获取签名配置信息
        $signInfo = base_fun::getConfig('sign');

        // 获取签名密钥
        $signKey = isset($signInfo[$data[self::$_signKey]]) ? $signInfo[$data[self::$_signKey]] : '';
        // 账号信息不存在
        if (empty($signKey)) {
            self::throwException(601);
        }

        //过滤掉不需要参与签名的sign参数
        $param = $data;
        unset($param[self::$_signName]);
        // 生成签名
        $sign = self::get_sign($param, $signKey);

        // 验证签名
        if ($sign !== strtoupper($data[self::$_signName])) {

            // 发送签名验证失败邮件报警
            self::send_email($data, $sign);

            // 签名验证失败
            self::throwException(602);
        }

        return true;
    }

    /**
     * 获取签名结果字符串
     * @author diven
     * @return string
     */
    public static function get_sign($data, $signKey)
    {
        return strtoupper(md5(self::assemble($data).$signKey));
    }

    /**
     * AC签名算法
     * @author diven
     * @return null|string
     */
    public static function assemble($params)
    {
        if(!is_array($params))  return null;
        ksort($params,SORT_STRING); //根据数组键名排序
        $sign = '';
        foreach($params AS $key=>$val){
            $sign .= $key . (is_array($val) ? self::assemble($val) : $val);
        }

        self::$_signStr = $sign;

        return $sign;
    }

    /**
     * 发送报警邮件
     * @author diven
     * @return bool
     */
    protected static function send_email($data, $sign)
    {
        // 发送签名验证失败日志
        $content = '【data】';
        $content .= "<br><br>".print_r($data, true);
        $content .= '【sign】'.$sign;
        $content .= "<br><br>".print_r(self::$_signStr, true);

        // 发送邮件
        $subject = "【".app::get('site')->getConf('site.name')."】签名认证结果";

        return base_email::alert($subject, $content, base_fun::getConfig('sign_mail'));
    }


    /**
     * 格式化请求结束返回数据
     * @author Diven
     * @param number $code 响应编码
     * @param string $msg 响应提示信息
     * @param array $data 响应数据
     * @param string $space
     * @param bool $isJson
     * @return array
     */
    protected static function endJson($code, $msg, $data = array(), $space = 'code', $isJson = true)
    {
        if (self::$token) {
            $ext['token'] = self::$token;
        }

        return base_fun::endJson($code, $msg, $data, $space, $isJson, $ext);
    }

    /**
     * 成功的返回数据
     * @author Diven
     * @param array $data 响应的数据
     * @return array
     */
    protected static function endSuccess($data = array())
    {
        $code = self::$_successCode;
        $msg = self::$_successMsg;

        return self::endJson($code, $msg, $data);
    }

    /**
     * 失败的返回数据
     * @author Diven
     * @param object $e
     * @param array $data
     * @return array
     */
    protected static function endError($e, $data = array())
    {
        if($e instanceof Exception){
            $code = $e->getCode();
            $msg = $e->getMessage();
        }else{
            $code = self::$_errorCode;
            $msg = self::$_errorMsg;
        }

        return self::endJson($code, $msg, $data);
    }

    /**
     * 抛出一个用户异常
     * @author zzp
     * @param $code
     * @param $message
     * @throws b2c_exception_ApiException
     */
    public static function throwException($code, $message = null)
    {
        throw new b2c_exception_ApiException($message, $code);
    }

    /**
     * 抛出一个参数缺失异常
     * @author zzp
     * @param $paramName
     */
    public static function paramMissing($paramName)
    {
        self::throwException(self::$_missingParamCode, $paramName);
    }

    /**
     * 获取页码
     * @author zzp
     * @return int
     */
    public function getPage()
    {
        return intval($this->getParam(self::$pageParamName)) ?: self::$defaultPage;
    }

    /**
     * 获取条数
     * @author zzp
     * @return int
     */
    public function getRows()
    {
        $rows = intval($this->getParam(self::$rowsParamNmae));
        $rows = $rows ?: self::$defaultRows;
        $rows > self::$maxRows && $rows = self::$maxRows;

        return  $rows;
    }

    /**
     * 检查用户登录状态
     * @author zzp
     * @param bool $throwException
     * @return bool
     */
    public function checkIsLogin($throwException = true)
    {
        if (!$this->check_login()) {
            if ($throwException) {
                self::throwException(100001);
            }

            return false;
        }

        return true;
    }

    /**
     * 获取输入参数
     * @author zzp
     * @param $name
     * @param string $default
     * @param null $filter
     * @param null $datas
     * @return mixed
     */
    public function getParam($name='',$default='',$filter=null,$datas=null)
    {
        return base_fun::getParam($name, $default, $filter, $datas);
    }

    /**
     * 获取memberId
     * @author zzp
     * @return mixed
     */
    public function getMemberId()
    {
        return kernel::single('b2c_user_object')->get_member_session();
    }

    //以下为原来方法
    /**
     * 设置微信分享功能
     * @author diven
     */
    public function set_weixin_share($data = array())
    {
        //如果没有图片就默认logo
        if (empty($data['imgUrl'])) {
            $data['imgUrl'] = base_storager::image_path(app::get('weixin')->getConf('weixin_basic_setting.weixin_logo'));
        }
        if (empty($data['shareTitle'])) {
            $data['shareTitle'] = app::get('weixin')->getConf('weixin_basic_setting.weixin_name');
        }
        if (empty($data['linelink'])) {
            $data['linelink'] = $this->_request->get_full_http_host().$_SERVER['REQUEST_URI'];
        }
        if (empty($data['descContent'])) {
            $data['descContent'] = app::get('weixin')->getConf('weixin_basic_setting.weixin_brief');
        }
        //初始化分享参数
        $this->pagedata['signPackage'] =  $param = kernel::single('weixin_wechat')->getSignPackage();
        $this->pagedata['weixin'] = $data;
        $this->pagedata['from_weixin'] = $this->from_weixin;
    }

    public function set_weixin_openid(){
        kernel::single('base_session')->start();
        $nodes_obj = app::get('b2c')->model('shop');
        $nodes = $nodes_obj->count( array('node_type'=>'wechat','status'=>'bind'));

        if( $nodes <= 0 ){
            if( !empty($_GET['signature']) &&  !empty($_GET['openid']) ){
                $bind = app::get('weixin')->model('bind')->getRow('id',array('eid'=>$_GET['u_eid'],'status'=>'active'));
                $flag = kernel::single('weixin_object')->check_wechat_sign($_GET['signature'], $_GET['openid']);
                if( $flag && !empty($bind)){
                    $openid = $_GET['openid'];
                }
            }elseif( !empty($_GET['code']) && !empty($_GET['state']) ){
                $bind = app::get('weixin')->model('bind')->getRow('id',array('eid'=>$_GET['state'],'status'=>'active'));
                if( !empty($bind) &&  kernel::single('weixin_wechat')->get_oauth2_accesstoken($bind['id'],$_GET['code'],$result) ){
                    $openid = $result['openid'];
                }
            }

            if( $openid ){
                $wechat_obj = kernel::single('weixin_wechat');
                $bindTagData = app::get('pam')->model('bind_tag')->getRow('tag_name,member_id',array('open_id'=>$openid));
                if( $bindTagData ){
                    $_SESSION['weixin_u_nickname'] = $wechat_obj->emoji_decode( $bindTagData['tag_name'] );
                    $_SESSION['account']['member'] = $bindTagData['member_id'];
                    $this->bind_member($bindTagData['member_id']);
                }else{
                    $res = $wechat_obj->get_basic_userinfo($bind['id'],$openid);

                    $wap_wxlogin = app::get("weixin")->getConf('weixin_basic_setting.wxlogin');
                    if( $wap_wxlogin == 'true' ){
                        $member_id = kernel::single('b2c_user_passport')->create($res,$openid);
                        if($member_id ){
                            $_SESSION['account']['member'] = $member_id;
                            $this->bind_member($member_id);
                        }
                    }
                    $_SESSION['weixin_u_nickname'] = $wechat_obj->emoji_decode( $res['nickname'] );
                }
                $_SESSION['weixin_u_openid'] = $openid;
                $_SESSION['is_bind_weixin'] = false;
            }
        }else{
            $wechat = kernel::single('weixin_wechat');
            if( !empty($_GET['code']) && !empty($_GET['state']) ){
                $data = $wechat->matrix_openid($_GET['code']);
                $openid = isset($data['openid']) ? $data['openid'] : '';
                $access_token = isset($data['access_token']) ? $data['access_token'] : '';

                //通过微信菜单打开时，不算做导购，清cookie
                $path = kernel::base_url().'/index.php/wap/';
                $this->cookie_path = $path;
                $this->set_cookie('penker','',time()-3600);
                $this->set_cookie('guide_identity','',time()-3600);
            }

            if( $openid ){
                $bindTagData = app::get('pam')->model('bind_tag')->getRow('tag_name,member_id',array('open_id'=>$openid));
                if( $bindTagData ){
                    $_SESSION['weixin_u_nickname'] = $wechat_obj->emoji_decode( $bindTagData['tag_name'] );
                    $_SESSION['account']['member'] = $bindTagData['member_id'];
                    $this->bind_member($bindTagData['member_id']);
                }else{
                    $res = $wechat->matrix_userinfo($openid,$access_token);
                    if( isset($res['nickname']) ){

                        $wap_wxlogin = app::get("weixin")->getConf('weixin_basic_setting.wxlogin');
                        if( $wap_wxlogin == 'true' ){
                            $member_id = kernel::single('b2c_user_passport')->create($res,$openid);
                            if( $member_id ){
                                $_SESSION['account']['member'] = $member_id;
                                $this->bind_member($member_id);
                            }
                        }
                        $_SESSION['weixin_u_nickname'] = $wechat_obj->emoji_decode( $res['nickname'] );
                    }
                }
                $_SESSION['weixin_u_openid'] = $openid;
                $_SESSION['is_bind_weixin'] = false;
            }
        }
        return true;
    }

    public function verify_member(){
        kernel::single('base_session')->start();
        $userObject = kernel::single('b2c_user_object');
        if (app::get('b2c')->member_id = $userObject->get_member_id()) {
            $data = $userObject->get_members_data(array('members'=>'member_id'));
            if ($data) {
                //登录受限检测
                $res = $this->loginlimit(app::get('b2c')->member_id, $redirect);
                if ($res) {
                    self::throwException(100002);
                } else {
                    return true;
                }
            } else {
                self::throwException(100000);
            }
        } else {
            self::throwException(100001);
        }
    }
    /**
     * loginlimit-登录受限检测
     *
     * @param      none
     * @return     void
     */
    function loginlimit($mid,&$redirect) {
        $services = kernel::servicelist('loginlimit.check');
        if ($services){
            foreach ($services as $service){
                $redirect = $service->checklogin($mid);
            }
        }
        return $redirect?true:false;
    }//End Function

    public function bind_member($member_id){
        $columns = array(
            'account'=> 'member_id,login_account,login_password',
            'members'=> 'member_id,member_lv_id,cur,lang',
        );
        $userObject = kernel::single('b2c_user_object');
        $cookie_expires = $userObject->cookie_expires ? time() + $userObject->cookie_expires * 60 : 0;
        $data = $userObject->get_members_data($columns);
        //$secstr = kernel::single('b2c_user_passport')->gen_secret_str($member_id, $data['account']['login_name'], $data['account']['login_password']);
        $login_name = $userObject->get_member_name($data['account']['login_name']);
        $this->cookie_path = kernel::base_url().'/';
        #$this->set_cookie('MEMBER',$secstr,0);
        $this->set_cookie('UNAME',$login_name,$cookie_expires);
        $this->set_cookie('MLV',$data['members']['member_lv_id'],$cookie_expires);
        $this->set_cookie('CUR',$data['members']['cur'],$cookie_expires);
        $this->set_cookie('LANG',$data['members']['lang'],$cookie_expires);
        $this->set_cookie('S[MEMBER]',$member_id,$cookie_expires);
    }

    public function _check_verify_member($member_id=0)
    {
        if (isset($member_id) && $member_id)
        {
            $userObject = kernel::single('b2c_user_object');
            $current_member_id = $userObject->get_member_id();
            if ($member_id != $current_member_id)
            {
                $this->begin();
                $this->end(false,  app::get('b2c')->_('订单无效！'), $this->gen_url(array('app'=>'wap','ctl'=>'default','act'=>'index')));
            }
            else
            {
                return true;
            }
        }

        return false;
    }

    public function get_current_member()
    {
        return kernel::single('b2c_user_object')->get_current_member();
    }

    function set_cookie($name,$value,$expire=false,$path=null){
        if(!$this->cookie_path){
            $this->cookie_path = kernel::base_url().'/';
            #$this->cookie_path = substr(PHP_SELF, 0, strrpos(PHP_SELF, '/')).'/';
            $this->cookie_life =  app::get('b2c')->getConf('system.cookie.life');
        }
        $this->cookie_life = $this->cookie_life > 0 ? $this->cookie_life : 315360000;
        $expire = $expire === false ? time()+$this->cookie_life : $expire;
        setcookie($name,$value,$expire,$this->cookie_path);
        $_COOKIE[$name] = $value;
    }

    function check_login(){
        kernel::single('base_session')->start();
        if($_SESSION['account'][pam_account::get_account_type('b2c')]){
            return true;
        }else{
            return false;
        }
    }
    /*获取当前登录会员的会员等级*/
    function get_current_member_lv()
    {
        kernel::single('base_session')->start();
        if($member_id = $_SESSION['account'][pam_account::get_account_type('b2c')]){
            $member_lv_row = app::get("pam")->model("account")->db->selectrow("select member_lv_id from sdb_b2c_members where member_id=".intval($member_id));
            return $member_lv_row ? $member_lv_row['member_lv_id'] : -1;
        }
        else{
            return -1;
        }
    }
    function setSeo($app,$act,$args=null){
        // 触屏版暂时用pc端的seo信息
        $app = str_ireplace("wap_","site_",$app);
        $seo = kernel::single('site_seo_base')->get_seo_conf($app,$act,$args);
        $this->title = $seo['seo_title'];
        $this->keywords = $seo['seo_keywords'];
        $this->description = $seo['seo_content'];
        $this->nofollow = $seo['seo_nofollow'];
        $this->noindex = $seo['seo_noindex'];
    }//End Function

    function get_member_fav($member_id=null){
        $obj_member_goods = app::get('b2c')->model('member_goods');
        return $obj_member_goods->get_member_fav($member_id);
    }

    //活动推广来源记录 2017-9-22
    function action_refer_record(){
        $source = $this->_request->get_get('source');
        if($source){
            kernel::single('base_session')->start();
            $_SESSION['action_refer_record'] = $source;
        }

    }

    //获取来源
    public function getSource()
    {
        $source = $this->getParam('source','');
        if(strpos($source,'xiaochengxu') !== false){
            return 'xiaochengxu';
        }else {
            return 'app';
        }
    }

    //关于注册和订单获取平台来源
    public function get_scource()
    {
        //对于来源判断
        $source = $this->getParam('source','');
        if(strpos($source,'xiaochengxu') !== false){
            $source =  'xiaochengxu';
        }elseif(strpos($source,'android') !== false) {
            $source =  'android';
        }elseif(strpos($source,'ios') !== false){
            $source =  'ios';
        }else{
            $source =  'api';
        }

        return $source;
    }
}
