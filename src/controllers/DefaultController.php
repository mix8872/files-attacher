<?php

namespace mix8872\filesAttacher\controllers;

use mix8872\filesAttacher\models\FileContent;
use richardfan\sortable\SortableAction;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use mix8872\filesAttacher\models\File;
use yii\web\NotFoundHttpException;

/**
 * MenuController implements the CRUD actions for Menu model.
 */
class DefaultController extends \yii\web\Controller
{		
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'ajax-update' => ['POST'],
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @return bool|string
     */
    public function getViewPath()
    {
        return Yii::getAlias('@vendor/mix8872/files-attacher/src/views');
    }

    /**
     * @return array
     */
    public function actions(){
        return [
            'sort' => [
                'class' => SortableAction::class,
                'activeRecordClassName' => 'mix8872\filesAttacher\models\File',
                'orderColumn' => 'order',
            ],
        ];
    }

    /**
     * Lists all Menu models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => File::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Menu model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new File();

		return $this->render('create', [
			'model' => $model,
		]);
    }

    /**
     * @param $id
     * @return bool
     */
    public function actionAjaxUpdate($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax) {
            $content = FileContent::find()->where(['file_id' => $id])->indexBy('id')->all();
            if (\yii\base\Model::loadMultiple($content, Yii::$app->request->post())) {
                $result = true;
                foreach ($content as $item) {
                    if (!$item->save()) {
                        error_log(print_r($item->getErrorSummary(1),1));
                        $result = false;
                    }
                }
                return $result;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * Deletes an existing Menu model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        return $this->findModel($id)->delete();
    }

	
	
//------------------------------------------------------------------
	
	/**
     * Finds the Menu model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Menu the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = File::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
