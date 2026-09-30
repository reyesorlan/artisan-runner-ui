# Artisan Runner UI

A small Laravel package that gives your team a web UI to **browse and run `php artisan` commands** — no terminal, no SSH, no copy-pasting command lines.

Pick a command from the list, fill in its arguments and options in a form, click **Run**, and see the output in the browser.

```
php artisan db:seed --class=UsersTableSeeder
```

...becomes a click.

## Features

- **Command browser** — lists every non-hidden artisan command registered in your app, with descriptions, searchable as you type.
- **Auto-generated forms** — arguments and options are read from each command's Symfony Console definition, so inputs always match the real signature (defaults shown as placeholders, flags rendered as checkboxes).
- **Off by default** — the UI is disabled unless you explicitly turn it on.
- **Environment-gated** — returns `404` in any environment you don't list (defaults to `local`, `qa`, `staging`, `uat`).
- **Authenticated by default** — routes ship with the `web` and `auth` middleware; add your own policies/abilities on top.
- **Command allow/deny lists** — restrict exactly which commands can be shown and run; wildcards supported. A sensible deny list (e.g. `db:wipe`, `migrate:fresh`, `tinker`, `down`) is included out of the box.
- **Strict parameter handling** — the run endpoint only accepts arguments/options the command actually declares; everything else is ignored.
- **Audit trail** — every execution is logged with the acting user, command, and parameters.
- **Zero frontend build** — the UI is a single self-contained Blade view with vanilla JS. No npm, no assets to compile.
- **Publishable** — config and views can be published for customization.

## Requirements

| Requirement | Version |
|---|---|
| PHP | `^8.2` |
| Laravel (illuminate/console, illuminate/support) | `^10.0`, `^11.0`, `^12.0` |

## Installation

```bash
composer require codesquirrel/artisan-runner-ui
```

The `CodeSquirrel\ArtisanRunnerUI\ArtisanRunnerUIServiceProvider` service provider is auto-discovered.

### Enable the UI

The UI is **off by default**. Enable it for an environment:

```bash
# .env
ARTISAN_RUNNER_UI_ENABLED=true
```

That's it — the UI is now available at:

```
/artisan-runner-ui
```

### (Optional) Publish config and views

```bash
php artisan vendor:publish --tag=artisan-runner-ui-config
php artisan vendor:publish --tag=artisan-runner-ui-views
```

## Configuration

Default config (`config/artisan-runner-ui.php`):

```php
return [
    // Master switch. Off unless explicitly enabled.
    'enabled' => env('ARTISAN_RUNNER_UI_ENABLED', false),

    // URL prefix for the UI routes.
    'path' => env('ARTISAN_RUNNER_UI_PATH', 'artisan-runner-ui'),

    // Middleware applied to the UI routes. Add your own, e.g. 'can:run-artisan-runner-ui'
    'middleware' => ['web', 'auth'],

    // Environments where the UI is available; anything else returns 404.
    'environments' => ['local', 'qa', 'staging', 'uat'],

    // If not empty, ONLY these commands are shown/runnable (wildcards allowed).
    'allowed' => [],

    // Never shown or runnable (wildcards allowed).
    'denied' => [
        'tinker', 'serve', 'down', 'up',
        'db:wipe', 'migrate:fresh', 'migrate:reset', 'migrate:refresh', 'migrate:rollback',
        'key:generate', 'env:*', 'queue:work', 'queue:listen', 'schedule:work',
        'schedule:run', 'vendor:publish', 'package:discover',
    ],
];
```

| Key | Description |
|---|---|
| `enabled` | Master switch. The package registers no routes at all when `false`. |
| `path` | URL prefix for the UI. Change it if `artisan-runner-ui` isn't to your taste. |
| `middleware` | Applied on top of the built-in environment check. Add abilities like `can:run-artisan-runner-ui` here. |
| `environments` | Allowed app environments. Requests from any other environment get a `404`. |
| `allowed` | Allow list. When non-empty, only matching commands are visible and runnable. Wildcards (`migrate:*`) supported. |
| `denied` | Deny list. Matching commands are never shown or runnable, even if allowed. Wildcards supported. |

## Usage

1. Visit `/artisan-runner-ui` (or your configured path) in a browser while logged in.
2. Search for a command in the sidebar and click it.
3. Fill in the generated form — text inputs for arguments/options that take values, checkboxes for flags.
4. Click **Run**, confirm the prompt, and read the command output in the terminal-style panel.

### Routes

| Method | URI | Name |
|---|---|---|
| `GET` | `/{path}` | `artisan-runner-ui.index` |
| `POST` | `/{path}/run` | `artisan-runner-ui.run` |

The `run` endpoint returns JSON:

```json
{ "exit_code": 0, "output": "Database seeded successfully." }
```

### Audit logging

Every execution is logged via the app logger with the acting user, command, and parameters:

```
Artisan Runner UI run { user: 42, command: "db:seed", params: { "--class": "UsersTableSeeder" } }
```

## Security notes

- **Disabled by default** — no routes are registered unless `ARTISAN_RUNNER_UI_ENABLED=true`.
- **Environment gate first** — the `EnsureEnvironmentAllowed` middleware runs before anything else and returns `404` in disallowed environments, so the route doesn't announce itself.
- **Auth required out of the box** — the `auth` middleware is part of the default route group; the UI is only visible to logged-in users.
- **Deny list for dangerous commands** — destructive, long-running, and environment-mutating commands are blocked by default. Extend it as needed.
- **No command injection** — commands are executed via `Artisan::call()` using the registered command name; user input is passed as parameters, and only parameters declared by the command's signature are accepted.
- **Keep it off production** — don't add `production` to `environments` unless you have a very good reason and a strict `allowed` list.

## Development

```bash
composer install
vendor/bin/phpunit
```

Package tests run on [Orchestra Testbench](https://github.com/orchestral/testbench) + PHPUnit 11.

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.


