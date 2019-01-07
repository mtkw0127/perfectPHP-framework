<?php
class DbManager
{
    // key: DBを特定するキー value: PDO Object
    protected $connections = array();
    // key: リポジトリ名 value: DBを特定するキー
    protected $repository_connection_map = array();
    // key: リポジトリ名 value: Repository Object
    protected $repositories = array();
    /**
     *  PDOを利用しデータベースに接続し
     *  その接続状態を$connectionで管理するため
     * @param Number $name 接続を特定するためのキー
     * @param Array  $prams PDOの接続条件
     */

    public function __construct() {
        $t = array();
        $t['dns'] = DB_TYPE . 'dbname=' . DB_NAME. ';host=' . DB_HOST;
        $t['user'] = DB_USER;
        $t['password'] = DB_PASSWORD;
        $t['options'] = '';
        $this->connect('User', $t);
    }

    public function connect($name, $params)
    {
        $params = array_merge(array(
            'dns' => null,
            'user' => '',
            'password' => '',
            'options' => array(),
        ), $params);

        try {
            $con = new PDO(
                $params['dns'],
                $params['user'],
                $params['password']
            );

            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->connections[$name] = $con;

        } catch (PDOException $e) {
            error_log("[". date('Y-m-d H:i:s') . "]". 'DB接続失敗' ."\n", 3, ERROR_LOG_PATH);
        }

    }
    /**
     * 接続状態を取得する
     * @param String $name DBとの接続状態($connection)を特定するためのキー
     * @return PDOObject
     *  接続が特定できた場合、その接続状態
     *  接続が特定できない場合、先頭の接続状態
     */
    public function getConnection($name = null)
    {
        if(is_null($name)) {
            // 最初の接続を使用
            return current($this->connections);
        }

        return $this->connections[$name];
    }
    /**
     * リポジトリとDBを対応つける
     * @param Array  $repository_name リポジトリ名。そのリポジトリが所属するPDOObjectに対応したキーへアクセスする。
     * @param String $name 接続を特定するためのキー
     */
    public function setRepositoryConnectionMap($repository_name, $name)
    {
        $this->repository_connection_map[$repository_name] = $name;
    }
    /**
     * リポジトリ名からPDOインスタンスを取得する
     * @param String リポジトリ名
     * @return PDOObject リポジトリ名に対応したPDOオブジェクトを取得する
     *                   対応したObjectがなければ先頭のオブジェクトを返す
     */
    public function getConnectionForRepository($repository_name)
    {
        if(!empty($this->repository_connection_map[$repository_name])){
            $name = $this->repository_connection_map[$repository_name];
            $con = $this->getConnection($name);
        } else {
            $con = $this->getConnection();
        }


        return $con;
    }

    /**
     * リポジトリインスタンスの取得
     * 存在しない場合はインスタンスを生成
     * @param String $repository_name リポジトリ名
     * @return Object Repository Object
     */
    public function get($repository_name)
    {
        if(!isset($this->repositories[$repository_name]))
        {
            $repository_class = $repository_name . 'Repository';
            $con = $this->getConnectionForRepository($repository_name);
            $repository = new $repository_class($con);

            $this->repositories[$repository_name] = $repository;
        }


        return $this->repositories[$repository_name];
    }

    public function __destruct()
    {
        /**
         * Repositoryクラス内でPDOを保持しているため
         * $connectionsを破棄する前にRepositoryを破棄
         */
        foreach($this->repositories as $repository)
        {
            unset($repository);
        }

        foreach ($this->connections as $con) {
            unset($con);
        }

        unset($repository_connection_map);
    }
}
?>