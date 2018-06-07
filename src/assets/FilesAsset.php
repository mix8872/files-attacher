<?php
/**
 * Created by PhpStorm.
 * User: Mix
 * Date: 30.10.2017
 * Time: 10:32
 */

namespace mix8872\filesAttacher\assets;


class FilesAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/mix8872/files-attacher/src/assets';
    public $css = [
        'fileinput/css/fileinput.min.css',
        'css/magnific-popup.css',
        'css/files.css',
    ];
    public $js = [
        'fileinput/js/plugins/sortable.min.js',
        'fileinput/js/fileinput.min.js',
        'fileinput/js/locales/ru.js',
        'fileinput/themes/explorer-fa/theme.js',
        'fileinput/themes/fa/theme.js',
        'fileinput/js/plugins/popper.min.js',
        'js/jquery.magnific-popup.min.js',
        'js/files.js',
    ];

    public $depends = [
		'yii\jui\JuiAsset',
		'mix8872\admin\assets\MainAsset',
    ];
}