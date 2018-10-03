<?php

namespace mix8872\filesAttacher\controllers;

use Yii;
use yii\filters\VerbFilter;
use mix8872\filesAttacher\models\File;
use yii\web\NotFoundHttpException;

/**
 * MenuController implements the CRUD actions for Menu model.
 */
class FilesController extends \yii\web\Controller
{		
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
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
            'query' => Menu::find()->where(['depth' => 0]),
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

    /**
     * Deletes an existing Menu model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        return json_encode($this->findModel($id)->delete());
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
