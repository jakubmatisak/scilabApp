<?php

namespace App\Http\Controllers;

use App\Models\Experiment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Services\ExperimentService;

 /**
 * @OA\Tag(
 *     name="Experiments",
 *     description="API Endpoints for Experiments"
 * )
 */
class ExperimentController extends Controller
{
    /**
     *  @OA\Get(
     *      path="/api/experiments",
     *      tags={"Experiments"},
     *      summary="Get a list of experiments",
     *      description="Returns all experiments",
     *      security={{"bearerAuth": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="experiments",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="file_name", type="string", example="experiment_files/1622619815_1619954846_tcn.zcos"),
     *                      @OA\Property(property="context", type="string", example="{}"),
     *                      @OA\Property(property="output", type="string", example="{}"),
     *                      @OA\Property(property="save", type="integer", example=0),
     *                      @OA\Property(property="created_by", type="integer", example=1),
     *                      @OA\Property(property="created_at", type="string", format="date-time"),
     *                      @OA\Property(property="updated_at", type="string", format="date-time")
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
            $search = $request->query('search', "");
            $experiments =  Experiment::orderBy($sortByKey, $sortByOrder)->where('name', 'LIKE', '%' . $search . '%')->get();
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
     *  @OA\Post(
     *      path="/api/experiments",
     *      tags={"Experiments"},
     *      summary="Create a new experiment",
     *      description="Stores a new experiment",
     *      security={{"bearerAuth": {}}},
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"file", "name", "context", "output", "save"},
     *                  @OA\Property(property="file", type="string", format="binary", description="Experiment file"),
     *                  @OA\Property(property="name", type="string", description="Name", example="Experiment"),
     *                  @OA\Property(property="context", type="string", description="Context", example="{""time"": 15, ""H"": ""8*2^3""}"),
     *                  @OA\Property(property="output", type="string", description="Output", example="[""time"", ""velocity"", ""height""]"),
     *                  @OA\Property(property="save", type="integer", description="Save", enum={0, 1})
     *              ),
     *         ),
     *      ),
     *      @OA\Parameter(
     *         name="Accept",
     *         in="header",
     *         required=true,
     *         description="Authentication token",
     *         @OA\Schema(
     *             type="string",
     *             default="application/json"
     *         )
     *      ),
     *      @OA\Response(
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
     *      ),
     *      @OA\Response(
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
     *                      property="name",
     *                      type="array",
     *                      @OA\Items(
     *                          type="string",
     *                          example="The name field is required."
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
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Name is required when saving experiment",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="error_message",
     *                  type="string",
     *                  example="Name is required when saving the experiment."
     *              )
     *          )
     *      )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
            'name' => 'string',
            'context' => 'required',
            'output' => 'required|json',
            'save' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $saveString = $request->input('save', false);
        $save = filter_var($saveString, FILTER_VALIDATE_BOOLEAN);

        if ($save && !$request->has('name')) {
            return response()->json(['error_message' => "Name is required when saving the experiment."], 400);
        }

        $file = $request->file('file');
        $originalFileName = $file->getClientOriginalName();
        $fileName = time().'_'.$originalFileName;
        $filePath = $file->storeAs('experiment_files', $fileName);
        $inputContext = json_decode($request->input('context'), true);

        if($save){
            $experiment = Experiment::create([
                'file_name' => $originalFileName,
                'file_path' => $filePath,
                'name' => $request->input('name'),
                'context' => ['data' => $inputContext],
                'output' => $request->input('output'),
                'created_by' => auth()->id(),
            ]);
            
            return response()->json(["experiment"=>$experiment], 201);
        }

        $input_values = collect($inputContext)
            ->sortBy('order')
            ->mapWithKeys(fn($item) => [$item['key'] => $item['value']])
            ->toArray();
            
        $output_values = json_decode($request->input('output'));

        $result_array = ExperimentService::simulateExperiment((object)$input_values, $output_values, $filePath);

        if(!$save){
            Storage::delete($filePath);
        }

        return response()->json(["simulation"=>$result_array], 200);
    }

    /**
     *  @OA\Get(
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
     *              @OA\Property(
     *                  property="experiment",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="file_name", type="string", example="experiment_files/example.zcos"),
     *                  @OA\Property(property="context", type="string", example="{}"),
     *                  @OA\Property(property="output", type="string", example="{}"),
     *                  @OA\Property(property="save", type="integer", example=0),
     *                  @OA\Property(property="created_by", type="integer", example=1),
     *                  @OA\Property(property="created_at", type="string", format="date-time"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time")
     *              )
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
            return response()->json(["message"=>"There is no experiment with that id."], 404);
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
            return response()->json(["message"=>"There is no experiment with that id."], 404);
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
     *  @OA\Post(
     *      path="/api/experiments/{id}",
     *      tags={"Experiments"},
     *      summary="Update existing experiment",
     *      description="Updates experiment",
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the experiment",
     *          @OA\Schema(type="string"),
     *          example=1
     *      ),
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(property="file", type="string", format="binary", description="Experiment file"),
     *                  @OA\Property(property="name", type="string", description="Name", example="Experiment"),
     *                  @OA\Property(property="context", type="string", description="Context", example="{""time"": 15, ""H"": ""8*2^3""}"),
     *                  @OA\Property(property="output", type="string", description="Output", example="[""time"", ""velocity"", ""height""]"),
     *              ),
     *         ),
     *      ),
     *      @OA\Parameter(
     *         name="Accept",
     *         in="header",
     *         required=true,
     *         description="Authentication token",
     *         @OA\Schema(
     *             type="string",
     *             default="application/json"
     *         )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Experiment updated successfully",
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
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Invalid input",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="name",
     *                      type="array",
     *                      @OA\Items(
     *                          type="string",
     *                          example="The name field must be a string."
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="context",
     *                      type="array",
     *                      @OA\Items(
     *                          type="string",
     *                          example="The context field must be a valid JSON string."
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="output",
     *                      type="array",
     *                      @OA\Items(
     *                          type="string",
     *                          example="The output field must be a valid JSON string."
     *                      )
     *                  ),
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="Missing permissions",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="You don't have permission to edit this experiment."
     *              ),
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
    public function update(Request $request, string $id)
    {
        if(!$id){
            return response()->json(["message"=>"Invalid id."], 400);
        }

        try {
            $experiment = Experiment::findOrFail($id);
            $userId = auth()->id();
            $user = User::findOrFail($userId);

            if ($userId != $experiment->created_by && $user->is_admin != true) {
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

            $result_array = ExperimentService::simulateExperiment($input_values, $output_values, $filePath);

            return response()->json(["simulation"=>$result_array], 201);

        } catch(\Exception $_) {
            return response()->json(["message"=>"There is no experiment with that id."], 404);
        }
    }

    /**
     *  @OA\Delete(
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
     *              )
     *          )
     *      )
     * )
     */
    public function destroy(string $id)
    {
        if(!$id){
            return response()->json(["message"=>"Invalid id."], 400);
        }

        try{
            $experiment = Experiment::findOrFail($id);
            $userId = auth()->id();
            $user = User::findOrFail($userId);

            if ($userId != $experiment->created_by && $user->is_admin != true) {
                return response()->json(["message"=>"You don't have permission to remove this experiment."], 403);
            }

            $filePath = $experiment->file_path;

            try{
                Storage::delete($filePath);

                $experiment->delete();
            } catch(\Exception $exception){
                return response()->json(["message"=>"Error - {$exception->getMessage()}"], 500);
            }

            return response()->json(['message' => 'Experiment deleted successfully.'], 200);
        } catch(\Exception $_){
            return response()->json(["message"=>"There is no experiment with that id."], 404);
        }
    }

    /**
     *  @OA\Post(
     *      path="/api/experiments/{id}/simulate",
     *      tags={"Experiments"},
     *      summary="Simulate experiment",
     *      description="Simulates already existing experiment",
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the experiment",
     *          @OA\Schema(type="string"),
     *          example=1
     *      ),
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"context"},
     *                  @OA\Property(property="context", type="string", description="Context", example="{""time"": 15, ""H"": ""8*2^3""}"),
     *              ),
     *         ),
     *      ),
     *      @OA\Parameter(
     *         name="Accept",
     *         in="header",
     *         required=true,
     *         description="Authentication token",
     *         @OA\Schema(
     *             type="string",
     *             default="application/json"
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Experiment simulated successfully",
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
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Invalid input",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="context",
     *                      type="array",
     *                      @OA\Items(
     *                          type="string",
     *                          example="The context field is required."
     *                      )
     *                  ),
     *              ),
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
     *              )
     *          )
     *      )
     * )
     */
    public function simulate(Request $request, string $id) {
        if(!$id){
            return response()->json(["message"=>"Invalid id."], 400);
        }

        $validator = Validator::make($request->all(), [
            'context' => 'required|json',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $experiment = Experiment::findOrFail($id);
        
        try{
            $output_values = json_decode($experiment->output);
            $input_values = collect(json_decode($request->input('context'), true))
                ->sortBy('order')
                ->mapWithKeys(fn($item) => [$item['key'] => $item['value']])
                ->toArray();

            $filePath = $experiment->file_path;

            $result_array = ExperimentService::simulateExperiment((object)$input_values, $output_values, $filePath);

            return response()->json(["simulation"=>$result_array], 200);
        } catch(\Exception $_){
            // FIXME: any error triggers 404
            return response()->json(["message"=>"Error during simulation occurred."], 500);
        }
    }

    /**
     *  @OA\Post(
     *      path="/api/experiments/get_context",
     *      tags={"Experiments"},
     *      summary="Returns simulation context",
     *      description="Get simulation context from simulation scheme",
     *      security={{"bearerAuth": {}}},
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"file"},
     *                  @OA\Property(property="file", type="string", format="binary", description="Experiment file"),
     *              ),
     *         ),
     *      ),
     *      @OA\Parameter(
     *         name="Accept",
     *         in="header",
     *         required=true,
     *         description="Authentication token",
     *         @OA\Schema(
     *             type="string",
     *             default="application/json"
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Experiment schema get successfully",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="context",
     *                  type="object",
     *                  @OA\Property(
     *                      property="H",
     *                      type="string",
     *                      example="40"
     *                  ),
     *                  @OA\Property(
     *                      property="P",
     *                      type="string",
     *                      example="80"
     *                  ),
     *                  @OA\Property(
     *                      property="endtime",
     *                      type="string",
     *                      example="15"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
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
     *                  )
     *              ),
     *          )
     *      )
     * )
     */
    public function getContext(Request $request) {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $file = $request->file('file');
            $originalFileName = $file->getClientOriginalName();
            $fileName = time().'_'.$originalFileName;
            $filePath = $file->storeAs('experiment_files', $fileName);
            $context = ExperimentService::getSimulationContext($filePath);
            Storage::delete($filePath);
            return response()->json(['context' => $context], 200);
        } catch(\Exception $e){
            return response()->json(["message"=>$e->getMessage()], 500);
        }
    }
}
