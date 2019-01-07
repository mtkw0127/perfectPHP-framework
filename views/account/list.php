<?php $this->setLayoutVar('title', 'アカウント') ?>

<h2>Mini-blogユーザ一覧</h2>
<?php foreach($users as $user):?>
<div>
    <a href="<?php echo $base_url?>/user/<?php echo $user['user_name']?>">
        <?php echo $this->escape($user['user_name']); ?>
    </a>
</div>
<?php endforeach;?>