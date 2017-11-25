<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-2-4
 * Time: 下午7:04
 */
header("Content-Type:application/javascript;charset=UTF-8");
date_default_timezone_set('Asia/Chongqing');
set_time_limit(0);
$C = new stdClass();
$C->webpath = dirname(__file__) . '/';
require_once 'config/conf_main.php';
require_once 'includes/class_mysql.php';
require_once 'includes/func_main.php';
/**
https://cars.app.autohome.com.cn/cars_v8.5.0/cars/brands-pm2.json
https://cars.app.autohome.com.cn/cars_v8.5.0/cars/seriesprice-pm2-b33-t16-v8.5.0-c110100.json
https://cars.app.autohome.com.cn/carinfo_v8.5.0/cars/seriessummary-pm2-s18-t-c110100-v8.5.0.json
https://cars.app.autohome.com.cn/cfg_v8.5.0/cars/speccompare.ashx?type=1&specids=29396%2C29397%2C29398%2C29399%2C29400%2C29401&cityid=110100&site=1&pl=2
*/
$C->DB_HOST_M = 'localhost';
$C->DB_USER = 'root';
$C->DB_PASS='';
$C->DB_NAME='test';

$dbm = new mysql($C->DB_HOST_M, $C->DB_USER, $C->DB_PASS, $C->DB_NAME);

function getUrlJson($url){
    sleep(1);
    $oCurl = curl_init();
    if(stripos($url,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($oCurl, CURLOPT_HEADER, 1);
    $user_agent = 'Android	6.0.1	autohome	8.5.0	Android';
    curl_setopt($oCurl, CURLOPT_USERAGENT, $user_agent);
    curl_setopt($oCurl, CURLINFO_HEADER_OUT, TRUE);
    $header = [
        'Connection: Keep-Alive',
        'Accept-Encoding: gzip'
    ];
    curl_setopt($oCurl, CURLOPT_HTTPHEADER,$header);

    curl_setopt($oCurl, CURLOPT_ENCODING, "gzip");
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    $headerSize = curl_getinfo($oCurl, CURLINFO_HEADER_SIZE);
    //$header = substr($sContent, 0, $headerSize);
    $sContent = substr($sContent, $headerSize);
    curl_close($oCurl);
    if(intval($aStatus["http_code"])==200){
        //file_put_contents("content.log","{$url}\n{$sContent}\n",FILE_APPEND);
        return json_decode($sContent,true);
    }else{
        return false;
    }

}

/**
 * 获取品牌列表
 */
function getBrandList(){
    global $dbm;
    $res = getUrlJson('https://cars.app.autohome.com.cn/cars_v8.5.0/cars/brands-pm2.json');
    $brandlist = $res['result']['brandlist'];
    //print_r($brandlist);
    //exit();
    foreach ($brandlist as $abc){
        foreach ($abc['list'] as $brand){
            //存入数据库
            $dbm->query("replace into l_brand(`id`,`name`,`imgurl`,`letter`,`sort`)VALUES ('{$brand['id']}','{$brand['name']}','{$brand['imgurl']}','{$abc['letter']}','{$brand['sort']}')");
            getSeriesprice($brand);
        }
    }
}

/**
 * 获取车型
 * @param $brand
 */
function getSeriesprice($brand){
    global $dbm;
    $url = "https://cars.app.autohome.com.cn/cars_v8.5.0/cars/seriesprice-pm2-b{$brand['id']}-t16-v8.5.0-c110100.json";
    $res = getUrlJson($url);
    $fctlist = $res['result']['fctlist'];

    foreach ($fctlist as $item){
        foreach ($item['serieslist'] as $serie){
            //存入数据库
            $dbm->query("replace into l_series(`id`,`brandid`,`tradename`,`name`,`imgurl`,`levelid`,`levelname`,`price`,`isar`,`isvr`,`sort`,`istuan`,`issell`)VALUES ('{$serie['id']}','{$brand['id']}','{$item['name']}','{$serie['name']}','{$serie['imgurl']}','{$serie['levelid']}','{$serie['levelname']}','{$serie['price']}','{$serie['isar']}','{$serie['isvr']}','{$serie['sort']}','{$serie['istuan']}','1')");
            getSeriessummary($serie);
        }
    }

    $otherfctlist = $res['result']['otherfctlist'];
    foreach ($otherfctlist as $item){
        foreach ($item['serieslist'] as $serie){
            //存入数据库
            $dbm->query("replace into l_series(`id`,`brandid`,`tradename`,`name`,`imgurl`,`levelid`,`levelname`,`price`,`isar`,`isvr`,`sort`,`istuan`,`issell`)VALUES ('{$serie['id']}','{$brand['id']}','{$item['name']}','{$serie['name']}','{$serie['imgurl']}','{$serie['levelid']}','{$serie['levelname']}','{$serie['price']}','{$serie['isar']}','{$serie['isvr']}','{$serie['sort']}','{$serie['istuan']}','0')");
            getSeriessummary($serie);
        }
    }
}

/**
 * 获取车型下配置
 * @param $serie
 */
function getSeriessummary($serie){
    print_r($serie);
    global $dbm;
    $url = "https://cars.app.autohome.com.cn/carinfo_v8.5.0/cars/seriessummary-pm2-s{$serie['id']}-t-c110100-v8.5.0.json";
    $res = getUrlJson($url);
    $enginelist = $res['result']['enginelist'];

    foreach ($enginelist as $item){
        foreach ($item['yearspeclist'] as $list){
            $ids = [];
            foreach ($list['speclist'] as $val){
                //todo:存入数据库
                $dbm->query("replace into l_speclist(`id`,`seriesid`,`specname`,`name`,`minprice`,`labletype`,`paramisshow`,`price`,`pricename`,`saletype`,`state`,`attention`,`canaskprice`,`description`,`yearname`,`yearvalue`)VALUES ('{$val['id']}','{$serie['id']}','{$list['name']}','{$val['name']}','{$val['minprice']}','{$val['labletype']}','{$val['paramisshow']}','{$val['price']}','{$val['pricename']}','{$val['saletype']}','{$val['state']}','{$val['attention']}','{$val['canaskprice']}','{$val['description']}','{$item['yearname']}','{$item['yearvalue']}')");
                $ids[] = $val['id'];
            }
            getSpeccompare($ids);
        }
    }

}

$str = file_get_contents('111.txt');
$arr = explode("\n",$str);
$arr = array_flip($arr);

/**
 * 获取配置详情
 */
function getSpeccompare($ids){
    global $dbm,$arr;
    $str = implode(',',$ids);
    $url = "https://cars.app.autohome.com.cn/cfg_v8.5.0/cars/speccompare.ashx?type=1&specids={$str}&cityid=110100&site=1&pl=2";
    $res = getUrlJson($url);
    //todo:分析存入数据库
    $configitems = $res['result']['configitems'];

    foreach ($configitems as $configitem){
        $items = $configitem['items'];
        foreach ($items as $it){
            if($it['id'] == '-1') {
                if(isset($arr[$it['name']])){
                    $it['id']  = $arr[$it['name']]+1;
                }else{
                    file_put_contents('222.txt',"{$it['name']}\n",FILE_APPEND);
                }

            }
            $dbm->query("replace into l_config(`id`,`itemtype`,`name`)VALUES ('{$it['id']}','{$configitem['itemtype']}','{$it['name']}')");
            foreach ($it['modelexcessids'] as $key=>$item){
                $dbm->query("replace into l_modelexcessid(`id`,`configid`,`specid`,`priceinfo`,`value`)VALUES ('{$item['id']}','{$it['id']}','{$ids[$key]}','{$item['priceinfo']}','{$item['value']}')");
            }
        }
    }
    $paramitems = $res['result']['paramitems'];
    foreach ($paramitems as $paramitem){
        $items = $paramitem['items'];
        foreach ($items as $it){
            if($it['id'] == '-1') {
                if(isset($arr[$it['name']])){
                    $it['id']  = $arr[$it['name']]+1;
                }else{
                    file_put_contents('222.txt',"{$it['name']}\n",FILE_APPEND);
                }
            }
            $dbm->query("replace into l_config(`id`,`itemtype`,`name`)VALUES ('{$it['id']}','{$paramitem['itemtype']}','{$it['name']}')");
            foreach ($it['modelexcessids'] as $key=>$item){
                $dbm->query("replace into l_modelexcessid(`id`,`configid`,`specid`,`value`)VALUES ('{$item['id']}','{$it['id']}','{$ids[$key]}','{$item['value']}')");
            }
        }
    }
    //exit();
}
getBrandList();