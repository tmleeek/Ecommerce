<?php
class Unleaded_Import_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getCategory($line){

        $CS = "/";

        $cats = array();

        if(isset($line['Year']) && $line['Year'] != ''){
            $cats[] = "YMM" . $CS . $line['Year'];

            if(isset($line['Make']) && $line['Make'] != ''){
                $cats[] = "YMM" . $CS . $line['Year'] . $CS . $line['Make'];

                if(isset($line['Model']) && $line['Model'] != ''){
                    $cats[] = "YMM" . $CS . $line['Year'] . $CS . $line['Make'] . $CS . $line['Model'];
                }

            }

        }

        return $cats;

    }

    public function duplicate($line,$value,$count){

        $tmp = array();

        $i = 1;

        while ($i <= $count) {
            $tmp[] = $value;
            $i++;
        }

        return $tmp;

    }

}