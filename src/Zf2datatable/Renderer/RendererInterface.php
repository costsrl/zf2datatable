<?php
namespace Zf2datatable\Renderer;

use Laminas\View\Model\ViewModel;

interface RendererInterface
{

    /**
     *
     * @return array
     */
    public function getSortConditions ();

    /**
     *
     * @return array
     */
    public function getFilters ();

    /**
     * Return the name of the renderer
     *
     * @return string
     */
    public function getName();

    /**
     * Determine if the renderer is for export
     *
     * @return boolean
     */
    public function isExport ();

    /**
     * Determin if the renderer is HTML
     * It can be export + html -> f.x.
     * printing for HTML
     *
     * @return boolean
     */
    public function isHtml ();

    /**
     * Execute all...
     *
     * @return ViewModel Response\Stream
     */
    public function execute ();
}
