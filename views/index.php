

<div class="section no-pad-bot" id="index-banner">
  <div class="container container-video">
    <div class="video-bg-wrap">
      <div class="video-overlay"></div>
      <video class="video-bg lazy-hidden" loop="" autoplay="" data-ipad="false" data-apple-source="http://rfcdn.com/retrofuzz/ipad/header-final-ipad-201502161045.mp4" poster="http://retrofuzz.com/assets/images/video-stills/intro_for_ipad.jpg" style="background-image: url(http://retrofuzz.com/undefined);">
        <source data-src="http://rfcdn.com/retrofuzz/header-v5-h264-2.mp4" type="video/mp4" src="http://rfcdn.com/retrofuzz/header-v5-h264-2.mp4">
        <source data-src="http://rfcdn.com/retrofuzz/header-v5-ogg-1.ogg" type="video/ogg" src="http://rfcdn.com/retrofuzz/header-v5-ogg-1.ogg">
      </video>
    </div>
    <div class="start">
      <h1 class="header center white-text">The moment</h1>
      <div class="row center">
        <h5 class="header col s12 light white-text">Sometimes it just takes a moment to forget a life, <br> but sometimes life is not enough to forget for a moment.</h5>
      </div>
      <div class="row center">
        <a href="/session/start/" id="download-button" class="btn-large waves-effect waves-light red">Start</a>
      </div>
    </div>
  </div>
</div>


<div class="container">
  <div class="section">
  <br><br>
    <!--   Icon Section   -->
    <div class="row">
      <?php foreach($data as $session): ?>
          <div class="col s12 m4">
              <div class="card small">
                  <div class="card-image">
                      <a href="/session/<?= $session->id?>"><img src="http://rnt.test.shot.x340.org/images/<?= $session->thumb ?>" height="180"></a>
                  </div>
                  <div class="card-content">
                    <p><b>Session #<?=$session->id?></b>: <?=date('d M H:i',strtotime($session->start))?></p> 
                  </div>
                  <div class="card-action">
                      <a href="/session/<?= $session->id?>">Посмотреть</a>
                  </div>
              </div>
          </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
