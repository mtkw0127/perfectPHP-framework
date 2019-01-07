<?php
    class Session
    {
        public static $sessionStarted = false;
        protected static $sessionIdRegenerated = false;

        public $attr = 12;

        public function __construct() {
            if (!self::$sessionStarted) {
                session_start();

                self::$sessionStarted = true;
            }
        }

        public function set($name, $value) {
            $_SESSION[$name] = $value;
        }

        public function get($name, $default = null) {
            if(isset($_SESSION[$name])){
                return $_SESSION[$name];
            }
            return $default;
        }

        public function clear() {
            $_SESSION = array();
        }

        public function regenerate($destroy = true) {
            if(!self::$sessionIdRegenerated) {
                session_regenerate_id($destroy);

                self::$sessionIdRegenerated = true;
            }
        }

        public function setAuthenticated($bool) {
            $this->set('_authenticated', (bool)$bool);

            $this->regenerate();
        }

        public function isAuthenticated() {
            return $this->get('_authenticated', false);
        }

        // public function setSessionStarted() {
        //     self::$sessionStarted = false;
        // }

        // public function getSessionStarted() {
        //     return self::$sessionStarted;
        // }
    }
?>