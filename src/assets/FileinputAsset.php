<?php
/**
 * Created by PhpStorm.
 * User: Mix
 * Date: 30.10.2017
 * Time: 10:32
 */

namespace mix8872\filesAttacher\assets;


class FileinputAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/mix8872/files-attacher/src/assets';
    public $css = [
        'bs-fileinput/css/fileinput.min.css',
    ];
    public $js = [
        'bs-fileinput/js/plugins/sortable.min.js',
        'bs-fileinput/js/fileinput.min.js',
        'bs-fileinput/js/locales/ru.js',
        'bs-fileinput/themes/explorer-fa/theme.js',
        'bs-fileinput/themes/fa/theme.js',
        'bs-fileinput/js/plugins/popper.min.js',
    ];

    public $depends = [
		'mix8872\filesAttacher\assets\FilesAsset',
    ];
}