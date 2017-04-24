<?php
class MinworkAutoloader
{

    const DEFAULT_FILE_EXTENSION = ".php";

    const DEFAULT_NAMESPACE_SEPARATOR = "\\";

    const DEFAULT_DIR_SEPARATOR = "/";

    const DEFAULT_NAMESPACE_LOWERCASE = true;

    private $fileExtension;

    private $basicNamespace;

    private $basicPath;

    private $namespaceSeparator;

    private $namespaceLowercase;

    /**
     * Creates new autoloader
     *
     * @param string $namespace
     *            Basic class namespace (ignored in creation of file path)
     * @param string $path
     *            Basic path to search for classes files
     */
    public function __construct($namespace = null, $path = null)
    {
        $this->basicNamespace = $namespace ?? 'Minwork';
        $this->basicPath = ($path == self::DEFAULT_DIR_SEPARATOR ? "" : is_null($path) ? preg_replace('/'.DIRECTORY_SEPARATOR.'Core$/', '', __DIR__) : $path);
        $this->fileExtension = self::DEFAULT_FILE_EXTENSION;
        $this->namespaceSeparator = self::DEFAULT_NAMESPACE_SEPARATOR;
        $this->namespaceLowercase = self::DEFAULT_NAMESPACE_LOWERCASE;
        if (substr($this->basicPath, -1) === self::DEFAULT_DIR_SEPARATOR || substr($this->basicPath, -1) === DIRECTORY_SEPARATOR) {
            $this->basicPath = substr($this->basicPath, 0, - 1);
        }
    }

    /**
     * Automatically register Minwork autoloader
     */
    public static function registerDefault()
    {
        $self = new self();
        return $self->register();
    }
    
    /**
     * Sets basic namespace
     *
     * @param string $namespace            
     */
    public function setBasicNamespace($namespace)
    {
        $this->basicNamespace = $namespace;
        return $this;
    }

    /**
     * Sets basic class search path
     *
     * @param string $path            
     */
    public function setBasicPath($path)
    {
        $this->basicPath = $path;
        return $this;
    }

    /**
     * Sets classes file extension
     *
     * @param string $extension            
     */
    public function setFileExtension($extension)
    {
        $this->fileExtension = $extension;
        return $this;
    }

    /**
     * Sets namespace separator
     *
     * @param string $separator            
     */
    public function setNamespaceSeparator($separator)
    {
        $this->namespaceSeparator = $separator;
        return $this;
    }

    /**
     * Add to autoloader queue
     */
    public function register()
    {
        spl_autoload_register(array(
            $this,
            'autoload'
        ));
        return $this;
    }

    /**
     * Remove from autoloader queue
     */
    public function unregister()
    {
        spl_autoload_unregister(array(
            $this,
            'autoload'
        ));
        return $this;
    }

    /**
     * Loads class or interface
     *
     * @param string $className
     *            Name of the class to load
     */
    public function autoload($className)
    {
        $class = $className;
        $filename = '';
        
        if (! empty($this->basicNamespace)) {
            if (mb_strtolower(substr($className, 0, strlen($this->basicNamespace . $this->namespaceSeparator))) === mb_strtolower($this->basicNamespace . $this->namespaceSeparator)) {
                // Cut basic namespace
                $class = substr($className, strlen($this->basicNamespace . $this->namespaceSeparator));
            } else {
                // Can't load namespace doesn't fir
                return false;
            }
        }
        // Extract namespace and class
        if (false !== ($namespacePosition = strripos($class, $this->namespaceSeparator))) {
            $namespace = substr($class, 0, $namespacePosition);
            $class = substr($class, $namespacePosition + 1);
            $ns = str_replace($this->namespaceSeparator, DIRECTORY_SEPARATOR, $namespace);
            $filename = ($this->namespaceLowercase ? strtolower($ns) : $ns) . DIRECTORY_SEPARATOR;
        }
        
        $filename .= str_replace('_', DIRECTORY_SEPARATOR, $class) . $this->fileExtension;
        $path = (! empty($this->basicPath) ? $this->basicPath . DIRECTORY_SEPARATOR : '') . $filename;
        
        if (file_exists($path)) {
            require $path;
            return true;
        }
        
        return false;
    }
}
