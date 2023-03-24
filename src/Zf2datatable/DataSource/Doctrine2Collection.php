<?php
namespace Zf2datatable\DataSource;

use Zf2datatable\DataSource\PhpArray;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Collection;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Doctrine\ORM\QueryBuilder;
use Zf2datatable\Event\Zf2datatableEvent;

class Doctrine2Collection extends AbstractDataSource
{

    /**
     *
     * @var Collection
     */
    private $data;

    /**
     *
     * @var EntityManager
     */
    private $em;

    /**
     * Data source
     *
     * @param mixed $data
     */
    public function __construct($data)
    {
        if ($data instanceof Collection) {
            $this->data = $data;
        } else {
            $return = $data;
            if (is_object($data)) {
                $return = 'instanceof ' . get_class($return);
            }
            throw new \InvalidArgumentException('Unknown data input: "' . $return . '"');
        }
    }

    /**
     *
     * @return Collection
     */
    public function getData()
    {
        return $this->data;
    }


    /**
     *
     * @return Collection
     */
    public function getDataDetail()
    {
        return $this->data;
    }



    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    public function execute()
    {

        $hydrator = new DoctrineObject($this->getEntityManager());

        $dataPrepared = array();
        foreach ($this->getData() as $row) {
            $dataExtracted = $hydrator->extract($row);

            $rowExtracted = array();
            foreach ($this->getColumns() as $column) {
                /* @var $column \ZfcDatagrid\Column\AbstractColumn */
                $part1 = $column->getSelectPart1();
                $part2 = $column->getSelectPart2();

                if ($part2 === null) {
                    if (isset($dataExtracted[$part1])) {
                        $rowExtracted[$column->getUniqueId()] = $dataExtracted[$part1];
                    }
                } else {
                    // NESTED
                    if (isset($dataExtracted[$part1])) {
                        $dataExtractedNested = $hydrator->extract($dataExtracted[$part1]);
                        if (isset($dataExtractedNested[$part2])) {
                            $rowExtracted[$column->getUniqueId()] = $dataExtractedNested[$part2];
                        }
                    }
                }
            }

            $dataPrepared[] = $rowExtracted;
        }

        $source = new PhpArray($dataPrepared);
        $source->setColumns($this->getColumns());
        $source->setSortConditions($this->getSortConditions());
        $source->setFilters($this->getFilters());
        $source->execute();

        $this->setPaginatorAdapter($source->getPaginatorAdapter());
    }


    public function executeDetail($filterValue=array())
    {

      $hydrator = new DoctrineObject($this->getEntityManager());
      $qb = $this->getData();
      if($qb instanceof \Doctrine\ORM\QueryBuilder){
           $alias = current($qb->getRootAliases());
           while(list($chiave,$valore) = each($filterValue)){
                $qb->andWhere( $alias.'.'.$chiave.' = '.$valore);
           }
           //echo $this->getData()->getQuery()->getSql();
           $row = $this->getData()->getQuery()->getResult();


           if(is_array($row[0])){
              $dataExtracted = $row[0];
           }
           else
              $dataExtracted = $hydrator->extract($row[0]);
       }
       return $dataExtracted;
    }

    /** external query **/
    public function executeExternalQuery($source=array())
    {
    }

    /** to populate **/
    public function findByIdentity($where){
        /*if(null===$entiyObject){
            $entityCls   = $this->getEntity();
            $entiyObject = new $entityCls();
        }*/

        return $this->getEntityManager()->getRepository($this->getEntity())->find($where);
    }

    /** default bind object **/
    public function getDefaultBindObject(){
        $entityCls   = $this->getEntity();
        return new $entityCls();
    }

    /**   update **/
    public function update($entity,$where=null,$contextParams=null){

        $shortCircuit = function ($r){
            if (is_object($r)) {
                return true;
            }
            return false;
        };

        $event = new Zf2datatableEvent();
        $event->setName('pre.'.__FUNCTION__);
        $event->setTarget($entity);
        //$event->setParams();
        $event->setContext($contextParams);
        $event->stopPropagation(false); // Clear before triggering

        $this->getEventManager()->setEventPrototype($event);
        $results = $this->getEventManager()->triggerEventUntil($shortCircuit,$event);
        if ($results->stopped()) {
            $entity = $results->last();
        }


        //$this->getEventManager()->triggerEvent($event);
        /*$results= $this->getEventManager()->trigger('pre.'.__FUNCTION__,$entity,$paramns);*/

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        $event->setName('post.'.__FUNCTION__);
        $event->setTarget($entity);
        //$event->setParams();
        $event->setContext($contextParams);
        $event->stopPropagation(false);
         // Clear before triggering
        $results = $this->getEventManager()->triggerEvent($event);
        //$results= $this->getEventManager()->trigger('post.'.__FUNCTION__,$entity,$paramns);

        return true;
    }

    /**   insert **/
    public function insert($entity,$contextParams=null){
        $shortCircuit = function ($r){
            if (is_object($r)) {
                return true;
            }
            return false;
        };
        $event = new Zf2datatableEvent();
        $event->setName('pre.'.__FUNCTION__);
        $event->setTarget($entity);
        //$event->setParams();
        $event->setContext($contextParams);
        $event->stopPropagation(false); // Clear before triggering
        $this->getEventManager()->setEventPrototype($event);
        $results = $this->getEventManager()->triggerEventUntil($shortCircuit,$event);
        if ($results->stopped()) {
            $entity = $results->last();
        }
        //$results= $this->getEventManager()->trigger('pre.'.__FUNCTION__,$entity,$contextParams);

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();


        $event = new Zf2datatableEvent();
        $event->setName('post.'.__FUNCTION__);
        $event->setTarget($entity);
        //$event->setParams();
        $event->setContext($contextParams);
        $event->stopPropagation(false);
         // Clear before triggering
        $results = $this->getEventManager()->triggerEvent($event);
        //$results= $this->getEventManager()->trigger('post.'.__FUNCTION__,$entity,$contextParams);

        return true;
    }

    /**   delete **/
    public function delete($where,$contextParams=null){
        $event = new Zf2datatableEvent();
        //$results= $this->getEventManager()->trigger('pre.'.__FUNCTION__,$entity,$paramns);
        $event->setName('pre.'.__FUNCTION__);
        $event->setTarget($entity);
        //$event->setParams();
        $event->setContext($contextParams);
        $event->stopPropagation(false); 

        $entity = $this->getEntityManager()->find($this->getEntity(), $where);
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();

        $event->setName('post.'.__FUNCTION__);
        $event->setTarget($entity);
        //$event->setParams();
        $event->setContext($contextParams);

        $event->stopPropagation(false); // Clear before triggering
        $results =$this->getEventManager()->triggerEvent($event);
        //$results= $this->getEventManager()->trigger('post.'.__FUNCTION__,$entity,$paramns);
        return true;
    }
    
    /** restutuisce il valore presente nella entita **/
    public function getPrimaryValueData($Entity,$primaryColoumns){
        $key = "";
        if(is_array($primaryColoumns)){
            $rec = 0;
            foreach ($primaryColoumns as $column){
                $method="get".ucfirst($column);
                if($rec > 0)
                    $key="&";
    
                    $key.=sprintf('%s=', $column).$Entity->$method();
                    $rec++;
            }
        }
        return $key;
    }

    /**
     *
     * @return multitype:multitype:array  multitype:multitype:multitype:string unknown
     */
    public function getMetaDataInfo(){

        $em     =   $this->getEntityManager();
        $cmf    =   $em->getMetadataFactory();

       $class  =   $cmf->getMetadataFor($this->getEntity());
        
        /*$table  	= $metaData->getTable($this->table);
        $columns 	= $metaData->getColumns($this->table);
        $contraints = $metaData->getConstraints($this->table);
        $views 		= $metaData->getViews();*/

        //var_dump($class);
        $columns        = array();
        $contraints     = array();
        $associations   = array();
        foreach ($class->fieldMappings as $key => $fieldMap){
            $columns[$key]=array('name'=>$fieldMap['fieldName'],'datatype'=>strtoupper($fieldMap['type']));
        }

        foreach ($class->fieldMappings as $key => $fieldMap){
            if($fieldMap['id']==true)
                $contraints[$key]=array('name'=>$fieldMap['columnName'],'datatype'=>strtoupper($fieldMap['type']));
        }


        foreach ($class->associationMappings as $key => $associationMapping){
            $associations[$key] = $associationMapping;

        }
        

        return [["columns"=>$columns],
                ["entity_associations"=>$associations],
                ["constraints"=>$contraints]
               ];

    }


    public function getForeignKey(){
        $em     =   $this->getEntityManager();
        $cmf    =   $em->getMetadataFactory();
        $class  =   $cmf->getMetadataFor($this->getEntity());

        //associationMappings
        foreach ($class->associationMappings as $relation){
            $this->foreign_key[$relation['fieldName']]=array('targetEntity'=>$relation['targetEntity']);
        }

    }
}
