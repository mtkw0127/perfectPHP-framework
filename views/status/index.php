<?php $this->setLayoutVar('title', 'ホーム')?>

<h2>ホーム</h2>

<form action="<?php echo $base_url?>/status/post" method="POST">
    <input type="hidden" name="_token" value="<?php echo $this->escape($_token); ?>">

    <?php if(isset($errors) && count($errors) > 0):?>
    <?php echo $this->render('errors', array('errors' => $erros));?>
    <?php endif;?>

    <textarea name="body" cols="60" rows="2"><?php echo $this->escape($body);?></textarea>

    <p>
        <input type="submit" value="発言">
    </p>
</form>

<div id="statuses">
    <h2>タイムライン（あなたの投稿＋フォローユーザの投稿）</h2>
    <?php foreach($statuses as $status):?>
    <?php echo $this->render('status/status', array(
            'status' => $status,
        ));?>
    <?php endforeach;?>
</div>