<?php

namespace CodeSquirrel\ArtisanRunnerUI\Http\Controllers;

use CodeSquirrel\ArtisanRunnerUI\CommandCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

class ArtisanRunnerUIController extends Controller
{
    public function index(CommandCatalog $catalog)
    {
        return view('artisan-runner-ui::index', ['commands' => $catalog->all()]);
    }

    public function run(Request $request, CommandCatalog $catalog): JsonResponse
    {
        $data = $request->validate([
            'command' => ['required', 'string'],
            'arguments' => ['array'],
            'options' => ['array'],
        ]);

        $command = $catalog->find($data['command']);
        abort_if($command === null, 403, 'Command not available.');

        // Only pass what the command actually declares.
        $params = ['--no-interaction' => true];

        foreach ($command['arguments'] as $arg) {
            $value = $data['arguments'][$arg['name']] ?? null;
            if ($value === null || $value === '') {
                continue;
            }
            $params[$arg['name']] = $arg['array'] ? $this->split($value) : $value;
        }

        foreach ($command['options'] as $opt) {
            $value = $data['options'][$opt['name']] ?? null;
            if ($value === null || $value === '' || $value === false) {
                continue;
            }
            $params['--'.$opt['name']] = match (true) {
                ! $opt['acceptsValue'] => true,
                $opt['array'] => $this->split($value),
                default => $value,
            };
        }

        Log::info('Artisan Runner UI run', [
            'user' => $request->user()?->getAuthIdentifier(),
            'command' => $command['name'],
            'params' => $params,
        ]);

        try {
            $exitCode = Artisan::call($command['name'], $params);
            $output = Artisan::output();
        } catch (Throwable $e) {
            $exitCode = 1;
            $output = $e->getMessage();
        }

        return response()->json(['exit_code' => $exitCode, 'output' => $output]);
    }

    private function split(mixed $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
    }
}