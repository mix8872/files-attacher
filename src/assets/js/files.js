(function ($) {

    $(document).on('click', 'a.delete-attachment-file', function (e) {
        e.preventDefault();
        var that = $(this);
        var url = that.attr('href');
        var table = that.closest('table');
        var tr = that.closest('tr');
        if (confirm('Вы действительно хотите удалить элемент?')) {
            $.ajax({
                url: url,
                method: 'post',
                success: function (response) {
                    table.trigger('fileDeleted');
                    tr.remove();
                    if (!$('tbody tr', table).length) {
                        table.remove();
                    }
                    return false;
                },
            });
        }
    });
    $('a.lightbox').magnificPopup({
        type: 'image',
    });

    $('.file-edit-submit').on('click', function(e){
        e.preventDefault();
        var $this = $(this),
            url = $this.data('url'),
            fields = $this.parents('.modal-content').find('input.form-control'),
            data = {};
        fields.each(function(key, item){
            data[$(item).attr('name')] = $(item).val();
        });

        $this.removeClass('btn-primary').html('<i class="fa fa-spin fa-square"></i>');

        $.ajax({
            url: url,
            data: data,
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response) {
                    jQuery.Notification.notify(
                        'success',
                        'top center',
                        '',
                        'Свойства файла успешно сохранены'
                    );
                    $this.addClass('btn-success').html('Сохранено');
                } else {
                    jQuery.Notification.notify(
                        'danger',
                        'top center',
                        '',
                        'Ошибка сохранения'
                    );
                    $this.addClass('btn-primary').html('Сохранить');
                }
            }
        });
    });

}(jQuery));