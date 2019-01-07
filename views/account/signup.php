<?php $this->setLayoutVar('title', 'アカウント登録') ?>

<h2>アカウント登録</h2>
<form action="//mini-blog.test/index_dev.php/account/register" method="POST">
    <input type="hidden" name="_token" value="<?php echo $this->escape($_token);?>" />

    <?php if(isset($errors) && count($errors) > 0):?>
    <?php echo $this->render('errors', array('errors' => $erros));?>
    <?php endif;?>
    <?php echo $this->render('account/inputs', array(
        'user_name' => $user_name,
        'password' => $password,
    ))?>
    <p>
        <button type="submit">登録</button>
    </p>
</form>