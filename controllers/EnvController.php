<?php
namespace app\controllers;

use Yii;
use app\models\Env;
use app\models\EnvSearch;
use app\library\util\http\Ral;
use yii\base\Exception;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
/**
 * EvnController implements the CRUD actions for Env model.
 */
class EnvController extends Controller
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
                    'index' => ['POST', 'GET'],
                    'view' => ['POST', 'GET'],
                ],
            ],
        ];
    }

    /**
     * Lists all Env models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new EnvSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Env model.
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
     * Creates a new Env model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Env();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Env model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Env model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $config = Yii::$app->params['sandEnv'];
        if (!$config) {
            throw new \Exception(__CLASS__ . ' ' . __FUNCTION__ . ' config is empty');
        }
        $url = $model->agent_url;
        $params = array(
            'action' => 'delcopy',
            'path' => $config['path'],
            'uname' => $model->branch_name,
        );

        $ret = $this->requestAgent($url, $params);
        if (isset($ret['err']) && $ret['err']) {
            throw new \Exception(__CLASS__ . ' ' . __FUNCTION__ . ' requestAgent failed');
        }
        //逻辑判断
        if (!is_array($ret['data'])) {
            $err = json_decode($ret['data'], true);
            if (isset($err['errno']) && !$err['errno']) {
                $this->findModel($id)->delete();
            }
        }
        return $this->redirect(['index']);
    }

    public function actionEditFile($id)
    {
        $model = $this->findModel($id);
        return $this->render('editFile', [
            'model' => $model,
        ]);
    }

    public function actionUpdateCode($id)
    {
        $model = $this->findModel($id);
        return $this->render('updatecode', ['model' => $model]);
    }

    public function actionClone($id)
    {
        $model = $this->findModel($id);
        return $this->render('clone', ['model' => $model]);
    }

    /**
     * Finds the Env model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Env the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Env::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    /**
     * @param $url
     * @param $params
     * @return array
     */
    private function requestAgent($url, $params) {
        return Ral::requestUrl($url, $params);
    }
}
