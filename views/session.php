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
      <a href="/session/finish/" id="download-button" class="btn-large waves-effect waves-light red">Остановить</a>
    <?else:?>
      <h3>Moments:</h3>
      <div class="row">
        <div class="col s12 m4">
          <div class="card small card-img">
            <div class="card-image">
              <a class="fancybox" rel="group" href="http://www.wareable.com/media/images/2014/10/aaaafortheawesome-1412334292-8otB-column-width-inline.jpg"><img src="http://www.wareable.com/media/images/2014/10/aaaafortheawesome-1412334292-8otB-column-width-inline.jpg"></a>
            </div>
          </div>
        </div>
        <div class="col s12 m4">
          <div class="card small card-img">
            <div class="card-image">
              <a class="fancybox" rel="group" href="http://www.wareable.com/media/images/2014/10/aaaafortheawesome-1412334292-8otB-column-width-inline.jpg"><img src="http://www.wareable.com/media/images/2014/10/windlands-oculus-rift-1-1412334393-0NUf-column-width-inline.jpg"></a>
            </div>
          </div>
        </div>
        <div class="col s12 m4">
          <div class="card small card-img">
            <div class="card-image">
              <a class="fancybox" rel="group" href="http://www.wareable.com/media/images/2014/10/aaaafortheawesome-1412334292-8otB-column-width-inline.jpg"><img src="http://www.wareable.com/media/images/2014/10/vanguard-v-4-1-1412334418-pFCs-column-width-inline.jpg"></a>
            </div>
          </div>
        </div>
        <div class="col s12 m4">
          <div class="card small card-img">
            <div class="card-image">
              <a class="fancybox" rel="group" href="http://www.wareable.com/media/images/2014/10/aaaafortheawesome-1412334292-8otB-column-width-inline.jpg"><img src="http://www.wareable.com/media/images/2014/10/aaaafortheawesome-1412334292-8otB-column-width-inline.jpg"></a>
            </div>
          </div>
        </div>
      </div>
    <?endif;?>
    <br><br>
  </div>
</div>
