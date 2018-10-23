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
use yii\base\Security;
use yii\widgets\ActiveForm;

$class = explode('\\', $model->className());

$security = new Security();
$uniqueName = $security->generateRandomString(10);
?>
<div class="form-group">
    <?php if ($title): ?>
        <label class="control-label" for="gallery-title"><?= $title ?></label>
    <?php endif; ?>
    <input type="file"
           name="<?= 'Attachment[' . $class[sizeof($class) - 1] . ']' . ($model->id ? '[' . $model->id . ']' : '') . '[' . $tag . ']' . ($multiple ? '[]' : '') ?>"
           id="<?= $uniqueName ?>" <?= $multiple ? 'multiple' : '' ?> accept="<?= $filetypes ?>"
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
                        mainClass: 'input-group',
                        allowedFileTypes: JSON.parse('" . $allowedFileTypes . "'),
                        allowedFileExtensions: JSON.parse('" . $allowedFileExtensions . "'),
                        browseClass: 'btn btn-default'
                    });
                });
            }(jQuery));
        "); ?>
    <?php if (!$multiple):
        $files = $query->one();
        if (!empty($files)): ?>
            <table>
                <tr>
                    <?php $type = explode('/', $files->mime_type); ?>
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
                            <?= Html::tag('i', '', ['class' => 'glyphicon glyphicon-file', 'style' => 'font-size: 100px;']) ?>
                        </td>
                        <td>
                            <?= Html::tag('span', $files->name . '.' . $type[1]) ?>
                        </td>
                    <?php endif; ?>
                    <td>
                        <?= Html::a('<i class="glyphicon glyphicon-remove"></i>', ['/filesAttacher/default/delete', 'id' => $files->id], [
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
            'sortUrl' => Url::to(['/filesAttacher/default/sort']),
            'rowOptions' => [
                'class' => 'file_row'
            ],
            'columns' => [

                'name',
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
                            return Html::a('<span class="glyphicon glyphicon-pencil"></span>', '#', [
                                'data' => [
                                    'toggle' => 'modal',
                                    'target' => '#file-' . $model->id . '-edit-modal'
                                ]
                            ]);
                        },
                        'delete' => function ($url, $model) {
                            return Html::a('<span class="glyphicon glyphicon-remove"></span>', '#', [
                                'class' => 'delete-attachment-file',
                            ]);
                        }
                    ]
                ],
            ],
        ]); ?>
        <?php if ($files = $query->all()): ?>
            <?php foreach ($files as $file): ?>
                <div id="file-<?= $file->id ?>-edit-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                     aria-hidden="true"
                     style="display: none;">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <?php $form = ActiveForm::begin(); ?>
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h4 class="modal-title">Свойства файла <?= $file->name ?></h4>
                            </div>
                            <div class="modal-body">
                                <ul class="nav nav-tabs navtab-bg nav-justified">
                                    <?php $i = 0; ?>
                                    <?php foreach ($languages as $key => $lang): ?>
                                        <li class="<?= $i++ == 0 ? 'active' : '' ?>">
                                            <a href="#tab-<?= $lang ?>" data-toggle="tab" aria-expanded="<?= $i == 0 ? 'true' : 'false' ?>">
                                                <?= $key ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <div class="tab-content">
                                    <?php $i = 0; ?>
                                    <?php foreach ($languages as $key => $lang): ?>
                                        <?php $content = $file->getContent($lang); ?>
                                        <div class="tab-pane<?= $i++ == 0 ? ' active' : '' ?>" id="tab-<?= $lang ?>">
                                            <?php if (preg_match('/image/ui', $file->mime_type)): ?>
                                                <?= $form->field($content, '[' . $content->id . ']name') ?>
                                                <?= $form->field($content, '[' . $content->id . ']title') ?>
                                            <?php endif; ?>
                                            <?= $form->field($content, '[' . $content->id . ']description') ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <?= Html::button('Сохранить', ['class' => 'btn btn-primary file-edit-submit', 'data-url' => Url::to(['/filesAttacher/default/ajax-update', 'id' => $file->id])]) ?>
                            </div>
                            <?php ActiveForm::end(); ?>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>