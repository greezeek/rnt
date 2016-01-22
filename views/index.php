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
        <h5 class="header col s12 light white-text">Sometimes it just takes a moment to forget a life, but sometimes life is not enough to forget for a moment.</h5>
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
                      <img src="http://rnt.test.shot.x340.org/images/<?= $session->thumb ?>">
                      <span class="card-title"><?= $session->name ?></span>
                  </div>
                  <div class="card-content">
                      <p><?= $session->name ?></p>
                  </div>
                  <div class="card-action">
                      <a href="#">Посмотреть</a>
                  </div>
              </div>
          </div>
      <?php endforeach; ?>
        
<!--      <div class="col s12 m4">-->
<!--        <div class="card small">-->
<!--            <div class="card-image">-->
<!--              <img src="http://www.wareable.com/media/images/2014/10/aaaafortheawesome-1412334292-8otB-column-width-inline.jpg">-->
<!--              <span class="card-title">Качели</span>-->
<!--            </div>-->
<!--            <div class="card-content">-->
<!--              <p>Иван Иванов</p>-->
<!--            </div>-->
<!--            <div class="card-action">-->
<!--              <a href="#">Посмотреть</a>-->
<!--            </div>-->
<!--          </div>-->
<!--      </div>-->
<!--      <div class="col s12 m4">-->
<!--        <div class="card small">-->
<!--            <div class="card-image">-->
<!--              <img src="http://www.wareable.com/media/images/2014/10/windlands-oculus-rift-1-1412334393-0NUf-column-width-inline.jpg">-->
<!--              <span class="card-title">Хоррор</span>-->
<!--            </div>-->
<!--            <div class="card-content">-->
<!--              <p>Петр Петров</p>-->
<!--            </div>-->
<!--            <div class="card-action">-->
<!--              <a href="#">Посмотреть</a>-->
<!--            </div>-->
<!--          </div>-->
<!--      </div>-->
<!--      <div class="col s12 m4">-->
<!--        <div class="card small">-->
<!--            <div class="card-image">-->
<!--              <img src="http://www.wareable.com/media/images/2014/10/vanguard-v-4-1-1412334418-pFCs-column-width-inline.jpg">-->
<!--              <span class="card-title">Полет</span>-->
<!--            </div>-->
<!--            <div class="card-content">-->
<!--              <p>Константин Константинопольский</p>-->
<!--            </div>-->
<!--            <div class="card-action">-->
<!--              <a href="#">Посмотреть</a>-->
<!--            </div>-->
<!--          </div>-->
<!--      </div>-->
    </div>
  </div>
</div>
