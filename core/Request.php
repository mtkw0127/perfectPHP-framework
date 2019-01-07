<?php

class Request
{
    public function isPost()
    {
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            return true;
        }

        return false;
    }

    public function getGet($name, $default = null)
    {
        if(isset($_GET[$name])){
            return $_GET[$name];
        }

        return $default;
    }

    public function getPost($name, $default = null)
    {
        error_log("[". date('Y-m-d H:i:s') . "] name =>". $name ."\n", 3, ERROR_LOG_PATH);

        if(!empty($_POST[$name])){
            return $_POST[$name];
        } else {
            error_log("[". date('Y-m-d H:i:s') . "]". $name . "のPOSTはないです" ."\n", 3, ERROR_LOG_PATH);
        }

        return $default;
    }

    public function getHost()
    {
        if(!empty($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }

        return $_SERVER['SERVER_NAME'];
    }

    public function isSsl()
    {
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            return true;
        }

        return false;
    }

    // host以降のURL全て
    public function getRequestUri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    // host以降のURL全て先頭の「/」を削除
    public function getRequestUriForView()
    {
        return ltrim($this->getBaseUrl(), '/');
    }

    public function getBaseUrl()
    {
        $script_name = $_SERVER['SCRIPT_NAME'];

        error_log("[". date('Y-m-d H:i:s') . "]". "AT getBaseUrl => " . $_SERVER['REQUEST_URI'] ."\n", 3, ERROR_LOG_PATH);

        $request_uri = $this->getRequestUri();

        // フロントコントローラがURIに含まれている場合は「test/index.php/list」
        // test/index.phpがベースURLとなるため、$_SERVER['SCRIPT_NAME']=test/index.phpを返す
        if(0 === strpos($request_uri, $script_name)) {
            error_log("[". date('Y-m-d H:i:s') . "]". "条件１" ."\n", 3, ERROR_LOG_PATH);
            return $script_name;
        }
        // フロントコントローラがURIに含まれていない場合は「test/test.php/list」
        // test/がベースURLとなるため、$_SERVER['SCRIPT_NAME']=test/index.phpからtestにする
        else if (0 === strpos($request_uri, dirname($script_name))) {
            error_log("[". date('Y-m-d H:i:s') . "]". "条件２" ."\n", 3, ERROR_LOG_PATH);
            return rtrim(dirname($script_name), '/');
        }

        return '';
    }

    public function getPathInfo()
    {
        $base_url = $this->getBaseUrl();// フロントコントローラまでのURL
        $request_uri = $this->getRequestUri();// host以降のURL全て

        // REQUEST_URIからgetパラメータ部分を除去
        if(false !== ($pos = strpos($request_uri, '?'))){//?の位置を探す
            $request_uri = substr($request_uri, 0, $pos);//?より前を取得
        }

        // request_uri - baseURl = pathinfo
        $path_info = (string)substr($request_uri, strlen($base_url));

        error_log("[". date('Y-m-d H:i:s') . "]". "AT getPathInfo => " . $path_info ."\n", 3, ERROR_LOG_PATH);

        return $path_info;

    }
}

?>