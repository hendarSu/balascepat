<div id="toast-root" class="pointer-events-none fixed inset-0 z-[100] flex flex-col items-end gap-2 p-4 sm:p-6"></div>
<script>
    (function(){
        function el(html){
            const t = document.createElement('template');
            t.innerHTML = html.trim();
            return t.content.firstChild;
        }
        function cls(type){
            switch(type){
                case 'success': return 'bg-green-600 text-white';
                case 'error': return 'bg-red-600 text-white';
                case 'warning': return 'bg-yellow-600 text-white';
                case 'info': default: return 'bg-neutral-900 text-white';
            }
        }
        window.toast = function(message, type = 'info', opts = {}){
            const root = document.getElementById('toast-root');
            if(!root) return alert(message);
            const duration = opts.duration || 2800;
            const icon = {
                success: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4.5 12.75 6 6 9-13.5"/></svg>',
                error: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Z"/></svg>',
                warning: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg>',
                info: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9h.01M11 12h1v6h1M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Z"/></svg>'
            }[type] || '';

            const node = el(`
                <div class="pointer-events-auto transform transition-all duration-300 translate-y-2 opacity-0 ${cls(type)} shadow-lg rounded-md flex items-center gap-2 px-3 py-2 text-sm">
                    <span class="shrink-0">${icon}</span>
                    <span>${message}</span>
                </div>
            `);
            root.appendChild(node);
            requestAnimationFrame(()=>{
                node.classList.remove('translate-y-2','opacity-0');
            });
            const close = () => {
                node.classList.add('translate-y-2','opacity-0');
                setTimeout(()=> node.remove(), 280);
            };
            setTimeout(close, duration);
            node.addEventListener('click', close);
        };
    })();
</script>

