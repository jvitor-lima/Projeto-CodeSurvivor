<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List NativePHP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #f3f4f6; }
        .task-card { background: white; border-radius: 8px; padding: 12px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="p-4">
    <div class="max-w-md mx-auto">
        <h1 class="text-2xl font-bold mb-6 text-center text-blue-600">Minhas Tarefas</h1>

        <!-- Formulário de Criação (POST Explícito) -->
        <form action="{{ route('tasks.store') }}" method="POST" class="mb-6">
            @csrf
            <div class="flex gap-2">
                <input type="text" name="title" placeholder="Nova tarefa..." required
                    class="flex-1 p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg font-bold">
                    +
                </button>
            </div>
        </form>

        <!-- Lista de Tarefas -->
        <div class="space-y-3">
            @forelse($tasks as $task)
                <div class="task-card flex items-center justify-between">
                    <div class="flex items-center gap-3 flex-1">
                        <!-- Toggle Status (POST Direto) -->
                        <form action="{{ route('tasks.toggle', $task) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-6 h-6 rounded-full border-2 flex items-center justify-center {{ $task->completed ? 'bg-green-500 border-green-500' : 'border-gray-300' }}">
                                @if($task->completed)
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                @endif
                            </button>
                        </form>

                        <span class="{{ $task->completed ? 'line-through text-gray-400' : 'text-gray-800' }} font-medium">
                            {{ $task->title }}
                        </span>
                    </div>

                    <!-- Deletar (POST Direto) -->
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Excluir esta tarefa?')">
                        @csrf
                        <button type="submit" class="text-red-500 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </form>
                </div>
            @empty
                <p class="text-center text-gray-500 mt-10">Nenhuma tarefa pendente!</p>
            @endforelse
        </div>
    </div>

    @if($errors->any())
        <div class="fixed bottom-4 left-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ $errors->first() }}
        </div>
    @endif
</body>
</html>
