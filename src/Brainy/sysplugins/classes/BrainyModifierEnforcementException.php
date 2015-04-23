<?php

/**
 * Exception used to indicate a failure to properly wrap expressions with
 * a modifier.
 *
 * @package Brainy
 * @see Smarty::$enforce_expression_modifiers
 */
class BrainyModifierEnforcementException extends SmartyCompilerException { }
