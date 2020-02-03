<?php
\mix8872\filesAttacher\assets\FileinputAsset::register($this);
?>
<label class="control-label" for="<?= $uniqueName ?>"><?= $label ?></label>
<input type="file"
       name="<?= 'Attachment[' . $model->formName() . ']' . ($model->id ? '[' . $model->id . ']' : '') . '[' . $tag . ']' . ($multiple ? '[]' : '') ?>"
       id="<?= $uniqueName ?>" <?= $multiple ? 'multiple' : '' ?> accept="<?= $inputFileTypes ?>"
       class="form-control"
       title="Выбрать файл"/>

<?php
$this->registerJs("
    (function($){
        $(function(){
            $('#" . $uniqueName . "').fileinput({
                showUpload: false,
                minFileCount: 0,
                language: 'ru',
                previewFileType:'any',
                browseLabel: '',
                removeLabel: '',
                theme: 'fa',
                ". ($theme === \mix8872\filesAttacher\widgets\FilesWidget::THEME_BROWSE_DRAGDROP ? '
                browseOnZoneClick: true,
                ' : '
                dropZoneEnabled: false,
                ') . "
                mainClass: 'input-group',
                allowedFileTypes: JSON.parse('" . $jsAllowedFileTypes . "'),
                allowedFileExtensions: JSON.parse('" . $jsAllowedFileExtensions . "'),
                browseClass: 'btn btn-secondary'
            });
        });
    }(jQuery));
");
?>