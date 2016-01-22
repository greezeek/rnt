<div class="container">
  <div class="section">
    
    <h1>Cессия #<?=$session->id?></h1>
    <div>
      Начало: <b><?=date('d.m.Y H:i:s',strtotime($session->start))?></b>
    </div>
    <div>
      Cтатус: <b><?=($session->end?'завершена':'активна')?></b>
    </div>
    <?if (!$session->end):?>
      <br><br>
      <a href="/session/finish/" id="download-button" class="btn-large waves-effect waves-light red">Остановить</a>
    <?else:?>  
      
      
    <?endif;?>
    
  </div>
</div>
