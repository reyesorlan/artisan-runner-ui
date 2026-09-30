<?php

namespace CodeSquirrel\ArtisanRunnerUI;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command;

class CommandCatalog
{
    private const GLOBAL_OPTIONS = [
        'help', 'quiet', 'verbose', 'version', 'ansi', 'no-ansi', 'no-interaction', 'env',
    ];

    public function all(): array
    {
        return collect(Artisan::all())
            ->filter(fn (Command $c, string $name) => ! $c->isHidden() && $this->isAllowed($name))
            ->map(fn (Command $c) => $this->describe($c))
            ->sortBy('name')
            ->values()
            ->all();
    }

    public function find(string $name): ?array
    {
        $command = Artisan::all()[$name] ?? null;

        return $command && $this->isAllowed($name) ? $this->describe($command) : null;
    }

    public function isAllowed(string $name): bool
    {
        $allowed = config('artisan-runner-ui.allowed', []);

        if ($allowed && ! Str::is($allowed, $name)) {
            return false;
        }

        return ! Str::is(config('artisan-runner-ui.denied', []), $name);
    }

    private function describe(Command $command): array
    {
        $definition = $command->getDefinition();

        return [
            'name' => $command->getName(),
            'description' => $command->getDescription(),
            'arguments' => collect($definition->getArguments())->map(fn ($a) => [
                'name' => $a->getName(),
                'description' => $a->getDescription(),
                'required' => $a->isRequired(),
                'array' => $a->isArray(),
                'default' => $a->getDefault(),
            ])->values()->all(),
            'options' => collect($definition->getOptions())
                ->reject(fn ($o) => in_array($o->getName(), self::GLOBAL_OPTIONS, true))
                ->map(fn ($o) => [
                    'name' => $o->getName(),
                    'description' => $o->getDescription(),
                    'acceptsValue' => $o->acceptValue(),
                    'array' => $o->isArray(),
                    'default' => $o->getDefault(),
                ])->values()->all(),
        ];
    }
}