$(".date").text(dateTime.getCurrentDate(lang));
$('.current-time').html(dateTime.formatTime(dateTime.getCurrentTime(true), format));

setInterval(function () {
    $('.current-time').html(dateTime.formatTime(dateTime.getCurrentTime(true), format));
}, 1000);

const widget = $('.widget');

$.ajax({
    url: widget.data("remote"),
    headers: {'Api-Access-Token': widget.data("apiAccessToken")},
    success: function (mosque) {
        // hijri date
        $(".hijriDate").text(writeIslamicDate(mosque.hijriAdjustment, lang));

        // shuruq
        $('.shuruq .time').html(dateTime.formatTime(mosque.shuruq, format));

        // jumua
        if(mosque.jumua) {
            $('.jumua .time').html(dateTime.formatTime(mosque.jumua, format));
        }

        if(!mosque.jumua){
            $('.jumua').css("visibility", "hidden");
        }

        // times
        $.each(mosque.times, function (i, time) {
            $('.prayers .time').eq(i).html(dateTime.formatTime(time, format));
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
