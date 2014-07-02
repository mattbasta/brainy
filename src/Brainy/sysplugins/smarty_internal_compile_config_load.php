<?php
/**
 * Smarty Internal Plugin Compile Config Load
 *
 * Compiles the {config load} tag
 *
 * @package Brainy
 * @subpackage Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Config Load Class
 *
 * @package Brainy
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Config_Load extends Smarty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $required_attributes = array('file');
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $shorttag_order = array('file','section');
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $optional_attributes = array('section', 'scope');

    /**
     * Compiles code for the {config_load} tag
     *
     * @param  array  $args     array with attributes from parser
     * @param  object $compiler compiler object
     * @return string compiled code
     */
    public function compile($args, $compiler) {
        static $_is_legal_scope = array('local' => true,'parent' => true,'root' => true,'global' => true);
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        // save posible attributes
        $conf_file = $_attr['file'];
        if (isset($_attr['section'])) {
            $section = $_attr['section'];
        } else {
            $section = 'null';
        }
        $scope = 'local';
        // scope setup
        if (isset($_attr['scope'])) {
            $_attr['scope'] = trim($_attr['scope'], "'\"");
            if (isset($_is_legal_scope[$_attr['scope']])) {
                $scope = $_attr['scope'];
           } else {
                $compiler->trigger_template_error('illegal value for "scope" attribute', $compiler->lex->taglineno);
           }
        }
        // create config object
        $_output = "\$_config = new Smarty_Internal_Config($conf_file, \$_smarty_tpl->smarty, \$_smarty_tpl);\n";
        $_output .= "\$_config->loadConfigVars($section, '$scope');\n";

        return $_output;
    }

}
