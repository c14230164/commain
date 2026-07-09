@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" x-data="{ 
    selected: [], 
    handleMerge() {
        const name = prompt('Masukkan Nama Proyek Gabungan Baru:');
        if (!name || name.trim() === '') return;
        
        $refs.mergeName.value = name;
        $refs.mergeForm.submit();
    }
}">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Projects</h1>
            <p class="text-slate-500 mt-1">Manage and review your qualitative research projects.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="handleMerge()" 
                    :disabled="selected.length < 2"
                    :class="selected.length < 2 ? 'opacity-50 cursor-not-allowed bg-slate-200 text-slate-400' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100'"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-bold transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                Merge Selected Projects
            </button>
            <form action="{{ route('projects.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2" x-data="{ uploading: false }">
                @csrf
                <input type="file" name="file" id="import_file" class="hidden" accept=".json" @change="uploading = true; $el.form.submit()">
                <label for="import_file" class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-white text-slate-700 border border-slate-200 rounded-xl font-medium hover:bg-slate-50 transition-colors shadow-sm">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    <span x-text="uploading ? 'Importing...' : 'Import Project JSON'"></span>
                </label>
            </form>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Project
            </a>
        </div>
    </div>
    <!-- Hidden Merge Form -->
    <form x-ref="mergeForm" action="{{ route('projects.merge') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="new_name" x-ref="mergeName">
        <template x-for="id in selected" :key="id">
            <input type="hidden" name="project_ids[]" :value="id">
        </template>
    </form>

    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl flex items-center gap-3">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex items-center gap-3">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        {{ session('error') }}
    </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 w-10">
                            <!-- Checkbox Column -->
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500">No</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Project Name</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 text-center">Questions</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Last Edited</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($projects as $index => $project)
                    <tr class="hover:bg-slate-50/50 transition-colors" :class="selected.includes('{{ $project->id }}') ? 'bg-indigo-50/50' : ''">
                        <td class="px-6 py-4">
                            <input type="checkbox" value="{{ $project->id }}" x-model="selected"
                                   class="w-5 h-5 text-indigo-600 bg-white border-slate-300 rounded focus:ring-indigo-500">
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-400">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-900">{{ $project->name }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $project->question_count }} Questions
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500">
                            {{ $project->updated_at->format('d M Y, H:i') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('projects.setup', $project) }}" class="inline-flex items-center px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-200 transition-colors">
                                    Edit
                                </a>
                                <a href="{{ route('projects.review', $project) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-semibold hover:bg-indigo-100 transition-colors">
                                    Review
                                </a>
                                <a href="{{ route('projects.export', $project) }}" class="inline-flex items-center px-3 py-1.5 bg-slate-50 text-slate-600 border border-slate-200 rounded-lg text-sm font-semibold hover:bg-slate-100 transition-colors" title="Export JSON">
                                    Export
                                </a>
                                <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Delete this project and all its data?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-500 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                </div>
                                <h3 class="text-lg font-bold text-slate-900">No Projects found</h3>
                                <p class="text-slate-500">Get started by creating your first project.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
