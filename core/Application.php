<?php

abstract class Application{
    protected $debug = false;
    protected $request;
    protected $response;
    protected $session;
    protected $db_manager;
    protected $login_action = array();

    public function __construct ($debug = false) {
        $this->setDebugMode($debug);
        $this->initialize();
        $this->configure();
    }

    protected function setDebugMode ($debug)
    {
        if($debug){
            $this->debug=true;
            ini_set('display_errors', 1);
            error_reporting(-1);
        } else {
            $this->debug = false;
            ini_set('display_errors', 0);
        }
    }

    protected function initialize()
    {
        // シングルトンに変えてみよう
        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->db_manager = new DbManager();
        $this->router = new Router($this->registerRoutes());
    }

    protected function configure()
    {

    }

    abstract public function getRootDir();

    abstract protected function registerRoutes();

    public function isDebugMode ()
    {
        return $this->debug;
    }

    public function getRequest ()
    {
        return $this->request;
    }

    public function getResponse ()
    {
        return $this->response;
    }

    public function getSession ()
    {
        return $this->session;
    }

    public function getDbManager ()
    {
        return $this->db_manager;
    }

    public function getControllerDir ()
    {
        return $this->getRootDir() . '/controllers';
    }

    public function getViewDir ()
    {
        return $this->getRootDir() . '/views';
    }

    public function getModelDir ()
    {
        return $this->getRootDir() . '/models';
    }

    /**
     * リクエストのパスからコントローラとアクションを特定し、実行する
     *
     * @return Null
     */
    public function run ()
    {
        try {
            $params = $this->router->resolve($this->request->getPathInfo());
            if($params === false) {
                // todo-A
                throw new HttpNotFoundException('HttpNotFoundException：No route found for　' . $this->request->getPathInfo());
            }
            $controller = $params['controller'];
            $action = $params['action'];

            error_log("[". date('Y-m-d H:i:s') . "]". "AT Application run コントローラとアクションも特定したし、実行するか！" ."\n", 3, ERROR_LOG_PATH);

            $this->runAction($controller, $action, $params);
        } catch (HttpNotFoundException $e) {
            $this->render404Page($e);
        } catch (UnauthrizedActionException $e) {
            list($controller, $action) = $this->login_action;
            $this->runAction($controller, $action);
        }

        // echoでhttpレスポンスと描画
        $this->response->send();
    }

    /**
     * コントローラのアクションを実行する
     *
     * @return Null
     */
    public function runAction ($controller_name, $action, $params = array())
    {
        // 先頭の文字を大文字に変換
        $controller_class = ucfirst($controller_name) . 'Controller';

        $controller = $this->findController($controller_class);
        if($controller === false) {
            // todo-B
            throw new HttpNotFoundException($controller_class . 'controller in not found.');
        }

        // コントローラクラスのrun
        error_log("[". date('Y-m-d H:i:s') . "]". "AT Application runAction.". "contrlller：" . $controller_class ."\n", 3, ERROR_LOG_PATH);
        error_log("[". date('Y-m-d H:i:s') . "]". "AT Application runAction.". "action：" . $action ."\n", 3, ERROR_LOG_PATH);

        $content = $controller->run($action, $params);

        $this->response->setContent($content);
    }

    /**
     * コントローラに当たるファイルを特定する
     *
     * @return Object Controllerのオブジェクト
     */
    protected function findController ($controller_class)
    {
        if (!class_exists($controller_class)) {
            $controller_file = $this->getControllerDir() . '/' . $controller_class . '.php';

            if(!is_readable($controller_file)) {
                return false;
            } else {
                require_once $controller_file;

                if(!class_exists($controller_class)) {
                    return false;
                }
            }
        }

        return new $controller_class($this);
    }

    /**
     * 404Pageを出力する
     *
     * @return 404Page
     */
    protected function render404Page($e)
    {
        $this->response->setStatusCode(404, 'Not Found');
        $message = $this->isDebugMode() ? $e->getMessage() : 'Page not found.';
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        $this->response->setContent(<<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>404</title>
</head>
<body>
        {$message}
</body>
</html>
EOF
        );
    }
}