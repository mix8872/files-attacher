<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div id="file-<?= $model->id ?>-edit-modal" class="modal fade" tabindex="-1" role="dialog"
     aria-hidden="true"
     style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Свойства файла <?= mb_strimwidth($model->name, 0, 30, ' ...') ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs navtab-bg nav-justified">
                    <?php $i = 0; ?>
                    <?php foreach ($languages as $key => $lang):
                        if (preg_match('/\w{2}-\w{2}/ui', $lang)) {
                            $lang = strtolower(preg_replace('/(\w{2})-(\w{2})/ui', "\$1", $lang));
                        }
                        ?>
                        <li class="<?= $i++ == 0 ? 'active' : '' ?>">
                            <a href="#tab-<?= $lang ?>" data-toggle="tab"
                               aria-expanded="<?= $i == 0 ? 'true' : 'false' ?>">
                                <?= $lang ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="tab-content">
                    <?php $i = 0; ?>
                    <?php foreach ($languages as $key => $lang):
                        if (preg_match('/\w{2}-\w{2}/ui', $lang)) {
                            $lang = strtolower(preg_replace('/(\w{2})-(\w{2})/ui', "\$1", $lang));
                        }
                        $content = $model->getLangContent($lang); ?>
                        <div class="tab-pane<?= $i++ == 0 ? ' active' : '' ?>" id="tab-<?= $lang ?>">
                            <?php if (preg_match('/image/ui', $model->mime_type)): ?>
                                <div class="form-group">
                                    <label class="control-label"
                                           for="file-<?= $content->id ?>-name">Name</label>
                                    <?= Html::activeTextInput($content, '[' . $content->id . ']name', ['class' => 'form-control']) ?>
                                </div>
                                <div class="form-group">
                                    <label class="control-label"
                                           for="file-<?= $content->id ?>-title">Title</label>
                                    <?= Html::activeTextInput($content, '[' . $content->id . ']title', ['class' => 'form-control']) ?>
                                </div>
                            <?php endif; ?>
                            <div class="form-group">
                                <label class="control-label" for="file-<?= $content->id ?>-description">Description</label>
                                <?= Html::activeTextInput($content, '[' . $content->id . ']description', ['class' => 'form-control']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <?= Html::button('Сохранить', ['class' => 'btn btn-primary file-edit-submit', 'data-url' => Url::to(['/filesAttacher/default/ajax-update', 'id' => $model->id])]) ?>
            </div>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
