@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-12" x-data="{ filter: 'ALL' }">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-12 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Project Review</h1>
            <p class="text-slate-500 mt-1">Statistics and variable distribution for <span class="font-bold text-slate-700">{{ $project->name }}</span></p>
        </div>
        
        <div class="flex items-center gap-3">
            <span class="text-sm font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap">Filter:</span>
            <select x-model="filter" class="bg-white border border-slate-200 rounded-xl px-4 py-2.5 font-semibold text-slate-700 focus:ring-4 focus:ring-indigo-100 outline-none shadow-sm cursor-pointer min-w-[200px]">
                <option value="ALL">ALL QUESTIONS</option>
                @foreach($data as $q)
                    <option value="{{ $q['id'] }}">Question {{ $q['order'] }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="space-y-16">
        @foreach($data as $q)
        <section x-show="filter === 'ALL' || filter === '{{ $q['id'] }}'" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 class="bg-white border border-slate-200 rounded-[32px] p-8 shadow-sm">
            
            <header class="mb-10 border-b border-slate-100 pb-6 flex items-start gap-4">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold shrink-0 shadow-lg shadow-indigo-100">
                    {{ $q['order'] }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900 leading-tight">Question {{ $q['order'] }}</h2>
                    <p class="text-slate-500 mt-1 font-medium">{{ $q['text'] }}</p>
                </div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
                <!-- Table Side -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Variable Name</th>
                                <th class="px-4 py-3 text-center">Count</th>
                                <th class="px-4 py-3 text-right">Percentage</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($q['variables'] as $index => $v)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-4 text-sm text-slate-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-4 text-sm font-bold text-slate-900">{{ $v['name'] }}</td>
                                <td class="px-4 py-4 text-sm text-center font-bold text-indigo-600 bg-indigo-50/30 rounded-xl">
                                    {{ $v['count'] }}
                                </td>
                                <td class="px-4 py-4 text-sm text-right font-medium text-slate-600">
                                    {{ $v['percentage'] }}%
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-400 italic">No data collected yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if(count($q['variables']) > 0)
                        <tfoot>
                            <tr class="bg-slate-50/50 font-bold text-slate-700">
                                <td colspan="2" class="px-4 py-3 text-sm">TOTAL RESPONDENTS WHO ANSWERED</td>
                                <td class="px-4 py-3 text-center text-sm">{{ $q['total_respondents'] }}</td>
                                <td class="px-4 py-3 text-right text-sm italic text-slate-400">Total % can exceed 100%</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

                <!-- Chart Side -->
                <div class="bg-slate-50/50 rounded-3xl p-8 flex flex-col items-center justify-center min-h-[400px]">
                    @if(count($q['variables']) > 0)
                        <div class="w-full max-w-[350px] relative">
                            <canvas id="chart-{{ $q['id'] }}"></canvas>
                        </div>
                    @else
                        <div class="text-center text-slate-400">
                            <svg class="w-16 h-16 mx-auto mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                            <p class="font-medium">No chart data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>
        @endforeach
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const questionsData = @json($data);
        
        questionsData.forEach(q => {
            if (q.variables.length > 0) {
                const ctx = document.getElementById(`chart-${q.id}`).getContext('2d');
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: q.variables.map(v => v.name),
                        datasets: [{
                            data: q.variables.map(v => v.count),
                            backgroundColor: [
                                '#6366f1', '#a855f7', '#ec4899', '#f43f5e', 
                                '#f59e0b', '#10b981', '#06b6d4', '#3b82f6',
                                '#64748b'
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    font: {
                                        family: "'Plus Jakarta Sans', sans-serif",
                                        weight: '600'
                                    }
                                }
                            },
                        }
                    }
                });
            }
        });
    });
</script>
@endpush
@endsection
