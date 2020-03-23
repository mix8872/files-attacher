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
use himiklab\sortablegrid\SortableGridView;
use mix8872\filesAttacher\widgets\FilesWidget;

?>
<div class="form-group">
    <?php if ($theme === FilesWidget::THEME_DRAGDROP) : ?>
        <?= $this->render('_dragdrop', compact('uniqueName',
            'label', 'model', 'tag', 'multiple', 'inputFileTypes',
            'jsAllowedFileTypes', 'jsAllowedFileExtensions', 'width', 'height', 'maxCount', 'multiple', 'query')) ?>
    <?php else: ?>
        <?= $this->render('_fileinput', compact('uniqueName',
            'label', 'model', 'tag', 'multiple', 'inputFileTypes',
            'jsAllowedFileTypes', 'jsAllowedFileExtensions', 'maxCount', 'theme')) ?>
    <?php endif; ?>
    <?php if (!$multiple):
        $file = $query->one();
        if ($file): ?>
            <table class="file-table">
                <tr>
                    <?php $type = explode('/', $file->mime_type); ?>
                    <?php if ($showPreview && $theme !== FilesWidget::THEME_DRAGDROP): ?>
                        <?php if ($type[0] == 'image'): ?>
                            <td>
                                <?= Html::a(
                                    Html::img($file->url, ['width' => '100px']),
                                    $file->url,
                                    ['class' => 'lightbox']
                                ); ?>
                            </td>
                        <?php elseif ($type[0] == 'video'): ?>
                            <td>
                                <?= Html::tag('video', Html::tag('source', '', ['src' => $file->url, 'type' => $file->mime_type]),
                                    ['width' => '100px', 'controls' => true]
                                ) ?>
                            </td>
                        <?php else: ?>
                            <td>
                                <?= Html::tag('i', '', ['class' => 'fa far fa-file', 'style' => 'font-size: 100px;']) ?>
                            </td>
                            <td>
                                <?= Html::tag('span', $file->name . '.' . $type[1]) ?>
                            </td>
                        <?php endif; ?>
                    <?php endif; ?>
                    <td>
                        <?= Html::a('<span class="fa fa-pencil fas fa-pencil-alt"></span>', '#', [
                            'title' => Yii::t('files', 'Редактировать атрибуты'),
                            'data' => [
                                'toggle' => 'modal',
                                'target' => '#file-' . $file->id . '-edit-modal'
                            ]
                        ]) ?>
                        <?= Html::a('<i class="fa fa-times"></i>', ['/filesAttacher/default/delete', 'id' => $file->id], [
                            'title' => Yii::t('files', 'Удалить'),
                            'class' => 'delete-attachment-file',
                        ]) ?>
                    </td>
                </tr>
            </table>
            <?= $this->render('_edit-popup', ['model' => $file, 'languages' => $languages]) ?>
        <?php endif; ?>
    <?php else : ?>
        <?= SortableGridView::widget([
            'dataProvider' => new ActiveDataProvider(['query' => $query]),
            'rowOptions' => function ($model) {
                return ['id' => $model->id];
            },
            'showOnEmpty' => false,
            'emptyText' => '',
            'sortableAction' => Url::to(['/filesAttacher/default/sort']),
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
                    'value' => function ($model) use ($languages) {
                        $popup = $this->render('_edit-popup', compact('model', 'languages'));
                        if (preg_match("/^image\/.+$/i", $model->mime_type)) {
                            return Html::a(
                                    Html::img($model->url, ['width' => '50px']),
                                    $model->url,
                                    ['class' => 'lightbox']
                                ) . $popup;
                        } elseif (preg_match("/^video\/.+$/i", $model->mime_type)) {
                            return Html::tag(
                                    'video',
                                    Html::tag('source', '', ['src' => $model->url, 'type' => $model->mime_type]),
                                    ['width' => '50px', 'controls' => true]
                                ) . $popup;
                        } else {
                            return Html::a(Yii::t('files', 'Preview'), [$model->url], ['tagret' => '_blank']) . $popup;
                        }
                    }
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update} {delete}',
                    'buttons' => [
                        'update' => function ($url, $model) {
                            return Html::a('<span class="fa fa-pencil fas fa-pencil-alt"></span>', '#', [
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
</div>
