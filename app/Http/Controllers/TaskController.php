<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index()
    {
        // Força a leitura do banco para garantir que não estamos vendo cache da WebView
        $tasks = Task::orderBy('created_at', 'desc')->get();
        return view('tasks.index', compact('tasks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        try {
            // Inicia transação para garantir persistência no SQLite
            DB::beginTransaction();
            
            $task = new Task();
            $task->title = $request->title;
            $task->completed = false;
            $task->save();
            
            DB::commit();

            Log::info("Tarefa criada com sucesso: ID {$task->id}");

            // O status 303 é CRUCIAL para WebViews Android não tentarem reenviar o POST como GET na mesma URL
            return redirect()->to(route('tasks.index'), 303);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao persistir no SQLite: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro de persistência: ' . $e->getMessage()]);
        }
    }

    public function toggle(Task $task)
    {
        try {
            $task->completed = !$task->completed;
            $task->save();
            
            return redirect()->to(route('tasks.index'), 303);
        } catch (\Exception $e) {
            Log::error('Erro ao alternar status: ' . $e->getMessage());
            return back();
        }
    }

    public function destroy(Task $task)
    {
        try {
            $task->delete();
            return redirect()->to(route('tasks.index'), 303);
        } catch (\Exception $e) {
            Log::error('Erro ao deletar: ' . $e->getMessage());
            return back();
        }
    }
}
