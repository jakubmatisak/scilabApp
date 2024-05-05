<?php

namespace App\Services;

class ExperimentService
{
    public static function simulateExperiment(object $contextValues, array $outputValues, string $filePath) {
        $context = "";
        foreach ($contextValues as $key => $value) {
            $context .= "Context.{$key}={$value};";
        }

        $script = "SCRIPT=\"loadXcosLibs();loadScicos();importXcosDiagram('/var/www/scilabApp/storage/app/" . $filePath . "');Context=struct();" . $context . "scicos_simulate(scs_m,list(),Context,'nw');\" /var/www/scilabApp/docker/run-script.sh";
        
        $result = shell_exec($script);

        $result = explode("\n\n", $result);

        $result_array = [];
        foreach ($result as $string) {
            $string = trim($string);
            $values = array_map(function($item) {
                return floatval(trim($item));
            }, explode("\n", $string));

            $values_count = count($values);
            $output_count = count($outputValues);

            if($values_count <= $output_count){
                $obj = [];
                for($i = 0; $i < $values_count; $i++){
                    $obj[$outputValues[$i]] = $values[$i];
                }

                array_push($result_array, $obj);
            } else {
                array_push($result_array, $values);
            }
        }

        return $result_array;
    }
}