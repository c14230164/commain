<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Question;
use App\Models\Respondent;
use App\Models\Variable;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function home()
    {
        return view('home');
    }

    public function index()
    {
        $projects = Project::latest('updated_at')->get();
        return view('projects.index', compact('projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'question_count' => 'required|integer|min:1|max:99',
        ]);

        $name = $request->name;
        if (empty($name)) {
            $count = Project::count() + 1;
            $name = "Project " . $count;
        }

        $project = Project::create([
            'name' => $name,
            'question_count' => $request->question_count,
        ]);

        return redirect()->route('projects.setup', $project);
    }

    public function setup(Project $project)
    {
        // Load questions if they exist
        $project->load('questions');
        return view('projects.setup', compact('project'));
    }

    public function storeQuestions(Request $request, Project $project)
    {
        $request->validate([
            'project_name' => 'required|string|max:255',
            'questions' => 'required|array',
            'questions.*' => 'required|string',
        ]);

        $project->update(['name' => $request->project_name]);

        foreach ($request->questions as $index => $text) {
            $project->questions()->updateOrCreate(
                ['order' => $index + 1],
                ['text' => $text]
            );
        }

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('projects.setup', $project)->with('status', 'questions-saved');
    }

    public function storeRespondent(Request $request, Project $project)
    {
        $request->validate([
            'paper_id' => 'required|string',
            'castor_name' => 'nullable|string',
            'answers' => 'required|array', // question_id => [variable names]
        ]);

        // Create or update respondent
        $respondent = Respondent::updateOrCreate(
            ['project_id' => $project->id, 'paper_id' => $request->paper_id],
            ['castor_name' => $request->castor_name]
        );

        // Sync variables
        $variableIds = [];
        foreach ($request->answers as $questionId => $variableNames) {
            foreach ($variableNames as $name) {
                if (empty($name)) continue;
                
                $variable = Variable::firstOrCreate([
                    'question_id' => $questionId,
                    'name' => trim($name),
                ]);
                $variableIds[] = $variable->id;
            }
        }

        $respondent->variables()->sync($variableIds);

        return response()->json([
            'success' => true,
            'respondent' => $respondent,
            'summary' => $this->getSummaryData($project)
        ]);
    }

    public function getSuggestions(Question $question)
    {
        $suggestions = Variable::where('question_id', $question->id)
            ->distinct()
            ->pluck('name');
        
        return response()->json($suggestions);
    }

    public function getSummary(Project $project)
    {
        return response()->json($this->getSummaryData($project));
    }

    public function getRespondents(Project $project)
    {
        $respondents = $project->respondents()->latest()->get(['id', 'paper_id', 'castor_name']);
        return response()->json($respondents);
    }

    public function getRespondentFull(Project $project, Respondent $respondent)
    {
        $respondent->load('variables.question');
        return response()->json($respondent);
    }

    public function getVariableRespondents(Variable $variable)
    {
        $respondents = $variable->respondents()->get(['respondents.id', 'paper_id', 'castor_name']);
        return response()->json($respondents);
    }

    public function review(Project $project)
    {
        $questions = $project->questions()->with(['variables' => function ($query) {
            $query->withCount('respondents')->orderBy('respondents_count', 'desc');
        }])->get();

        $data = $questions->map(function ($question) {
            // New denominator: total unique respondents who answered this specific question
            $totalRespondentsWhoAnswered = Respondent::whereHas('variables', function($q) use ($question) {
                $q->where('question_id', $question->id);
            })->count();
            
            return [
                'id' => $question->id,
                'order' => $question->order,
                'text' => $question->text,
                'total_respondents' => $totalRespondentsWhoAnswered,
                'variables' => $question->variables->filter(fn($v) => $v->respondents_count > 0)->map(function ($variable) use ($totalRespondentsWhoAnswered) {
                    return [
                        'name' => $variable->name,
                        'count' => $variable->respondents_count,
                        'percentage' => $totalRespondentsWhoAnswered > 0 
                            ? round(($variable->respondents_count * 100) / $totalRespondentsWhoAnswered, 2) 
                            : 0
                    ];
                })->values()
            ];
        });

        return view('projects.review', compact('project', 'data'));
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('status', 'project-deleted');
    }

    public function deleteRespondent(Project $project, Respondent $respondent)
    {
        $respondent->delete();
        return response()->json(['success' => true, 'summary' => $this->getSummaryData($project)]);
    }

    public function renameVariable(Request $request, Variable $variable)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $newName = trim($request->name);
        
        if ($variable->name === $newName) {
            return response()->json(['success' => true]);
        }

        $existingVariable = Variable::where('question_id', $variable->question_id)
            ->where('name', $newName)
            ->first();

        if ($existingVariable) {
            // Merge: Transfer respondents to the existing variable
            $respondentIds = $variable->respondents()->pluck('respondents.id');
            $existingVariable->respondents()->syncWithoutDetaching($respondentIds);
            
            // Delete the old variable
            $variable->delete();
        } else {
            // Simple rename
            $variable->update(['name' => $newName]);
        }

        return response()->json(['success' => true]);
    }

    private function getSummaryData(Project $project)
    {
        return $project->questions()->with(['variables' => function($q) {
            $q->withCount('respondents');
        }])->get()->map(function($question) {
            return [
                'id' => $question->id,
                'order' => $question->order,
                'variables' => $question->variables->filter(fn($v) => $v->respondents_count > 0)->map(function($v) {
                    return [
                        'id' => $v->id,
                        'name' => $v->name,
                        'count' => $v->respondents_count
                    ];
                })->values()
            ];
        });
    }
}
