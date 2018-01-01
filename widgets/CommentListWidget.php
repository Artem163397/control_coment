<?php
/**
 * CommentListWidget.php
 * @author Revin Roman
 * @link https://rmrevin.ru
 */

namespace teo_crm\yii\module\Comments\widgets;

use teo_crm\yii\module\Comments;
use yii\helpers\Html;

/**
 * Class CommentListWidget
 * @package rmrevin\yii\module\Comments\widgets
 */
class CommentListWidget extends \yii\base\Widget
{

    /** @var string|null */
    public $theme;

    /** @var string */
    public $viewFile = 'comment-list';

    /** @var array */
    public $viewParams = [];

    /** @var array */
    public $options = ['class' => 'comments-widget'];

    /** @var string */
    public $entity;

    /** @var string */
    public $anchorAfterUpdate = '#comment-%d';

    /** @var array */
    public $pagination = [
        'pageParam' => 'page',
        'pageSizeParam' => 'per-page',
        'pageSize' => 20,
        'pageSizeLimit' => [1, 50],
    ];

    /** @var array */
    public $sort = [
        'defaultOrder' => [
            'id' => SORT_ASC,
        ],
    ];

    /** @var bool */
    public $showDeleted = true;

    /** @var bool */
    public $showCreateForm = true;

    public function init()
    {
        parent::init();

        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
    }

    /**
     * Register asset bundle
     */
    protected function registerAssets()
    {
        CommentListAsset::register($this->getView());
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->registerAssets();

        $this->processDelete();

        /** @var Comments\models\Comment $CommentModel */
        $CommentModel = \Yii::createObject(Comments\Module::instance()->model('comment'));
        $CommentsQuery = $CommentModel::find()
            ->byEntity($this->entity);

        if (false === $this->showDeleted) {
            $CommentsQuery->withoutDeleted();
        }

        $CommentsDataProvider = \Yii::createObject([
            'class' => \yii\data\ActiveDataProvider::className(),
            'query' => $CommentsQuery->with(['author', 'lastUpdateAuthor']),
            'pagination' => $this->pagination,
            'sort' => $this->sort,
        ]);

        $params = $this->viewParams;
        $params['CommentsDataProvider'] = $CommentsDataProvider;

        $content = $this->render($this->viewFile, $params);

        //------------------------------------------------------------------------------------//
        /*$comment = $CommentModel::find()->byEntity($this->entity)->one();
        $creator = \app\models\Users::findOne($comment->created_by); $tip = "";

        if($creator->type == 1) $tip = 'Администратор';
        if($creator->type == 2) $tip = 'Аналитик';
        if($creator->type == 3) $tip = 'Менеджер проекта';
        if($creator->type == 4) $tip = 'Клиент';
        if($creator->type == 5) $tip = 'Программист';
        if($creator->type == 6) $tip = 'Компания';

        if($tip != ""){
            
            $subject = "Добавлены новые комментарии";
            $task = \app\models\Tasks::findOne($this->entity);
            $proyekt = $task->project0->name;
            $sprint = $task->sprint0->name;
            $zadacha = $task->task_title;
            $id = $task->id;
            $ssilka = 'http://' . $_SERVER['SERVER_NAME'] ."/tasks/view?id=".$task->id;
            $data = $task->date_delivery;
            $meneger = $task->managerProject->fio;
            $priority = $task->priority0->name; 
            $etap = $task->stage0->name; 
                        
            $message = 'Тема: ' . $subject . "%0A" . 'Проект: ' . $proyekt . "%0A" .'Спринт: ' . $sprint . "%0A" . 'Задача: ' . $zadacha . "%0A" . 'ИД задачи: ' . $id . "%0A". 'Ссылка: ' . $ssilka . "%0A" . "Дата сдачи: ". $data."%0A". 'Менежер: '. $meneger . "%0A" . "Приоритет: ". $priority . "%0A" . "Этап: " . $etap ."%0A" . "Комментарии: " . $comment->text . "%0A"  . $tip . " оставил комментария"; 

            $task->SendSmsToTelegram($task->managerProject->telegram_id,$message);//Менеджер проекта
            $task->SendSmsToTelegram($task->executor0->telegram_id,$message);//Исполнитель
            $task->SendSmsToTelegram($task->project0->client0->telegram_id,$message);//клиенту

            $company = \Yii::$app->user->identity->company;
            $admin = \app\models\Users::find()->where(['company' => $company, 'type' => 1])->all();
            foreach ($admin as $value) {
                $task->SendSmsToTelegram($value->telegram_id,$message);//администратору
            }

            $companies = \app\models\Users::find()->where(['company' => $company, 'type' => 6])->all();
            foreach ($companies as $value) {
                $task->SendSmsToTelegram($value->telegram_id,$message);//kompany
            }  
        }*/
        //---------------------------------------------------------------------------------------------//  

        return Html::tag('div', $content, $this->options);
    }

    private function processDelete()
    {
        $delete = (int)\Yii::$app->getRequest()->get('delete-comment');
        if ($delete > 0) {

            /** @var Comments\models\Comment $CommentModel */
            $CommentModel = \Yii::createObject(Comments\Module::instance()->model('comment'));

            /** @var Comments\models\Comment $Comment */
            $Comment = $CommentModel::find()
                ->byId($delete)
                ->one();

            if ($Comment->isDeleted()) {
                return;
            }

            if (!($Comment instanceof Comments\models\Comment)) {
                throw new \yii\web\NotFoundHttpException(\Yii::t('app', 'Comment not found.'));
            }

            if (!$Comment->canDelete()) {
                throw new \yii\web\ForbiddenHttpException(\Yii::t('app', 'Access Denied.'));
            }

            $Comment->deleted = $CommentModel::DELETED;
            $Comment->update();
        }
    }

    /**
     * @inheritdoc
     */
    public function getViewPath()
    {
        return empty($this->theme)
            ? parent::getViewPath()
            : (\Yii::$app->getViewPath() . DIRECTORY_SEPARATOR . $this->theme);
    }
}
