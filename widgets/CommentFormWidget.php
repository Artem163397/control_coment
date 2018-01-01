<?php
/**
 * CommentFormWidget.php
 * @author Revin Roman
 * @link https://rmrevin.ru
 */

namespace teo_crm\yii\module\Comments\widgets;

use teo_crm\yii\module\Comments;


/**
 * Class CommentFormWidget
 * @package rmrevin\yii\module\Comments\widgets
 */
class CommentFormWidget extends \yii\base\Widget
{

    /** @var string|null */
    public $theme;

    /** @var string */
    public $entity;

    /** @var Comments\models\Comment */
    public $Comment;

    /** @var string */
    public $anchor = '#comment-%d';

    /** @var string */
    public $viewFile = 'comment-form';

    /**
     * Register asset bundle
     */
    protected function registerAssets()
    {
        CommentFormAsset::register($this->getView());
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->registerAssets();

        /** @var Comments\forms\CommentCreateForm $CommentCreateForm */
        $CommentCreateFormClassData = Comments\Module::instance()->model(
            'commentCreateForm', [
                'Comment' => $this->Comment,
                'entity' => $this->entity,
            ]
        );

        $CommentCreateForm = \Yii::createObject($CommentCreateFormClassData);

        if ($CommentCreateForm->load(\Yii::$app->getRequest()->post())) {
            if ($CommentCreateForm->validate()) {
                if ($CommentCreateForm->save()) {
                        //echo "id=".$CommentCreateForm->Comment->id;die;

        //------------------------------------------------------------------------------------//
        /** @var Comments\models\Comment $CommentModel */
        $CommentModel = \Yii::createObject(Comments\Module::instance()->model('comment'));
        $comment = $CommentModel::find()->where(['id' => $CommentCreateForm->Comment->id])->one();
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
        }
        //---------------------------------------------------------------------------------------------//  

                    \Yii::$app->getResponse()
                        ->refresh(sprintf($this->anchor, $CommentCreateForm->Comment->id))
                        ->send();

                    exit;
                }
            }
        }

        return $this->render($this->viewFile, [
            'CommentCreateForm' => $CommentCreateForm,
        ]);
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
