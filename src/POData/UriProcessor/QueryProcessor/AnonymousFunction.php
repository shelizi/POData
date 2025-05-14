<?php

namespace POData\UriProcessor\QueryProcessor;
use POData\Common\Messages;

/**
 * Class AnonymousFunction
 * @package POData\UriProcessor\QueryProcessor
 */
class AnonymousFunction
{
    /**
     * An array of parameters to the function represented by this instance
     * 
     * @var array
     */
    private $_parameters;

    /**
     * Parameters as string separated by comma
     * 
     * @var string
     */
    private $_parametersAsString;

    /**
     * body of the function represented by this instance
     * 
     * @var string
     */
    private $_code;

    /**
     * Reference to the anonymous function represented by this instance
     * reference will be the name of the function in the form char(0).lamba_n.
     * 
     * @var string
     */
    private $_reference = null;
    
    /**
     * Static array to store generated closures
     * 
     * @var array
     */
    private static $_closures = array();

    /**
     * Create new instance of AnonymousFunction
     * 
     * @param array  $parameters Array of parameters
     * @param string $code       Body of the function
     */
    public function __construct($parameters, $code)
    {
        $this->_parameters = $parameters;
        foreach ($this->_parameters as $parameter) {
            if (strpos($parameter, '$') !== 0) {
                throw new \InvalidArgumentException(
                    Messages::anonymousFunctionParameterShouldStartWithDollarSymbol()
                );
            } 
        }

        $this->_parametersAsString = implode(', ', $this->_parameters);
        $this->_code = $code;
    }

    /**
     * Gets function parameters as array.
     * 
     * @return array
     */
    public function getParameters()
    {
        return $this->_parameters;
    }

    /**
     * Gets function parameters as string seperated by comma
     * 
     * @return string
     */
    public function getParametersAsString()
    {
        return $this->_parametersAsString;
    }

    /**
     * Gets number of parameters
     * 
     * @return int
     */
    public function getParametersCount()
    {
        return count($this->_parameters);
    }

    /**
     * Gets function body
     * 
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Gets reference to the anonymous function.
     * 
     * @return string
     */
    public function getReference()
    {
        if (is_null($this->_reference)) {
            // 使用唯一ID作為函數名稱
            $this->_reference = 'lambda_' . uniqid();
            // 創建匯名函數對象
            $closureCode = "return function({$this->_parametersAsString}) {
                {$this->_code}
            };"; 
            self::$_closures[$this->_reference] = eval($closureCode);
        }

        return chr(0) . $this->_reference;
    }
    
    /**
     * Call the anonymous function with the given parameters
     * 
     * @param string $functionName Function reference name
     * @param array $params Parameters to pass to the function
     * 
     * @return mixed Result of the function call
     */
    public static function callFunction($functionName, $params)
    {
        // 移除開頭的 chr(0)
        $name = substr($functionName, 1);
        if (isset(self::$_closures[$name])) {
            return call_user_func_array(self::$_closures[$name], $params);
        }
        
        return null;
    }
}