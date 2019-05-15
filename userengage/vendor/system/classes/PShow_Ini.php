<?php

/**
 * File from http://PrestaShow.pl
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *  @authors     PrestaShow.pl <kontakt@prestashow.pl>
 *  @copyright   2018 PrestaShow.pl
 *  @license     https://prestashow.pl/license
 */
class PShow_Ini
{

    /**
     * @param string $file
     * @return mixed
     */
    public static function read($file)
    {
        return ((file_exists($file)) ? parse_ini_file($file, true) : false);
    }

    /**
     * @param array $array
     * @param string $file
     * @return boolean
     */
    public static function write(array $array, $file)
    {
        $res = array();

        foreach ($array as $key => $val) {
            if (!is_array($val)) {
                $res[] = "$key = " . (is_numeric($val) ? $val : '"' . $val . '"');
                continue;
            }

            $res[] = "[$key]";
            foreach ($val as $skey => $sval) {
                $res[] = "$skey = " . (is_numeric($sval) ? $sval : '"' . $sval . '"');
            }
        }

        return file_put_contents($file, implode("\r\n", $res));
    }
}
