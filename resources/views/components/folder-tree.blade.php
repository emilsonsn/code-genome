@foreach ($tree as $folder => $children)
    <div class="flex items-center gap-2 text-slate-300 font-mono">
        <span class="text-slate-500">
            {!! str_repeat('|&nbsp;&nbsp;&nbsp;', $level ?? 0) !!}--
        </span>
        <i class="fa-solid fa-folder text-yellow-500"></i>
        <span>{{ $folder }}</span>
    </div>

    @if (!empty($children))
        @include('components.folder-tree', [
            'tree' => $children,
            'level' => ($level ?? 0) + 1,
        ])
    @endif
@endforeach