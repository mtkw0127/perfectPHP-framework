<?php
class AccountController extends Controller
{
    protected $auth_action = array('index', 'signout');

    public function indexAction() {
        $user = $this->session->get('user');
        $followings = $this->db_manager->get('User')
                            ->fetchAllFollowingsByUserId($user['id']);


        return $this->render(array(
            'user' => $user,
            'followings' => $followings
        ));
    }

    public function listAction() {
        $users = $this->db_manager->get('User')->fetchAllUsers();
        return $this->render(array(
            'users' => $users
        ));
    }

    public function signinAction() {
        if($this->session->isAuthenticated()){
            return $this->redirect('/account');
        }

        return $this->render(array(
            'user_name' => '',
            'password'  => '',
            '_token'    => $this->generateCsrfToken('account/signin'),
        ));
    }

    public function signupAction()
    {
        return $this->render(array(
            'user_name' => '',
            'password' => '',
            '_token' => $this->generateCsrfToken('account/signup')
        ));
    }

    public function authenticateAction() {
        if($this->session->isAuthenticated()){
            return $this->redirect('/account');
        }

        if(!$this->request->isPost()){
            return $this->forward404();
        }

        $token = $this->request->getPost('_token');
        if(!$this->checkCsrfToken('account/signin', $token)){
            return $this->redirect('/account/signin');
        }

        $user_name = $this->request->getPost('user_name');
        $password  = $this->request->getPost('password');

        $errors = array();

        if(!strlen($user_name)) {
            $errors[] = 'ユーザIDを入力してください';
        }

        if(!strlen($password)) {
            $errors[] = 'パスワードを入力して下さい';
        }

        if(count($errors) === 0) {
            $user_repository = $this->db_manager->get('User');
            $user = $user_repository->fetchByUserName($user_name);
            if(!$user
            || ($user['password'] !== $user_repository->hashPassword($password)))
            {
                $errors[] = 'ユーザIDかパスワードが不正です';
            }else{
                $this->session->setAuthenticated(true);
                $this->session->set('user', $user);

                return $this->redirect('/');
            }
        }

        return $this->render(array(
            'user_name' => $user_name,
            'password'  => $password,
            'errors'    => $errors,
            '_token'    => $this->generateCsrfToken('account/signin'),
        ), 'signin');
    }

    public function signoutAction() {
        $this->session->clear();
        $this->session->setAuthenticated(false);

        return $this->redirect('/account/signin');
    }

    public function registerAction() {

        error_log("[". date('Y-m-d H:i:s') . "]". "AT Application AccountController registerAction.". "来ました！" ."\n", 3, ERROR_LOG_PATH);

        if(!$this->request->isPost()){
            error_log("[". date('Y-m-d H:i:s') . "]". "AT Application AccountController registerAction. ". "Not Post！！" ."\n", 3, ERROR_LOG_PATH);
            $this->forward404();
        }

        $token = $this->request->getPost('_token');
        error_log("[". date('Y-m-d H:i:s') . "] token =>". $token ."\n", 3, ERROR_LOG_PATH);

        if(!$this->checkCsrfToken('account/signup', $token)){
            error_log("[". date('Y-m-d H:i:s') . "]". "AT Application AccountController registerAction. ". "Not CSRF！！" ."\n", 3, ERROR_LOG_PATH);
            return $this->redirect('/account/signup');
        }

        $user_name = $this->request->getPost('user_name');
        $password = $this->request->getPost('password');

        $errors = array();

        if (!strlen($user_name)){
            $errors[] = 'ユーザIDを入力してください';
        } else if (!preg_match('/^\w{3,20}$/', $user_name)) {
            $errors[] = 'ユーザIDは半角英数字およびアンダースコアを3〜20文字以内で入力してください';
        } else if (!$this->db_manager->get('User')->isUniqueUserName($user_name)) {
            $errors[] = 'ユーザIDは既に使用されています';
        }

        if (!strlen($password)) {
            $errors[] = 'パスワードを入力してください';
        } else if (4 > strlen($password) || strlen($password) > 30) {
            $errors[] = 'パスワードは4から30文字以内で入力してください';
        }

        if(count($errors) === 0){
            $this->db_manager->get('User')->insert($user_name, $password);
            error_log("[". date('Y-m-d H:i:s') . "]". 'セッション開始' ."\n", 3, ERROR_LOG_PATH);
            $this->session->setAuthenticated($true);
            error_log("[". date('Y-m-d H:i:s') . "]". 'セッションok' ."\n", 3, ERROR_LOG_PATH);

            error_log("[". date('Y-m-d H:i:s') . "]". 'フェッチネーム開始' ."\n", 3, ERROR_LOG_PATH);
            $user = $this->db_manager->get('User')->fetchByUserName($user_name);
            error_log("[". date('Y-m-d H:i:s') . "]". 'フェッチネームok：'. $user_name ."\n", 3, ERROR_LOG_PATH);

            $this->session->set('user', $user);

            return $this->redirect('/');
        }

        return $this->render(array(
            'base_url'  => $this->request->getBaseUrl(),
            'user_name' => $user_name,
            'password'  => $password,
            'errors'    => $errors,
            '_token'    => $this->generateCsrfToken('account/signup'),
        ), 'signup');
    }

    public function followAction() {
        if(!$this->request->isPost()){
            $this->forward404();
        }

        $followingName = $this->request->getPost('following_name');
        if(!$followingName) {
            $this->forward404();
        }

        $token = $this->request->getPost('_token');
        if(!$this->checkCsrfToken('account/follow', $token)) {
            return $this->redirect('/user/'. $followingName);
        }

        $follow_user = $this->db_manager->get('User')->fetchByUserName($followingName);
        if(!$follow_user) {
            $this->forward404();
        }

        $user = $this->session->get('user');

        $follow_repository = $this->db_manager->get('Following');
        if($user['id'] !== $follow_user['id']
            && !$follow_repository->isFollowing($user['id'], $follow_user['id']))
        {
            $follow_repository->insert($user['id'], $follow_user['id']);
        }

        return $this->redirect('/user/'. $followingName);
    }

    public function unfollowAction() {

        if(!$this->request->isPost()) {
            return $this->redirect('/account');
        }

        $token = $this->request->getPost('_token');

        if(!$this->checkCsrfToken('account/follow', $token)) {
            return $this->redirect('/account');
        }

        $followingID = $this->request->getPost('user');
        $followingName = $this->request->getPost('following_name');
        $follow_repository = $this->db_manager->get('Following');

        $user = $this->session->get('user');
        $result = $follow_repository->unfollow($user['id'], $followingID);

        return $this->redirect('/user/'. $followingName);
    }
}