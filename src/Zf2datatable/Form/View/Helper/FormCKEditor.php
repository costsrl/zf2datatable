<?php
namespace Zf2datatable\Form\View\Helper;

use \Laminas\Form\View\Helper\FormTextarea as FormTextarea;

class FormCKEditor extends FormTextarea
{
    /**
     * @var \Laminas\View\Helper\EscapeJs
     */
    protected $escapeJsHelper;
    protected $placeHolder = null;
    /**
     * @see \Laminas\Form\View\Helper\FormTextarea::render()
     * @param \Laminas\Form\ElementInterface $element
     * @return string
     */
    public function render(\Laminas\Form\ElementInterface $oElement){
        $this->getCustomPlaceHolder()->__invoke('bottomScripts')->append('<script language="JavaScript">CKEDITOR.replace('.\Laminas\Json\Json::encode($oElement->getName()).');</script>');
        return parent::render($oElement);
        //.$this->getEscapeJsHelper()->__invoke('CKEDITOR.replace('.\Laminas\Json\Json::encode($oElement->getName()).')');
    }
    /**
     * @see \Laminas\Form\View\Helper\FormTextarea::__invoke()
     * @param ElementInterface|null $element
     * @return string|\CKEditorBundle\Form\View\HelperFormCKEditorHelper
     */
    public function __invoke(\Laminas\Form\ElementInterface $oElement){
        return $oElement ? $this->render($oElement): $this;
    }
    /**
     * Retrieve the escapeJs helper
     * @return \Laminas\View\Helper\EscapeJs
     */
    protected function getEscapeJsHelper(){
        if($this->escapeJsHelper)
            return $this->escapeJsHelper;
        if(method_exists($this->view, 'plugin'))
            $this->escapeJsHelper = $this->view->plugin('escapejs');
        if(!$this->escapeJsHelper instanceof \Laminas\View\Helper\EscapeJs)
            $this->escapeJsHelper = new \Laminas\View\Helper\EscapeJs();

        return $this->escapeJsHelper;
    }

    protected function getCustomPlaceHolder(){


        if(method_exists($this->view, 'plugin')){
            $this->placeHolder = $this->view->plugin('placeholder');
            return $this->placeHolder;
        }

        if(!$this->placeHolder instanceof \Laminas\View\Helper\Placeholder)
            $this->placeHolder = new \Laminas\View\Helper\Placeholder();


     return $this->placeholder;
    }
}

?>