$(".iframe-button").click(function () {
    let iframe = '<iframe src="' + $(this).data('url') + '" frameborder="0" scrolling="no" style="width: 100%; height: 260px;"></iframe>'
    $('#iframe-modal #iframe-text').text(iframe);
    $('#iframe-modal #iframe-html').html(iframe);
    $('#iframe-modal').modal();
});