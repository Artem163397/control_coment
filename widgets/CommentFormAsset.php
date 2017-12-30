<?php
/**
 * CommentFormAsset.php
 * @author Revin Roman
 * @link https://rmrevin.ru
 */

namespace teo_crm\yii\module\Comments\widgets;

/**
 * Class CommentFormAsset
 * @package rmrevin\yii\module\Comments\widgets
 */
class CommentFormAsset extends \yii\web\AssetBundle
{

    public $sourcePath = '@vendor/teo_crm/yii2-comments/widgets/_assets';

    public $css = [
        'comment-form.css',
    ];
}
