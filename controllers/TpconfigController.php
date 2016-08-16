<?php

namespace app\controllers;

use app\library\sandconsole\controllers\SandConsoleBaseController;
use Yii;
use app\models\Tpconfig;
use app\models\TpconfigSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\library\util\http\Ral;

/**
 * TpconfigController implements the CRUD actions for Tpconfig model.
 */
class TpconfigController extends SandConsoleBaseController
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
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Tpconfig models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TpconfigSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Tpconfig model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Tpconfig model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Tpconfig();
	$post = $this->getPostWithToken();
	if ($model->load($post) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Tpconfig model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
	$post = $this->getPostWithToken();
        if ($model->load($post) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Tpconfig model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Tpconfig model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Tpconfig the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Tpconfig::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    private function getPostWithToken() {
	    $post= Yii::$app->request->post();
	    $tp_name = $post['Tpconfig']['tp_name'];
	    $order_type = $post['Tpconfig']['order_type'];
	    $meta_token = $tp_name . '1006' . $order_type;
	    $token = sha1($meta_token);
	    if (!empty($tp_name) && !empty($order_type)) {
		    $post['Tpconfig']['token'] = $token;
		    $detail = json_decode($post['Tpconfig']['detail'], true);
		    $detail['tokeninfo'] = $meta_token;
		    $post['Tpconfig']['detail'] = json_encode($detail);
		    $url = $this->getIndexUrl() . '?r=outer/set-tp&order_type='.$order_type.'&token='.$token;
		    $params = array(
			'order_type' => $order_type,
			'token'      => $token
		    );
	            $ret = Ral::requestUrl($url, $params);
		    if ($ret['err'])
			return array();
		
		    $ret['data'] = json_decode($ret['data'], true);
		    if (!isset($ret['data']['errno']) || (isset($ret['data']['errno']) && $ret['data']['errno'])) {
			//error
			return array();
		    }
	    }
	    return $post;
    }
}
