<?php
namespace Zf2datatable\Column\Formatter;

use Zf2datatable\Column\AbstractColumn;

class Link extends AbstractFormatter
{

    protected $paramFromGet = array();

    protected $validRenderers = array(
        'jqGrid',
        'bootstrapTable'
    );

    public function getFormattedValue(AbstractColumn $column)
    {
        $row = $this->getRowData();
        $value = $row[$column->getUniqueId()];

        //\Laminas\Debug\Debug::dump($row);


        if($this->getAttribute('href')!== null){
            $href=$this->getAttribute('href');
        }
        else
            $href=$value;


        foreach($this->getParamFromGet() as $key => $val){
            if(array_key_exists($key,$row)){
                if ($val['overwriteKey'] != '')
                {
                    $param = $val['overwriteKey'];
                }
                else
                {
                    $param = $key;
                }

                if(preg_match('#\?#',$href))
                {
                    $href.=sprintf('&%s=%s', $param, $row[$key]);
                }
                else
                {
                    $href.=sprintf('?%s=%s', $param, $row[$key]);
                }
            }
            else{
                if(preg_match('#\?#',$href))
                {
                    $href.=sprintf('&%s=%s', $param, $val);
                }
                else
                {
                    $href.=sprintf('?%s=%s', $param, $val);
                }
            }
        }




        if($this->getAttribute('title')!== null){
            $title=sprintf('title=%s', $this->getAttribute('title'));
        }
        else
            $title='';


        if($this->getAttribute('target')!== null){
            $target=sprintf('target=%s', $this->getAttribute('target'));
        }
        else
            $target='';

        return '<a href="' . $href . '"  '.$title.'  '.$target.' >' . $value . '</a>';
    }

    public function setParamFromGet($key,$value='',$overwriteKey=''){
        $this->paramFromGet[$key] = array('value'=>$value,'overwriteKey' => $overwriteKey);
    }

    public function getParamFromGet(){
        return $this->paramFromGet;
    }
}