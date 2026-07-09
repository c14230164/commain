<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Question;
use App\Models\Respondent;
use App\Models\Variable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectExportImportController extends Controller
{
    public function export(Project $project)
    {
        // Eager load everything needed
        $project->load([
            'questions.variables',
            'respondents.variables'
        ]);

        $data = [
            'name' => $project->name,
            'question_count' => $project->question_count,
            'questions' => $project->questions->map(function ($q) {
                return [
                    'id' => $q->id,
                    'order' => $q->order,
                    'text' => $q->text,
                    'variables' => $q->variables->map(function ($v) {
                        return [
                            'id' => $v->id,
                            'name' => $v->name,
                        ];
                    })
                ];
            }),
            'respondents' => $project->respondents->map(function ($r) {
                return [
                    'paper_id' => $r->paper_id,
                    'castor_name' => $r->castor_name,
                    'variable_ids' => $r->variables->pluck('id')
                ];
            })
        ];

        $filename = Str::slug($project->name) . '_export.json';
        
        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json,txt',
        ]);

        $json = file_get_contents($request->file('file')->getRealPath());
        $data = json_decode($json, true);

        if (!$data || !isset($data['name'])) {
            return back()->with('error', 'Invalid JSON format');
        }

        DB::beginTransaction();
        try {
            // 1. Project Handle
            $name = $data['name'];
            if (Project::where('name', $name)->exists()) {
                $name .= ' - Imported';
            }

            $project = Project::create([
                'name' => $name,
                'question_count' => $data['question_count']
            ]);

            // 2. Questions & Variables Map
            $variableMap = []; // old_id => new_id

            foreach ($data['questions'] as $qData) {
                $question = $project->questions()->create([
                    'order' => $qData['order'],
                    'text' => $qData['text']
                ]);

                foreach ($qData['variables'] as $vData) {
                    $variable = Variable::create([
                        'question_id' => $question->id,
                        'name' => $vData['name']
                    ]);
                    $variableMap[$vData['id']] = $variable->id;
                }
            }

            // 3. Respondents & Pivot Relations
            foreach ($data['respondents'] as $rData) {
                $respondent = $project->respondents()->create([
                    'paper_id' => $rData['paper_id'],
                    'castor_name' => $rData['castor_name']
                ]);

                // Map variable IDs
                $newVariableIds = [];
                foreach ($rData['variable_ids'] as $oldId) {
                    if (isset($variableMap[$oldId])) {
                        $newVariableIds[] = $variableMap[$oldId];
                    }
                }

                if (!empty($newVariableIds)) {
                    $respondent->variables()->sync($newVariableIds);
                }
            }

            DB::commit();
            return redirect()->route('projects.index')->with('success', 'Project imported successfully as ' . $name);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error during import: ' . $e->getMessage());
        }
    }

    public function merge(Request $request)
    {
        $request->validate([
            'project_ids' => 'required|array|min:2',
            'new_name' => 'required|string|max:255',
        ]);

        $projectIds = $request->project_ids;
        $projects = Project::whereIn('id', $projectIds)->get();

        // 1. Validation: Question Count
        $counts = $projects->pluck('question_count')->unique();
        if ($counts->count() > 1) {
            return back()->with('error', 'Gagal Merge! Proyek yang dipilih harus memiliki JUMLAH SOAL yang sama.');
        }

        // 2. Validation: Duplicate Paper IDs
        $tempPaperIds = [];
        foreach ($projects as $project) {
            $ids = $project->respondents()->pluck('paper_id')->toArray();
            foreach ($ids as $id) {
                if (isset($tempPaperIds[$id])) {
                    return back()->with('error', "Gagal Merge! Terjadi duplikasi Nomor Kertas Castor: [{$id}]. Silakan periksa kembali inputan tim Anda.");
                }
                $tempPaperIds[$id] = true;
            }
        }

        DB::beginTransaction();
        try {
            // 3. Create New Project
            $newProject = Project::create([
                'name' => $request->new_name,
                'question_count' => $counts->first()
            ]);

            // 4. Clone Questions and keep mapping
            $questionMap = []; // old_order => new_question_id
            $firstProject = $projects->first();
            foreach ($firstProject->questions as $q) {
                $newQ = $newProject->questions()->create([
                    'order' => $q->order,
                    'text' => $q->text
                ]);
                $questionMap[$q->order] = $newQ->id;
            }

            // 5. Clone Respondents & Consolidated Variables
            // We need a variable mapping to prevent duplicates: [new_question_id][lowercase_name] => new_variable_id
            $variableCache = [];

            foreach ($projects as $oldProject) {
                // Eager load respondents with variables and their questions
                $respondents = $oldProject->respondents()->with('variables.question')->get();

                foreach ($respondents as $oldRespondent) {
                    $newRespondent = $newProject->respondents()->create([
                        'paper_id' => $oldRespondent->paper_id,
                        'castor_name' => $oldRespondent->castor_name
                    ]);

                    $newVariableIds = [];
                    foreach ($oldRespondent->variables as $oldVar) {
                        $qOrder = $oldVar->question->order;
                        $newQuestionId = $questionMap[$qOrder];
                        $varName = trim($oldVar->name);
                        $varKey = strtolower($varName);

                        if (!isset($variableCache[$newQuestionId][$varKey])) {
                            $newVar = Variable::create([
                                'question_id' => $newQuestionId,
                                'name' => $varName
                            ]);
                            $variableCache[$newQuestionId][$varKey] = $newVar->id;
                        }

                        $newVariableIds[] = $variableCache[$newQuestionId][$varKey];
                    }

                    if (!empty($newVariableIds)) {
                        $newRespondent->variables()->sync($newVariableIds);
                    }
                }
            }

            DB::commit();
            return redirect()->route('projects.index')->with('success', "Proyek berhasil digabungkan menjadi: " . $request->new_name);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error during merge: ' . $e->getMessage());
        }
    }
}
