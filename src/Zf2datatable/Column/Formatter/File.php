<?php
namespace Zf2datatable\Column\Formatter;

use Zf2datatable\Column\AbstractColumn;

class File extends AbstractFormatter
{

    /**
     * @var string
     */
    const paramsSeparator = '~';
    protected $validRenderers = array(
        'jqGrid',
        'bootstrapTable'
    );

    public function getFormattedValue(AbstractColumn $column)
    {
        $row = $this->getRowData();
        $rowValue = $row[$column->getUniqueId()];

        if($rowValue!=''){
            //estract
            $value = explode(self::paramsSeparator, $rowValue);
        }



        if($this->getAttribute('href')!== null){
            $href=$this->getAttribute('href');
        }
        else
            $href=$value;


        if($this->getAttribute('path')!== null){
            $path=$this->getAttribute('path')."/";
        }
        else
            $path='?op=f&file=';



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
        
        if($this->getAttribute('class')!== null){
            $class = sprintf("class='%s'", $this->getAttribute('class'));
        }
        else
            $class = '';
        return sprintf('<a href="%s%s"  %s  %s  %s > %s </a>', $path, $rowValue, $title, $target, $class, $value[1]);
    }


}