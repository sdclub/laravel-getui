<?php 
namespace Sdclub\IGeTui;

use Illuminate\Config\Repository;
use GetuiSDK\IGeTui;
use GetuiSDK\IGeTui\Template\IGtNotificationTemplate;
use GetuiSDK\IGeTui\Template\IGtNotyPopLoadTemplate;
use GetuiSDK\IGeTui\Template\IGtLinkTemplate;
use GetuiSDK\IGeTui\Template\IGtTransmissionTemplate;
use GetuiSDK\IGeTui\Core\DictionaryAlertMsg;
use GetuiSDK\IGeTui\Core\IGtSingleMessage;
use GetuiSDK\IGeTui\Core\IGtAPNPayload;
use GetuiSDK\IGeTui\Core\IGtTarget;

class IGetuiPush {

    private $app_id;
    private $app_key;
    private $app_secret;
    private $master_secret;

    /**
     * 初始化配置
     * @author Jamie<327240570@qq.com>
     * @since  2016-12-16T21:23:24+0800
     * @param  Repository               $config [description]
     */
    public function __construct(Repository $config) {
        $conf = $config['igetui'];
        $this->app_id = $conf['app_id'];
        $this->app_key = $conf['app_key'];
        $this->app_secret = $conf['app_secret'];
        $this->master_secret = $conf['master_secret'];
    }

    /**
     * 根据regId发送通知
     * @author Jamie<327240570@qq.com>
     * @since  2016-12-17T04:34:39+0800
     * @param  string                   $cid   	[description]
     * @param  array                    $data   [description]
     * @param  integer                  $tid  	[description]
     * @return [type]                           [description]
     */
    public function send_id($cid = '', $data=[], $tid = 1) {
        $igetui = new IGeTui(NULL, $this->app_key, $this->master_secret, false);
        $template = $this->getTemplate($data, $tid);
        //个推信息体
        $message = new IGtSingleMessage();
        $message->setIsOffline(true); //是否离线
        $message->setOfflineExpireTime(3600 * 12 * 1000); //离线时间
        $message->setData($template); //设置推送消息类型
        //接收方
        $target = new IGtTarget();
        $target->setAppId($this->app_id);
        $target->setClientId($cid);
        //$target->set_alias($this->alias);
        try {
            $rep = $igetui->pushMessageToSingle($message, $target);
        } catch (RequestException $e) {
            $requstId = e . getRequestId();
            $rep = $igetui->pushMessageToSingle($message, $target, $requstId);
        }
        return $rep;
    }

    /**
     * 根据regIds多条发送
     * @author Jamie<327240570@qq.com>
     * @since  2017-06-30T01:44:46+0800
     * @param  array                    $regIds  [description]
     * @param  array                    $data    [description]
     * @param  string                   $client  [description]
     * @param  integer                  $retries [description]
     * @return [type]                            [description]
     */
    public function send_ids($regIds = [], $data = [], $client = 'android', $retries = 1) {
		
	}

	/**
     * 整合消息模板
     * @param $data
     * @param int $type 1=透传功能模板, 2=通知弹框下载模板, 3=通知链接模板, 4=通知透传模板
     */
	private function getTemplate($data, $template_id = 1) {
		if ($template_id==1) {
			//安卓通知栏提示 ios 会在应用内提示 透传功能模板
            $template = $this->IGtNotificationTemplate($data);
		} elseif ($template_id==2) {
			//通知弹框下载模板
            $template = $this->IGtNotyPopLoadTemplate($data);
		} elseif ($template_id==3) {
			//链接
            $template = $this->IGtLinkTemplate($data);
		} elseif ($template_id==4) {
			//通知透传模板
            $template = $this->IGtTransmissionTemplate($data);
		}
		return $template;
	}

	//所有推送接口均支持四个消息模板，依次为通知弹框下载模板，通知链接模板，通知透传模板，透传模板
    //注：IOS离线推送需通过APN进行转发，需填写pushInfo字段，目前仅不支持通知弹框下载功能
    //这是下载模板  ios不支持
    private function IGtNotyPopLoadTemplate($data) {
        $template = new IGtNotyPopLoadTemplate();
        $template->setAppId($this->app_id);//应用appid
        $template->setAppkey($this->app_key);//应用appkey
        //通知栏
        $template->setNotyTitle($data['notyTitle']);//通知栏标题
        $template->setNotyContent($data['notyContent']);//通知栏内容
        $template->setNotyIcon("");//通知栏logo
        $template->setIsBelled(true);//是否响铃
        $template->setIsVibrationed(true);//是否震动
        $template->setIsCleared(true);//通知栏是否可清除
        //弹框
        $template->setPopTitle($data['popTitle']);//弹框标题
        $template->setPopContent($data['popContent']);//弹框内容
        $template->setPopImage("");//弹框图片
        $template->setPopButton1("下载");//左键
        $template->setPopButton2("取消");//右键
        //下载
        $template->setLoadIcon("");//弹框图片
        $template->setLoadTitle($data['loadTitle']);
        $template->setLoadUrl($data['loadUrl']);
        $template->setIsAutoInstall(false);
        $template->setIsActived(true);
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        return $template;
    }

    //通知连接模板   安卓在通知栏打开连接   ios要在应用内弹出对话框 点击打开safari
    private function IGtLinkTemplate($data) {
        $template = new IGtLinkTemplate();
        $template->setAppId($this->app_id); //应用appid
        $template->setAppkey($this->app_key); //应用$this->APPKEY
        $template->setTitle($data['title']); //通知栏标题
        $template->setText($data['text']); //通知栏内容
        $template->setTogo(""); //通知栏logo
        $template->setTsRing(true); //是否响铃
        $template->setTsVibrate(true); //是否震动
        $template->setTsClearable(true); //通知栏是否可清除
        $template->setUrl($data['url']); //打开连接地址
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        //iOS推送需要设置的pushInfo字段
        $apn = new IGtAPNPayload();
        $alertmsg = new DictionaryAlertMsg();
        $apn->alertMsg = $alertmsg;//"alertMsg";
        //$apn->badge = 11;
        $apn->actionLocKey = "启动";
        //        $apn->category = "ACTIONABLE";
        //        $apn->contentAvailable = 1;
        $apn->locKey = "通知栏内容";
        $apn->title = "通知栏标题";
        $apn->titleLocArgs = array("titleLocArgs");
        $apn->titleLocKey = "通知栏标题";
        $apn->body = "body";
        $apn->customMsg = array("payload" => "payload");
        $apn->launchImage = "launchImage";
        $apn->locArgs = array("locArgs");
        $apn->sound = ("test1.wav");;
        $template->setApnInfo($apn);
        return $template;
    }

    //安卓通知栏推送  //通知透传 //IPHONE 会在应用内弹出提示
    private function IGtNotificationTemplate($data) {
        $template = new IGtNotificationTemplate();
        $template->setAppId($this->app_id); //应用appid
        $template->setAppkey($this->app_key); //应用$this->APPKEY
        $template->setTransmissionType(1); //透传消息类型
        $template->setTransmissionContent($data['content']); //透传内容
        $template->setTitle($data['title']); //通知栏标题
        $template->setText($data['text']); //通知栏内容
        $template->setLogo(''); //通知栏logo
        $template->setIsRing(true); //是否响铃
        $template->setIsVibrate(true); //是否震动
        $template->setIsClearable(true); //通知栏是否可清除
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        //iOS推送需要设置的pushInfo字段
        $apn = new IGtAPNPayload();
        $alertmsg = new DictionaryAlertMsg();
        $apn->alertMsg = $alertmsg;//"alertMsg";
        //$apn->badge = 11;
        $apn->actionLocKey = "启动";
        //        $apn->category = "ACTIONABLE";
        //        $apn->contentAvailable = 1;
        $apn->locKey = "通知栏内容";
        $apn->title = "通知栏标题";
        $apn->titleLocArgs = array("titleLocArgs");
        $apn->titleLocKey = "通知栏标题";
        $apn->body = $data['body'];
        $apn->customMsg = array("payload" => $data['payload']);
        $apn->launchImage = "launchImage";
        $apn->locArgs = array("locArgs");
        //$apn->sound=("test1.wav");;
        $template->setApnInfo($apn);
        return $template;
    }

    //IPHONE 通知栏提示 //安卓会启动应用 可在应用内拿到透传的内容
    private function IGtTransmissionTemplate($data) {
        $template = new IGtTransmissionTemplate();
        $template->setAppId($this->app_id); //应用appid
        $template->setAppkey($this->app_key); //应用$this->APPKEY
        $template->setTransmissionType(1); //透传消息类型
        $template->setTransmissionContent($data['content']); //透传内容
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        //APN简单推送
        //        $template = new IGtAPNTemplate();
        //        $apn = new IGtAPNPayload();
        //        $alertmsg=new SimpleAlertMsg();
        //        $alertmsg->alertMsg="";
        //        $apn->alertMsg=$alertmsg;
        ////        $apn->badge=2;
        ////        $apn->sound="";
        //        $apn->add_customMsg("payload","payload");
        //        $apn->contentAvailable=1;
        //        $apn->category="ACTIONABLE";
        //        $template->set_apnInfo($apn);
        //        $message = new IGtSingleMessage();
        //APN高级推送
        $apn = new IGtAPNPayload();
        $alertmsg = new DictionaryAlertMsg();
        $alertmsg->body = $data['body'];
        $alertmsg->actionLocKey = "ActionLockey";
        $alertmsg->locKey = "LocKey";
        $alertmsg->locArgs = array("locargs");
        $alertmsg->launchImage = "launchimage";
//        IOS8.2 支持
        $alertmsg->title = $data['title'];
        $alertmsg->titleLocKey = "TitleLocKey";
        $alertmsg->titleLocArgs = array("TitleLocArg");
        $apn->alertMsg = $alertmsg;
        //$apn->badge = 7;
        $apn->sound = "";
        $apn->add_customMsg("payload", $data['payload']);
        $apn->contentAvailable = 1;
        $apn->category = "ACTIONABLE";
        $template->set_apnInfo($apn);
        return $template;
    }
}