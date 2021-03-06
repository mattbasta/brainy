<?php
/**
 * @package Brainy
 * @author Matt Basta
 * @author Uwe Tews
 */

namespace Box\Brainy\Templates;

use Box\Brainy\Brainy;

trait TemplateData
{
    /**
     * template variables
     *
     * @var      array
     * @internal
     */
    public $tpl_vars = array();
    /**
     * Parent template (if any)
     *
     * @var      Template
     * @internal
     * @todo     This should probably be moved to TemplateBase
     */
    public $parent = null;

    /**
     * Assigns $value to the variable in $var. If an associative array is
     * passed as the only parameter, it is a mapping of variables to assign to
     * the values to assign to them.
     *
     * @param  array|string    $var   the template variable name(s)
     * @param  mixed|null|void $value the value to assign
     * @param  int|void        $scope the scope to associate with the Smarty_Variable instance
     * @return TemplateData current TemplateData (or Smarty or Template) instance for chaining
     */
    public function assign($var, $value = null, $scope = -1)
    {
        if (is_array($var)) {
            foreach ($var as $_key => $_val) {
                if ($_key != '') {
                    $this->assignSingleVar($_key, $_val, $scope);
                }
            }
        } else {
            if ($var != '') {
                $this->assignSingleVar($var, $value, $scope);
            }
        }

        return $this;
    }

    /**
     * Assigns $value to the variale $var.
     *
     * @param  string $var   the template variable name
     * @param  mixed  $value the value to assign
     * @param  int    $scope the scope to associate with the Smarty_Variable
     * @return void
     */
    protected function assignSingleVar($var, &$value, $scope = -1)
    {
        if ($scope === -1) {
            $scope = Brainy::$default_assign_scope;
        }

        $this->tpl_vars[$var] = $value;

        if ($scope === Brainy::SCOPE_LOCAL) {
            return;
        }

        if ($scope === Brainy::SCOPE_PARENT) {
            if ($this->parent != null) {
                $this->parent->tpl_vars[$var] = $value;
            }
        } elseif ($scope === Brainy::SCOPE_ROOT || $scope === Brainy::SCOPE_GLOBAL) {
            $pointer = $this->parent;
            while ($pointer != null) {
                $pointer->tpl_vars[$var] = $value;
                $pointer = $pointer->parent;
            }
        }

        if ($scope === Brainy::SCOPE_GLOBAL) {
            Brainy::$global_tpl_vars[$var] = $value;
        }
    }

    /**
     * Assigns a global Smarty variable to the global scope.
     *
     * @param  string $varname the global variable name
     * @param  mixed  $value   the value to assign
     * @return TemplateData current TemplateData (or Smarty or Template) instance for chaining
     * @todo   This may not work with multiple Brainy instances.
     */
    public function assignGlobal($varname, $value = null)
    {
        if (!$varname) {
            return $this;
        }

        Brainy::$global_tpl_vars[$varname] = $value;

        $ptr = $this;
        while ($ptr instanceof Template) {
            $ptr->tpl_vars[$varname] = clone Brainy::$global_tpl_vars[$varname];
            $ptr = $ptr->parent;
        }

        return $this;
    }

    /**
     * Returns a single or all assigned template variables
     *
     * @param  string       $varname        Name of variable to process, or null to return all
     * @param  TemplateData $ptr            Optional reference to data object
     * @param  boolean      $search_parents Whether to include results from parent scopes
     * @return string|array variable value or or array of variables
     */
    public function getTemplateVars($varname = null, $ptr = null, $search_parents = true)
    {

        if (isset($varname)) {
            return $this->getVariable($varname, $ptr, $search_parents, false);
        }

        $output = array();
        if ($ptr === null) {
            $ptr = $this;
        }
        while ($ptr !== null) {
            foreach ($ptr->tpl_vars as $key => $var) {
                if (!array_key_exists($key, $output)) {
                    $output[$key] = $var;
                }
            }
            // not found, try at parent
            $ptr = $search_parents ? $ptr->parent : null;
        }
        if ($search_parents && isset(Brainy::$global_tpl_vars)) {
            foreach (Brainy::$global_tpl_vars as $key => $var) {
                if (!array_key_exists($key, $output)) {
                    $output[$key] = $var;
                }
            }
        }

        return $output;
    }

    /**
     * Clear the given assigned template variable.
     *
     * @param  string|string[] $varName The template variable(s) to clear
     * @return TemplateData current TemplateData (or Smarty or Template) instance for chaining
     */
    public function clearAssign($varName)
    {
        if (is_array($varName)) {
            foreach ($varName as $var) {
                unset($this->tpl_vars[$var]);
            }
        } else {
            unset($this->tpl_vars[$varName]);
        }

        return $this;
    }

    /**
     * Clear all the assigned template variables.
     * @return TemplateData current TemplateData instance for chaining
     */
    public function clearAllAssign()
    {
        $this->tpl_vars = array();
        if ($this->parent) {
            $this->applyDataFrom($this->parent->getTemplateVars());
        }
        return $this;
    }

    /**
     * Return the contents of an assigned variable.
     *
     * @param  string            $variable       the name of the Smarty variable
     * @param  TemplateData|null $_ptr           Optional reference to the data object
     * @param  boolean           $search_parents Whether to search in the parent scope
     * @param  boolean           $error_enable   Whether to raise an error when the variable is not found.
     * @return mixed The contents of the variable.
     */
    public function getVariable($variable, $_ptr = null, $search_parents = true, $error_enable = true)
    {
        if ($_ptr === null) {
            $_ptr = $this;
        }
        while ($_ptr !== null) {
            if (isset($_ptr->tpl_vars[$variable])) {
                // found it, return it
                return $_ptr->tpl_vars[$variable];
            }
            // not found, try at parent
            if ($search_parents) {
                $_ptr = $_ptr->parent;
            } else {
                $_ptr = null;
            }
        }
        if (isset(Brainy::$global_tpl_vars[$variable])) {
            // found it, return it
            return Brainy::$global_tpl_vars[$variable];
        }

        return null;
    }


    /**
     * Copies each variable from the source into this object, creating new
     * `Variable` objects along the way.
     * @param  TemplateData $source
     * @param  bool|void    $override Whether to override existing values
     * @return void
     */
    public function cloneDataFrom(&$source, $override = true)
    {
        foreach ($source->tpl_vars as $name => &$var) {
            if (!$override && isset($this->tpl_vars[$name])) {
                continue;
            }
            $this->tpl_vars[$name] = $var;
        }
    }

    /**
     * Applies all of the data to the current object
     * @param  TemplateData $target
     * @param  bool|void    $override Whether to override existing values
     * @return void
     */
    public function applyDataFrom(array $source, $override = true)
    {
        foreach ($source as $name => &$value) {
            if (!$override && isset($this->tpl_vars[$name])) {
                continue;
            }
            $this->tpl_vars[$name] = $value;
        }
    }
}
