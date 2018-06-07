<?php

namespace mix8872\filesAttacher\controllers;

use richardfan\sortable\SortableAction;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use mix8872\filesAttacher\models\Files;

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
                'activeRecordClassName' => 'mix8872\filesAttacher\models\Files',
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
            'query' => Files::find(),
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
        $model = new Menu();
		
		if ($model->load(Yii::$app->request->post()) && $model->makeRoot()->save()) {
			Yii::$app->getSession()->setFlash('success', 'Меню успешно создано, теперь можно добавить новые пункты меню');
			return $this->redirect(['update', 'id' => $model->id]);
		}

		return $this->render('create', [
			'model' => $model,
		]);
    }

    public function actionAjaxUpdate($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax) {
            $model = Files::findOne($id);
            unset($model->url);
            unset($model->trueUrl);
            unset($model->sizes);
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return true;
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
        if (($model = Files::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
