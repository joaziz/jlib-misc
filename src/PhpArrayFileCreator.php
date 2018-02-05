<?php
/**
 * Created by PhpStorm.
 * User: joe
 * Date: 28/01/18
 * Time: 03:11 م
 */

namespace Jlib\Misc;


class PhpArrayFileCreator
{
    public static function createFile($file, $array = [], $return = false)
    {
        $cont = "<?php\n return \n" . self::arrayToString($array) . ";";


        if ($return)
            return $cont;


        File::put($file, $cont);

    }

    public static function writeArrayToFile($file, $array)
    {
        $content = require $file;
        if (is_array($content)) {

            foreach ($array as $key => $item)
                if (!isset($content[$key]))
                    $content[$key] = $item;

            self::createFile($file, $content);
        }

    }

    public static function arrayToString($array)
    {
        $data = "";

        foreach ($array as $item => $value) {
            $data .= "        \"$item\"=>\"$value\",\n";
        }

        return "    [\n" . $data . "    ]";

    }
}