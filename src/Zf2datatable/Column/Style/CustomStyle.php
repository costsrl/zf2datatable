<?php
namespace Zf2datatable\Column\Style;

class CustomStyle extends AbstractStyle
{
    protected $styleHtmlProperties = [];
    
    /**
     * @return the $styleHtmlProperties
     */
    public function getStyleHtmlProperties()
    {
        return $this->styleHtmlProperties;
    }

    /**
     * @param multitype: $styleHtmlProperties
     */
    public function setStyleHtmlProperties($styleHtmlProperties)
    {
        $this->styleHtmlProperties = $styleHtmlProperties;
    }

    public function __construct($styleProperties= null)
    {
        if(is_array($styleProperties)){
            $this->setStyleHtmlProperties($styleProperties);
        }
    }
    
    
    public function getCustomStyle(){
        
        $propertyString = '';
        foreach ($this->styleHtmlProperties as $property => $value){
            $propertyString.=$property.':'.$value.";";
        }
        
        return $propertyString;
    }
}

