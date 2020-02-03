<?php
/**
 * Created by PhpStorm.
 * User: Mix
 * Date: 30.10.2017
 * Time: 10:31
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\data\ActiveDataProvider;
use richardfan\sortable\SortableGridView;
use mix8872\filesAttacher\widgets\FilesWidget;

?>
<div class="form-group">
    <?php if ($theme === FilesWidget::THEME_DRAGDROP) : ?>
        <?= $this->render('_dragdrop', compact('uniqueName',
            'label', 'model', 'tag', 'multiple', 'inputFileTypes',
            'jsAllowedFileTypes', 'jsAllowedFileExtensions', 'width', 'height', 'maxcount', 'multiple', 'query')) ?>
    <?php else: ?>
        <?= $this->render('_fileinput', compact('uniqueName',
            'label', 'model', 'tag', 'multiple', 'inputFileTypes',
            'jsAllowedFileTypes', 'jsAllowedFileExtensions', 'maxcount', 'theme')) ?>
    <?php endif; ?>
    <?php if (!$multiple):
        $files = $query->one();
        if ($files): ?>
            <table class="file-table">
                <tr>
                    <?php $type = explode('/', $files->mime_type); ?>
                    <?php if ($showPreview && $theme !== FilesWidget::THEME_DRAGDROP): ?>
                        <?php if ($type[0] == 'image'): ?>
                            <td>
                                <?= Html::a(
                                    Html::img($files->url, ['width' => '200px']),
                                    $files->url,
                                    ['class' => 'lightbox']
                                ); ?>
                            </td>
                        <?php elseif ($type[0] == 'video'): ?>
                            <td>
                                <?= Html::tag('video', Html::tag('source', '', ['src' => $files->url, 'type' => $files->mime_type]),
                                    ['width' => '200px', 'controls' => true]
                                ) ?>
                            </td>
                        <?php else: ?>
                            <td>
                                <?= Html::tag('i', '', ['class' => 'fa far fa-file', 'style' => 'font-size: 100px;']) ?>
                            </td>
                            <td>
                                <?= Html::tag('span', $files->name . '.' . $type[1]) ?>
                            </td>
                        <?php endif; ?>
                    <?php endif; ?>
                    <td>
                        <?= Html::a('<span class="fa fas fa-pencil-alt"></span>', '#', [
                            'title' => Yii::t('files', 'Редактировать атрибуты'),
                            'data' => [
                                'toggle' => 'modal',
                                'target' => '#file-' . $files->id . '-edit-modal'
                            ]
                        ]) ?>
                        <?= Html::a('<i class="fa fa-times"></i>', ['/filesAttacher/default/delete', 'id' => $files->id], [
                            'title' => Yii::t('files', 'Удалить'),
                            'class' => 'delete-attachment-file',
                        ]) ?>
                    </td>
                </tr>
            </table>
        <?php endif; ?>
    <?php else : ?>
        <?= SortableGridView::widget([
            'dataProvider' => new ActiveDataProvider(['query' => $query]),
            'rowOptions' => function ($model) {
                return ['id' => $model->id];
            },
            'showOnEmpty' => false,
            'emptyText' => '',
            'sortUrl' => Url::to(['/filesAttacher/default/sort']),
            'rowOptions' => [
                'class' => 'file_row'
            ],
            'columns' => [
                [
                    'attribute' => 'name',
                    'value' => function ($model) {
                        return mb_strimwidth($model->name, 0, 30, ' ...');
                    }
                ],
                'mime_type',
                [
                    'attribute' => 'filename',
                    'format' => 'raw',
                    'value' => function ($model) {
                        if (preg_match("/^image\/.+$/i", $model->mime_type)) {
                            return Html::a(
                                Html::img($model->url, ['width' => '150px']),
                                $model->url,
                                ['class' => 'lightbox']
                            );
                        } elseif (preg_match("/^video\/.+$/i", $model->mime_type)) {
                            return Html::tag(
                                'video',
                                Html::tag('source', '', ['src' => $model->url, 'type' => $model->mime_type]),
                                ['width' => '150px', 'controls' => true]
                            );
                        } else {
                            return Html::a(Yii::t('files', 'Preview'), [$model->url], ['tagret' => '_blank']);
                        }
                    }
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update} {delete}',
//                        'width' => '50px',
                    'buttons' => [
                        'update' => function ($url, $model) {
                            return Html::a('<span class="fa fas fa-pencil-alt"></span>', '#', [
                                'data' => [
                                    'toggle' => 'modal',
                                    'target' => '#file-' . $model->id . '-edit-modal'
                                ]
                            ]);
                        },
                        'delete' => function ($url, $model) {
                            return Html::a('<span class="fa fa-times"></span>', ['/filesAttacher/default/delete', 'id' => $model->id], [
                                'class' => 'delete-attachment-file',
                            ]);
                        }
                    ]
                ],
            ],
        ]); ?>
    <?php endif; ?>
    <?php if ($files = $query->all()): ?>
        <?php foreach ($files as $file): ?>
            <div id="file-<?= $file->id ?>-edit-modal" class="modal fade" tabindex="-1" role="dialog"
                 aria-hidden="true"
                 style="display: none;">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Свойства файла <?= mb_strimwidth($file->name, 0, 30, ' ...') ?></h4>
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
                                    $content = $file->getLangContent($lang); ?>
                                    <div class="tab-pane<?= $i++ == 0 ? ' active' : '' ?>" id="tab-<?= $lang ?>">
                                        <?php if (preg_match('/image/ui', $file->mime_type)): ?>
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
                            <?= Html::button('Сохранить', ['class' => 'btn btn-primary file-edit-submit', 'data-url' => Url::to(['/filesAttacher/default/ajax-update', 'id' => $file->id])]) ?>
                        </div>

                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>