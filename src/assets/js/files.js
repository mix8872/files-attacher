(function($){
    $(document).on('click','a.delete-attachment-file',function(e){
        e.preventDefault();
        var that = $(this);
        var url = that.attr('href');
        if (confirm('Вы действительно хотите удалить элемент?')) {
            $.ajax({
                url: url,
                method: 'post',
                success: function(response){
                    that.closest('tr').remove();
                }
            });
        }
    });
    $('a.lightbox').magnificPopup({
        type: 'image',
    });

    $('.js-update-attachment-description').on('click', function (e) {
        e.preventDefault();
        var that = $(this);
        var url = that.attr('href');
        var span = $('span', this);
        var parentRow = that.parents('.file_row');
        var inputDiv = parentRow.find('.file_hidden-input');
        var input = parentRow.find('input');
        var name = parentRow.find('.file_name');

        var data = {};
        data[input.attr('name')] = input.val();

        if (that.hasClass('is-edit')) {
            $.ajax({
                url: url,
                type: 'post',
                dataType: "json",
                data: data,
                success: function(result) {
                    if (result) {
                        if (span.hasClass('glyphicon-ok')) {
                            span.removeClass('glyphicon-ok').addClass('glyphicon-pencil');
                        }
                        inputDiv.hide(10);
                        name.text(input.val()).show(10);
                        that.removeClass('is-edit');
                    }
                }
            });
        } else {
            if (span.hasClass('glyphicon-pencil')) {
                span.removeClass('glyphicon-pencil').addClass('glyphicon-ok');
            }
            name.hide(10);
            inputDiv.show(10);
            that.addClass('is-edit');
        }
    })

}(jQuery));