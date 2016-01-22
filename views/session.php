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
                <a class="fancybox" rel="group" href="http://rnt.test.shot.x340.org/images/<?=$image->gif?>"><img src="http://rnt.test.shot.x340.org/images/<?=$image->thumb?>"></a>
              </div>
            </div>
          </div>
        <?endforeach;?>
      </div>

    <?endif;?>

    <br><br>

    <canvas id="canvas" height="450" width="600"></canvas>
    
    <script>
      var randomScalingFactor = function(){ return Math.round(Math.random()*100)};
      var lineChartData = {
        labels : [<?=implode(', ', array_values($beats)); ?>],
        datasets : [
          {
            label: "My First dataset",
            fillColor : "rgba(220,220,220,0.2)",
            strokeColor : "rgba(220,220,220,1)",
            pointColor : "rgba(220,220,220,1)",
            pointStrokeColor : "#fff",
            pointHighlightFill : "#fc0000",
            pointHighlightStroke : "rgba(220,220,220,1)",
            data : [<?=implode(', ', array_values($beats)); ?>]
          },
          {
            label: "My Second dataset",
            fillColor : "rgba(151,187,205,0.2)",
            strokeColor : "rgba(151,187,205,1)",
            pointColor : "rgba(151,187,205,1)",
            pointStrokeColor : "#fff",
            pointHighlightFill : "#fff",
            pointHighlightStroke : "rgba(151,187,205,1)",
            data : [<?=implode(', ', array_values($peaks)); ?>]

          }
        ]
      }
      window.onload = function(){
        var ctx = document.getElementById("canvas").getContext("2d");
        window.myLine = new Chart(ctx).Line(lineChartData, {
          responsive: true
        });
      }
    </script>

    <br><br>

  </div>
</div>
