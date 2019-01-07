<?php

abstract class Controller
{
    protected $controller_name;
    protected $action_name;
    protected $application;
    protected $request;
    protected $response;
    protected $session;
    protected $db_manager;
    protected $auth_actions = array();

    public function __construct($application)
    {
        $this->controller_name = strtolower(substr(get_class($this), 0, -10));

        $this->application = $application;
        $this->request     = $application->getRequest();
        $this->response    = $application->getResponse();
        $this->session     = $application->getSession();
        $this->db_manager  = $application->getDbManager();
    }

    public function run($action, $params = array())
    {
        $this->action_name = $action;

        $action_method = $action . 'Action';

        error_log("[". date('Y-m-d H:i:s') . "]". "AT Controller run.". "実行するメソッドは：" . $action_method ."\n", 3, ERROR_LOG_PATH);

        if(!method_exists($this, $action_method)) {
            $this->forward404();
        }

        if($this->needsAuthentication($action) && !$this->session->isAuthenticated()){
            throw new UnauthrizedActionException();
        }

        // コントローラに記述したメソッドを実行
        // このクラスのrenderの戻り値が返ってくる
        $content = $this->$action_method($params);

        return $content;
    }

    protected function needsAuthentication($action)
    {
        if($this->auth_actions === true ||
            (is_array($this->auth_actions) && in_array($action, $this->auth_actions)))
        {
            return true;
        }

        return false;
    }

    /**
     * ビューファイルを読み込みレンダリングする
     *
     * @param Something ビューで描画したい変数たち
     * @return Contents 描画する内容
     */
    protected function render($variables = array(), $template = null, $layout = 'layout') {
        $defaults = array(
            'url_not_slash' => $this->request->getRequestUriForView(),
            'request' => $this->request,
            'base_url' => $this->request->getBaseUrl(),
            'session' => $this->session,
        );

        $view = new View($this->application->getViewDir(), $defaults);

        if(is_null($template)) {
            $template = $this->action_name;
        }

        $path = $this->controller_name . '/' . $template;

        return $view->render($path, $variables, $layout);
    }

    /**
     * 404エラー画面へリダイレクトする
     */
    protected function forward404()
    {
        throw new HttpNotFoundException('Forwarded 404 page from ' . $this->controller_name . '/' . $this->action_name);
    }

    /**
     * 任意のURLへリダイレクトする
     */
    protected function redirect($url)
    {
        if(!preg_match('#https?://#', $url)){
            error_log("[". date('Y-m-d H:i:s') . "]". '正規表現NO'. $url ."\n", 3, ERROR_LOG_PATH);

            $protocol = $this->request->isSsl() ? 'https://' : 'http://';
            $host = $this->request->getHost();
            $base_url = $this->request->getBaseUrl();

            error_log("[". date('Y-m-d H:i:s') . "]". 'protocol=> '. $protocol ."\n", 3, ERROR_LOG_PATH);
            error_log("[". date('Y-m-d H:i:s') . "]". 'hosts => '. $host ."\n", 3, ERROR_LOG_PATH);
            error_log("[". date('Y-m-d H:i:s') . "]". 'baseurl => '. $base_url ."\n", 3, ERROR_LOG_PATH);


            $url = $protocol . $host . $base_url . $url;
            error_log("[". date('Y-m-d H:i:s') . "]". '正規表現NO => '. $url ."\n", 3, ERROR_LOG_PATH);

        }else{
            error_log("[". date('Y-m-d H:i:s') . "]". '正規表現OK'. $user_name ."\n", 3, ERROR_LOG_PATH);

        }

        $this->response->setStatusCode(302, 'Found');
        $this->response->setHttpHeader('Location', $url);
    }

    /**
     * トークンの生成し、セッションへ格納
     */
    protected function generateCsrfToken($form_name)
    {
        $key = 'csrf_tokens/' . $form_name;
        error_log("[". date('Y-m-d H:i:s') . "]". "AT Controller generateCsrfToken. key => ". $key ."\n", 3, ERROR_LOG_PATH);
        $tokens = $this->session->get($key, array());
        if(count($tokens) >= 10) {
            array_shift($tokens);
        }

        $token = sha1($form_name . session_id() . microtime());
        $tokens[] = $token;

        $this->session->set($key, $tokens);

        return $token;
    }

    /**
     * セッションのトークンとの比較と、トークンを削除
     */
    protected function checkCsrfToken($form_name, $token)
    {
        $key = 'csrf_tokens/' . $form_name;
        $tokens = $this->session->get($key, array());

        error_log("[". date('Y-m-d H:i:s') . "]". "AT Controller checkCsrfToken. token => ". $token ."\n", 3, ERROR_LOG_PATH);
        error_log("[". date('Y-m-d H:i:s') . "]". "AT Controller checkCsrfToken. tokens => ". $tokens ."\n", 3, ERROR_LOG_PATH);
        error_log("[". date('Y-m-d H:i:s') . "]". "AT Controller checkCsrfToken. key => ". $key ."\n", 3, ERROR_LOG_PATH);

        if(false !== ($pos = array_search($token, $tokens, true))) {
            unset($tokens[$pos]);
            $this->session->set($key, $tokens);
            return true;
        }

        return false;
    }
}