<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class MinworkAutoloader
{

    const DEFAULT_FILE_EXTENSION = ".php";

    const DEFAULT_NAMESPACE_SEPARATOR = "\\";

    const DEFAULT_DIR_SEPARATOR = "/";

    const DEFAULT_NAMESPACE_LOWERCASE = true;

    /**
     * Extension of files that can match given namespace
     * 
     * @var string
     */
    private $fileExtension;

    /**
     * Namespace part that will be trimmed before trying to load class (according to PSR-4)
     * 
     * @var string
     */
    private $basicNamespace;

    /**
     * Search directory for classes (namespace converted to file path will be appended to this directory path)
     * 
     * @var string
     */
    private $basicPath;

    /**
     * Separator for namespace which will be used to conver namespace parts to directory path
     * 
     * @var string
     */
    private $namespaceSeparator;

    /**
     * Whether or not class namespace will be converted to lowercase before trying to match file
     * 
     * @var bool
     */
    private $namespaceLowercase;

    /**
     * Creates new autoloader
     *
     * @param string|null $namespace
     *            Basic class namespace (ignored in creation of file path)
     * @param string|null $path
     *            Basic path to search for classes files
     */
    public function __construct(?string $namespace = null, ?string $path = null): void
    {
        $this->basicNamespace = $namespace ?? 'Minwork';
        $this->basicPath = $path == self::DEFAULT_DIR_SEPARATOR ? '' : ($path ?? preg_replace('/' . DIRECTORY_SEPARATOR . 'Core$/', '', __DIR__));
        $this->fileExtension = self::DEFAULT_FILE_EXTENSION;
        $this->namespaceSeparator = self::DEFAULT_NAMESPACE_SEPARATOR;
        $this->namespaceLowercase = self::DEFAULT_NAMESPACE_LOWERCASE;
        if (substr($this->basicPath, - 1) === self::DEFAULT_DIR_SEPARATOR || substr($this->basicPath, - 1) === DIRECTORY_SEPARATOR) {
            $this->basicPath = substr($this->basicPath, 0, - 1);
        }
    }

    /**
     * Automatically register Minwork autoloader
     */
    public static function registerDefault(): self
    {
        $self = new self();
        return $self->register();
    }

    /**
     * Sets basic namespace
     *
     * @param string $namespace            
     */
    public function setBasicNamespace(string $namespace): self
    {
        $this->basicNamespace = $namespace;
        return $this;
    }

    /**
     * Sets basic class search path
     *
     * @param string $path            
     */
    public function setBasicPath(string $path): self
    {
        $this->basicPath = $path;
        return $this;
    }

    /**
     * Sets classes file extension
     *
     * @param string $extension            
     */
    public function setFileExtension(string $extension): self
    {
        $this->fileExtension = $extension;
        return $this;
    }

    /**
     * Sets namespace separator
     *
     * @param string $separator            
     */
    public function setNamespaceSeparator(string $separator): self
    {
        $this->namespaceSeparator = $separator;
        return $this;
    }

    /**
     * Add to autoloader queue
     */
    public function register(): self
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
    public function unregister(): self
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
     * @return bool
     */
    public function autoload(string $className): bool
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
