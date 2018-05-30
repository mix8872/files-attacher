<?php
/**
 * Created by PhpStorm.
 * User: Mix
 * Date: 30.10.2017
 * Time: 10:31
 */

use yii\helpers\Html;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\base\Security;

$class = explode('\\', $model->className());

$security = new Security();
$uniqueName = $security->generateRandomString(10);
?>
<div class="panel panel-default">
    <div class="panel-body">
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
                        mainClass: 'input-group-lg',
                        allowedFileTypes: JSON.parse('" . $allowedFileTypes . "'),
                        allowedFileExtensions: JSON.parse('" . $allowedFileExtensions . "'),
                    });
                });
            }(jQuery));
        "); ?>
        <?php if ($list && !$multiple):
            $files = $model->getFiles($tag);
            if (!empty($files)): ?>
                <table>
                    <tr>
                        <?php $type = explode('/', $files[0]->mime_type); ?>
                        <?php if ($type[0] == 'image'): ?>
                            <td>
                                <?= Html::a(
                                    Html::img($files[0]->url, ['width' => '200px']),
                                    $files[0]->url,
                                    ['class' => 'lightbox']
                                ); ?>
                            </td>
                        <?php elseif ($type[0] == 'video'): ?>
                            <td>
                                <?= Html::tag('video', Html::tag('source', '', ['src' => $files[0]->url, 'type' => $files[0]->mime_type]),
                                    ['width' => '200px', 'controls' => true]
                                ) ?>
                            </td>
                        <?php else: ?>
                            <td>
                                <?= Html::tag('i', '', ['class' => 'glyphicon glyphicon-file', 'style' => 'font-size: 100px;']) ?>
                            </td>
                            <td>
                                <?= Html::tag('span', $files[0]->name . '.' . $type[1]) ?>
                            </td>
                        <?php endif; ?>
                        <td>
                            <?= Html::a('<i class="glyphicon glyphicon-remove"></i>', ['/admin/files/delete', 'id' => $files[0]->id], [
                                'class' => 'delete-attachment-file',
                            ]) ?>
                        </td>
                    </tr>
                </table>
            <?php endif;
        endif; ?>
        <?php if ($list && $multiple): ?>
            <?= GridView::widget([
                'dataProvider' => new ActiveDataProvider(['query' => $model->getFiles($tag, 1),]),
                'rowOptions' => function ($model) {
                    return ['id' => $model->id];
                },
                'showOnEmpty' => false,
                'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],
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
                                return Html::a(Yii::t('admin', 'Preview'), [$model->url], ['tagret' => '_blank']);
                            }
                        }
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
//				'header' => 'Редактирование',
                        'template' => '{delete}',
                        'buttons' => [
                            'delete' => function ($url, $model) {
                                return Html::a('<i class="glyphicon glyphicon-remove"></i>', ['/filesAttacher/default/delete', 'id' => $model->id], [
                                    'class' => 'delete-attachment-file',
                                ]);
                            }
                        ]
                    ],
                ],
            ]); ?>
        <?php endif; ?>
    </div>
</div>