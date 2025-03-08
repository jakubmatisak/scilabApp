<?php

namespace App\Services;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;

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
        Log::info("SIM: preparing simulation with:", ['context' => $context]);

        $script = "SCRIPT=\"loadXcosLibs();loadScicos();importXcosDiagram('/var/www/scilabApp/storage/app/" . $filePath . "');Context=struct();" . $context . "scicos_simulate(scs_m,list(),Context,'nw');\" /var/www/scilabApp/docker/run-script.sh";
        $timeout = 6;
        $maxRetries = 3;
        $result = "";

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $execResult = ExperimentService::executeWithTimeout($script, $timeout);

                if (empty($execResult['stderr'])) {
                    $result = $execResult['stdout'];
                    Log::info("SIM: simulation [" . $attempt . "] done, result:", ['length' => strlen($result), 'execTime' => $execResult['execTime']]);
                    break;
                }
                Log::error("SIM: simulation [" . $attempt . "/" . $maxRetries . "] has error output: ", ['error' => $execResult['stderr']]);

            } catch (\Exception $e) {
                Log::error("SIM: simulation failed with exception: " . $e->getMessage());
                throw new HttpException(500, 'Simulation execution failed');
            }
        }

        if ($result == "") {
            Log::error("SIM: ending unsuccessful simulation");
            throw new HttpException(500, 'Simulation execution failed');
        }

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
        Log::info("SIM: simulation processed - END");

        return count($result_array) > 1 ? $result_array : [];
    }

    public static function getSimulationContext(string $filePath) {
        $script = "SCRIPT=\"loadXcosLibs();loadScicos();importXcosDiagram('/var/www/scilabApp/storage/app/" . $filePath . "');disp(scs_m.props.context);\" /var/www/scilabApp/docker/run-script.sh";
        $timeout = 8;
        $maxRetries = 3;
        $result = "";

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $execResult = ExperimentService::executeWithTimeout($script, $timeout);

                if (empty($execResult['stderr'])) {
                    $result = $execResult['stdout'];
                    Log::info("G-CONTEXT: try [" . $attempt . "] done, result:", ['length' => strlen($result), 'execTime' => $execResult['execTime']]);
                }
                Log::warning("G-CONTEXT: try [" . $attempt . "/" . $maxRetries . "] has error output: ", ['error' => $execResult['stderr']]);

            } catch (\Exception $e) {
                Log::error("G-CONTEXT: failed with exception: ". $e->getMessage());
                throw new HttpException(500, 'Failed with exception');
            }
        }

        if ($result == "") {
            Log::error("G-CONTEXT: ending unsuccessful extraction");
            throw new HttpException(500, 'Extraction result is empty');
        }

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

    public static function executeWithTimeout(string $command, int $timeoutInSeconds): array
    {
        $descriptors = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"],
        ];

        $process = proc_open($command, $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new \RuntimeException("Failed to start the process.");
        }

        $startTime = time();
        $stdout = '';
        $stderr = '';

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        try {
            while (true) {
                $status = proc_get_status($process);
                if (!$status['running']) {
                    break;
                }
                $stdout .= stream_get_contents($pipes[1]);
                $stderr .= stream_get_contents($pipes[2]);

                if ((time() - $startTime) > $timeoutInSeconds) {
                    proc_terminate($process);
                    return ['stdout' => $stdout, 'stderr' => "Process timed out after {$timeoutInSeconds} seconds, error stack: " . $stderr];
                }
                usleep(250000);
            }
            $stdout .= stream_get_contents($pipes[1]);
            $stderr .= stream_get_contents($pipes[2]);

        } finally {
            foreach ($pipes as $pipe) {
                if (is_resource($pipe)) {
                    fclose($pipe);
                }
            }
            proc_close($process);
        }
        return ['stdout' => $stdout, 'stderr' => $stderr, 'execTime' => (time() - $startTime)];
    }
}
