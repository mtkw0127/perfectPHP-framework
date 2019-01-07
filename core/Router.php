<?php 

class Router
{
    protected $routes;

    public function __construct($definitions)
    {
        $this->routes = $this->compileRoutes($definitions);
    }
    // $definitionsはLaravelで言うweb.phpで定義されたもの
    // { URL => {Controller => method} }
    // と言う定義になっている
    public function compileRoutes($definitions)
    {
        $routes = array();
        foreach($definitions as $url => $params) {
            // ltrimでurlの左端の「/」を削除
            // その後「/」で分割
            // /controller/test/hoge => controller/test/hoge => [controller, test, hoge]
            $tokens = explode('/', ltrim($url, '/'));
            foreach($tokens as $i => $token) {
                // :が左端にあれば
                if(0 === strpos($token, ':')) {
                    // 1文字目以降を抜き出す
                    $name = substr($token, 1);
                    // 正規表現が利用できる形に変形
                    $token = "(?P<" . $name . ">[^/]+)";
                }
                $tokens[$i] = $token;
            }

            $pattern = '/' . implode('/', $tokens);

            $routes[$pattern] = $params;
        }
        return $routes;
    }

    public function resolve($path_info)
    {
        // 先頭の１文字目を取ってきて「/」で無けれあば「/」をつける
        if('/' !== substr($path_info, 0, 1)) {
            $path_info = '/' . $path_info;
        }

        // マッチするルートがないか探す
        foreach($this->routes as $pattern => $params) {
            if(preg_match('#^' . $pattern . '$#', $path_info, $matches)) {
                $params = array_merge($params, $matches);
                error_log("[". date('Y-m-d H:i:s') . "]". "マッチできた。" ."\n", 3, ERROR_LOG_PATH);
                return $params;
            }
        }

        error_log("[". date('Y-m-d H:i:s') . "]". "マッチしなかった。" ."\n", 3, ERROR_LOG_PATH);

        return false;
    }
}

?>