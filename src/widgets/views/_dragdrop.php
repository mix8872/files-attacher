<?php
\mix8872\filesAttacher\assets\DragdropAsset::register($this);
?>
    <label class="control-label" for="<?= $uniqueName ?>"><?= $label ?></label>
    <input type="file"
           name="<?= 'Attachment[' . $model->formName() . ']' . ($model->id ? '[' . $model->id . ']' : '') . '[' . $tag . ']' . ($multiple ? '[]' : '') ?>"
           id="<?= $uniqueName ?>" <?= $multiple ? 'multiple' : '' ?>
           accept="<?= $inputFileTypes ?>"
           data-height="<?= $height ?>"
           data-width="<?= $width ?>"
        <?php if (!$multiple && $file = $query->one()) : ?>
            data-default-file="<?= $file->url ?>"
            data-show-remove="true"
        <?php endif; ?>
    />


<?php
$this->registerJs("
    $('#$uniqueName').DropifyMultiple({
        messages: {
            'default': 'Перетащите сюда файл или кликните для выбора',
            'replace': 'Перетащите сюда файл или кликните для замены',
            'remove':  'отмена',
        }
    });
");