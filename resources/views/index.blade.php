<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Artisan Runner UI</title>
        <style>
            body { font-family: system-ui, sans-serif; margin: 0; display: flex; height: 100vh; }
            aside { width: 300px; border-right: 1px solid #ddd; overflow: auto; padding: 12px; }
            aside a { display: block; padding: 4px 6px; color: #222; text-decoration: none; border-radius: 4px; }
            aside a:hover { background: #eef; }
            main { flex: 1; overflow: auto; padding: 20px; }
            input[type=text] { width: 100%; padding: 6px; box-sizing: border-box; }
            label { display: block; margin-top: 12px; font-weight: 600; }
            small { display: block; color: #666; font-weight: 400; }
            button { margin-top: 16px; padding: 8px 16px; cursor: pointer; }
            pre { background: #111; color: #0f0; padding: 12px; border-radius: 6px; white-space: pre-wrap; }
            pre.error { color: #f66; }
        </style>
    </head>
    <body>
        <aside>
            <input type="text" id="search" placeholder="Search commands...">
            <div id="list"></div>
        </aside>
        <main id="panel"><p>Select a command.</p></main>

        <script>
            const commands = @json($commands);
            const runUrl = @json(route('artisan-runner-ui.run'));
            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const list = document.getElementById('list');
            const panel = document.getElementById('panel');
            const search = document.getElementById('search');

            function el(tag, props = {}, ...children) {
            const node = Object.assign(document.createElement(tag), props);
            children.forEach(c => node.append(c));
            return node;
            }

            function renderList() {
            const q = search.value.toLowerCase();
            list.replaceChildren(...commands
                .filter(c => c.name.toLowerCase().includes(q))
                .map(c => el('a', {
                href: '#', textContent: c.name,
                onclick: e => { e.preventDefault(); select(c); },
                })));
            }

            function field(kind, def, inputs) {
            const flag = kind === 'option' && !def.acceptsValue;
            const input = el('input', {
                type: flag ? 'checkbox' : 'text',
                placeholder: def.default != null && !flag ? String(def.default) : '',
            });
            inputs.push({ kind, def, input });
            const label = el('label', {}, (kind === 'option' ? '--' : '') + def.name);
            if (def.description) label.append(el('small', { textContent: def.description }));
            return el('div', {}, flag ? el('label', {}, input, ' --' + def.name) : label, flag ? '' : input);
            }

            function select(c) {
            const inputs = [];
            const out = el('pre');
            out.hidden = true;

            const form = el('div', {},
                el('h2', { textContent: 'php artisan ' + c.name }),
                el('p', { textContent: c.description }),
                ...c.arguments.map(a => field('argument', a, inputs)),
                ...c.options.map(o => field('option', o, inputs)),
                el('button', { textContent: 'Run', onclick: () => run(c, inputs, out) }),
                out
            );
            panel.replaceChildren(form);
            }

            async function run(c, inputs, out) {
            if (!confirm('Run "php artisan ' + c.name + '"?')) return;

            const body = { command: c.name, arguments: {}, options: {} };
            inputs.forEach(({ kind, def, input }) => {
                body[kind + 's'][def.name] = input.type === 'checkbox' ? input.checked : input.value;
            });

            out.hidden = false;
            out.className = '';
            out.textContent = 'Running...';

            try {
                const res = await fetch(runUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify(body),
                });
                const data = await res.json();
                out.textContent = data.output ?? data.message ?? 'Request failed (' + res.status + ')';
                out.className = res.ok && data.exit_code === 0 ? '' : 'error';
            } catch (e) {
                out.textContent = e.message;
                out.className = 'error';
            }
            }

            search.addEventListener('input', renderList);
            renderList();
        </script>
    </body>
</html>