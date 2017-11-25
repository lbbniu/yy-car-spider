<?php
function __autoload($class_name)
{
    global $C;
    require_once ($C->systempath . 'classes/class_' . $class_name . '.php');
}
function is_valid_email($email)
{
    return preg_match('/^[a-zA-Z0-9._%-]+@([a-zA-Z0-9.-]+\.)+[a-zA-Z]{2,4}$/u', $email);
}
function is_valid_num($num)
{
    return $num == strval(intval($num));
}
function is_valid_mobile($mobile)
{
    return preg_match('/^1[0-9]{10}$/i', $mobile);
}
function show_filesize($bytes)
{
    $kb = ceil($bytes / 1024);
    if ($kb < 1024) {
        return $kb . 'KB';
    }
    $mb = round($kb / 1024, 1);
    return $mb . 'MB';
}
function str_cut($string, $start=0,$length, $dot = '..') {
    $strlen = strlen($string);
    if($strlen <= $length) return $string;
    $string = str_replace(array('&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), array(' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), $string);
    $strcut = '';
    $n = $tn = $noc = 0;
    while($n < $strlen) {
        $t = ord($string[$n]);
        if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
            $tn = 1; $n++; $noc++;
        } elseif(194 <= $t && $t <= 223) {
            $tn = 2; $n += 2; $noc += 2;
        } elseif(224 <= $t && $t < 239) {
            $tn = 3; $n += 3; $noc += 2;
        } elseif(240 <= $t && $t <= 247) {
            $tn = 4; $n += 4; $noc += 2;
        } elseif(248 <= $t && $t <= 251) {
            $tn = 5; $n += 5; $noc += 2;
        } elseif($t == 252 || $t == 253) {
            $tn = 6; $n += 6; $noc += 2;
        } else {
            $n++;
        }
        if($noc >= $length) break;
    }
    if($noc > $length) $n -= $tn;
    $strcut = substr($string, $start, $n);
    $strcut = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;'), $strcut);
    if( $strlen==strlen($strcut)){
        return $strcut;
    }else{
        return $strcut.$dot;
    }
}
function my_session_name($domain)
{
    global $C;
    return $C->RNDKEY . str_replace(array('.', '-'), '', $domain);
}
function cookie_domain()
{
    global $C;
    $tmp = $GLOBALS['C']->DOMAIN;
    if (substr($tmp, 0, 2) == 'm.')
    {
        $tmp = substr($tmp, 2);
    }
    $pos = strpos($tmp, '.');
    if (false === $pos)
    {
        return '';
    }
    if (preg_match('/^[0-9\.]+$/', $tmp))
    {
        return $tmp;
    }
    return '.' . $tmp;
}
function _authcode($string, $operation = 'DECODE', $expiry = 0) {
    $key = 'c5s1t6o';
    $ckey_length = 4;
    $key = md5($key);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5('csto'), -$ckey_length)) : '';
    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if($operation == 'DECODE') {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}
function jiami($text){
    return _authcode($text,'');
}
function jiemi($text){
    return _authcode($text,'DECODE');
}
function is_mobile() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $mobile_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
    $is_mobile = false;
    foreach ($mobile_agents as $device) {
        if (stristr($user_agent, $device)) {
            $is_mobile = true;
            break;
        }
    }
    return $is_mobile;
}
function is_weixin(){
    if ( strpos(strtolower($_SERVER['HTTP_USER_AGENT']), strtolower('MicroMessenger')) !== false ) {
        return true;
    }
    return false;
}
function getUserInfol($code,$appid,$appsecret)
{
    $access_token = "";
    $access_token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
    $access_token_json = https_request($access_token_url);
    return json_decode($access_token_json, true);
//    $access_token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
//    $access_token_json = file_get_contents($access_token_url);
//    $access_token_json=json_decode($access_token_json, true);
//    $url="https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token_json["access_token"]."&openid=".$access_token_json["openid"]."&lang=zh_CN";
//    $user_token_json = file_get_contents($url);
//    return json_decode($user_token_json, true);

}
function https_request($url)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);
// if (curl_errno($curl)) {return 'ERROR ';}
    if (curl_errno($curl)) {return false; }
    curl_close($curl);
    return $data;
}
function makeUid(){
    global $C;
    $unique = uniqid("yiche",true).microtime();
    $u[0] = md5($unique);
    $u[1] = md5($C->key.$u[0]);
//setcookie("uid",$u[0],time()+3600);
//setcookie("sign",$u[1],time()+3600);
    $_SESSION['_uid']=$u[0];
    $_SESSION['_sign']=$u[1];
    return $u;
}
function pic_url($tmp){
    global $C;
    $upyun = new upyun($C->youpai_name, $C->youpai_id, $C->youpai_pw);
    $thumbname = time() + rand(1000, 999999);
    $fn = '/'.$thumbname.'.jpg';
    $fh = fopen($tmp, 'r');
    $ret = $upyun->writeFile($fn, $fh, true);
    $url=$C->up_url.$fn;
    return $url;
}

/**
 * 合并图片
 * @param $image1   底层图片
 * @param $image2   顶层图片
 * @param $filename  输出名字
 * @param $w        合成宽度
 * @param $h        合成高度
 * @param $dx       底层图片X位置
 * @param $dy       底层图片Y位置
 * @return mixed   输出文件名
 */
function combine_image($image1,$image2,$filename,$w,$h,$dx,$dy)//$image1底层图片  $image2合并图片 $c合并X坐标 $d合并Y坐标
{

    $image = imagecreatetruecolor( $w,$h );
    imagesavealpha($image, true);
    $color = imagecolorallocatealpha($image, 255, 255, 255,0);//黑色0 0 0 白色  255 255 255 透明度0-127
    imagefill($image, 0, 0, $color);

    $imgInfo=getimagesize($image1);
    $width=$imgInfo["0"];
    $height=$imgInfo["1"];
    $img1 = imagecreatefrompng($image1);
    imagecopy($image, $img1,$dx ,$dy ,0, 0,$width,$height);
    //imagepng($image,'D:/WWW/vliang/app83/zpimage/'.'img1'.'.png');
    $imgInfo=getimagesize($image2);
    $width=$imgInfo["0"];
    $height=$imgInfo["1"];
    $img2 = imagecreatefrompng($image2);
    imagecopy($image, $img2,0 ,0 ,0, 0,$width,$height);
    imagepng($image,$filename);
    imagedestroy($image);
    imagedestroy($img1);
    imagedestroy($img2);
    chmod($filename,0777);
    return $filename;
}

function resizeImage($image,$width,$height,$nw,$nh) {

    $newImage = imagecreatetruecolor( $nw,$nh );
    imagesavealpha($newImage, true);
    $color = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
    imagefill($newImage, 0, 0, $color);
    $source=imagecreatefrompng($image);
    imagecopyresampled($newImage,$source,0,0,0,0,$nw,$nh,$width,$height);
    imagepng($newImage,$image);
    chmod($image, 0777);
    return $image;
}

/**
 * 拆切图片及缩放和放大
 * @param $src  原图字符串
 * @param $s_w  原图开始位置
 * @param $s_h  原图结束位置
 * @param $w    拆切宽度
 * @param $h    拆切高度
 * @param $dst  输入图片文件名字
 * @param $d_w  输出宽度
 * @param $d_h  输出高度
 */
function resizeThumbImage($src, $s_w, $s_h,$w, $h, $dst,$d_w,$d_h){
    $newImage = imagecreatetruecolor($d_w,$d_h);
    imagesavealpha($newImage, true);
    $color = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
    imagefill($newImage, 0, 0, $color);
    $source = imagecreatefromstring($src);
    imagecopyresampled($newImage,$source,0,0,$s_w,$s_h,$d_w,$d_h,$w,$h);
    imagepng($newImage,$dst);
    chmod($dst, 0777);
    return $dst;
}

function resizeThumbnailImage($thumb_image_name, $image, $width, $height, $start_width, $start_height){
    list($imagewidth, $imageheight, $imageType) = getimagesize($image);
    $imageType = image_type_to_mime_type($imageType);
    $newImage = imagecreatetruecolor($width,$height);
    imagesavealpha($newImage, true);
    $color = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
    imagefill($newImage, 0, 0, $color);
    switch($imageType) {
        case "image/gif":
            $source=imagecreatefromgif($image);
            break;
        case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
            $source=imagecreatefromjpeg($image);
            break;
        case "image/png":
        case "image/x-png":
            $source=imagecreatefrompng($image);
            break;
    }
    imagecopyresampled($newImage,$source,0,0,$start_width,$start_height,$width,$height,$width,$height);
    switch($imageType) {
        case "image/gif":
            imagegif($newImage,$thumb_image_name);
            break;
        case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
            imagejpeg($newImage,$thumb_image_name,90);
            break;
        case "image/png":
        case "image/x-png":
            imagepng($newImage,$thumb_image_name);
            break;
    }
    chmod($thumb_image_name, 0777);
    //unlink($image);
    return $thumb_image_name;
}