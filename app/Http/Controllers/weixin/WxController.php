<?php

namespace App\Http\Controllers\weixin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use App\Model\User\WxuserModel;
use DB;
class WxController extends Controller
{
    public function valid(){
        echo $_GET['echostr'];
    }
    public function valide(){
        //接收微信服务器推送
        $content=file_get_contents("php://input");
        $time=date('Y-m-d H:i:s',time());
        $str=$time.$content."\n";
        file_put_contents("logs/wxlog.log",$str,FILE_APPEND);
        $data=simplexml_load_string($content);
        //var_dump($data);
//         echo 'ToUserName: '. $data->ToUserName;echo '</br>';        // 公众号ID
//         echo 'FromUserName: '. $data->FromUserName;echo '</br>';    // 用户OpenID
//         echo 'CreateTime: '. $data->CreateTime;echo '</br>';        // 时间戳
//         echo 'MsgType: '. $data->MsgType;echo '</br>';              // 消息类型
//         echo 'Event: '. $data->Event;echo '</br>';                  // 事件类型
//         echo 'EventKey: '. $data->EventKey;echo '</br>';
        $wx_id = $data->ToUserName;// 公众号ID
        $openid = $data->FromUserName;//用户OpenID
        var_dump($openid);exit;
        $event = $data->Event;//事件类型

        //扫码关注事件
        if($event=='subscribe'){
            //根据openid判断用户是否已存在
            $local_user = WxuserModel::where(['openid'=>$openid])->first();
            if($local_user){
                //用户之前关注过
                echo '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$wx_id.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['. '欢迎回来 '. $local_user['nickname'] .']]></Content></xml>';
            }else{
                //用户首次关注
                //获取用户信息
                $arr = $this->getUserInfo($openid);
                //用户信息入库
                $user_info = [
                    'openid'    => $arr['openid'],
                    'nickname'  => $arr['nickname'],
                    'sex'  => $arr['sex'],
                    'headimgurl'  => $arr['headimgurl'],
                ];
                $id = WxuserModel::insertGetId($user_info);
                echo '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$wx_id.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['. '欢迎关注 '. $arr['nickname'] .']]></Content></xml>';
            }
        }
    }
    //获取微信accesstoken
    public function getAccessToken(){
        //是否有缓存
        $key='wx_access_token';
        $token=Redis::get($key);
        //var_dump($token);exit;
        if($token){
            //return $token;
            echo "con cache：";
        }else{
            echo "sin cache：";
            $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_APPSECRET');
            //echo $url;
            $response=file_get_contents($url);
            //echo $response;
            $arr=json_decode($response,true);
            //print_r($arr);

            //存缓存accesstoken
            $key='wx_access_token';
            Redis::set($key,$arr['access_token']);
            Redis::expire($key,3600);
            $token=$arr['access_token'];
        }
        return $token;
    }
    public function test(){
        $access_token=$this->getAccessToken();
        echo $access_token;
    }
    //获取用户信息
    public function getUserInfo($openid){
        $url='https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->getAccessToken().'&openid='.$openid.'&lang=zh_CN';
        $data=file_get_contents($url);
        $arr=json_decode($data,true);
        return $arr;
    }
}
