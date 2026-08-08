<nav class="flex-1 space-y-1 p-3 text-sm font-medium">
    <a href="{{ route('forms.index') }}" class="flex items-center gap-2.5 rounded-md px-3 py-2 transition-colors duration-fast hover:bg-accent hover:text-accent-foreground {{ request()->routeIs('forms.index') ? 'bg-accent text-accent-foreground' : 'text-muted-foreground' }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M9 8h6m-8 8H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v0m2 12a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2m10-8a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2z"/></svg>
        Forms
    </a>
    <a href="{{ route('forms.create') }}" class="flex items-center gap-2.5 rounded-md px-3 py-2 transition-colors duration-fast hover:bg-accent hover:text-accent-foreground {{ request()->routeIs('forms.create') ? 'bg-accent text-accent-foreground' : 'text-muted-foreground' }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        New Form
    </a>
    <a href="{{ route('imports.docx') }}" class="flex items-center gap-2.5 rounded-md px-3 py-2 transition-colors duration-fast hover:bg-accent hover:text-accent-foreground {{ request()->routeIs('imports.*') ? 'bg-accent text-accent-foreground' : 'text-muted-foreground' }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
        Import
    </a>
</nav>

<div class="border-t border-border p-3">
    <a href="{{ route('settings.index') }}" class="flex items-center gap-2.5 rounded-md px-3 py-2 text-sm font-medium transition-colors duration-fast hover:bg-accent hover:text-accent-foreground {{ request()->routeIs('settings.*') ? 'bg-accent text-accent-foreground' : 'text-muted-foreground' }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
        Settings
    </a>
    <p class="mt-2 px-3 text-xs text-muted-foreground">Form Builder v2</p>
</div>
