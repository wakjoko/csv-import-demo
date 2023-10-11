<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Import;
use App\Models\JobBatch;
use App\Jobs\ImportProductsJob;

class ImportController extends Controller
{
    public function index()
    {
        $keys = (new JobBatch)->getAppends();

        $imports = Import::with('batch')
            ->get()
            ->each(function ($import) use ($keys) {
                // transform to DTO
                $appends = $import->batch?->only($keys) ?? array_combine($keys, ['pending', 0]);

                foreach ($appends as $key => $value) {
                    $import->{$key} = $value;
                }

                $import->unsetRelation('batch');
            });

        return response()->json($imports);
    }

    public function import()
    {
        if (!request()->has('files')) {
            return new Exception('Nothing to import.');
        }
        
        $imports = [];

        foreach (request()->file('files') as $uploaded) {
            $file = $uploaded->storeAs($uploaded->getFilename());

            $import = Import::create([
                'file_name' => $uploaded->getClientOriginalName(),
            ]);

            ImportProductsJob::dispatchAfterResponse($file, $import);

            $imports[] = $import;
        }

        return response()->json($imports);
    }

    public function status($id)
    {
        session_start();
        session_write_close();
        ignore_user_abort(true);

        ini_set('output_buffering', 'On');

        return response()->stream(function () use ($id) {
            while (true) {
                if (connection_aborted()) {
                    die;
                }

                $import = Import::with('batch')->select('batch_id')->find($id);
                $data = [];

                if ($import && $import->batch) {
                    $data = $import->batch->only('status', 'progress');
                }

                $data = collect($data)->toJson();
                echo "data: {$data}";
                echo "\n\n";

                ob_flush();
                flush();
                sleep(2);
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
