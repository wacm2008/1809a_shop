<?php

namespace App\Http\Controllers\weixin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use DB;
class WxController extends Controller
{
    public function valid(){
        echo $_GET['echostr'];
    }
    public function valide(){
        //接收微信服务器推送
        $content=file_get_contents("php://input");
        $data=simplexml_load_string($content);
        //var_dump($data);

        $time=date('Y-m-d H:i:s',time());
        $str=$time.$content."\n";
        file_put_contents("logs/wxlog",$str,FILE_APPEND);
        echo "SUCCESS";
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
    //数据库测试
    public function sql(){
        $data=DB::table('shop_area')->get();
        print_r($data);
    }
    //获取用户信息
    public function getUserInfo($openid){
        $url='https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->getAccessToken().'&openid='.$openid.'&lang=zh_CN';
        $data=file_get_contents($url);
        $arr=json_decode($data,true);
        return $arr;
    }
}
