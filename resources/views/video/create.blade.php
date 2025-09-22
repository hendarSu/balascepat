<x-layouts.app :title="__('Tambah Video')" :breadcrumbs="[
    ['label' => __('Dashboard'), 'url' => route('dashboard')],
    ['label' => __('Video'), 'url' => route('video.index')],
    ['label' => __('Tambah')],
]">
    <div class="space-y-6">
        <flux:heading>{{ __('Tambah Video') }}</flux:heading>

        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4 bg-white dark:bg-zinc-900">
            <form id="upload-form" class="space-y-4" enctype="multipart/form-data">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <flux:field>
                            <flux:label>{{ __('Judul') }}</flux:label>
                            <flux:input name="title" required />
                        </flux:field>
                    </div>
                    <div>
                        <flux:field>
                            <flux:label>{{ __('Visibilitas') }}</flux:label>
                            <div class="flex items-center gap-3">
                                <input type="hidden" name="is_public" value="0">
                                <input type="checkbox" name="is_public" value="1" class="size-4 rounded border-neutral-300 text-neutral-900 dark:bg-zinc-900 dark:border-neutral-700">
                                <span class="text-sm">{{ __('Publik') }}</span>
                            </div>
                        </flux:field>
                    </div>
                </div>

                <flux:field>
                    <flux:label>{{ __('Deskripsi') }}</flux:label>
                    <flux:textarea name="description" rows="3" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('File Video') }}</flux:label>
                    <input id="video-input" class="hidden" type="file" name="video" accept="video/*" required />
                    <div id="video-dropzone" class="mt-2 flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-neutral-300 p-8 text-center text-neutral-600 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-zinc-800">
                        <div class="text-sm"><span class="font-medium text-neutral-900 dark:text-neutral-100">{{ __('Pilih file') }}</span> {{ __('atau tarik & lepas di sini') }}</div>
                        <div class="text-xs text-neutral-400">MP4, MOV, AVI, WMV — max 100KB</div>
                        <div id="video-selected" class="hidden rounded-md bg-neutral-100 px-2 py-1 text-xs text-neutral-700 dark:bg-zinc-800 dark:text-neutral-200"></div>
                    </div>
                </flux:field>

                <div class="flex items-center gap-2">
                    <flux:button id="btn-upload" type="submit" variant="primary">{{ __('Upload') }}</flux:button>
                    <flux:button id="btn-convert" type="button" class="hidden" data-video-id="" variant="primary">{{ __('Konversi ke HLS (single key)') }}</flux:button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const uploadForm = document.getElementById('upload-form');
    const btnUpload = document.getElementById('btn-upload');
    const btnConvert = document.getElementById('btn-convert');
    const inputVideo = document.getElementById('video-input');
    const dropzone = document.getElementById('video-dropzone');
    const selectedInfo = document.getElementById('video-selected');

    function humanSize(bytes){
        if(!bytes && bytes !== 0) return '';
        const units=['B','KB','MB','GB'];
        let i=0; let n=bytes;
        while(n>=1024 && i<units.length-1){ n/=1024; i++; }
        return n.toFixed(2)+' '+units[i];
    }

    function showSelected(file){
        if(!file){ selectedInfo.classList.add('hidden'); selectedInfo.textContent=''; return; }
        selectedInfo.textContent = `${file.name} • ${humanSize(file.size)}`;
        selectedInfo.classList.remove('hidden');
    }

    dropzone?.addEventListener('click', ()=> inputVideo.click());
    inputVideo?.addEventListener('change', ()=> showSelected(inputVideo.files[0]));
    ;['dragenter','dragover'].forEach(evt=> dropzone?.addEventListener(evt, (e)=>{ e.preventDefault(); e.stopPropagation(); dropzone.classList.add('ring-2','ring-neutral-300','dark:ring-neutral-600'); }));
    ;['dragleave','drop'].forEach(evt=> dropzone?.addEventListener(evt, (e)=>{ e.preventDefault(); e.stopPropagation(); dropzone.classList.remove('ring-2','ring-neutral-300','dark:ring-neutral-600'); }));
    dropzone?.addEventListener('drop', (e)=>{
        const dt = e.dataTransfer;
        if(!dt || !dt.files || !dt.files.length) return;
        const file = dt.files[0];
        inputVideo.files = dt.files;
        showSelected(file);
    });

    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(uploadForm);
        // Client-side size validation 100KB
        if(!inputVideo.files.length){ toast('Silakan pilih file video', 'warning'); return; }
        if(inputVideo.files[0].size > 100 * 1024){
            toast('Ukuran file melebihi 100KB', 'error');
            return;
        }
        if(!inputVideo.files.length){ toast('Silakan pilih file video', 'warning'); return; }
        btnUpload.setAttribute('disabled', 'true');
        btnUpload.textContent = 'Uploading...';
        try {
            const res = await fetch("{{ route('video.upload') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                // Redirect to list after successful upload
                window.location.href = "{{ route('video.index') }}";
            } else {
                toast(data.message || 'Upload failed', 'error');
            }
        } catch (err) {
            toast('Upload error', 'error');
        } finally {
            btnUpload.removeAttribute('disabled');
            btnUpload.textContent = 'Upload';
        }
    });

    btnConvert?.addEventListener('click', async () => {
        const id = btnConvert.dataset.videoId;
        if (!id) return;
        btnConvert.setAttribute('disabled', 'true');
        btnConvert.textContent = 'Converting...';
        try {
            const res = await fetch("{{ route('video.convert') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                },
                body: JSON.stringify({ video_id: id, encryption_type: 'single' })
            });
            const data = await res.json();
            if (data.success) {
                window.location.href = "{{ route('video.index') }}";
            } else {
                toast(data.message || 'Convert failed', 'error');
            }
        } catch (err) {
            toast('Convert error', 'error');
        } finally {
            btnConvert.removeAttribute('disabled');
            btnConvert.textContent = 'Konversi ke HLS (single key)';
        }
    });
    </script>
</x-layouts.app>
