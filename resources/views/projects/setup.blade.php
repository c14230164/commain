@extends('layouts.app')

@section('content')
<div class="max-w-[1600px] mx-auto px-4 py-8" x-data="projectSetup()">
    <!-- Progress Stepper -->
    <div class="max-w-4xl mx-auto mb-10">
        <div class="flex items-center justify-between relative">
            <div class="absolute inset-0 top-1/2 -translate-y-1/2 h-0.5 bg-slate-200"></div>
            
            <div class="relative z-10 flex flex-col items-center gap-2 cursor-pointer" @click="step = 'questions'">
                <div :class="step === 'questions' ? 'bg-indigo-600' : 'bg-green-500'" 
                     class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold transition-colors">
                    <template x-if="step === 'questions'"><span>1</span></template>
                    <template x-if="step !== 'questions'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </template>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider" :class="step === 'questions' ? 'text-indigo-600' : 'text-slate-500'">Input Questions</span>
            </div>

            <div class="relative z-10 flex flex-col items-center gap-2">
                <div :class="step === 'castor' ? 'bg-indigo-600' : 'bg-slate-200'" 
                     class="w-10 h-10 rounded-full flex items-center justify-center transition-colors shadow-lg"
                     :class="step === 'castor' ? 'text-white shadow-indigo-200' : 'text-slate-400'">
                    <span class="font-bold">2</span>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider" :class="step === 'castor' ? 'text-indigo-600' : 'text-slate-500'">Input Castor</span>
            </div>
        </div>
    </div>

    <!-- Step 1: Input Questions -->
    <div x-show="step === 'questions'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" class="max-w-4xl mx-auto">
        <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-sm">
            <form @submit.prevent="saveQuestions" id="questionsForm" class="space-y-6">
                @csrf
                
                <div class="mb-10 pb-10 border-b border-slate-100">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Project Name</label>
                    <input type="text" name="project_name" value="{{ $project->name }}" required
                           class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-xl font-bold text-slate-900 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-600 outline-none transition-all shadow-sm">
                </div>
                @csrf
                @for($i = 1; $i <= $project->question_count; $i++)
                <div class="group">
                    <label class="block text-sm font-bold text-slate-700 mb-2 group-focus-within:text-indigo-600 transition-colors">Question {{ $i }}</label>
                    <textarea name="questions[]" rows="3" required placeholder="Enter the question text here..."
                              class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-100 focus:border-indigo-600 outline-none transition-all resize-none shadow-sm"
                              >{{ $project->questions->where('order', $i)->first()?->text }}</textarea>
                </div>
                @endfor

                <div class="pt-6 flex justify-end">
                    <button type="submit" :disabled="saving"
                            class="inline-flex items-center gap-2 px-8 py-4 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 disabled:opacity-50">
                        <span x-text="saving ? 'Saving...' : 'Next Step'"></span>
                        <svg x-show="!saving" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Step 2: Input Castor (2 Columns) -->
    <div x-show="step === 'castor'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" style="display: none;">
        <div class="grid grid-cols-1 lg:grid-cols-10 gap-8">
            <!-- Left Column: Form (70%) -->
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-sm">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900">Castor Input</h2>
                            <p class="text-slate-500 text-sm font-medium">Project: <span class="text-indigo-600">{{ $project->name }}</span></p>
                        </div>
                        <span class="px-4 py-1 bg-indigo-50 text-indigo-700 rounded-full text-sm font-bold" x-text="respondent.id ? 'Editing Data' : 'New Entry'"></span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-10">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">ID Kertas Jawaban <span class="text-red-500">*</span></label>
                            <input type="text" x-model="respondent.paper_id" required placeholder="e.g. CSR-001"
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-100 focus:border-indigo-600 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Castor (Optional)</label>
                            <input type="text" x-model="respondent.castor_name" placeholder="Researcher Name"
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-100 focus:border-indigo-600 outline-none transition-all">
                        </div>
                    </div>

                    <div class="space-y-8">
                        <template x-for="(q, qIndex) in questions" :key="q.id">
                            <div class="p-6 bg-slate-50/50 rounded-3xl border border-slate-100">
                                <div class="flex items-start gap-4 mb-4">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex-shrink-0 flex items-center justify-center text-white font-bold text-sm" x-text="q.order"></div>
                                    <h3 class="text-lg font-bold text-slate-800 pt-0.5" x-text="q.text"></h3>
                                </div>

                                <div class="space-y-3 ml-12">
                                    <template x-for="(v, vIndex) in respondent.answers[q.id]" :key="vIndex">
                                        <div class="flex items-center gap-3 group relative">
                                            <div class="flex-grow relative" x-data="{ open: false, searchTerm: '' }">
                                                <input type="text" 
                                                       x-model="respondent.answers[q.id][vIndex]"
                                                       @input="searchTerm = $event.target.value; open = true"
                                                       @focus="searchTerm = respondent.answers[q.id][vIndex]; open = true"
                                                       @click.away="open = false"
                                                       placeholder="Type variable..."
                                                       :class="isExistingVariable(q.id, respondent.answers[q.id][vIndex]) ? 'bg-yellow-50 border-yellow-300' : 'bg-white border-slate-200'"
                                                       class="w-full pl-4 pr-10 py-2.5 border rounded-xl focus:ring-4 focus:ring-indigo-100 focus:border-indigo-600 outline-none transition-all font-medium">
                                                
                                                <!-- Autocomplete Dropdown -->
                                                <div x-show="open && getFilteredSuggestions(q.id, searchTerm).length > 0" 
                                                     class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-xl py-1 overflow-hidden">
                                                    <template x-for="sug in getFilteredSuggestions(q.id, searchTerm)" :key="sug">
                                                        <button @click="respondent.answers[q.id][vIndex] = sug; open = false" 
                                                                class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 transition-colors font-medium text-slate-700" 
                                                                x-text="sug"></button>
                                                    </template>
                                                </div>
                                            </div>
                                            <button @click="removeVariable(q.id, vIndex)" 
                                                    class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all opacity-0 group-hover:opacity-100">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </template>
                                    
                                    <button @click="addVariable(q.id)" 
                                            class="inline-flex items-center gap-2 text-sm font-bold text-indigo-600 hover:text-indigo-800 transition-colors py-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        Add Variable
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Left Footer Actions -->
                    <div class="mt-12 flex items-center justify-between pt-8 border-t border-slate-100">
                        <button @click="showEditModal = true"
                                class="px-6 py-3 border border-slate-200 rounded-xl font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                            Edit / Load Prev
                        </button>
                        
                        <div class="flex items-center gap-4">
                            <template x-if="respondent.id">
                                <button @click="confirmDeleteRespondent()" :disabled="saving"
                                        class="p-4 text-red-500 hover:bg-red-50 border border-red-200 rounded-xl transition-all">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </template>
                            <button @click="submitRespondent('next')" :disabled="saving"
                                    class="px-8 py-3 bg-white text-indigo-600 border-2 border-indigo-600 rounded-xl font-bold hover:bg-indigo-50 transition-all disabled:opacity-50">
                                <span x-text="saving ? 'Saving...' : 'Next Respondent'"></span>
                            </button>
                            <button @click="submitRespondent('finish')" :disabled="saving"
                                    class="px-10 py-3 bg-indigo-600 text-white rounded-xl font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all disabled:opacity-50">
                                Finish
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Summary Table (30%) -->
            <div class="lg:col-span-3">
                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm sticky top-24">
                    <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center justify-between">
                        Summary
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    </h3>
                    
                    <div class="space-y-8 max-h-[calc(100vh-250px)] overflow-y-auto pr-2 custom-scrollbar">
                        <template x-for="qSummary in summary" :key="qSummary.id">
                            <div>
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                    <span class="w-5 h-5 bg-slate-100 border border-slate-200 rounded flex items-center justify-center text-[10px]" x-text="qSummary.order"></span>
                                    Question <span x-text="qSummary.order"></span>
                                </h4>
                                
                                <div class="bg-slate-50/50 rounded-2xl border border-slate-100 overflow-hidden text-sm">
                                    <table class="w-full">
                                        <thead>
                                            <tr class="text-slate-500 border-b border-slate-100">
                                                <th class="px-4 py-2 text-left font-semibold">Name</th>
                                                <th class="px-2 py-2 text-center font-semibold">Qty</th>
                                                <th class="px-2 py-2 text-right"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            <template x-for="v in qSummary.variables" :key="v.id">
                                                <tr class="hover:bg-white transition-colors">
                                                    <td class="px-4 py-3 font-bold text-slate-700" x-text="v.name"></td>
                                                    <td class="px-2 py-3 text-center font-medium text-slate-500" x-text="v.count"></td>
                                                    <td class="px-2 py-3 text-right whitespace-nowrap">
                                                        <button @click="renameVariable(v)" class="text-slate-400 hover:text-indigo-600 p-1" title="Rename Variable">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                        </button>
                                                        <button @click="openReviewModal(v)" class="text-indigo-600 hover:text-indigo-800 font-bold p-1">Lihat</button>
                                                    </td>
                                                </tr>
                                            </template>
                                            <template x-if="qSummary.variables.length === 0">
                                                <tr>
                                                    <td colspan="3" class="px-4 py-4 text-center text-slate-400 italic text-xs">No variables yet</td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit/Load Previous Modal -->
    <div x-show="showEditModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
         style="display: none;" @keydown.escape.window="showEditModal = false">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl p-8 max-h-[80vh] overflow-hidden flex flex-col" @click.away="showEditModal = false">
            <h2 class="text-2xl font-bold mb-6">Edit or Load Previous</h2>
            
            <div class="grid grid-cols-2 gap-4 mb-8">
                <button @click="step = 'questions'; showEditModal = false" class="p-6 border-2 border-slate-100 rounded-3xl hover:border-indigo-600 hover:bg-slate-50 transition-all text-center group">
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <span class="font-bold text-slate-800">Ganti Soal Pertanyaan</span>
                    <p class="text-xs text-slate-500 mt-1">Ubah narasi pertanyaan proyek</p>
                </button>
                <div class="p-6 bg-slate-50 rounded-3xl border-2 border-slate-100 text-center flex flex-col justify-center">
                    <span class="font-bold text-slate-800">Edit Variabel Castor</span>
                    <p class="text-xs text-slate-500 mt-1">Pilih data lama di bawah untuk di-edit</p>
                </div>
            </div>

            <div class="flex-grow overflow-y-auto custom-scrollbar">
                <div class="space-y-3">
                    <template x-for="r in respondents" :key="r.id">
                        <button @click="loadRespondentData(r.id)" class="w-full flex items-center justify-between p-4 bg-slate-50 hover:bg-indigo-50 border border-slate-100 rounded-2xl transition-all group">
                            <div class="flex flex-col items-start leading-tight">
                                <span class="font-bold text-slate-900" x-text="r.paper_id"></span>
                                <span class="text-sm text-slate-500" x-text="r.castor_name || 'No Name'"></span>
                            </div>
                            <span class="text-indigo-600 font-bold opacity-0 group-hover:opacity-100 transition-opacity">Load Data &rarr;</span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-100">
                <button @click="showEditModal = false" class="w-full py-3 bg-slate-100 text-slate-600 rounded-xl font-bold hover:bg-slate-200 transition-colors">Close</button>
            </div>
        </div>
    </div>

    <!-- Review/Lihat Variable Modal -->
    <div x-show="showReviewModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
         style="display: none;" @keydown.escape.window="showReviewModal = false">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8" @click.away="showReviewModal = false">
            <template x-if="selectedVariable">
                <div>
                    <h2 class="text-2xl font-bold mb-4" x-text="'Variable: ' + selectedVariable.name"></h2>
                    <p class="text-slate-500 mb-6">List of respondents using this variable:</p>
                    
                    <div class="space-y-3 max-h-[40vh] overflow-y-auto custom-scrollbar pr-2">
                        <template x-for="r in variableRespondents" :key="r.id">
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <div>
                                    <div class="font-bold text-slate-900" x-text="r.paper_id"></div>
                                    <div class="text-xs text-slate-500" x-text="r.castor_name || 'No Name'"></div>
                                </div>
                                <button @click="loadRespondentData(r.id); showReviewModal = false" class="px-3 py-1 bg-white border border-slate-200 rounded-lg text-sm font-bold text-indigo-600 hover:bg-indigo-50 transition-colors">Edit</button>
                            </div>
                        </template>
                    </div>

                    <button @click="showReviewModal = false" class="w-full mt-8 py-3 bg-slate-100 text-slate-600 rounded-xl font-bold hover:bg-slate-200 transition-colors">Close</button>
                </div>
            </template>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

@push('scripts')
<script>
    function projectSetup() {
        return {
            step: '{{ $project->questions->count() > 0 ? "castor" : "questions" }}',
            saving: false,
            questions: @json($project->questions),
            respondent: {
                id: null,
                paper_id: '',
                castor_name: '',
                answers: {}
            },
            suggestions: {}, // question_id => array of names
            summary: [],
            respondents: [],
            showEditModal: false,
            showReviewModal: false,
            selectedVariable: null,
            variableRespondents: [],

            init() {
                // Initialize answers mapping
                this.questions.forEach(q => {
                    this.respondent.answers[q.id] = [''];
                    this.fetchSuggestions(q.id);
                });
                this.fetchSummary();
                this.fetchRespondents();
            },

            async saveQuestions() {
                this.saving = true;
                const form = document.getElementById('questionsForm');
                const formData = new FormData(form);
                try {
                    const response = await axios.post('{{ route("projects.questions.store", $project) }}', formData);
                    if (response.data.success) {
                        location.reload(); // Easier to refresh state for new questions
                    }
                } catch (e) { alert('Error saving questions'); }
                this.saving = false;
            },

            addVariable(questionId) {
                this.respondent.answers[questionId].push('');
            },

            removeVariable(questionId, index) {
                if (this.respondent.answers[questionId].length > 1) {
                    this.respondent.answers[questionId].splice(index, 1);
                } else {
                    this.respondent.answers[questionId][0] = '';
                }
            },

            async fetchSuggestions(questionId) {
                try {
                    const response = await axios.get(`/projects/questions/${questionId}/suggestions`);
                    this.suggestions = { ...this.suggestions, [questionId]: response.data };
                } catch (e) {}
            },

            getFilteredSuggestions(questionId, term) {
                if (!term || !this.suggestions[questionId]) return [];
                const res = this.suggestions[questionId].filter(s => s.toLowerCase().includes(term.toLowerCase()));
                return res.slice(0, 5); // top 5
            },

            handleInput(questionId, index, val) {
                // Just keeping data reactive
            },

            isExistingVariable(qId, name) {
                if (!name || !this.suggestions[qId]) return false;
                return this.suggestions[qId].includes(name);
            },

            async fetchSummary() {
                try {
                    const response = await axios.get('{{ route("projects.summary", $project) }}');
                    this.summary = response.data;
                } catch (e) {}
            },

            async fetchRespondents() {
                try {
                    const response = await axios.get('{{ route("projects.respondents.list", $project) }}');
                    this.respondents = response.data;
                } catch (e) {}
            },

            async openReviewModal(variable) {
                this.selectedVariable = variable;
                try {
                    const response = await axios.get(`/projects/variables/${variable.id}/respondents`);
                    this.variableRespondents = response.data;
                    this.showReviewModal = true;
                } catch (e) {}
            },

            async loadRespondentData(respondentId) {
                try {
                    const response = await axios.get(`/projects/{{ $project->id }}/respondent/${respondentId}`);
                    const data = response.data;
                    this.respondent.id = data.id;
                    this.respondent.paper_id = data.paper_id;
                    this.respondent.castor_name = data.castor_name;
                    
                    // Reset answers then populate
                    this.questions.forEach(q => this.respondent.answers[q.id] = []);
                    data.variables.forEach(v => {
                        this.respondent.answers[v.question_id].push(v.name);
                    });
                    
                    // Ensure each question has at least one box
                    this.questions.forEach(q => {
                        if (this.respondent.answers[q.id].length === 0) this.respondent.answers[q.id] = [''];
                    });

                    this.showEditModal = false;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } catch (e) { alert('Error loading data'); }
            },

            async confirmDeleteRespondent() {
                if (!confirm('Hapus entry castor ini?')) return;
                this.saving = true;
                try {
                    const response = await axios.delete(`/projects/{{ $project->id }}/respondent/${this.respondent.id}`);
                    if (response.data.success) {
                        this.summary = response.data.summary;
                        this.fetchRespondents();
                        this.resetForm();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                } catch (e) { alert('Error deleting data'); }
                this.saving = false;
            },

            async renameVariable(variable) {
                const newName = prompt('Enter new name for variable "' + variable.name + '":', variable.name);
                if (!newName || newName.trim() === '' || newName === variable.name) return;

                try {
                    const response = await axios.post(`/projects/variables/${variable.id}/rename`, {
                        name: newName
                    });
                    if (response.data.success) {
                        this.fetchSummary();
                        this.fetchRespondents();
                        this.questions.forEach(q => this.fetchSuggestions(q.id));
                    }
                } catch (e) { alert('Error renaming variable'); }
            },

            async submitRespondent(type) {
                if (!this.respondent.paper_id) {
                    alert('Paper ID is required');
                    return;
                }

                // Check for existing paper_id if this is a fresh new entry
                if (!this.respondent.id) {
                    const existing = this.respondents.find(r => r.paper_id === this.respondent.paper_id);
                    if (existing) {
                        if (!confirm(`ID Kertas Jawaban "${this.respondent.paper_id}" sudah pernah digunakan di project ini.\n\nApakah anda ingin melihat/mengupdate data tersebut?`)) {
                            return;
                        }
                    }
                }
                
                this.saving = true;
                try {
                    const response = await axios.post('{{ route("projects.respondent.store", $project) }}', {
                        paper_id: this.respondent.paper_id,
                        castor_name: this.respondent.castor_name,
                        answers: this.respondent.answers
                    });
                    
                    if (response.data.success) {
                        this.summary = response.data.summary;
                        this.fetchRespondents();
                        
                        // Update suggestions for the questions edited
                        this.questions.forEach(q => this.fetchSuggestions(q.id));

                        if (type === 'next') {
                            this.resetForm();
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        } else {
                            window.location.href = '{{ route("projects.index") }}';
                        }
                    }
                } catch (e) { alert('Error saving data'); }
                this.saving = false;
            },

            resetForm() {
                this.respondent.id = null;
                this.respondent.paper_id = '';
                this.respondent.castor_name = '';
                this.questions.forEach(q => {
                    this.respondent.answers[q.id] = [''];
                });
            }
        }
    }
</script>
@endpush
@endsection
