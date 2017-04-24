<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Helper;

/**
 * Basic file management functions
 * 
 * @author Christopher Kalkhoff
 *        
 */
class FileHelper
{

    const DIR_SEPARATOR = "/";

    /**
     * Recursively create specified directory
     *
     * @param string $dir            
     * @return boolean
     */
    public static function createDirectory(string $dir)
    {
        $dir = str_replace(DIRECTORY_SEPARATOR, self::DIR_SEPARATOR, Formatter::removeLeadingSlash($dir));
        if (! is_dir($dir)) {
            $path = explode(self::DIR_SEPARATOR, $dir);
            $tmpPath = "";
            foreach ($path as $subFolder) {
                if (empty($subFolder)) {
                    $tmpPath .= self::DIR_SEPARATOR;
                }
                
                $tmpPath .= $subFolder . self::DIR_SEPARATOR;
                if (! is_dir($tmpPath)) {
                    mkdir($tmpPath);
                }
            }
        }
        return true;
    }

    /**
     * Recursively remove specified directory
     *
     * @param string $dir            
     * @return boolean
     */
    public static function removeDirectory(string $dir)
    {
        if (! Formatter::endsWith($dir, DIRECTORY_SEPARATOR) && ! Formatter::endsWith($dir, self::DIR_SEPARATOR)) {
            $dir .= self::DIR_SEPARATOR;
        }
        
        if (! is_dir($dir)) {
            return false;
        }
        
        if ($handle = opendir($dir)) {
            $dirsToVisit = array();
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    if (is_dir($dir . $file)) {
                        $dirsToVisit[] = $dir . $file;
                    } else 
                        if (is_file($dir . $file)) {
                            unlink($dir . $file);
                        }
                }
            }
            closedir($handle);
            foreach ($dirsToVisit as $w) {
                self::removeDirectory($w);
            }
        }
        rmdir($dir);
        return true;
    }
}