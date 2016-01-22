<div class="container">
  <div class="section">
    <h1>Session #<?=$session->id?></h1>
    <div>
      Start: <b><?=date('d.m.Y H:i:s',strtotime($session->start))?></b>
    </div>
    <?if ($session->end):?>
      End: <b><?=date('d.m.Y H:i:s',strtotime($session->end))?></b>
    <?endif;?>
    <div>
      Cтатус: <b><?=($session->end?'finished':'active')?></b>
    </div>
    <?if (!$session->end):?>
      <br><br>
      <a href="/session/finish/" id="download-button" class="btn-large waves-effect waves-light red">Finish</a>
    <?else:?>
      <h3>Moments: <?=count($images)?></h3>
      <div class="row">
        <?foreach ($images as $image):?>
          <div class="col s12 m4">
            <div class="card small card-img">
              <div class="card-image">
                <a class="fancybox" rel="group" href="http://rnt.test.shot.x340.org/images/<?=$image->gif?>"><i class="material-icons">play_arrow</i><img src="http://rnt.test.shot.x340.org/images/<?=$image->thumb?>"></a>
              </div>
            </div>
          </div>
        <?endforeach;?>
      </div>

    <?endif;?>
    <br><br>
  </div>
</div>
