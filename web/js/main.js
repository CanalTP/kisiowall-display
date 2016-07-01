$(document).ready(function () {
//            $("#slideshow > div:gt(0)").hide();
//
//            setInterval(function () {
//                $('#slideshow > div:first')
//                        .fadeOut(2000)
//                        .next()
//                        .fadeIn(2000)
//                        .end()
//                        .appendTo('#slideshow');
//            }, 10000);

    var responseTimes = $('#chart_div').data('responsetime');
//            $('.counter').each(function () {
//                var $this = $(this),
//                        countTo = $this.attr('data-count');
//
//                $({countNum: $this.text()}).animate({
//                            countNum: countTo
//                        },
//
//                        {
//
//                            duration: 8000,
//                            easing: 'linear',
//                            step: function () {
//                                $this.text(Math.floor(this.countNum));
//                            },
//                            complete: function () {
//                                $this.text(this.countNum);
//                                //alert('finished');
//                            }
//
//                        });
//
//            });
    google.charts.load('current', {'packages': ['corechart']});
    google.charts.setOnLoadCallback(function () {
        var dataTable = [['Time', 'Response time']];
        responseTimes = responseTimes["responseTimes"];
        for (date in responseTimes) {
            dataTable.push([date, responseTimes[date]]);
        }
        console.dir(dataTable);

        var data = google.visualization.arrayToDataTable(dataTable);

        var options = {
            height: 500,
            title: 'Navitia response time',
            titleTextStyle: {fontSize: 25},
            hAxis: {title: 'Time', titleTextStyle: {color: '#333'}},
            vAxis: {minValue: responseTimes.min, maxValue: responseTimes.max}
        };

        var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
        chart.draw(data, options);
    });
});