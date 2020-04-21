$(".date").text(dateTime.getCurrentDate(lang));
$('.current-time').html(dateTime.formatTime(dateTime.getCurrentTime(true), format));

setInterval(function () {
    $('.current-time').html(dateTime.formatTime(dateTime.getCurrentTime(true), format));
}, 1000);

const widget = $('.widget');

function getTimesFromCalendar(mosque) {
    let date = new Date();
    let time = addZero(date.getHours()) + ":" + addZero(date.getMinutes());
    let times = mosque.times;
    let ishaTime = times[4];

    if (time > ishaTime) {
        let month = dateTime.getTomorrowMonth();
        let day = dateTime.getTomorrowDay();
        times = mosque.calendar[month][day];
    }
    return times;
}

$.ajax({
    url: widget.data("remote") + "?calendar",
    headers: {'Api-Access-Token': widget.data("apiAccessToken")},
    success: function (mosque) {

        // hijri date
        $(".hijriDate").text(writeIslamicDate(mosque.hijriAdjustment, lang));

        // shuruq
        $('.shuruq .time').html(dateTime.formatTime(mosque.shuruq, format));

        // jumua
        if (mosque.jumua) {
            $('.jumua .time').html(dateTime.formatTime(mosque.jumua, format));
        }

        if (!mosque.jumua) {
            $('.jumua').css("visibility", "hidden");
        }

        // times
        let times = getTimesFromCalendar(mosque);
        $.each(times, function (i, time) {
            $('.prayers .time').eq(i).html(dateTime.formatTime(time, format));
        });

        let date = new Date();
        let now = addZero(date.getHours()) + ":" + addZero(date.getMinutes());
        let timesElm = $(".prayers > div");
        timesElm.eq(0).addClass("prayer-hilighted");
        $.each(times, function (i, time) {
            if(now <= time){
                timesElm.removeClass("prayer-hilighted");
                timesElm.eq(i).addClass("prayer-hilighted");
                return false;
            }
        });

        //iqama
        $.each(mosque.iqama, function (i, time) {
            var iqama = time + "'";
            if (mosque.fixedIqama[i] !== "") {
                iqama = mosque.fixedIqama[i];
            }

            $('.prayers .iqama').eq(i).html(dateTime.formatTime(iqama, format));
        });
    }
});
