<div
    x-data="{ online: navigator.onLine }"
    x-init="
        window.addEventListener('online', () => online = true);
        window.addEventListener('offline', () => online = false);
    "
    x-show="!online"
    x-transition
    class="fixed top-0 left-0 right-0 z-50 bg-amber-100 border-b border-amber-300 px-4 py-2 text-center text-sm font-medium text-amber-800"
    style="display: none;"
>
    <span class="mr-2">📡</span>
    Anda sedang offline. Beberapa fitur mungkin terbatas.
</div>
