<div class="space-y-4">
    <div>
        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Dashboard URL</h4>
        <div class="flex items-center space-x-2" x-data="{ copied: false }">
            <input 
                type="text" 
                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100" 
                value="{{ $dashboardUrl }}" 
                readonly 
            >
            <button 
                type="button" 
                class="px-3 py-2 bg-blue-500 text-white rounded-md text-sm hover:bg-blue-600 min-w-[80px]" 
                @click="
                    const el = document.createElement('textarea');
                    el.value = '{{ $dashboardUrl }}';
                    document.body.appendChild(el);
                    el.select();
                    document.execCommand('copy');
                    document.body.removeChild(el);
                    copied = true;
                    setTimeout(() => copied = false, 2000);
                "
            >
                <span x-show="!copied">Copy</span>
                <span x-show="copied">Copied!</span>
            </button>
        </div>
    </div>

    <div>
        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Password</h4>
        <div class="flex items-center space-x-2" x-data="{ copied: false }">
            <input 
                type="text" 
                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 font-mono" 
                value="{{ $password }}" 
                readonly 
            >
            <button 
                type="button" 
                class="px-3 py-2 bg-green-500 text-white rounded-md text-sm hover:bg-green-600 min-w-[80px]" 
                @click="
                    const el = document.createElement('textarea');
                    el.value = '{{ $password }}';
                    document.body.appendChild(el);
                    el.select();
                    document.execCommand('copy');
                    document.body.removeChild(el);
                    copied = true;
                    setTimeout(() => copied = false, 2000);
                "
            >
                <span x-show="!copied">Copy</span>
                <span x-show="copied">Copied!</span>
            </button>
        </div>
    </div>

    @if($lastAccessed)
    <div>
        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Last Accessed</h4>
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $lastAccessed }}</p>
    </div>
    @endif

    <div>
        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Status</h4>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $isActive ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
            {{ $isActive ? 'Active' : 'Inactive' }}
        </span>
    </div>

    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
        <h5 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Instructions</h5>
        <ol class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
            <li>1. Share the Dashboard URL with your external users</li>
            <li>2. Provide them with the password for access</li>
            <li>3. Users will be able to view project progress and tickets</li>
        </ol>
    </div>
</div>