<?php
/**
 * Created by PhpStorm.
 * User: joe
 * Date: 18/02/18
 * Time: 02:21 م
 */

namespace Jlib\Misc;


class ClassModifire
{
    public static function extractNamespaceFromFile($src)
    {
        $ns = NULL;
        $handle = fopen($src, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (strpos($line, 'namespace') === 0) {
                    $parts = explode(' ', $line);
                    $ns = rtrim(trim($parts[1]), ';');
                    break;
                }
            }
            fclose($handle);
        }
        return $ns;
    }
}