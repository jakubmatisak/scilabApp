<?php

namespace App\Http\Controllers;

use App\Models\Experiment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ExperimentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Experiment::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
            'context' => 'required|json',
            'output' => 'required|json',
            'save' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $file = $request->file('file');
        $originalFileName = $file->getClientOriginalName();
        $filePath = $file->storeAs('experiment_files', $originalFileName);

        $experiment = Experiment::create([
            'file_name' => $filePath,
            'context' => $request->input('context'),
            'output' => $request->input('output'),
            'save' => $request->input('save', false),
            // 'created_by' => auth()->id(),
            'created_by' => 1,
        ]);


        $scriptCommand = 'loadXcosLibs();loadScicos();importXcosDiagram(\'' . $filePath . '\');Context=struct();scicos_simulate(scs_m,list(),Context,\'nw\');';
        $script = 'SCRIPT="' . $scriptCommand .'" /usr/bin/ssh -q -o "UserKnownHostsFile=/dev/null" -o "StrictHostKeyChecking=no" -o "SendEnv=SCRIPT" root@scilab -- /opt/bp-app/run-script.sh 2>&1';

        $result = shell_exec($script);

        return response()->json($result, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Experiment::findOrFail($id);
    }

    /**
     * Get the specified schema.
     */
    public function getSchemaFile($id)
    {
        $experiment = Experiment::findOrFail($id);
        $filePath = $experiment->file_name;
        // $filePath = $experiment->get('file_name');

        return Storage::response($filePath);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $experiment = Experiment::findOrFail($id);
        $filePath = $experiment->file_name;

        Storage::delete($filePath);

        $experiment->delete();

        return response()->json(['message' => 'Experiment deleted successfully']);
    }
}
