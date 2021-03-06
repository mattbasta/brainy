<?php

namespace Box\Brainy\Templates;

/**
 * @author Rodney Rehm
 */
class CompiledTemplate
{
    /**
     * Compiled Filepath
     * @var string
     */
    public $filepath = null;

    /**
     * Compiled Timestamp
     * @var integer|null
     */
    public $timestamp = null;

    /**
     * Compiled Existence
     * @var boolean
     */
    public $exists = false;

    /**
     * Compiled Content Loaded
     * @var boolean
     */
    public $loaded = false;

    /**
     * Template was compiled
     * @var boolean
     */
    public $isCompiled = false;

    /**
     * Source Object
     * @var TemplateSource
     */
    public $source = null;

    /**
     * Metadata properties
     *
     * populated by Template::decodeProperties()
     *
     * @var array
     */
    public $properties = null;

    /**
     * create Compiled Object container
     *
     * @param TemplateSource $source source object this compiled object belongs to
     */
    public function __construct(TemplateSource $source)
    {
        $this->source = $source;
    }

    /**
     * Loads the template from disk
     * @param  Template $_smarty_tpl
     * @return void
     */
    public function load($_smarty_tpl)
    {
        include $this->filepath;
        $this->loaded = true;
        $this->isCompiled = true;
    }
}
