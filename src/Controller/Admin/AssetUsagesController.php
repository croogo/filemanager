<?php

namespace Croogo\FileManager\Controller\Admin;

use Cake\Event\Event;

class AssetUsagesController extends AppController {

    public $uses = array(
        'Croogo/FileManager.AssetUsages',
    );

    public function initialize() {
        parent::initialize();

        $excludeActions = array(
            'changeType', 'unregister',
        );
        if (in_array($this->request->getParam('action'), $excludeActions)) {
            $this->Security->setConfig('validatePost', false);
            $this->getEventManager()->off($this->Csrf);
        }
    }

    public function add() {
        if ($this->request->getQuery()) {
            $assetId = $this->request->getQuery('asset_id');
            $model = $this->request->getQuery('model');
            $foreignKey = $this->request->getQuery('foreign_key');
            $type = $this->request->getQuery('type');

            $conditions = array(
                'asset_id' => $assetId,
                'model' => $model,
                'foreign_key' => $foreignKey,
            );
            $exist = $this->AssetUsages->find()
                ->where($conditions)
                ->count();
            if ($exist === 0) {
                $assetUsage = $this->AssetUsages->newEntity([
                    'asset_id' => $assetId,
                    'model' => $model,
                    'foreign_key' => $foreignKey,
                    'type' => $type,
                ]);
                $saved = $this->AssetUsages->save($assetUsage);
                if ($saved) {
                    $this->Flash->success('Asset added');
                }
            } else {
                $this->Flash->error('Asset already exist');
            }
        }
        $this->redirect($this->referer());
    }

    public function changeType() {
        $this->viewBuilder()->className('Json');
        $result = true;
        $data = array('pk' => null, 'value' => null);
        if (isset($this->request->data['pk'])) {
            $data = $this->request->data;
        } elseif (isset($this->request->query['pk'])) {
            $data = $this->request->query;
        }

        $id = $data['pk'];
        $value = $data['value'];

        if (isset($id)) {
            $entity = $this->AssetUsages->get($id);
            $entity->set('type', $value);
            $result = $this->AssetUsages->save($entity);
        }
        $this->set(compact('result'));
        $this->set('_serialize', 'result');
    }

    public function unregister() {
        $this->viewBuilder()->setClassName('Json');
        $result = false;
        if ($id = $this->request->getData('id')) {
            $assetUsage = $this->AssetUsages->get($id);
            $result = $this->AssetUsages->delete($assetUsage);
        }
        $this->set(compact('result'));
        $this->set('_serialize', 'result');
    }

}
