@php use App\Game\Config\UIConfig; @endphp
<div class="min-h-screen relative overflow-hidden bg-slate-950">
    {{-- Camada de Fundo do Mapa --}}
    <div class="absolute inset-0 z-0 transition-transform duration-1000 ease-out"
        style="background-image: url('{{ asset($this->getSelectedMapBackgroundAsset()) }}'); background-size: cover; background-position: center; filter: brightness(0.75) contrast(1.1) saturate(0.9);">
    </div>

    {{-- Efeito de Grid de Scanner --}}
    <div class="absolute inset-0 z-1 pointer-events-none opacity-10" style="background-image: linear-gradient(#334155 1px, transparent 1px), linear-gradient(90deg, #334155 1px, transparent 1px); background-size: 50px 50px;"></div>

    {{-- Overlay de Vinheta --}}
    <div class="absolute inset-0 z-2 pointer-events-none bg-[radial-gradient(circle_at_center,transparent_0%,rgba(2,6,23,0.7)_100%)]"></div>

    {{-- Conteúdo Interativo --}}
    <div class="relative z-10 h-screen flex flex-col">
        
        {{-- Header Tático --}}
        <header class="p-8 flex justify-between items-start bg-gradient-to-b from-black/60 to-transparent">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse shadow-[0_0_10px_#10b981]"></div>
                    <h1 class="text-4xl font-black text-white tracking-tighter uppercase">CodeSurvivor <span class="text-emerald-500">Mapa</span></h1>
                </div>
                @php $selectedMap = $this->getSelectedMap(); @endphp
                <p class="text-slate-400 font-mono text-xs uppercase tracking-[0.3em]">{{ $selectedMap['name'] }} // {{ $selectedMap['subtitle'] }}</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    @foreach ($this->getCampaignMaps() as $mapId => $campaignMap)
                        @php
                            $isMapUnlocked = $campaignMap['unlocked'] ?? false;
                            $mapCompletion = $campaignMap['completion'] ?? ['completed' => 0, 'total' => 0];
                        @endphp
                        <button
                            type="button"
                            wire:click="selectMap({{ $mapId }})"
                            {{ $isMapUnlocked ? '' : 'disabled' }}
                            class="px-4 py-2 rounded border text-left transition-all
                                {{ $this->selectedMap === $mapId
                                    ? 'bg-emerald-500/20 border-emerald-400 text-emerald-100 shadow-[0_0_18px_rgba(16,185,129,0.22)]'
                                    : ($isMapUnlocked
                                        ? 'bg-black/45 border-slate-700 text-slate-300 hover:border-emerald-500/60 hover:text-white'
                                        : 'bg-slate-950/45 border-slate-800 text-slate-600 cursor-not-allowed') }}"
                        >
                            <div class="text-[10px] font-black uppercase tracking-[0.22em]">{{ $campaignMap['name'] }}</div>
                            <div class="mt-0.5 text-[11px] font-bold uppercase tracking-wider">{{ $mapCompletion['completed'] }}/{{ $mapCompletion['total'] }} fases</div>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-6">
                @php $selectedCompletion = $this->getSelectedMapCompletion(); @endphp
                <div class="p-4 bg-black/60 backdrop-blur-md border border-slate-800 rounded-sm">
                    <div class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-1">Progresso do jogo</div>
                    <div class="flex items-end gap-2">
                        <div class="text-3xl font-black text-emerald-400 tabular-nums">
                            {{ $selectedCompletion['completed'] }}<span class="text-slate-600 text-xl">/</span>{{ $selectedCompletion['total'] }}
                        </div>
                        <div class="text-[10px] text-slate-400 font-bold uppercase mb-1">Fases</div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Área Central do Mapa --}}
        {{-- overflow-visible: evita que ícones das fases nas bordas e os tooltips/descrições sejam cortados --}}
        <main class="flex-grow relative overflow-visible">
            {{-- Container dos Pontos de Interesse (POIs) --}}
            <div class="absolute inset-0 flex items-center justify-center">
                {{-- Aumentado de 1000x600 para 1200x800 --}}
                <div class="relative w-[1200px] h-[800px]">
                    
                    @php $visibleLevels = $this->getLevels(); @endphp
                    @foreach ($visibleLevels as $level => $levelData)
                        @php
                            $status = $this->getLevelStatus($level);
                            $isLocked = $status === 'locked';
                            $isCompleted = $status === 'completed';
                            $isAvailable = $status === 'available';
                            $posX = $levelData['mapLocation']['x'] ?? 0;
                            $posY = $levelData['mapLocation']['y'] ?? 0;
                            $iconAsset = $this->getMapIconAsset($levelData['mapIcon']);
                            $tooltipAbove = $posY >= 420;
                        @endphp

                        {{-- Ponto de Fase --}}
                        {{-- hover:z-50 eleva a fase em foco acima das vizinhas, garantindo que ícone e tooltip não fiquem sobrepostos/cortados --}}
                        <div class="absolute group hover:z-50" style="left: {{ $posX }}px; top: {{ $posY }}px;">
                            
                            {{-- Linha de Conexão --}}
                            @php
                                $levelKeys = array_keys($visibleLevels);
                                $currentLevelIndex = array_search($level, $levelKeys, true);
                                $nextLevelKey = $currentLevelIndex !== false ? ($levelKeys[$currentLevelIndex + 1] ?? null) : null;
                            @endphp
                            @if ($nextLevelKey !== null)
                                @php
                                    $nextLevel = $visibleLevels[$nextLevelKey];
                                    $nextX = $nextLevel['mapLocation']['x'];
                                    $nextY = $nextLevel['mapLocation']['y'];
                                    $distX = $nextX - $posX;
                                    $distY = $nextY - $posY;
                                    $angle = atan2($distY, $distX) * 180 / M_PI;
                                    $length = sqrt($distX**2 + $distY**2);
                                @endphp
                                <div class="absolute top-1/2 left-1/2 h-1 border-t-4 border-dotted {{ $isCompleted ? 'border-emerald-500/45' : 'border-slate-500/35' }} origin-left pointer-events-none"
                                     style="width: {{ $length }}px; transform: rotate({{ $angle }}deg); z-index: -1;">
                                </div>
                            @endif

                            {{-- Marcador Interativo --}}
                            <button 
                                wire:click="selectLevel({{ $level }})"
                                {{-- class="relative transform transition-all duration-300 hover:scale-110 focus:outline-none {{ $isLocked ? 'grayscale opacity-40 cursor-not-allowed' : 'cursor-pointer' }}" --}}
                                class="relative transform transition-all duration-300 focus:outline-none {{ $isLocked ? 'opacity-85 cursor-not-allowed' : 'cursor-pointer hover:scale-110' }}"
                                {{ $isLocked ? 'disabled' : '' }}
                            >
                                {{-- Halo de Destaque --}}
                                @if ($isAvailable)
                                    <div class="absolute -inset-6 bg-emerald-500/20 rounded-full animate-ping opacity-50"></div>
                                @endif

                                {{-- Ícone da Edificação (Aumentado de w-24 h-24 para w-32 h-32) --}}
                                {{-- <div class="relative z-10 w-32 h-32 flex items-center justify-center bg-black/60 backdrop-blur-md rounded-2xl border-2 {{ $isCompleted ? 'border-emerald-500 shadow-[0_0_20px_rgba(16,185,129,0.3)]' : ($isAvailable ? 'border-white animate-pulse shadow-[0_0_20px_rgba(255,255,255,0.2)]' : 'border-slate-800') }} shadow-2xl"> --}}
                                    <div class="relative z-10 w-32 h-32 flex items-center justify-center rounded-2xl border-2 backdrop-blur-md shadow-2xl
                                        {{ $isCompleted 
                                            ? 'bg-black/60 border-emerald-500 shadow-[0_0_20px_rgba(16,185,129,0.3)]' 
                                            : ($isAvailable 
                                                ? 'bg-black/55 border-white animate-pulse shadow-[0_0_20px_rgba(255,255,255,0.2)]' 
                                                : 'bg-slate-900/55 border-slate-500/60 shadow-[0_0_16px_rgba(148,163,184,0.12)]') 
                                        }}">
                                    {{-- <img src="{{ asset($iconAsset) }}" class="w-24 h-24 object-contain drop-shadow-[0_5px_15px_rgba(0,0,0,0.5)]" alt="{{ $levelData['name'] }}" /> --}}
                                    <img 
                                        src="{{ asset($iconAsset) }}" 
                                        class="w-24 h-24 object-contain transition-all duration-300
                                            {{ $isLocked 
                                                ? 'opacity-70 saturate-50 drop-shadow-[0_4px_10px_rgba(0,0,0,0.35)]' 
                                                : 'drop-shadow-[0_5px_15px_rgba(0,0,0,0.5)]' 
                                            }}" 
                                        alt="{{ $levelData['name'] }}" 
                                    />
                                    
                                    {{-- Badge de Status --}}
                                    <div class="absolute -top-3 -right-3 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter shadow-lg border
                                        {{ $isCompleted 
                                            ? 'bg-emerald-500 text-black border-emerald-300' 
                                            : ($isAvailable 
                                                ? 'bg-white text-black border-white' 
                                                : 'bg-slate-700/90 text-slate-200 border-slate-500/70') 
                                        }}">
                                        @if($isCompleted) CONCLUIDA @elseif($isAvailable) JOGAR @else BLOQUEADA @endif
                                    </div>
                                </div>

                                {{-- Tooltip de Informação --}}
                                <div class="absolute {{ $tooltipAbove ? 'bottom-full mb-4' : 'top-full mt-4' }} left-1/2 -translate-x-1/2 w-56 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none z-50">
                                    <div class="bg-black/95 backdrop-blur-2xl border border-slate-700 p-4 rounded-lg shadow-2xl">
                                        <div class="text-[10px] text-emerald-400 font-mono mb-1">FASE #0{{ $levelData['mapLevel'] ?? $level }}</div>
                                        <div class="text-base font-black text-white leading-tight mb-2">{{ $levelData['name'] }}</div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ $levelData['difficulty'] }}</span>
                                            @if ($isLocked)
                                                <span class="text-[10px] text-red-500 font-bold uppercase">BLOQUEADA</span>
                                            @endif
                                        </div>
                                    </div>
                                    {{-- Seta do Tooltip --}}
                                    <div class="w-3 h-3 bg-black/95 border-slate-700 rotate-45 absolute left-1/2 -translate-x-1/2 {{ $tooltipAbove ? '-bottom-1.5 border-r border-b' : '-top-1.5 border-t border-l' }}"></div>
                                </div>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </main>

        {{-- Footer de Navegação/Ações --}}
        <footer class="p-8 flex justify-between items-center bg-gradient-to-t from-black/80 to-transparent">
            <div class="flex flex-wrap items-center gap-4">
                <a href="/" class="px-8 py-3 bg-slate-900/50 hover:bg-red-900/20 hover:border-red-900/50 text-slate-400 hover:text-red-400 border border-slate-800 rounded-sm text-xs font-black uppercase tracking-widest transition-all">
                    Voltar ao menu
                </a>
                <div class="flex flex-wrap items-center gap-3 rounded border border-slate-800 bg-black/55 px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Concluída</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-white"></span> Disponível</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-slate-600"></span> Bloqueada</span>
                </div>
            </div>

            <div class="text-right">
                <div class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-1">Proxima fase</div>
                @php
                    $nextAvailable = collect($this->getLevels())->first(fn($l, $id) => $this->getLevelStatus($id) === 'available');
                @endphp
                @if ($nextAvailable)
                    <div class="text-2xl font-black text-white uppercase tracking-tighter">{{ $nextAvailable['name'] }}</div>
                @else
                    <div class="text-2xl font-black text-emerald-500 uppercase tracking-tighter">Jogo concluido</div>
                @endif
            </div>
        </footer>
    </div>

    {{-- Efeito de Scanline --}}
    <div class="absolute inset-0 z-50 pointer-events-none opacity-[0.03] bg-[linear-gradient(rgba(18,16,16,0)_50%,rgba(0,0,0,0.25)_50%),linear-gradient(90deg,rgba(255,0,0,0.06),rgba(0,255,0,0.02),rgba(0,0,255,0.06))] bg-[length:100%_2px,3px_100%]"></div>
</div>
