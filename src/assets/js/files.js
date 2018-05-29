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
}(jQuery));