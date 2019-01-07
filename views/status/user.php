<?php $this->setLayoutVar('title', $user['user_name']);?>

<h2>
    ユーザ名：
    <?php echo $this->escape($user['user_name']);?>
</h2>

<?php if(!is_null($following)): ?>
<?php if($following):?>
<form action="<?php echo $base_url?>/unfollow" method="POST">
    <input type="hidden" name="_token" value="<?php echo $this->escape($_token);?>">
    <input type="hidden" name="following_name" value="<?php echo $this->escape($user['user_name']); ?>">
    <button name="user" value="<?php echo $user['id']?>">フォロー解除</button>
</form>
<?php else:?>
<form action="<?php echo $base_url;?>/follow" method="POST">
    <input type="hidden" name="_token" value="<?php echo $this->escape($_token); ?>">
    <input type="hidden" name="following_name" value="<?php echo $this->escape($user['user_name']); ?>">
    <button type="submit">フォローする</button>
</form>
<?php endif;?>
<?php endif;?>

<h2>今までの投稿</h2>

<div id="statuses">
    <?php foreach($statuses as $status): ?>
    <?php echo $this->render('status/status', array('status' => $status));?>
    <?php endforeach;?>
</div>