$(".copyright-year").text((new Date()).getFullYear());

$("#whatsapp-select").bind("change keyup", function (event) {
    let self = this;
    $.ajax({
        url: $(self).data("remote"),
        headers: {'Api-Access-Token': $(self).data("token")},
        data: {
            country: $(this).val()
        },
        success: function (data) {
            window.location.href = data.web.whatsapp;
        }
    });
});