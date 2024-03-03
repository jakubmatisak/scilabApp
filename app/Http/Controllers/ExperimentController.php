<?php

namespace App\Http\Controllers;

use App\Models\Experiment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Experiments",
 *     description="API Endpoints for Experiments"
 * )
 */
 /**
 * @OA\SecurityScheme(
 *     type="http",
 *     securityScheme="bearerAuth",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class ExperimentController extends Controller
{
    /**
     * @OA\Get(
     *  path="/api/experiments",
     *  tags={"Experiments"},
     *  summary="Get a list of experiments",
     *  description="Returns all experiments",
     *  security={{"bearerAuth": {}}},
     *  @OA\Response(
     *      response=200,
     *      description="Successful operation",
     *      @OA\JsonContent(
     *          @OA\Property(
     *              property="experiments",
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="file_name", type="string", example="experiment_files/1622619815_1619954846_tcn.zcos"),
     *                  @OA\Property(property="context", type="string", example="{}"),
     *                  @OA\Property(property="output", type="string", example="{}"),
     *                  @OA\Property(property="save", type="integer", example=0),
     *                  @OA\Property(property="created_by", type="integer", example=1),
     *                  @OA\Property(property="created_at", type="string", format="date-time"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time")
     *                  )
     *              )
     *          )
     *      )
     *  )
     */
    public function index(Request $request)
    {
        try{
            $perPage = $request->query('perPage', 5);
            $sortByKey = $request->query('sortByKey', "id");
            $sortByOrder = $request->query('sortByOrder', "asc");
            $experiments =  Experiment::orderBy($sortByKey, $sortByOrder)->get();
            if($perPage == -1) {
                return response()->json(["experiments"=>["data"=>$experiments, "total"=>$experiments->count()]]);
            }
            $paginator = collect($experiments)->paginate($perPage);

            return response()->json(["experiments"=> $paginator], 200);
        }catch(\Exception $exception){
            return response()->json(["message"=>"Error - {$exception->getMessage()}"]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/experiments",
     *     tags={"Experiments"},
     *     summary="Create a new experiment",
     *     description="Stores a new experiment",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"file", "context", "output", "save"},
     *                  @OA\Property(property="file", type="string", format="binary", description="Experiment file"),
     *                  @OA\Property(property="context", type="string", description="Context"),
     *                  @OA\Property(property="output", type="string", description="Output"),
     *                  @OA\Property(property="save", type="integer", description="Save", enum={0, 1})
     *              ),
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="Accept",
     *         in="header",
     *         required=true,
     *         description="Authentication token",
     *         @OA\Schema(
     *             type="string",
     *             default="application/json"
     *         )
     *     ),
     *     @OA\Response(
     *          response=201,
     *          description="Experiment created successfully",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="simulation",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(
     *                          property="time",
     *                          type="number",
     *                      ),
     *                      @OA\Property(
     *                          property="height",
     *                          type="number",
     *                      ),
     *                      @OA\Property(
     *                          property="velocity",
     *                          type="number",
     *                      ),
     *                  ),
     *                  example={
     *                      {"time": 0.0, "height": 80.0, "velocity": 0.0},
     *                      {"time": 0.1, "height": 80.0, "velocity": 0.4801576}
     *                  }
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Invalid input",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="file",
     *                      type="array",
     *                      @OA\Items(
     *                          type="string",
     *                          example="The file field is required."
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="context",
     *                      type="array",
     *                      @OA\Items(
     *                          type="string",
     *                          example="The context field is required."
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="output",
     *                      type="array",
     *                      @OA\Items(
     *                          type="string",
     *                          example="The output field is required."
     *                      )
     *                  ),
     *              ),
     *          )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
            'name' => 'required|string',
            'context' => 'required|json',
            'output' => 'required|json',
            'save' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $file = $request->file('file');
        $originalFileName = $file->getClientOriginalName();
        $fileName = time().'_'.$originalFileName;
        $filePath = $file->storeAs('experiment_files', $fileName);

        $experiment = Experiment::create([
            'file_name' => $originalFileName,
            'file_path' => $filePath,
            'name' => $request->input('name'),
            'context' => $request->input('context'),
            'output' => $request->input('output'),
            'created_by' => auth()->id(),
        ]);

        $output_values = json_decode($request->input('output'));
        $input_values = json_decode($request->input('context'));

        $context = "";
        foreach ($input_values as $key => $value) {
            $context .= "Context.{$key}={$value};";
        }
        
        $script = "ssh -i ~/.ssh/id_rsa -p 2222 -q -o \"UserKnownHostsFile=/dev/null\" -o \"StrictHostKeyChecking=no\" root@localhost 'SCRIPT=\"loadXcosLibs();loadScicos();importXcosDiagram('\'/opt/bp-app/1622619815_1619954846_tcn.zcos\'');Context=struct();" . $context . "scicos_simulate(scs_m,list(),Context,'\'nw\'');\" export SCRIPT;' /opt/bp-app/run-script.sh";

        $result = shell_exec($script);

        $result = explode("\n\n", $result);
        array_shift($result);

        $result_array = [];
        foreach ($result as $string) {
            $string = trim($string);
            $values = array_map(function($item) {
                return floatval(trim($item));
            }, explode("\n", $string));

            $values_count = count($values);
            $output_count = count($output_values);

            if($values_count <= $output_count){
                $obj = [];
                for($i = 0; $i < $values_count; $i++){
                    $obj[$output_values[$i]] = $values[$i];
                }

                array_push($result_array, $obj);
            } else {
                array_push($result_array, $values);
            }
        }

        return response()->json(["simulation"=>$result_array], 201);
    }

    /**
     * @OA\Get(
     *      path="/api/experiments/{id}",
     *      tags={"Experiments"},
     *      summary="Get an experiment by ID",
     *      description="Returns an experiment based on ID",
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the experiment",
     *          @OA\Schema(type="string"),
     *          example=1
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="file_name", type="string", example="experiment_files/1622619815_1619954846_tcn.zcos"),
     *              @OA\Property(property="context", type="string", example="{}"),
     *              @OA\Property(property="output", type="string", example="{}"),
     *              @OA\Property(property="save", type="integer", example=0),
     *              @OA\Property(property="created_by", type="integer", example=1),
     *              @OA\Property(property="created_at", type="string", format="date-time"),
     *              @OA\Property(property="updated_at", type="string", format="date-time")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid ID supplied",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Invalid id."
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Experiment not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="There is no experiment with that id."
     *              )
     *          )
     *      )
     * )
     */
    public function show(string $id)
    {
        if(!$id){
            return response()->json(["message"=>"Invalid id."], 400);
        }

        try{
            $experiment = Experiment::findOrFail($id);

            return response()->json(["experiment"=>$experiment], 200);
        } catch(\Exception $_){
            return response()->json(["message"=>"There is no experiment with that id."], 400);
        }
    }

    /**
     * Get the specified schema.
     */
    public function getSchemaFile($id)
    {
        if(!$id){
            return response()->json(["message"=>"Invalid id."], 400);
        }

        try{
            $experiment = Experiment::findOrFail($id);
            $filePath = $experiment->file_path;

            return Storage::response($filePath);
        } catch(\Exception $_){
            return response()->json(["message"=>"There is no experiment with that id."], 400);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if(!$id){
            return response()->json(["message"=>"Invalid id."], 400);
        }

        try {
            $experiment = Experiment::findOrFail($id);
            $userId = auth()->id();

            if ($userId != $experiment->created_by) {
                return response()->json(["message"=>"You don't have permission to edit this experiment."], 403);
            }

            $validator = Validator::make($request->all(), [
                'file' => 'file',
                'name' => 'string',
                'context' => 'json',
                'output' => 'json',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $originalFileName = $experiment->file_name;
            $filePath = $experiment->file_path;
            $name = $request->input('name', $experiment->name);
            $context = $request->input('context', $experiment->context);
            $output = $request->input('output', $experiment->output);

            if ($request->has('file')) {
                Storage::delete($filePath);

                $file = $request->file('file');
                $originalFileName = $file->getClientOriginalName();
                $fileName = time().'_'.$originalFileName;
                $filePath = $file->storeAs('experiment_files', $fileName);
            }
            
            $experiment->update([
                'file_name' => $originalFileName,
                'file_path' => $filePath,
                'name' => $name,
                'context' => $context,
                'output' => $output,
            ]);

            $output_values = json_decode($output);
            $input_values = json_decode($context);

            $context = "";
            foreach ($input_values as $key => $value) {
                $context .= "Context.{$key}={$value};";
            }
            
            $script = "ssh -i ~/.ssh/id_rsa -p 2222 -q -o \"UserKnownHostsFile=/dev/null\" -o \"StrictHostKeyChecking=no\" root@localhost 'SCRIPT=\"loadXcosLibs();loadScicos();importXcosDiagram('\'/opt/bp-app/1622619815_1619954846_tcn.zcos\'');Context=struct();" . $context . "scicos_simulate(scs_m,list(),Context,'\'nw\'');\" export SCRIPT;' /opt/bp-app/run-script.sh";

            $result = shell_exec($script);

            $result = explode("\n\n", $result);
            array_shift($result);

            $result_array = [];
            foreach ($result as $string) {
                $string = trim($string);
                $values = array_map(function($item) {
                    return floatval(trim($item));
                }, explode("\n", $string));

                $values_count = count($values);
                $output_count = count($output_values);

                if($values_count <= $output_count){
                    $obj = [];
                    for($i = 0; $i < $values_count; $i++){
                        $obj[$output_values[$i]] = $values[$i];
                    }

                    array_push($result_array, $obj);
                } else {
                    array_push($result_array, $values);
                }
            }

            return response()->json(["simulation"=>$result_array], 201);

        } catch(\Exception $_) {
            return response()->json(["message"=>"There is no experiment with that id."], 404);
        }
    }

    /**
     * @OA\Delete(
     *
     *      path="/api/experiments/{id}",
     *      tags={"Experiments"},
     *      summary="Delete an experiment by ID",
     *      description="Deletes an experiment based on ID",
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the experiment",
     *          @OA\Schema(type="string"),
     *          example=1
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Experiment deleted successfully",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Experiment deleted successfully"
     *              ),
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=true
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid ID supplied",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Invalid id."
     *              ),
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=false
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Experiment not found",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="There is no experiment with that id."
     *              ),
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=false
     *              )
     *          )
     *      )
     * )
     */
    public function destroy(string $id)
    {
        if(!$id){
            return response()->json(["message"=>"Invalid id.", 'success' => false], 400);
        }
        try{
            $experiment = Experiment::findOrFail($id);
            $filePath = $experiment->file_path;

            try{
                Storage::delete($filePath);

                $experiment->delete();
            } catch(\Exception $exception){
                return response()->json(["message"=>"Error - {$exception->getMessage()}"], 500);
            }

            return response()->json(['message' => 'Experiment deleted successfully.', 'success' => true], 200);
        } catch(\Exception $_){
            return response()->json(["message"=>"There is no experiment with that id.", 'success' => false], 404);
        }
    }

    public function simulate(Request $request, string $id) {
        if(!$id){
            return response()->json(["message"=>"Invalid id.", 'success' => false], 400);
        }

        $validator = Validator::make($request->all(), [
            'context' => 'required|json',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $experiment = "";
        
        try{
            $experiment = Experiment::findOrFail($id);

            $output_values = json_decode($experiment->output);
            $input_values = json_decode($request->input('context'));

            $context = "";
            foreach ($input_values as $key => $value) {
                $context .= "Context.{$key}={$value};";
            }

            $script = "ssh -i ~/.ssh/id_rsa -p 2222 -q -o \"UserKnownHostsFile=/dev/null\" -o \"StrictHostKeyChecking=no\" root@localhost 'SCRIPT=\"loadXcosLibs();loadScicos();importXcosDiagram('\'/opt/bp-app/1622619815_1619954846_tcn.zcos\'');Context=struct();" . $context . "scicos_simulate(scs_m,list(),Context,'\'nw\'');\" export SCRIPT;' /opt/bp-app/run-script.sh";

            $result = shell_exec($script);
            
            $result = explode("\n\n", $result);
            array_shift($result);

            $result_array = [];
            foreach ($result as $string) {
                $string = trim($string);
                $values = array_map(function($item) {
                    return floatval(trim($item));
                }, explode("\n", $string));

                $values_count = count($values);
                $output_count = count($output_values);

                if($values_count <= $output_count){
                    $obj = [];
                    for($i = 0; $i < $values_count; $i++){
                        $obj[$output_values[$i]] = $values[$i];
                    }

                    array_push($result_array, $obj);
                } else {
                    array_push($result_array, $values);
                }
            }

            return response()->json(["simulation"=>$result_array], 201);
        } catch(\Exception $_){
            return response()->json(["message"=>"There is no experiment with that id."], 400);
        }
    }
}
