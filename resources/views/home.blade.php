@extends('layouts.app')

@section('content')
<div class="h-[calc(100vh-128px)] flex items-center justify-center bg-slate-50 relative overflow-hidden" x-data="{ showModal: false }">
    <!-- Background Accents -->
    <div class="absolute top-0 -left-4 w-72 h-72 bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
    <div class="absolute top-0 -right-4 w-72 h-72 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
    <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>

    <div class="relative z-10 text-center px-4">
        <h1 class="text-5xl font-extrabold tracking-tight text-slate-900 mb-4">
            Qualitative Analysis <span class="text-indigo-600">Simplified.</span>
        </h1>
        <p class="text-lg text-slate-600 mb-10 max-w-2xl mx-auto">
            Manage your research papers, variables, and respondents in one integrated platform designed for qualitative researchers.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <button @click="showModal = true" 
                    class="w-full sm:w-auto px-8 py-4 bg-indigo-600 text-white rounded-2xl font-semibold shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all focus:ring-4 focus:ring-indigo-100">
                New Project
            </button>
            <a href="{{ route('projects.index') }}" 
               class="w-full sm:w-auto px-8 py-4 bg-white text-slate-900 border border-slate-200 rounded-2xl font-semibold shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all focus:ring-4 focus:ring-slate-100 text-center">
                Preview Project
            </a>
        </div>
    </div>

    <!-- Modal New Project -->
    <div x-show="showModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
         @click.away="showModal = false"
         style="display: none;">
        
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 relative overflow-hidden" x-data="{ count: 1 }">
            <h2 class="text-2xl font-bold mb-6 text-slate-900">Configure Your Project</h2>
            
            <form action="{{ route('projects.store') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Project Name</label>
                    <input type="text" name="name" placeholder="Leave empty for auto-name"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-100 focus:border-indigo-600 outline-none transition-all">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-3">Number of Questions (1-99)</label>
                    <div class="flex items-center gap-4">
                        <button type="button" @click="if(count > 1) count--" 
                                class="w-12 h-12 flex items-center justify-center rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                        </button>
                        
                        <input type="number" name="question_count" x-model="count" min="1" max="99" 
                               class="w-full text-center text-xl font-bold bg-transparent border-none focus:ring-0">
                        
                        <button type="button" @click="if(count < 99) count++"
                                class="w-12 h-12 flex items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 hover:bg-indigo-200 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="showModal = false"
                            class="flex-1 px-6 py-3 border border-slate-200 rounded-xl font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 px-6 py-3 bg-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-colors">
                        Confirm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    @keyframes blob {
        0% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    .animate-blob { animation: blob 7s infinite; }
    .animation-delay-2000 { animation-delay: 2s; }
    .animation-delay-4000 { animation-delay: 4s; }
</style>
@endsection
