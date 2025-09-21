<x-layouts.app :title="__('Videos')" :breadcrumbs="[
    ['label' => __('Dashboard'), 'url' => route('dashboard')],
    ['label' => __('Video')],
]">
    <div class="space-y-6">
        @php
            $activeStatus = $filters['status'] ?? '';
            $hasFilters = (trim($filters['q'] ?? '') !== '') || ($filters['status'] ?? '') !== '' || ($filters['visibility'] ?? '') !== '' || ($filters['sort'] ?? 'newest') !== 'newest' || (int)($filters['per_page'] ?? 10) !== 10;
        @endphp

        <!-- Topbar like reference -->
        <div class="flex items-center justify-between">
            <flux:heading size="xl">{{ __('Videos') }}</flux:heading>
            <div class="flex items-center gap-2">
                <button type="button" id="btn-toggle-filter" class="inline-flex items-center gap-2 rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5h18M6 12h12M10 19h4"/></svg>
                    {{ __('Filter') }}
                </button>
                <button type="button" id="btn-export" class="inline-flex items-center gap-2 rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                    {{ __('Export') }}
                </button>
                <flux:link :href="route('video.create')">
                    <flux:button variant="primary">
                        <span class="inline-flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                            {{ __('Create video') }}
                        </span>
                    </flux:button>
                </flux:link>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-neutral-200 dark:border-neutral-700">
            <nav class="-mb-px flex flex-wrap gap-6 text-sm">
                @php $tab = fn($label,$value)=>[ 'label'=>$label, 'value'=>$value ]; @endphp
                @foreach ([
                    $tab(__('All'),''),
                    $tab(__('Completed'),'completed'),
                    $tab(__('Processing'),'processing'),
                    $tab(__('Uploaded'),'uploaded'),
                    $tab(__('Failed'),'failed'),
                ] as $t)
                    @php $active = $activeStatus===$t['value']; @endphp
                    <a href="{{ route('video.index', array_merge(request()->except('page'), ['status' => $t['value']])) }}"
                       class="inline-flex items-center border-b-2 px-1.5 py-3 font-medium transition-colors
                        {{ $active ? 'border-neutral-900 text-neutral-900 dark:border-neutral-100 dark:text-neutral-100' : 'border-transparent text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100' }}">
                        {{ $t['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>

        <!-- Filters Drawer (toggle) -->
        <div id="filters-panel" class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900 p-3 md:p-4 hidden">
            <form method="GET" action="{{ route('video.index') }}" class="grid gap-3 md:gap-4">
                <div class="grid gap-2 md:grid-cols-6 lg:grid-cols-12">
                    <div class="md:col-span-3 lg:col-span-6">
                        <flux:input placeholder="{{ __('Search title / description / file...') }}" name="q" value="{{ $filters['q'] ?? '' }}" />
                    </div>
                    <div class="md:col-span-1 lg:col-span-2">
                        <select name="status" class="w-full rounded-md border border-neutral-300 bg-white px-2 py-2 text-sm dark:border-neutral-700 dark:bg-zinc-900">
                            @php $s = $filters['status'] ?? '' @endphp
                            <option value="">{{ __('All Status') }}</option>
                            <option value="uploaded" @selected($s==='uploaded')>Uploaded</option>
                            <option value="processing" @selected($s==='processing')>Processing</option>
                            <option value="completed" @selected($s==='completed')>Completed</option>
                            <option value="failed" @selected($s==='failed')>Failed</option>
                        </select>
                    </div>
                    <div class="md:col-span-1 lg:col-span-2">
                        <select name="visibility" class="w-full rounded-md border border-neutral-300 bg-white px-2 py-2 text-sm dark:border-neutral-700 dark:bg-zinc-900">
                            @php $v = $filters['visibility'] ?? '' @endphp
                            <option value="">{{ __('All Visibility') }}</option>
                            <option value="public" @selected($v==='public')>{{ __('Public') }}</option>
                            <option value="private" @selected($v==='private')>{{ __('Private') }}</option>
                        </select>
                    </div>
                    <div class="md:col-span-1 lg:col-span-2">
                        <select name="sort" class="w-full rounded-md border border-neutral-300 bg-white px-2 py-2 text-sm dark:border-neutral-700 dark:bg-zinc-900">
                            @php $sort = $filters['sort'] ?? 'newest' @endphp
                            <option value="newest" @selected($sort==='newest')>{{ __('Newest') }}</option>
                            <option value="oldest" @selected($sort==='oldest')>{{ __('Oldest') }}</option>
                            <option value="most_viewed" @selected($sort==='most_viewed')>{{ __('Most viewed') }}</option>
                            <option value="title_asc" @selected($sort==='title_asc')>{{ __('Title A→Z') }}</option>
                            <option value="title_desc" @selected($sort==='title_desc')>{{ __('Title Z→A') }}</option>
                        </select>
                    </div>
                    <div class="md:col-span-1 lg:col-span-2">
                        <select name="per_page" class="w-full rounded-md border border-neutral-300 bg-white px-2 py-2 text-sm dark:border-neutral-700 dark:bg-zinc-900">
                            @php $pp = (int) ($filters['per_page'] ?? 10) @endphp
                            @foreach([10,20,50,100] as $n)
                                <option value="{{ $n }}" @selected($pp===$n)>{{ $n }}/{{ __('page') }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('video.index') }}" class="text-sm text-neutral-600 hover:underline dark:text-neutral-300">{{ __('Reset') }}</a>
                    <flux:button type="submit" variant="primary">{{ __('Apply') }}</flux:button>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="sticky top-0 z-[1] text-left bg-neutral-50 dark:bg-neutral-800">
                        <tr class="text-neutral-600 dark:text-neutral-300">
                            <th class="px-4 py-3 font-medium">{{ __('Judul') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Ukuran') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Publik') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Dilihat') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Dibuat') }}</th>
                            <th class="px-4 py-3 font-medium text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($videos as $v)
                        <tr class="border-t border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50/50 dark:hover:bg-neutral-800/50">
                            <td class="px-4 py-3">
                                <div class="font-medium" id="title-{{ $v->id }}">{{ $v->title }}</div>
                                <div class="text-xs text-neutral-500" id="desc-{{ $v->id }}">{{ $v->description ? \Illuminate\Support\Str::limit($v->description, 80) : $v->original_filename }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    @class([
                                        'bg-yellow-100 text-yellow-800' => $v->status==='uploaded',
                                        'bg-blue-100 text-blue-800' => $v->status==='processing',
                                        'bg-green-100 text-green-800' => $v->status==='completed',
                                        'bg-red-100 text-red-800' => $v->status==='failed',
                                    ])
                                ">{{ ucfirst($v->status) }}</span>
                            </td>
                            <td class="px-4 py-3">{{ number_format(($v->file_size ?? 0)/1024/1024, 2) }} MB</td>
                            <td class="px-4 py-3" id="public-{{ $v->id }}">{{ $v->is_public ? 'Ya' : 'Tidak' }}</td>
                            <td class="px-4 py-3">{{ number_format($v->view_count ?? 0) }}</td>
                            <td class="px-4 py-3">{{ $v->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    @if(in_array($v->status, ['uploaded','failed']))
                                        <button class="btn-convert inline-flex items-center rounded-md border border-neutral-300 bg-white px-3 py-1.5 text-xs text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200" data-id="{{ $v->id }}">{{ __('Encrypt/Convert') }}</button>
                                    @endif

                                    @if($v->status === 'completed' && $v->hls_path)
                                        <button class="btn-preview inline-flex items-center rounded-md border border-neutral-300 bg-white px-3 py-1.5 text-xs text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200" data-playlist-url="{{ route('video.playlist', ['playlist' => basename($v->hls_path)]) }}">{{ __('Preview') }}</button>
                                    @endif

                                    <button class="btn-edit inline-flex items-center rounded-md border border-neutral-300 bg-white px-3 py-1.5 text-xs text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200" data-id="{{ $v->id }}" data-title="{{ $v->title }}" data-description="{{ $v->description }}">{{ __('Edit') }}</button>
                                    <button class="btn-copy-link inline-flex items-center rounded-md border border-neutral-300 bg-white px-3 py-1.5 text-xs text-neutral-700 hover:bg-neutral-50 disabled:opacity-50 disabled:cursor-not-allowed dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200" data-id="{{ $v->id }}" data-public="{{ $v->is_public ? 1 : 0 }}" @disabled(!$v->is_public)>{{ __('Copy Link') }}</button>
                                    <button class="btn-copy-embed inline-flex items-center rounded-md border border-neutral-300 bg-white px-3 py-1.5 text-xs text-neutral-700 hover:bg-neutral-50 disabled:opacity-50 disabled:cursor-not-allowed dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200" data-id="{{ $v->id }}" data-public="{{ $v->is_public ? 1 : 0 }}" @disabled(!$v->is_public)>{{ __('Copy Embed') }}</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td class="px-4 py-6 text-center text-neutral-500" colspan="7">{{ __('Belum ada video.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex items-center justify-between px-4 py-3 text-sm">
                <div class="text-neutral-500">
                    {{ __('Menampilkan') }} {{ $videos->firstItem() ?? 0 }}–{{ $videos->lastItem() ?? 0 }} {{ __('dari') }} {{ $videos->total() }} {{ __('data') }}
                </div>
                <div>
                    {{ $videos->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
    // toggle filters panel like reference layout
    document.getElementById('btn-toggle-filter')?.addEventListener('click', ()=>{
        const p = document.getElementById('filters-panel');
        if (!p) return;
        p.classList.toggle('hidden');
    });

    // demo export
    document.getElementById('btn-export')?.addEventListener('click', ()=>{
        toast('Export coming soon', 'info');
    });

    const csrf = @json(csrf_token());

    async function postJson(url, payload){
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        let data;
        try { data = await res.json(); } catch(e) { data = null; }
        if(!res.ok){ throw new Error((data && data.message) || 'Request failed'); }
        return data;
    }

    function copy(text){
        if(navigator.clipboard && window.isSecureContext){
            return navigator.clipboard.writeText(text);
        } else {
            const ta = document.createElement('textarea');
            ta.value = text; document.body.appendChild(ta); ta.select();
            try { document.execCommand('copy'); } finally { document.body.removeChild(ta); }
            return Promise.resolve();
        }
    }

    document.addEventListener('click', async (e)=>{
        const btnEdit = e.target.closest('.btn-edit');
        if(btnEdit){
            e.preventDefault();
            openEditModal(btnEdit.dataset.id, btnEdit.dataset.title, btnEdit.dataset.description || '');
            return;
        }
        const btnPreview = e.target.closest('.btn-preview');
        if(btnPreview){
            e.preventDefault();
            openPreviewModal(btnPreview.dataset.playlistUrl);
            return;
        }
        const btn = e.target.closest('.btn-convert');
        if(btn){
            e.preventDefault();
            const id = btn.dataset.id;
            const label = btn.textContent;
            btn.setAttribute('disabled','true');
            btn.textContent = 'Converting...';
            try {
                const data = await postJson(@json(route('video.convert')), { video_id: id, encryption_type: 'single' });
                if(data.success){ toast('Konversi berhasil', 'success'); window.location.reload(); } else { toast(data.message || 'Convert failed', 'error'); }
            } catch(err){ toast(err.message, 'error'); }
            finally { btn.removeAttribute('disabled'); btn.textContent = label; }
        }

        const btnLink = e.target.closest('.btn-copy-link');
        if(btnLink){
            e.preventDefault();
            if(btnLink.hasAttribute('disabled')) return;
            const id = btnLink.dataset.id;
            const label = btnLink.textContent;
            btnLink.setAttribute('disabled','true'); btnLink.textContent = 'Generating...';
            try {
                const data = await postJson(@json(route('video.export.generate')), { video_id: id, allow_export: true });
                const link = data?.data?.export_url;
                if(link){ await copy(link); toast('Link copied', 'success'); } else { toast('Failed to get link', 'error'); }
            } catch(err){ toast(err.message, 'error'); }
            finally { btnLink.removeAttribute('disabled'); btnLink.textContent = label; }
        }

        const btnEmbed = e.target.closest('.btn-copy-embed');
        if(btnEmbed){
            e.preventDefault();
            if(btnEmbed.hasAttribute('disabled')) return;
            const id = btnEmbed.dataset.id;
            const label = btnEmbed.textContent;
            btnEmbed.setAttribute('disabled','true'); btnEmbed.textContent = 'Generating...';
            try {
                const data = await postJson(@json(route('video.export.generate')), { video_id: id, allow_embed: true });
                const code = data?.data?.embed_code || `<iframe src="${data?.data?.embed_url}" width="800" height="450" frameborder="0" allowfullscreen></iframe>`;
                if(code){ await copy(code); toast('Embed code copied', 'success'); } else { toast('Failed to get embed code', 'error'); }
            } catch(err){ toast(err.message, 'error'); }
            finally { btnEmbed.removeAttribute('disabled'); btnEmbed.textContent = label; }
        }
    });

    // Modal Edit
    function openEditModal(id, title, description){
        const overlay = document.getElementById('modal-edit');
        if(!overlay) return;
        overlay.classList.remove('hidden','opacity-0');
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-title').value = title || '';
        document.getElementById('edit-description').value = description || '';
        setTimeout(()=> overlay.querySelector('[data-dialog]').classList.remove('translate-y-2','opacity-0'), 10);
    }
    function closeEditModal(){
        const overlay = document.getElementById('modal-edit');
        if(!overlay) return;
        overlay.querySelector('[data-dialog]').classList.add('translate-y-2','opacity-0');
        setTimeout(()=> overlay.classList.add('hidden','opacity-0'), 200);
    }
    document.addEventListener('click', (e)=>{
        if(e.target.matches('#modal-edit') || e.target.closest('[data-close-modal]')){
            closeEditModal();
        }
    });

    // Preview modal functions
    let hlsInstance = null;
    function openPreviewModal(playlistUrl){
        const overlay = document.getElementById('modal-preview');
        if(!overlay) return;
        overlay.classList.remove('hidden','opacity-0');
        const dialog = overlay.querySelector('[data-dialog]');
        setTimeout(()=> dialog.classList.remove('translate-y-2','opacity-0'), 10);
        const video = document.getElementById('preview-video');
        if(window.Hls && Hls.isSupported()){
            if(hlsInstance){ hlsInstance.destroy(); }
            hlsInstance = new Hls({ lowLatencyMode: true });
            hlsInstance.loadSource(playlistUrl);
            hlsInstance.attachMedia(video);
        } else if (video.canPlayType('application/vnd.apple.mpegurl')){
            video.src = playlistUrl;
        }
        video.play().catch(()=>{});
    }
    function closePreviewModal(){
        const overlay = document.getElementById('modal-preview');
        if(!overlay) return;
        const dialog = overlay.querySelector('[data-dialog]');
        const video = document.getElementById('preview-video');
        try { video.pause(); } catch(e){}
        if(hlsInstance){ try { hlsInstance.destroy(); } catch(e){} hlsInstance = null; }
        dialog.classList.add('translate-y-2','opacity-0');
        setTimeout(()=> overlay.classList.add('hidden','opacity-0'), 200);
    }
    document.addEventListener('click', (e)=>{
        if(e.target.matches('#modal-preview') || e.target.closest('[data-close-preview]')){
            closePreviewModal();
        }
    });
    // Use event delegation so it works even if the form is defined later in DOM
    document.addEventListener('submit', async (e)=>{
        const form = e.target;
        if(!form || form.id !== 'form-edit') return;
        e.preventDefault();
        const id = document.getElementById('edit-id').value;
        const title = document.getElementById('edit-title').value.trim();
        const description = document.getElementById('edit-description').value.trim();
        const is_public = document.getElementById('edit-public').checked ? 1 : 0;
        const btn = document.getElementById('btn-save-edit');
        const label = btn.textContent;
        btn.setAttribute('disabled','true');
        btn.textContent = 'Saving...';
        try{
            const data = await postJson(@json(route('video.update.meta')), { video_id: id, title, description, is_public });
            if(data?.success){
                document.getElementById('title-'+id).textContent = data.data.title;
                const descNode = document.getElementById('desc-'+id);
                if(descNode){
                    descNode.textContent = (data.data.description || '').substring(0, 80) || '';
                }
                // update public column
                const pubCell = document.getElementById('public-'+id);
                if(pubCell){ pubCell.textContent = data.data.is_public ? 'Ya' : 'Tidak'; }
                // enable/disable copy buttons
                document.querySelectorAll(`[data-id="${id}"]`).forEach(el => {
                    if(el.classList.contains('btn-copy-link') || el.classList.contains('btn-copy-embed')){
                        el.dataset.public = data.data.is_public ? '1' : '0';
                        if(data.data.is_public){ el.removeAttribute('disabled'); } else { el.setAttribute('disabled','true'); }
                    }
                });
                toast('Updated', 'success');
                closeEditModal();
            } else {
                toast(data?.message || 'Update failed', 'error');
            }
        }catch(err){
            toast(err.message, 'error');
        }finally{
            btn.removeAttribute('disabled');
            btn.textContent = label;
        }
    });
    </script>

    <!-- Edit Modal -->
    <div id="modal-edit" class="fixed inset-0 z-[200] hidden opacity-0 bg-black/30 backdrop-blur-[1px] transition-opacity">
        <div data-dialog class="mx-auto mt-24 w-full max-w-lg transform rounded-lg bg-white p-4 shadow-xl transition-all dark:bg-zinc-900 dark:text-neutral-100 translate-y-2 opacity-0">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold">{{ __('Edit Video') }}</h3>
                <button data-close-modal class="text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200">×</button>
            </div>
            <form id="form-edit" class="mt-3 space-y-3">
                <input type="hidden" id="edit-id" />
                <div>
                    <label class="mb-1 block text-sm text-neutral-600 dark:text-neutral-300">{{ __('Title') }}</label>
                    <input id="edit-title" class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm dark:border-neutral-700 dark:bg-zinc-900" required />
                </div>
                <div>
                    <label class="mb-1 block text-sm text-neutral-600 dark:text-neutral-300">{{ __('Description') }}</label>
                    <textarea id="edit-description" rows="4" class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm dark:border-neutral-700 dark:bg-zinc-900"></textarea>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input id="edit-public" type="checkbox" class="size-4 rounded border-neutral-300 dark:border-neutral-700" />
                    <label for="edit-public" class="text-sm">{{ __('Public') }}</label>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" data-close-modal class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200">{{ __('Cancel') }}</button>
                    <button id="btn-save-edit" type="submit" class="inline-flex items-center rounded-md bg-neutral-900 px-3 py-2 text-sm text-white hover:bg-neutral-800 dark:bg-neutral-100 dark:text-neutral-900 dark:hover:bg-neutral-200">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview Modal -->
    <div id="modal-preview" class="fixed inset-0 z-[200] hidden opacity-0 bg-black/40 backdrop-blur-[1px] transition-opacity">
        <div data-dialog class="mx-auto mt-16 w-full max-w-5xl transform rounded-lg bg-black p-3 shadow-xl transition-all translate-y-2 opacity-0">
            <div class="flex items-center justify-between text-white px-1">
                <h3 class="text-base font-semibold">{{ __('Preview Video') }}</h3>
                <button data-close-preview class="text-white/80 hover:text-white">×</button>
            </div>
            <div class="mt-2 aspect-video w-full overflow-hidden rounded-lg bg-black">
                <video id="preview-video" class="h-full w-full" controls playsinline></video>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
</x-layouts.app>
