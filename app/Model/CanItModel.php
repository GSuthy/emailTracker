<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 7/22/14
 * Time: 12:32 PM
 */

class CanItModel extends AppModel {

    public function __construct() {
        parent::__construct();
    }

    public function hasField($name, $checkVirtual = false) {
        return (!in_array($name, array('created', 'modified', 'updated')));
    }

    public function beforeSave($options = array()) {
        if (!isset($this->request)) {
            $this->request = array('uri' => array('path' => $this->table));
        }
        unset($this->request['method']);
        if (!empty($this->data[$this->alias])) {
            $data = $this->data[$this->alias];
            if (!empty($data[$this->primaryKey])) {
                $id = $data[$this->primaryKey];
                if (!empty($this->existsCache[$id])) {
                    $data = array_merge($this->existsCache[$id], $data);
                }
            }
            $this->request['body'] = json_encode($data);
            $this->request['header']['Content-Type'] = 'application/json';
        }
        return parent::beforeSave($options);
    }

    public function buildQuery($type = 'first', $query = array()) {
        $query = parent::buildQuery($type, $query);
        $id = '';
        if (!empty($query['conditions'][$this->primaryKey])) {
            $id = "/{$query['conditions'][$this->primaryKey]}";
            unset($query['conditions'][$this->primaryKey]);
        }
        if (!empty($query['conditions']["{$this->alias}.{$this->primaryKey}"])) {
            $id = "/{$query['conditions']["{$this->alias}.{$this->primaryKey}"]}";
            unset($query['conditions']["{$this->alias}.{$this->primaryKey}"]);
        }

        $subPath = '';
        if (!empty($query['conditions'])) {
            foreach($query['conditions'] as $key => $value) {
                if (is_numeric($key)) {
                    if (is_array($value)) {
                        foreach ($value as $temp_value) {
                            $subPath .= '/' . urlencode($temp_value);
                        }
                    } else {
                        $subPath .= '/' . urlencode($value);
                    }
                } else {
                    $key = str_replace('*number*', '', $key);
                    if (is_array($value)) {
                        foreach ($value as $temp_value) {
                            $subPath .= '/' . urlencode($key) . '/' . urlencode($temp_value);
                        }
                    } else {
                        $subPath .= '/' . urlencode($key) . '/' . urlencode($value);
                    }
                }
            }
        }
        if(!isset($query['query'])) {
            $query['query'] = null;
        }

        if (!isset($this->request)) {
            $this->request = array();
        }

        //Stuff I added in
        if (isset($query['method'])) {
            $this->request['method'] = $query['method'];
        }
        if (isset($query['body'])) {
            $this->request['body'] = $query['body'];
        }
        //End of stuff I added in

        $defaultRequest = array(
            'uri' => array(
                'path' => "{$this->table}{$id}{$subPath}",
                'query' => $query['query']));
        $this->request = array_merge($defaultRequest, $this->request);

        return $query;
    }

    public function exists($id = null) {
        if ($id === null) {
            $id = $this->getID();
        }
        if ($id === false) {
            return false;
        }
        if (!array_key_exists($id, $this->existsCache)) {
            $this->existsCache[$id] = $this->find('all', array(
                'conditions' => array(
                    $this->alias . '.' . $this->primaryKey => $id
                ),
                'recursive' => -1,
                'callbacks' => false
            ));
        }
        return !empty($this->existsCache[$id]);
    }

    public function get($query = array()) {
        $query['method'] = 'GET';
        $results = $this->find('all', $query);
        return $results;
    }

    public function find($type = 'first', $query = array()) {
        return parent::find($type, $query);
    }
} 