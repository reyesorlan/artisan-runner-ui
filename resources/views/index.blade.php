<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="color-scheme" content="light dark">
        <title>Artisan Runner UI</title>
        <style>
            /*
             * Theme: built on system colors (Canvas / CanvasText) so it follows
             * the OS light/dark setting and blends with any host site.
             * Override any of these on :root to match your brand.
             */
            :root {
                color-scheme: light dark;
                --ar-bg: Canvas;
                --ar-fg: CanvasText;
                --ar-muted: color-mix(in srgb, CanvasText 58%, Canvas);
                --ar-line: color-mix(in srgb, CanvasText 13%, Canvas);
                --ar-line-strong: color-mix(in srgb, CanvasText 26%, Canvas);
                --ar-surface: color-mix(in srgb, CanvasText 4%, Canvas);
                --ar-hover: color-mix(in srgb, CanvasText 7%, Canvas);
                --ar-accent: CanvasText;
                --ar-accent-fg: Canvas;
                --ar-ok: light-dark(#067647, #47cd89);
                --ar-err: light-dark(#b42318, #f97066);
                --ar-radius: 8px;
                --ar-mono: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
                --ar-sans: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            }

            * { box-sizing: border-box; }

            body {
                margin: 0;
                height: 100vh;
                height: 100dvh;
                display: grid;
                grid-template-columns: 280px minmax(0, 1fr);
                background: var(--ar-bg);
                color: var(--ar-fg);
                font: 14px/1.5 var(--ar-sans);
                -webkit-font-smoothing: antialiased;
            }

            :focus-visible { outline: 2px solid var(--ar-fg); outline-offset: 2px; }
            button, input { font: inherit; color: inherit; }
            code, pre, kbd { font-family: var(--ar-mono); }

            /* ---------- Sidebar ---------- */
            aside {
                display: flex;
                flex-direction: column;
                min-height: 0;
                border-right: 1px solid var(--ar-line);
                background: var(--ar-surface);
            }
            .side-head { padding: 16px 16px 12px; }
            .brand { margin: 0 0 12px; font-size: 13px; font-weight: 600; letter-spacing: 0; }
            .brand span { color: var(--ar-muted); font-weight: 400; }

            .search { position: relative; }
            .search input {
                width: 100%;
                height: 34px;
                padding: 0 34px 0 10px;
                border: 1px solid var(--ar-line);
                border-radius: var(--ar-radius);
                background: var(--ar-bg);
            }
            .search input::placeholder { color: var(--ar-muted); }
            .search input:focus { border-color: var(--ar-line-strong); outline: none; box-shadow: 0 0 0 3px var(--ar-hover); }
            .search kbd {
                position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
                padding: 0 5px; font-size: 11px; line-height: 18px;
                color: var(--ar-muted); border: 1px solid var(--ar-line); border-radius: 4px;
                pointer-events: none;
            }

            #list { flex: 1; overflow: auto; padding: 0 8px 16px; scrollbar-width: thin; }
            .group-title {
                margin: 14px 8px 4px;
                font-size: 12px; font-weight: 500; color: var(--ar-muted);
            }
            .cmd {
                display: block; width: 100%;
                padding: 4px 8px;
                border: 0; border-radius: 6px;
                background: none; text-align: left; cursor: pointer;
                font-family: var(--ar-mono); font-size: 12.5px;
                white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            }
            .cmd:hover { background: var(--ar-hover); }
            .cmd[aria-current="true"] { background: var(--ar-accent); color: var(--ar-accent-fg); }
            .empty-list { padding: 16px 8px; color: var(--ar-muted); }

            /* ---------- Main ---------- */
            main { min-width: 0; min-height: 0; overflow: auto; }
            .wrap { max-width: 1000px; margin: 0 auto; padding: 32px 32px 64px; }

            .placeholder { padding-top: 12vh; color: var(--ar-muted); }
            .placeholder h1 { margin: 0 0 6px; font-size: 18px; color: var(--ar-fg); font-weight: 600; }
            .placeholder p { margin: 0; }
            .placeholder kbd {
                padding: 0 5px; font-size: 11px; border: 1px solid var(--ar-line); border-radius: 4px;
            }

            h2.title { margin: 0; font: 600 20px/1.3 var(--ar-mono); word-break: break-all; }
            .desc { margin: 6px 0 0; color: var(--ar-muted); max-width: 70ch; }

            section { margin-top: 28px; }
            section > h3 { margin: 0 0 12px; font-size: 13px; font-weight: 600; }
            section > h3 span { margin-left: 6px; color: var(--ar-muted); font-weight: 400; }

            .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px 20px; }
            .field.full { grid-column: 1 / -1; }
            .field label { display: block; margin-bottom: 6px; font-weight: 500; font-family: var(--ar-mono); font-size: 13px; }
            .field small { display: block; margin: 6px 0 0; color: var(--ar-muted); font-size: 12.5px; line-height: 1.4; }
            .field input[type=text] {
                width: 100%; height: 34px; padding: 0 10px;
                border: 1px solid var(--ar-line-strong);
                border-radius: var(--ar-radius);
                background: var(--ar-bg);
                font-family: var(--ar-mono); font-size: 13px;
            }
            .field input[type=text]::placeholder { color: var(--ar-muted); opacity: .7; }
            .field input[type=text]:focus { outline: none; border-color: var(--ar-fg); box-shadow: 0 0 0 3px var(--ar-hover); }
            .field.filled input[type=text] { border-color: var(--ar-fg); }
            .req { margin-left: 6px; font: 400 11px var(--ar-sans); color: var(--ar-err); }

            .flags { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 4px 20px; }
            .flag {
                display: flex; gap: 10px; align-items: flex-start;
                padding: 8px 10px; border-radius: 6px; cursor: pointer;
            }
            .flag:hover { background: var(--ar-surface); }
            .flag input { margin: 3px 0 0; accent-color: var(--ar-accent); width: 15px; height: 15px; flex: none; }
            .flag b { display: block; font: 500 13px var(--ar-mono); }
            .flag small { display: block; color: var(--ar-muted); font-size: 12.5px; line-height: 1.4; }

            /* ---------- Run bar ---------- */
            .runbar {
                position: sticky; bottom: 0; z-index: 1;
                margin-top: 32px; padding: 12px 0 16px;
                background: linear-gradient(to bottom, transparent, var(--ar-bg) 12px);
            }
            .runbar-inner {
                display: flex; align-items: center; gap: 8px;
                padding: 6px 6px 6px 12px;
                border: 1px solid var(--ar-line-strong);
                border-radius: 10px;
                background: var(--ar-bg);
            }
            .preview {
                flex: 1; min-width: 0;
                overflow-x: auto; white-space: nowrap; scrollbar-width: none;
                font: 12.5px var(--ar-mono);
            }
            .preview::-webkit-scrollbar { display: none; }
            .preview i { color: var(--ar-muted); font-style: normal; }

            .btn {
                height: 32px; padding: 0 12px;
                border: 1px solid var(--ar-line-strong); border-radius: 6px;
                background: var(--ar-bg); cursor: pointer; white-space: nowrap;
            }
            .btn:hover { background: var(--ar-hover); }
            .btn.primary { background: var(--ar-accent); color: var(--ar-accent-fg); border-color: var(--ar-accent); font-weight: 500; }
            .btn.primary:hover { opacity: .88; background: var(--ar-accent); }
            .btn:disabled { opacity: .5; cursor: progress; }
            .btn.sm { height: 28px; padding: 0 10px; font-size: 12.5px; }
            .btn.ghost { border-color: transparent; background: none; color: var(--ar-muted); }
            .btn.ghost:hover { background: var(--ar-hover); color: var(--ar-fg); }
            .btn[aria-pressed="true"] { background: var(--ar-hover); border-color: var(--ar-fg); }

            /* ---------- Output ---------- */
            .output { margin-top: 8px; border: 1px solid var(--ar-line); border-radius: 10px; overflow: hidden; }
            .output[hidden] { display: none; }
            .output-head {
                display: flex; align-items: center; gap: 10px;
                padding: 6px 6px 6px 14px;
                border-bottom: 1px solid var(--ar-line);
                background: var(--ar-surface);
                font-size: 12.5px;
            }
            .status { display: inline-flex; align-items: center; gap: 6px; }
            .status::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: var(--ar-muted); }
            .status.ok::before { background: var(--ar-ok); }
            .status.error::before { background: var(--ar-err); }
            .meta { flex: 1; color: var(--ar-muted); }
            .output pre {
                margin: 0; padding: 14px; max-height: 60vh; overflow: auto;
                background: var(--ar-bg);
                font-size: 12.5px; line-height: 1.6; white-space: pre; tab-size: 4;
            }
            .output pre.error { color: var(--ar-err); white-space: pre-wrap; }
            .output pre.soft { white-space: pre-wrap; word-break: break-word; }
            .output-head .btn { flex: none; }

            /* ---------- Confirm dialog ---------- */
            dialog {
                width: min(480px, calc(100vw - 32px));
                padding: 20px; border: 1px solid var(--ar-line-strong); border-radius: 12px;
                background: var(--ar-bg); color: var(--ar-fg);
            }
            dialog::backdrop { background: color-mix(in srgb, CanvasText 40%, transparent); }
            dialog h4 { margin: 0 0 4px; font-size: 15px; }
            dialog p { margin: 0 0 12px; color: var(--ar-muted); }
            dialog .warn { padding: 8px 12px; border: 1px solid var(--ar-err); border-radius: 6px; color: var(--ar-err); }
            dialog .warn[hidden] { display: none; }
            dialog code {
                display: block; padding: 10px 12px; overflow-x: auto; white-space: pre-wrap; word-break: break-all;
                border: 1px solid var(--ar-line); border-radius: 6px; background: var(--ar-surface); font-size: 12.5px;
            }
            dialog .actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px; }

            /* ---------- Small screens ---------- */
            @media (max-width: 800px) {
                body { grid-template-columns: 1fr; grid-template-rows: minmax(0, 36vh) minmax(0, 1fr); }
                aside { border-right: 0; border-bottom: 1px solid var(--ar-line); }
                .wrap { padding: 20px 16px 48px; }
                .grid, .flags { grid-template-columns: 1fr; }
            }

            @media (prefers-reduced-motion: no-preference) {
                .btn, .cmd, .flag { transition: background-color .12s, opacity .12s; }
            }
        </style>
    </head>
    <body>
        <aside>
            <div class="side-head">
                <p class="brand">Artisan <span>Runner</span></p>
                <div class="search">
                    <input type="text" id="search" placeholder="Search commands" autocomplete="off" spellcheck="false" aria-label="Search commands">
                    <kbd>/</kbd>
                </div>
            </div>
            <nav id="list" aria-label="Artisan commands"></nav>
        </aside>

        <main id="panel"></main>

        <dialog id="confirm">
            <h4>Run this command?</h4>
            <p>It will execute on the server right away.</p>
            <p class="warn" id="confirm-warn" hidden>This command can delete or overwrite data. Check that you are on the right environment.</p>
            <code id="confirm-cmd"></code>
            <div class="actions">
                <button type="button" class="btn" id="confirm-cancel">Cancel</button>
                <button type="button" class="btn primary" id="confirm-ok">Run command</button>
            </div>
        </dialog>

        <script>
            const commands = @json($commands);
            const runUrl = @json(route('artisan-runner-ui.run'));
            const csrf = document.querySelector('meta[name=csrf-token]').content;

            const list = document.getElementById('list');
            const panel = document.getElementById('panel');
            const search = document.getElementById('search');
            const dialog = document.getElementById('confirm');

            let current = null;

            // Commands that can destroy data get an extra warning in the confirm dialog.
            const DANGEROUS = new Set(['migrate:fresh', 'migrate:refresh', 'migrate:reset', 'migrate:rollback', 'db:wipe', 'queue:flush', 'key:generate', 'down']);

            function el(tag, props = {}, ...children) {
                const node = Object.assign(document.createElement(tag), props);
                children.forEach(c => node.append(c));
                return node;
            }

            /* ---------- Sidebar ---------- */

            function groupOf(name) {
                return name.includes(':') ? name.split(':')[0] : 'general';
            }

            function visibleCommands() {
                const q = search.value.trim().toLowerCase();
                return commands.filter(c => c.name.toLowerCase().includes(q));
            }

            function renderList() {
                const items = visibleCommands();

                if (!items.length) {
                    list.replaceChildren(el('div', { className: 'empty-list', textContent: 'No commands match "' + search.value + '".' }));
                    return;
                }

                const groups = new Map();
                items.forEach(c => {
                    const g = groupOf(c.name);
                    if (!groups.has(g)) groups.set(g, []);
                    groups.get(g).push(c);
                });

                // Ungrouped commands first, then namespaces alphabetically.
                const keys = [...groups.keys()].sort((a, b) =>
                    a === 'general' ? -1 : b === 'general' ? 1 : a.localeCompare(b));

                const nodes = [];
                keys.forEach(g => {
                    nodes.push(el('div', { className: 'group-title', textContent: g }));
                    groups.get(g).forEach(c => {
                        const btn = el('button', {
                            type: 'button',
                            className: 'cmd',
                            textContent: g === 'general' ? c.name : c.name.slice(g.length + 1),
                            title: c.name + (c.description ? ' - ' + c.description : ''),
                            onclick: () => select(c),
                        });
                        btn.dataset.name = c.name;
                        if (current && current.name === c.name) btn.setAttribute('aria-current', 'true');
                        nodes.push(btn);
                    });
                });
                list.replaceChildren(...nodes);
            }

            function markActive() {
                list.querySelectorAll('.cmd').forEach(b => {
                    if (current && b.dataset.name === current.name) {
                        b.setAttribute('aria-current', 'true');
                        b.scrollIntoView({ block: 'nearest' });
                    } else {
                        b.removeAttribute('aria-current');
                    }
                });
            }

            /* ---------- Command line helpers ---------- */

            function shellQuote(v) {
                return /^[\w.,:\/%+=-]+$/.test(v) ? v : "'" + v.replace(/'/g, "'\\''") + "'";
            }

            function isFlag(kind, def) {
                return kind === 'option' && !def.acceptsValue;
            }

            function readValue({ kind, def, input }) {
                return isFlag(kind, def) ? input.checked : input.value.trim();
            }

            function buildPayload(c, inputs) {
                const body = { command: c.name, arguments: {}, options: {} };
                inputs.forEach(item => {
                    const value = readValue(item);
                    if (value === '' || value === false) return; // skip untouched fields
                    body[item.kind + 's'][item.def.name] = value;
                });
                return body;
            }

            function buildCommandLine(c, inputs) {
                const parts = ['php artisan', c.name];
                inputs.filter(i => i.kind === 'argument').forEach(i => {
                    const v = readValue(i);
                    if (v !== '') parts.push(shellQuote(v));
                });
                inputs.filter(i => i.kind === 'option').forEach(i => {
                    const v = readValue(i);
                    if (v === '' || v === false) return;
                    parts.push(v === true ? '--' + i.def.name : '--' + i.def.name + '=' + shellQuote(v));
                });
                return parts.join(' ');
            }

            /* ---------- Form ---------- */

            let uid = 0;

            function textField(kind, def, inputs, full) {
                const id = 'f' + (++uid);
                const input = el('input', {
                    id,
                    type: 'text',
                    autocomplete: 'off',
                    spellcheck: false,
                    placeholder: def.default != null && def.default !== false ? String(def.default) : '',
                });
                const field = el('div', { className: 'field' + (full ? ' full' : '') });
                inputs.push({ kind, def, input, field });

                const label = el('label', { htmlFor: id, textContent: (kind === 'option' ? '--' : '') + def.name });
                if (def.required || def.isRequired) label.append(el('span', { className: 'req', textContent: 'required' }));

                field.append(label, input);
                if (def.description) field.append(el('small', { textContent: def.description }));
                return field;
            }

            function flagField(def, inputs) {
                const input = el('input', { type: 'checkbox' });
                inputs.push({ kind: 'option', def, input });
                return el('label', { className: 'flag' },
                    input,
                    el('span', {},
                        el('b', { textContent: '--' + def.name }),
                        def.description ? el('small', { textContent: def.description }) : ''));
            }

            function select(c, { push = true } = {}) {
                current = c;
                const inputs = [];
                const args = c.arguments || [];
                const opts = c.options || [];
                const valueOpts = opts.filter(o => o.acceptsValue);
                const flagOpts = opts.filter(o => !o.acceptsValue);

                const previewEl = el('div', { className: 'preview' });
                const runBtn = el('button', { type: 'button', className: 'btn primary', textContent: 'Run', title: 'Run (Ctrl + Enter)' });
                const copyBtn = el('button', { type: 'button', className: 'btn', textContent: 'Copy' });
                const resetBtn = el('button', { type: 'button', className: 'btn ghost', textContent: 'Reset', title: 'Clear all fields' });

                const updatePreview = () => {
                    previewEl.textContent = buildCommandLine(c, inputs);
                };

                // Output area
                const status = el('span', { className: 'status' });
                const meta = el('span', { className: 'meta' });
                const outCopy = el('button', { type: 'button', className: 'btn sm', textContent: 'Copy' });
                const wrapBtn = el('button', { type: 'button', className: 'btn sm', textContent: 'Wrap', title: 'Wrap long lines' });
                wrapBtn.setAttribute('aria-pressed', 'false');
                const clearBtn = el('button', { type: 'button', className: 'btn sm ghost', textContent: 'Clear' });
                const out = el('pre');
                const outputBox = el('div', { className: 'output', hidden: true },
                    el('div', { className: 'output-head' }, status, meta, wrapBtn, outCopy, clearBtn),
                    out);

                const nodes = [
                    el('h2', { className: 'title', textContent: c.name }),
                    c.description ? el('p', { className: 'desc', textContent: c.description }) : '',
                ];

                if (args.length) {
                    nodes.push(el('section', {},
                        el('h3', { textContent: 'Arguments' }),
                        el('div', { className: 'grid' }, ...args.map(a => textField('argument', a, inputs, args.length === 1)))));
                }

                if (valueOpts.length) {
                    nodes.push(el('section', {},
                        el('h3', { textContent: 'Options' }),
                        el('div', { className: 'grid' }, ...valueOpts.map(o => textField('option', o, inputs, false)))));
                }

                if (flagOpts.length) {
                    nodes.push(el('section', {},
                        el('h3', { textContent: 'Flags' }),
                        el('div', { className: 'flags' }, ...flagOpts.map(o => flagField(o, inputs)))));
                }

                nodes.push(
                    el('div', { className: 'runbar' },
                        el('div', { className: 'runbar-inner' }, previewEl, resetBtn, copyBtn, runBtn)),
                    outputBox
                );

                panel.replaceChildren(el('div', { className: 'wrap' }, ...nodes));
                panel.scrollTop = 0;

                const syncFilled = item => {
                    if (item.field) item.field.classList.toggle('filled', item.input.value.trim() !== '');
                };
                inputs.forEach(item => {
                    const refresh = () => { syncFilled(item); updatePreview(); };
                    item.input.addEventListener('input', refresh);
                    item.input.addEventListener('change', refresh);
                });
                updatePreview();

                resetBtn.addEventListener('click', () => {
                    inputs.forEach(item => {
                        if (isFlag(item.kind, item.def)) item.input.checked = false; else item.input.value = '';
                        syncFilled(item);
                    });
                    updatePreview();
                });

                const trigger = () => run(c, inputs, { runBtn, out, outputBox, status, meta });
                runBtn.addEventListener('click', trigger);
                panel.querySelector('.wrap').addEventListener('keydown', e => {
                    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') { e.preventDefault(); trigger(); }
                });

                copyBtn.addEventListener('click', () => copy(buildCommandLine(c, inputs), copyBtn, 'Copy'));
                outCopy.addEventListener('click', () => copy(out.textContent, outCopy, 'Copy'));
                wrapBtn.addEventListener('click', () => {
                    const on = out.classList.toggle('soft');
                    wrapBtn.setAttribute('aria-pressed', String(on));
                });
                clearBtn.addEventListener('click', () => { outputBox.hidden = true; });

                markActive();
                if (push) history.replaceState(null, '', '#' + encodeURIComponent(c.name));
            }

            /* ---------- Actions ---------- */

            async function copy(text, btn, label) {
                try {
                    await navigator.clipboard.writeText(text);
                    btn.textContent = 'Copied';
                } catch (e) {
                    btn.textContent = 'Copy failed';
                }
                setTimeout(() => { btn.textContent = label; }, 1200);
            }

            function confirmRun(commandLine, danger) {
                document.getElementById('confirm-cmd').textContent = commandLine;
                document.getElementById('confirm-warn').hidden = !danger;
                return new Promise(resolve => {
                    const ok = document.getElementById('confirm-ok');
                    const cancel = document.getElementById('confirm-cancel');
                    const done = result => {
                        ok.onclick = cancel.onclick = dialog.onclose = null;
                        if (dialog.open) dialog.close();
                        resolve(result);
                    };
                    ok.onclick = () => done(true);
                    cancel.onclick = () => done(false);
                    dialog.onclose = () => done(false);
                    dialog.showModal();
                    ok.focus();
                });
            }

            async function run(c, inputs, ui) {
                if (ui.runBtn.disabled) return;
                if (!(await confirmRun(buildCommandLine(c, inputs), DANGEROUS.has(c.name)))) return;

                const body = buildPayload(c, inputs);
                const started = performance.now();

                ui.runBtn.disabled = true;
                ui.runBtn.textContent = 'Running…';
                ui.outputBox.hidden = false;
                ui.status.className = 'status';
                ui.status.textContent = 'Running';
                ui.meta.textContent = '';
                ui.out.className = '';
                ui.out.textContent = '';

                let failed = false;
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
                    ui.out.textContent = (data.output ?? data.message ?? 'Request failed (' + res.status + ')').trim() || '(no output)';
                    failed = !(res.ok && data.exit_code === 0);
                    ui.meta.textContent = 'exit code ' + (data.exit_code ?? '–') + ' · HTTP ' + res.status + ' · ' + Math.round(performance.now() - started) + ' ms';
                } catch (e) {
                    failed = true;
                    ui.out.textContent = e.message;
                    ui.meta.textContent = Math.round(performance.now() - started) + ' ms';
                }

                ui.status.className = 'status ' + (failed ? 'error' : 'ok');
                ui.status.textContent = failed ? 'Failed' : 'Success';
                ui.out.className = failed ? 'error' : '';
                ui.runBtn.disabled = false;
                ui.runBtn.textContent = 'Run';
                ui.outputBox.scrollIntoView({ block: 'nearest' });
            }

            /* ---------- Empty state ---------- */

            function showPlaceholder() {
                panel.replaceChildren(el('div', { className: 'wrap' },
                    el('div', { className: 'placeholder' },
                        el('h1', { textContent: 'Choose a command' }),
                        el('p', {}, 'Pick one from the list, or press ', el('kbd', { textContent: '/' }), ' to search.'),
                        el('p', {}, 'Once a command is open, ', el('kbd', { textContent: 'Ctrl' }), ' + ', el('kbd', { textContent: 'Enter' }), ' runs it.'))));
            }

            /* ---------- Keyboard & startup ---------- */

            search.addEventListener('input', renderList);

            search.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    const first = visibleCommands()[0];
                    if (first) { select(first); }
                } else if (e.key === 'Escape') {
                    search.value = '';
                    renderList();
                    search.blur();
                }
            });

            document.addEventListener('keydown', e => {
                const typing = /^(INPUT|TEXTAREA|SELECT)$/.test(document.activeElement.tagName);
                if (e.key === '/' && !typing && !e.ctrlKey && !e.metaKey) {
                    e.preventDefault();
                    search.focus();
                    search.select();
                }
            });

            function fromHash() {
                const name = decodeURIComponent(location.hash.slice(1));
                const found = commands.find(c => c.name === name);
                if (found) select(found, { push: false }); else showPlaceholder();
            }

            window.addEventListener('hashchange', fromHash);
            renderList();
            fromHash();
        </script>
    </body>
</html>