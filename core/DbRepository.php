<?php

class DbRepository
{
    // PDO Object
    protected $con;

    public function __construct($con)
    {
        $this->setConnection($con);
    }

    public function setConnection($con)
    {
        $this->con = $con;
    }

    /**
     * SQL文の実行
     * @param String $sql SQL文
     * @param Array  $params プリペアドステートメントに格納する変数
     * @return Object Prepared Statement Object
     */
    public function execute($sql, $params = array()) {

        $stmt = $this->con->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    /**
     * 一件の取得
     * @param String $sql SQL文
     * @param Array  $params プリペアドステートメントに格納する変数
     * @return Array 一件の実行結果(連番)
     */
    public function fetch($sql, $params = array()) {
        return $this->execute($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 全件の取得
     * @param String $sql SQL文
     * @param Array  $params プリペアドステートメントに格納する変数
     * @return Array 全件の実行結果(連番)
     */
    public function fetchAll($sql, $params = array()) {
        return $this->execute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 一件の削除
     * @param String $sql SQL文
     * @param Array $params プリペアドステートメントに格納する変数
     * @return bool 削除完了かどうか
     */
    public function delete($sql, $params = array()) {
        return $this->execute($sql, $params);
    }
}

?>