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
                    $tab(__('Queued'),'queued'),
                    $tab(__('Processing'),'processing'),
                    $tab(__('Completed'),'completed'),
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
                            <option value="queued" @selected($s==='queued')>Queued</option>
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

        <!-- View Toggle -->
        <div class="flex items-center justify-end gap-2">
            <button id="btn-grid" class="inline-flex items-center rounded-md border px-3 py-1.5 text-xs
                {{ ($viewMode ?? 'grid')==='grid' ? 'border-neutral-900 text-neutral-900 dark:border-neutral-100 dark:text-neutral-100' : 'border-neutral-300 text-neutral-600 dark:border-neutral-700 dark:text-neutral-300' }}">
                Grid
            </button>
            <button id="btn-list" class="inline-flex items-center rounded-md border px-3 py-1.5 text-xs
                {{ ($viewMode ?? 'grid')==='list' ? 'border-neutral-900 text-neutral-900 dark:border-neutral-100 dark:text-neutral-100' : 'border-neutral-300 text-neutral-600 dark:border-neutral-700 dark:text-neutral-300' }}">
                List
            </button>
        </div>

        <!-- Grid -->
        <div id="grid-view" class="{{ ($viewMode ?? 'grid')==='grid' ? '' : 'hidden' }}">
            <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse ($videos as $v)
                <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900">
                    <div class="aspect-video w-full overflow-hidden" style="background: linear-gradient(135deg,#f5f5f5,#eaeaea);">
                        <img src="{{ $v->getPosterUrl() }}"
                             alt="poster"
                             class="h-full w-full object-cover"
                             onerror="this.src='{{ asset('image.png') }}'; this.onerror=null;" />
                    </div>
                    <div class="p-3">
                        <div class="font-semibold">{{ $v->title }}</div>
                        <div class="mt-1 line-clamp-2 text-xs text-neutral-500">{{ $v->description ?? $v->original_filename }}</div>
                        <div class="mt-3 flex items-center justify-between text-xs">
                            <div class="text-neutral-500">{{ number_format(($v->file_size ?? 0)/1024/1024,2) }} MB</div>
                            <div>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium
                                    {{ match($v->status){
                                        'queued' => 'bg-sky-100 text-sky-800',
                                        'processing' => 'bg-blue-100 text-blue-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'failed' => 'bg-red-100 text-red-800',
                                        default => 'bg-yellow-100 text-yellow-800'
                                    } }}
                                ">{{ ucfirst($v->status) }}</span>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            @if($v->status === 'completed' && $v->hls_path)
                                <button class="btn-preview inline-flex items-center rounded-md border border-neutral-300 bg-white px-3 py-1.5 text-xs text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200" data-playlist-url="{{ route('video.playlist', ['playlist' => basename($v->hls_path)]) }}">Preview</button>
                            @else
                                <span class="text-xs text-neutral-400">&nbsp;</span>
                            @endif
                            <div class="flex items-center gap-2">
                                <button class="btn-edit inline-flex items-center rounded-md border border-neutral-300 bg-white px-3 py-1.5 text-xs text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200" data-id="{{ $v->id }}" data-title="{{ $v->title }}" data-description="{{ $v->description }}">Edit</button>
                                <div class="copy-dropdown relative">
                                    <button class="copy-dropdown-toggle inline-flex items-center rounded-md border border-neutral-300 bg-white px-3 py-1.5 text-xs text-neutral-700 hover:bg-neutral-50 disabled:opacity-50 disabled:cursor-not-allowed dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200" @disabled(!$v->is_public)>
                                        Copy
                                        <svg class="ms-1 size-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08z" clip-rule="evenodd"/></svg>
                                    </button>
                                    <div class="copy-dropdown-menu absolute right-0 z-50 mt-1 hidden w-36 overflow-hidden rounded-md border border-neutral-200 bg-white p-1 text-xs shadow-lg dark:border-neutral-700 dark:bg-zinc-900">
                                        <button class="btn-copy-link block w-full rounded px-2 py-1.5 text-left hover:bg-neutral-50 disabled:opacity-50 disabled:cursor-not-allowed dark:hover:bg-zinc-800" data-id="{{ $v->id }}" data-public="{{ $v->is_public ? 1 : 0 }}" @disabled(!$v->is_public)>Copy Link</button>
                                        <button class="btn-copy-embed block w-full rounded px-2 py-1.5 text-left hover:bg-neutral-50 disabled:opacity-50 disabled:cursor-not-allowed dark:hover:bg-zinc-800" data-id="{{ $v->id }}" data-public="{{ $v->is_public ? 1 : 0 }}" @disabled(!$v->is_public)>Copy Embed</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                    <div class="text-sm text-neutral-500">Belum ada video.</div>
                @endforelse
            </div>
            <div class="px-1 py-3">{{ $videos->withQueryString()->links() }}</div>
        </div>

        <!-- Table -->
        <div id="list-view" class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900 {{ ($viewMode ?? 'grid')==='list' ? '' : 'hidden' }}">
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
                            <td class="px-4 py-3" id="status-cell-{{ $v->id }}">
                                <span id="status-badge-{{ $v->id }}" class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    @class([
                                        'bg-sky-100 text-sky-800' => $v->status==='queued',
                                        'bg-yellow-100 text-yellow-800' => $v->status==='uploaded',
                                        'bg-blue-100 text-blue-800' => $v->status==='processing',
                                        'bg-green-100 text-green-800' => $v->status==='completed',
                                        'bg-red-100 text-red-800' => $v->status==='failed',
                                    ])
                                ">{{ ucfirst($v->status) }}</span>
                                @if(in_array($v->status,['queued','processing']))
                                    <div class="mt-1 h-1.5 w-28 overflow-hidden rounded bg-neutral-200 dark:bg-neutral-800">
                                        <div class="h-1.5 rounded bg-blue-500" id="bar-{{ $v->id }}" style="width: {{ (int)($v->progress ?? 0) }}%"></div>
                                    </div>
                                    <div class="mt-0.5 text-[10px] text-neutral-500" id="pct-{{ $v->id }}">{{ (int)($v->progress ?? 0) }}%</div>
                                @endif
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
        // dropdown toggle
        const toggle = e.target.closest('.copy-dropdown-toggle');
        if(toggle){
            e.preventDefault();
            const dd = toggle.closest('.copy-dropdown');
            const menu = dd?.querySelector('.copy-dropdown-menu');
            // close other menus
            document.querySelectorAll('.copy-dropdown-menu').forEach(m=>{ if(m!==menu) m.classList.add('hidden'); });
            if(menu){ menu.classList.toggle('hidden'); }
            return;
        }
        // click outside to close
        if(!e.target.closest('.copy-dropdown')){
            document.querySelectorAll('.copy-dropdown-menu').forEach(m=> m.classList.add('hidden'));
        }
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
                const data = await postJson(@json(route('video.convert')), { video_id: id });
                if(data.success){
                    toast('Ditambahkan ke antrian', 'success');
                    // Update status cell to show queued + progress 0%
                    setQueuedUI(id);
                } else {
                    toast(data.message || 'Convert failed', 'error');
                }
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

    // Poll progress every 5s
    async function pollProgress(){
        try{
            const res = await fetch(@json(route('video.progress')));
            const json = await res.json();
            (json.data||[]).forEach(v => {
                const bar = document.getElementById('bar-'+v.id);
                const pct = document.getElementById('pct-'+v.id);
                if(!bar || !pct){ setQueuedUI(v.id); }
                const bar2 = document.getElementById('bar-'+v.id);
                const pct2 = document.getElementById('pct-'+v.id);
                if(bar2){ bar2.style.width = (v.progress||0)+'%'; }
                if(pct2){ pct2.textContent = (v.progress||0)+'%'; }
            });
        }catch(e){}
        setTimeout(pollProgress, 5000);
    }
    pollProgress();

    function setQueuedUI(id){
        const cell = document.getElementById('status-cell-'+id);
        const badge = document.getElementById('status-badge-'+id);
        if(badge){
            badge.textContent = 'Queued';
            badge.className = 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-sky-100 text-sky-800';
        }
        if(cell && !document.getElementById('bar-'+id)){
            const barWrap = document.createElement('div');
            barWrap.className = 'mt-1 h-1.5 w-28 overflow-hidden rounded bg-neutral-200 dark:bg-neutral-800';
            const bar = document.createElement('div');
            bar.id = 'bar-'+id; bar.className='h-1.5 rounded bg-blue-500'; bar.style.width='0%';
            barWrap.appendChild(bar);
            const pct = document.createElement('div');
            pct.id = 'pct-'+id; pct.className='mt-0.5 text-[10px] text-neutral-500'; pct.textContent='0%';
            cell.appendChild(barWrap); cell.appendChild(pct);
        }
    }

    // Realtime via Echo (optional)
    if (window.Echo) {
        try {
            window.Echo.channel('videos')
                .listen('.video.progress', (e) => {
                    if(!e || !e.id) return;
                    setQueuedUI(e.id);
                    const bar = document.getElementById('bar-'+e.id);
                    const pct = document.getElementById('pct-'+e.id);
                    if(bar){ bar.style.width = (e.progress||0)+'%'; }
                    if(pct){ pct.textContent = (e.progress||0)+'%'; }
                })
                .listen('.video.status', (e) => {
                    if(!e || !e.id) return;
                    const badge = document.getElementById('status-badge-'+e.id);
                    if(!badge) return;
                    const map = {
                        queued: ['bg-sky-100 text-sky-800','Queued'],
                        processing: ['bg-blue-100 text-blue-800','Processing'],
                        completed: ['bg-green-100 text-green-800','Completed'],
                        failed: ['bg-red-100 text-red-800','Failed'],
                        uploaded: ['bg-yellow-100 text-yellow-800','Uploaded'],
                    };
                    const cfg = map[e.status] || map.uploaded;
                    badge.className = 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium '+cfg[0];
                    badge.textContent = cfg[1];
                    if(e.status==='completed' || e.status==='failed'){
                        const bar = document.getElementById('bar-'+e.id);
                        const pct = document.getElementById('pct-'+e.id);
                        if(bar){ bar.parentElement?.remove(); }
                        if(pct){ pct.remove(); }
                    } else {
                        setQueuedUI(e.id);
                    }
                });
        } catch (err) {
            console.warn('Echo not ready', err);
        }
    }

    // View toggle + persist to DB
    function setView(mode){
        const grid = document.getElementById('grid-view');
        const list = document.getElementById('list-view');
        if(mode==='grid'){ grid.classList.remove('hidden'); list.classList.add('hidden'); }
        else { list.classList.remove('hidden'); grid.classList.add('hidden'); }
        // persist
        fetch(@json(route('video.viewmode')), { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf}, body: JSON.stringify({mode}) }).catch(()=>{});
    }
    document.getElementById('btn-grid')?.addEventListener('click', (e)=>{ e.preventDefault(); setView('grid'); });
    document.getElementById('btn-list')?.addEventListener('click', (e)=>{ e.preventDefault(); setView('list'); });

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
