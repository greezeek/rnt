<div class="container">
    <div class="section">
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
        <!--   Icon Section   -->
        <div class="row">
            <div class="col s12 m4">
                <div class="card small card-img">
                    <div class="card-image">
                        <a class="fancybox" rel="group" href="http://www.wareable.com/media/images/2014/10/aaaafortheawesome-1412334292-8otB-column-width-inline.jpg"><img src="http://www.wareable.com/media/images/2014/10/aaaafortheawesome-1412334292-8otB-column-width-inline.jpg"></a>
                        <span class="card-title">Качели</span>
                    </div>
                </div>
            </div>
            <div class="col s12 m4">
                <div class="card small card-img">
                    <div class="card-image">
                        <a class="fancybox" rel="group" href="http://www.wareable.com/media/images/2014/10/aaaafortheawesome-1412334292-8otB-column-width-inline.jpg"><img src="http://www.wareable.com/media/images/2014/10/windlands-oculus-rift-1-1412334393-0NUf-column-width-inline.jpg"></a>
                        <span class="card-title">Хоррор</span>
                    </div>
                </div>
            </div>
            <div class="col s12 m4">
                <div class="card small card-img">
                    <div class="card-image">
                        <a class="fancybox" rel="group" href="http://www.wareable.com/media/images/2014/10/aaaafortheawesome-1412334292-8otB-column-width-inline.jpg"><img src="http://www.wareable.com/media/images/2014/10/vanguard-v-4-1-1412334418-pFCs-column-width-inline.jpg"></a>
                        <span class="card-title">Полет</span>
                    </div>
                </div>
            </div>
        </div>
        <br><br>
        <a href="#" id="download-button" class="btn-large waves-effect waves-light red">Остановить</a>
    </div>
</div>
