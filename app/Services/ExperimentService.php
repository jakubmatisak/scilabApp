<?php

namespace App\Services;

class ExperimentService
{
    private static function convertToContextAware($equation) {
        return preg_replace_callback('/\b(?!Context\.)([a-zA-Z_]\w*)\b/', function ($matches) {
            return "Context." . $matches[1];
        }, $equation);
    }

    public static function simulateExperiment(object $contextValues, array $outputValues, string $filePath) {
        $context = "";
        foreach ($contextValues as $key => $value) {
            if (is_numeric($value)) {
                $context .= "Context.{$key}={$value};";
            } else {
                $context .= "Context.{$key}=" . ExperimentService::convertToContextAware($value) . ";";
            }
        }

        $script = "SCRIPT=\"loadXcosLibs();loadScicos();importXcosDiagram('/var/www/scilabApp/storage/app/" . $filePath . "');Context=struct();" . $context . "scicos_simulate(scs_m,list(),Context,'nw');\" /var/www/scilabApp/docker/run-script.sh";

        $result = shell_exec($script);

        $result = explode("\n", $result);
        while (!preg_match('/\d/', $result[0])) {
            array_shift($result);
        }
        $result = implode("\n", $result);

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

        return count($result_array) > 1 ? $result_array : [];
    }

    public static function getSimulationContext(string $filePath) {
        $script = "SCRIPT=\"loadXcosLibs();loadScicos();importXcosDiagram('/var/www/scilabApp/storage/app/" . $filePath . "');disp(scs_m.props.context);\" /var/www/scilabApp/docker/run-script.sh";
        $result = shell_exec($script);

        $result = explode("\n", $result);
        $idx = 0;
        // Remove all lines that does not contain any key nor value
        while($idx < count($result)){
            if(!preg_match('/\d/', $result[$idx])){
                array_splice($result, $idx, 1);
            } else {
                $idx++;
            }
        }

        // Trim the lines
        for($idx = 0; $idx < count($result); $idx++) {
            $line = $result[$idx];
            $line = preg_replace("/^!/", "", $line);
            $line = preg_replace("/!$/", "", $line);
            $line = trim($line);
            $line = preg_replace("/\s+/", " ", $line);
            $result[$idx] = $line;
        }

        // Split the result into arrays containing distinct values
        $result_array = [];
        $array = [];
        for($idx = 0; $idx < count($result); $idx++) {
            if(preg_match('/^[a-zA-Z]+[a-zA-Z0-9_]*[ ]*=/', $result[$idx])){
                if($idx != 0){
                    array_push($result_array, $array);
                }
                $array = [];
            }
            array_push($array, $result[$idx]);
        }

        $result = [];
        // Connect the inner arrays into key value pairs
        for($idx = 0; $idx < count($result_array); $idx++) {
            $line = implode("", $result_array[$idx]);
            $line = preg_replace("/;$/", "", $line);
            $keyValue = explode("=", $line, 2);
            $key = trim($keyValue[0]);
            $value = trim($keyValue[1]);
            $result[$key] = $value;
        }

        return $result;
    }
}
