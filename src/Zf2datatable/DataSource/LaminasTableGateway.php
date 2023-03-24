<?php
namespace Zf2datatable\DataSource;

use Zf2datatable\DataSource\LaminasSelect;

use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Db\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;
use Zf2datatable\Event\Zf2datatableEvent;

class LaminasTableGateway extends LaminasSelect
{
    protected $table;
    protected $tableGateway;
    protected $sourceObject;


    public function __construct($data=null)
    {
        /*if ($data instanceof \Laminas\Db\TableGateway\AbstractTableGateway) {
            $this->tableGateway     = $data;
            $this->table            = $this->tableGateway->getTable();
            $this->select           = $data->getDefaultSql();
        }
        else {
            throw new \InvalidArgumentException('A instance of Laminas\Db\SqlSelect is needed to use this dataSource!');
        }*/

    }

    /**
     *
     * @param \Laminas\Db\TableGateway\AbstractTableGateway $data
     */
    public function init($data){
        if ($data instanceof \Laminas\Db\TableGateway\AbstractTableGateway) {
            $this->tableGateway     = $data;
            $this->table            = $this->tableGateway->getTable();
            $this->select           = $data->getDefaultSql();
            $this->setSourceObject($data);
        }
        else {
            throw new \InvalidArgumentException('A instance of Laminas\Db\SqlSelect is needed to use this dataSource!');
        }

        if($this->getServiceLocator()->has('service_cache') && $this->getServiceLocator()->get('cache_metadata')['mode']=='enabled'){
            $this->setCache($this->getServiceLocator()->get('service_cache'));
        }

        return $this;
    }


    public function query($query, $where){
        $method='get'.$query;
        $this->select = $this->tableGateway->$method($where);
        return $this->select;
    }

    /**
     * @param array $data
     * @param $where
     * @return mixed
     */
    public function update($data, $where,$contextParams=null){
        $event = new Zf2datatableEvent();
        $dataEm = null;
        $shortCircuit = function ($r){
            if (is_array($r) || $r instanceof \ArrayObject) {
                return true;
            }
            return false;
        };
        
        
        $event->setName('pre.'.__FUNCTION__);
        $event->setTarget($this->tableGateway);
        $event->setParams($data);
        $event->setContext($contextParams);
        $event->stopPropagation(false); // Clear before triggering

        $this->getEventManager()->setEventPrototype($event);
        $results = $this->getEventManager()->triggerEventUntil($shortCircuit,$event);
        if ($results->stopped()) {
    	   $dataEm = $results->last();
    	}
    	
    	


       if($dataEm instanceof \ArrayObject){
            $datapreupdate = $dataEm->getArrayCopy();
       }
       elseif(is_array($dataEm)){
            $datapreupdate = $dataEm;
        }
       else{
            if($data instanceof \ArrayObject){
                $datapreupdate = $data->getArrayCopy();
            }
            else{
                $datapreupdate = data;
            }
        }
       
        $result =$this->tableGateway->update($datapreupdate, $where);
        
        $event->setName('post.'.__FUNCTION__);
        $event->setTarget($this->tableGateway);
        $event->setParams(array('data'=>$data,'where'=>$where));
        $event->setContext($contextParams);
        $event->stopPropagation(false); // Clear before triggering
        $this->getEventManager()->triggerEvent($event);
        return $result;
    }

    public function insert($data,$contextParams=null){
        $event = new Zf2datatableEvent();
        $dataEm = null;
        $shortCircuit = function ($r){
            if (is_array($r) || $r instanceof \ArrayObject) {
                return true;
            }
            return false;
        };

                
        $event->setName('pre.'.__FUNCTION__);
        $event->setTarget($this->tableGateway);
        $event->setParams($data);
        $event->setContext($contextParams);
        $event->stopPropagation(false); // Clear before triggering
        $this->getEventManager()->setEventPrototype($event);
        $results = $this->getEventManager()->triggerEventUntil($shortCircuit,$event);
        
        if ($results->stopped()) {
    	   $dataEm = $results->last();
    	}

        if($dataEm instanceof \ArrayObject)
            $datapreinsert = $dataEm->getArrayCopy();
        elseif(is_array($dataEm)){
            $datapreinsert = $dataEm;
        }
        else{
            if($data instanceof \ArrayObject){
                $datapreinsert = $data->getArrayCopy();
            }
            else{
                $datapreinsert = data;
            }
        }

        $this->getAdapter()->getAdapter()->getDriver()->getConnection()->beginTransaction();

        $result =$this->tableGateway->insert($datapreinsert);

        if($result){
            $lastInsertId = $this->getAdapter()->getAdapter()->getDriver()->getLastGeneratedValue();
    	    $this->getAdapter()->getAdapter()->getDriver()->getConnection()->commit();
    	    
    	    $event->setName('post.'.__FUNCTION__);
    	    $event->setTarget($this->tableGateway);
    	    $event->setParams(array("id"=>$lastInsertId, "data"=>$data));
    	    $event->setContext($contextParams);
    	    $event->stopPropagation(false); // Clear before triggering
    	    $this->getEventManager()->triggerEvent($event);
            return $result;
        }


        $this->getAdapter()->getAdapter()->getDriver()->getConnection()->rollback();

        return false;
    }

    public function delete($where,$contextParams=null){
         $event = new Zf2datatableEvent();
         $event->setName('pre.'.__FUNCTION__);
         $event->setTarget($this->tableGateway);
         $event->setParams($where);
         $event->setContext($contextParams);
         $event->stopPropagation(false);
          // Clear before triggering
         $results = $this->getEventManager()->triggerEvent($event);
         //$results = $this->getEventManager()->trigger('pre.'.__FUNCTION__,$this->tableGateway,$where);

         $this->getAdapter()->getAdapter()->getDriver()->getConnection()->beginTransaction();
         $result = $this->tableGateway->delete($where);
         if($result){
            $this->getAdapter()->getAdapter()->getDriver()->getConnection()->commit();

            $event->setName('post.'.__FUNCTION__);
            $event->setTarget($this->tableGateway);
            $event->setParams($where);
            $event->setContext($contextParams);
            $event->stopPropagation(false); // Clear before triggering
            $this->getEventManager()->triggerEvent($event);
            //$this->getEventManager()->trigger('post.'.__FUNCTION__,$this->tableGateway,$where);
            return $result;
         }
         $this->getAdapter()->getAdapter()->getDriver()->getConnection()->rollback();
         return false;
    }


    public function getSourceObject()
    {
        return $this->sourceObject;
    }


    public function setSourceObject($sourceObject)
    {
        $this->sourceObject = $sourceObject;
        return $this;
    }
}

?>