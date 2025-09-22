<x-layouts.app :title="__('Playground Video')" :breadcrumbs="[
    ['label' => __('Dashboard'), 'url' => route('dashboard')],
    ['label' => __('Playground Video')],
]">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">{{ __('Playground Video') }}</flux:heading>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <!-- Export Link Runner -->
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="mb-3 font-medium">{{ __('Export Link') }}</div>
                <div class="space-y-3">
                    <div>
                        <label class="mb-1 block text-sm text-neutral-600 dark:text-neutral-300">{{ __('Paste Export URL') }}</label>
                        <input id="pg-link" class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm dark:border-neutral-700 dark:bg-zinc-900" placeholder="https://domain.tld/v/{token}" />
                    </div>
                    <div class="flex items-center gap-2">
                        <button id="pg-link-open" class="inline-flex items-center rounded-md bg-neutral-900 px-3 py-2 text-sm text-white hover:bg-neutral-800 dark:bg-neutral-100 dark:text-neutral-900">{{ __('Open') }}</button>
                        <button id="pg-link-copy" class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200">{{ __('Copy') }}</button>
                        <button id="pg-link-to-embed" class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200">{{ __('Create Embed From Link') }}</button>
                    </div>
                    <p class="text-xs text-neutral-500">{{ __('Gunakan link dari menu Copy Link pada daftar video.') }}</p>
                </div>
            </div>

            <!-- Embed Preview -->
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="mb-3 font-medium">{{ __('Embed Code') }}</div>
                <div class="space-y-3">
                    <div>
                        <label class="mb-1 block text-sm text-neutral-600 dark:text-neutral-300">{{ __('Paste or Edit Embed Code') }}</label>
                        <textarea id="pg-embed" rows="6" class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-xs font-mono dark:border-neutral-700 dark:bg-zinc-900" placeholder="&lt;iframe src='https://domain.tld/e/{token}' width='800' height='450' frameborder='0' allowfullscreen&gt;&lt;/iframe&gt;"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <button id="pg-embed-copy" class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200">{{ __('Copy Code') }}</button>
                        <button id="pg-embed-preview" class="inline-flex items-center rounded-md bg-neutral-900 px-3 py-2 text-sm text-white hover:bg-neutral-800 dark:bg-neutral-100 dark:text-neutral-900">{{ __('Preview') }}</button>
                    </div>
                    <div class="rounded-lg border border-neutral-200 p-2 dark:border-neutral-700">
                        <div id="pg-embed-preview-area" class="relative aspect-video w-full overflow-hidden rounded bg-black/90"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyText(text){
            if(navigator.clipboard && window.isSecureContext){ return navigator.clipboard.writeText(text); }
            const ta=document.createElement('textarea'); ta.value=text; document.body.appendChild(ta); ta.select(); try{document.execCommand('copy');}finally{document.body.removeChild(ta);} return Promise.resolve();
        }

        const linkInput = document.getElementById('pg-link');
        // Open in modal (iframe)
        document.getElementById('pg-link-open')?.addEventListener('click', ()=>{
            const url = linkInput.value.trim();
            if(!url){ toast('Masukkan link terlebih dahulu', 'warning'); return; }
            openPlaygroundModal(url);
        });
        document.getElementById('pg-link-copy')?.addEventListener('click', async ()=>{
            const url = linkInput.value.trim();
            if(!url){ toast('Masukkan link terlebih dahulu', 'warning'); return; }
            await copyText(url); toast('Link disalin', 'success');
        });
        document.getElementById('pg-link-to-embed')?.addEventListener('click', ()=>{
            const url = linkInput.value.trim();
            if(!url){ toast('Masukkan link terlebih dahulu', 'warning'); return; }
            // Convert /v/{token} to /e/{token} if possible
            let embed = url.replace(/\/v\//, '/e/');
            const code = `<iframe src="${embed}" width="800" height="450" frameborder="0" allowfullscreen></iframe>`;
            document.getElementById('pg-embed').value = code;
            toast('Embed code dibuat', 'success');
        });

        const embedArea = document.getElementById('pg-embed');
        const previewWrap = document.getElementById('pg-embed-preview-area');
        document.getElementById('pg-embed-copy')?.addEventListener('click', async ()=>{
            const code = embedArea.value.trim();
            if(!code){ toast('Masukkan embed code', 'warning'); return; }
            await copyText(code); toast('Embed code disalin', 'success');
        });
        document.getElementById('pg-embed-preview')?.addEventListener('click', ()=>{
            const code = embedArea.value.trim();
            if(!code){ toast('Masukkan embed code', 'warning'); return; }
            const m = code.match(/src\s*=\s*[\"\']([^\"\']+)[\"\']/i);
            let src = m ? m[1] : null;
            if (src) {
                // Render responsive iframe that fills the preview box
                previewWrap.innerHTML = `<iframe src="${src}" style="width:100%;height:100%;border:0;display:block;" allowfullscreen></iframe>`;
            } else {
                // Fallback: inject given code, then try to stretch
                previewWrap.innerHTML = code;
                const ifr = previewWrap.querySelector('iframe, video');
                if (ifr) { ifr.style.width = '100%'; ifr.style.height='100%'; ifr.style.border='0'; ifr.style.display='block'; }
            }
        });
    </script>

    <!-- Open Modal (iframe) -->
    <div id="pg-modal" class="fixed inset-0 z-[200] hidden opacity-0 bg-black/40 backdrop-blur-[1px] transition-opacity">
        <div data-dialog class="mx-auto mt-10 w-full max-w-5xl transform rounded-lg bg-white shadow-xl transition-all dark:bg-zinc-900 translate-y-2 opacity-0">
            <div class="flex items-center justify-between border-b border-neutral-200 p-3 dark:border-neutral-700">
                <div class="text-sm font-medium text-neutral-700 dark:text-neutral-200">{{ __('Preview Link') }}</div>
                <button data-close class="rounded px-2 py-1 text-neutral-600 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-zinc-800">×</button>
            </div>
            <div class="aspect-video w-full overflow-hidden bg-black">
                <iframe id="pg-iframe" class="h-full w-full" src="about:blank" frameborder="0" allowfullscreen></iframe>
            </div>
        </div>
    </div>

    <script>
        function openPlaygroundModal(url){
            const overlay = document.getElementById('pg-modal');
            const iframe = document.getElementById('pg-iframe');
            if(!overlay || !iframe) return;
            // Use the provided URL as-is to respect chosen access mode
            iframe.src = url.trim();
            overlay.classList.remove('hidden','opacity-0');
            const dialog = overlay.querySelector('[data-dialog]');
            setTimeout(()=> dialog.classList.remove('translate-y-2','opacity-0'), 10);
        }
        function closePlaygroundModal(){
            const overlay = document.getElementById('pg-modal');
            const iframe = document.getElementById('pg-iframe');
            if(!overlay || !iframe) return;
            iframe.src = 'about:blank';
            const dialog = overlay.querySelector('[data-dialog]');
            dialog.classList.add('translate-y-2','opacity-0');
            setTimeout(()=> overlay.classList.add('hidden','opacity-0'), 200);
        }
        document.addEventListener('click', (e)=>{
            if(e.target.matches('#pg-modal') || e.target.closest('#pg-modal [data-close]')){
                closePlaygroundModal();
            }
        });
    </script>
</x-layouts.app>
