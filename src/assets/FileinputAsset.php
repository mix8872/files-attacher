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
        'bootstrap-fileinput/css/fileinput.min.css',
    ];
    public $js = [
        'bootstrap-fileinput/js/plugins/sortable.min.js',
        'bootstrap-fileinput/js/fileinput.min.js',
        'bootstrap-fileinput/js/locales/ru.js',
        'bootstrap-fileinput/themes/explorer-fa/theme.js',
        'bootstrap-fileinput/themes/fa/theme.js',
        'bootstrap-fileinput/js/plugins/popper.min.js',
    ];

    public $depends = [
		'mix8872\filesAttacher\assets\FilesAsset',
    ];
}