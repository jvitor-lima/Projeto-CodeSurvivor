@php use App\Game\Config\UIConfig; @endphp
@php
    $commandDefs = [
        ['snippet' => 'hero.moveRight()', 'label' => 'Mover Direita', 'icon' => 'arrow-r', 'color' => 'blue'],
        ['snippet' => 'hero.moveLeft()', 'label' => 'Mover Esquerda', 'icon' => 'arrow-l', 'color' => 'blue'],
        ['snippet' => 'hero.moveUp()', 'label' => 'Mover Cima', 'icon' => 'arrow-u', 'color' => 'blue'],
        ['snippet' => 'hero.moveDown()', 'label' => 'Mover Baixo', 'icon' => 'arrow-d', 'color' => 'blue'],
        ['snippet' => 'hero.attack()', 'label' => 'Atacar', 'icon' => 'attack', 'color' => 'rose'],
        ['snippet' => 'hero.wait()', 'label' => 'Esperar', 'icon' => 'clock', 'color' => 'slate'],
        ['snippet' => "repeat(3) {\n  hero.moveRight()\n}", 'label' => 'Repetir bloco', 'icon' => 'loop', 'color' => 'amber'],
    ];
    $levelLoreEntries = $this->getLevelLoreEntries();
    $levelTutorial = $this->getLevelTutorial();
@endphp
{{--
    game-board.blade.php
    View do jogo CodeSurvivor - Interface Imersiva de Sobrevivência Tática
--}}

<div
    class="focus-host min-h-screen overflow-x-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 p-3 sm:p-4 md:p-8 font-['Inter'] text-slate-100 antialiased"
    x-data="{
        showInventory: @entangle('showInventoryModal').live,
        showLore: false,
        activeLoreTab: 0,
        activeLorePanel: 'story',
        tutorial: @js($levelTutorial),
        tutorialStep: 0,
        showTutorial: false,
        focusMode: false,
        dragging: null,
        hasCommands: @js(trim((string) ($commands ?? '')) !== ''),
        inventoryDraggingSlot: null,
        autocompleteOpen: false,
        autocompleteSelected: 0,
        autocompleteMatches: [],
        autocompleteTop: 52,
        autocompleteLeft: 20,
        hintSpotlight: false,
        hintSpotlightTimer: null,
        commandSuggestions: [
            { insert: 'hero.moveRight()', label: 'hero.moveRight()', hint: 'Move Leon 1 casa para a direita', icon: 'arrow-r' },
            { insert: 'hero.moveLeft()', label: 'hero.moveLeft()', hint: 'Move Leon 1 casa para a esquerda', icon: 'arrow-l' },
            { insert: 'hero.moveUp()', label: 'hero.moveUp()', hint: 'Move Leon 1 casa para cima', icon: 'arrow-u' },
            { insert: 'hero.moveDown()', label: 'hero.moveDown()', hint: 'Move Leon 1 casa para baixo', icon: 'arrow-d' },
            { insert: 'hero.attack()', label: 'hero.attack()', hint: 'Ataca o inimigo a frente', icon: 'attack' },
            { insert: 'hero.wait()', label: 'hero.wait()', hint: 'Espera 1 turno para observar inimigos', icon: 'clock' },
            { insert: 'repeat(3) {\n  hero.moveRight()\n}', label: 'repeat(3) { ... }', hint: 'Repete um bloco de comandos', icon: 'loop' }
        ],
        tutorialIconSvg(name) {
            const icons = {
                hero: '<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.7\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M4.5 21a7.5 7.5 0 0 1 15 0\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 15.5 14 18l-2 2.5L10 18l2-2.5Z\'/></svg>',
                target: '<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.7\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 17a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 13.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z\'/></svg>',
                terminal: '<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.7\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M4 5h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'m7 10 3 2-3 2M12 15h5\'/></svg>',
                arrows: '<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.7\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 3v18M12 3 8.5 6.5M12 3l3.5 3.5M12 21l-3.5-3.5M12 21l3.5-3.5M3 12h18M3 12l3.5-3.5M3 12l3.5 3.5M21 12l-3.5-3.5M21 12l-3.5 3.5\'/></svg>',
                play: '<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.7\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M7 5.5v13l11-6.5-11-6.5Z\'/></svg>',
                light: '<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.7\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 3v2M5.6 5.6 7 7M3 12h2M19 12h2M17 7l1.4-1.4M9 18h6M10 21h4M8 12a4 4 0 1 1 8 0c0 1.8-1.1 2.8-2 4h-4c-.9-1.2-2-2.2-2-4Z\'/></svg>',
                bag: '<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.7\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M8 8V6a4 4 0 0 1 8 0v2\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M5 8h14l-1 13H6L5 8Z\'/></svg>',
                attack: '<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.7\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'m4 20 5-5M14 4l6 6-9 9-6-6 9-9Z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'m13 5 6 6\'/></svg>',
                warning: '<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.7\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 4 2.8 20h18.4L12 4Z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 9v5M12 17h.01\'/></svg>',
                info: '<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.7\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 11v5M12 8h.01\'/></svg>',
                loop: '<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.7\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M17 2l4 4-4 4\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M3 11V9a3 3 0 0 1 3-3h15\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M7 22l-4-4 4-4\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M21 13v2a3 3 0 0 1-3 3H3\'/></svg>',
                clock: '<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.7\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 6v6l3.5 2.1\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z\'/></svg>',
                'arrow-r': '<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.7\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M4 12h16M14 6l6 6-6 6\'/></svg>',
                'arrow-l': '<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.7\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M20 12H4M10 6l-6 6 6 6\'/></svg>',
                'arrow-u': '<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.7\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 20V4M6 10l6-6 6 6\'/></svg>',
                'arrow-d': '<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.7\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 4v16M6 14l6 6 6-6\'/></svg>'
            };

            return icons[name] ?? icons.info;
        },
        tutorialFocusLabel(focus) {
            const labels = {
                board: 'Tabuleiro',
                objective: 'Objetivo',
                editor: 'Editor',
                commands: 'Comandos',
                run: 'Executar',
                feedback: 'Feedback'
            };

            return labels[focus] ?? 'Tutorial';
        },
        tutorialFocusIs(focus) {
            return this.showTutorial && this.tutorial?.steps?.[this.tutorialStep]?.focus === focus;
        },
        init() {
            this.maybeOpenTutorial();

            window.addEventListener('codesurvivor-tutorial-changed', (event) => {
                this.tutorial = event.detail.tutorial ?? this.tutorial;
                this.tutorialStep = 0;
                this.maybeOpenTutorial();
            });

            window.addEventListener('codesurvivor-context-hint-added', () => {
                this.revealContextHint();
            });

            // Mantem o estado do botao Executar sincronizado quando o
            // editor e alterado pelo servidor (reset de fase, proxima fase,
            // carregar fase) -- casos que nao disparam o evento @input.
            this.$nextTick(() => this.refreshCommandState());
            $wire.$watch('commands', (value) => {
                this.hasCommands = (value ?? '').trim().length > 0;
            });

            // Espelha o modo foco como classe no <html> (fora do componente
            // Livewire), assim a classe nunca e removida pelo morph do Livewire
            // que roda a cada tecla digitada (wire:model.live).
            this.$watch('focusMode', (value) => {
                document.documentElement.classList.toggle('is-focus-mode', value);
            });
        },
        refreshCommandState() {
            const editor = this.$refs.commandEditor;
            this.hasCommands = !!editor && editor.value.trim().length > 0;
        },
        revealContextHint() {
            window.clearTimeout(this.hintSpotlightTimer);
            this.hintSpotlight = false;

            this.$nextTick(() => {
                window.requestAnimationFrame(() => {
                    const feedbackPanel = this.$refs.learningFeedback
                        ?? document.querySelector('[data-context-hint-panel]');

                    if (!feedbackPanel) return;

                    feedbackPanel.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                        inline: 'nearest'
                    });

                    this.hintSpotlight = true;
                    this.hintSpotlightTimer = window.setTimeout(() => {
                        this.hintSpotlight = false;
                    }, 2800);
                });
            });
        },
        tutorialKey() {
            return `codesurvivor:tutorial:v1:level:${this.tutorial?.level ?? {{ (int) ($gameState['level'] ?? 1) }}}`;
        },
        hasTutorial() {
            return Array.isArray(this.tutorial?.steps) && this.tutorial.steps.length > 0;
        },
        maybeOpenTutorial() {
            if (!this.hasTutorial()) return;
            if (window.localStorage.getItem(this.tutorialKey()) === 'dismissed') return;
            this.showTutorial = true;
        },
        openTutorial() {
            if (!this.hasTutorial()) return;
            this.tutorialStep = 0;
            this.showTutorial = true;
        },
        async toggleFocusMode() {
            if (this.focusMode) {
                await this.closeFocusMode();
                return;
            }

            this.showInventory = false;
            this.showLore = false;
            this.showTutorial = false;
            this.focusMode = true;

            try {
                if (document.fullscreenEnabled && !document.fullscreenElement) {
                    await this.$root.requestFullscreen();
                }
            } catch (error) {
                // O modo foco continua funcionando mesmo quando o navegador bloqueia tela cheia.
            }
        },
        async closeFocusMode() {
            this.focusMode = false;

            try {
                if (document.fullscreenElement) {
                    await document.exitFullscreen();
                }
            } catch (error) {
                // Sem acao: sair do modo visual ja devolve a tela normal.
            }
        },
        closeTutorial(persist = false) {
            this.showTutorial = false;
            if (persist) window.localStorage.setItem(this.tutorialKey(), 'dismissed');
        },
        nextTutorialStep() {
            if (this.tutorialStep < this.tutorial.steps.length - 1) {
                this.tutorialStep++;
                return;
            }

            this.closeTutorial(true);
        },
        previousTutorialStep() {
            if (this.tutorialStep > 0) this.tutorialStep--;
        },
        onDragStart(cmd) { this.dragging = cmd; },
        onDrop() {
            if (this.dragging) {
                this.insertCommand(this.dragging);
                this.dragging = null;
            }
        },
        normalizeSnippet(snippet) {
            return snippet.replace(/hero\.move(Right|Left|Up|Down)\(\s*\d+\s*\)/g, 'hero.move$1()');
        },
        insertCommand(snippet) {
            const editor = this.$refs.commandEditor;
            const normalized = this.normalizeSnippet(snippet);

            if (!editor) {
                $wire.insertCommand(normalized);
                return;
            }

            const currentValue = editor.value.trimEnd();
            const separator = currentValue.length > 0 ? '\n' : '';
            editor.value = currentValue + separator + normalized;
            editor.dispatchEvent(new Event('input', { bubbles: true }));

            requestAnimationFrame(() => {
                editor.focus({ preventScroll: true });
                editor.setSelectionRange(editor.value.length, editor.value.length);
            });
        },
        async runEditorCommands() {
            const editor = this.$refs.commandEditor;

            // Protecao extra: nada a executar sem comandos no terminal.
            if (editor && editor.value.trim().length === 0) {
                this.hasCommands = false;
                return;
            }

            if (editor) {
                await $wire.set('commands', editor.value);
            }

            await $wire.runCommands();
        },
        undoLastCommand() {
            const editor = this.$refs.commandEditor;
            if (!editor) return;

            editor.value = this.removeLastCommand(editor.value);
            editor.dispatchEvent(new Event('input', { bubbles: true }));

            requestAnimationFrame(() => {
                editor.focus({ preventScroll: true });
                editor.setSelectionRange(editor.value.length, editor.value.length);
            });
        },
        removeLastCommand(value) {
            const lines = value.replace(/\s+$/g, '').split('\n');
            while (lines.length && lines[lines.length - 1].trim() === '') lines.pop();
            if (!lines.length) return '';

            if (lines[lines.length - 1].trim() === '}') {
                let depth = 0;
                for (let i = lines.length - 1; i >= 0; i--) {
                    const line = lines[i].trim();
                    if (line === '}') depth++;
                    if (/^repeat\s*\(\s*\d+\s*\)\s*\{\s*$/.test(line)) {
                        depth--;
                        if (depth === 0) {
                            lines.splice(i);
                            return lines.join('\n');
                        }
                    }
                }
            }

            lines.pop();
            return lines.join('\n');
        },
        getEditorContext(editor) {
            const cursor = editor.selectionStart ?? 0;
            const value = editor.value;
            const lineStart = value.lastIndexOf('\n', cursor - 1) + 1;
            const beforeCursor = value.slice(lineStart, cursor);
            const indentation = beforeCursor.match(/^\s*/)?.[0] ?? '';
            const query = beforeCursor.slice(indentation.length);
            const lineIndex = value.slice(0, lineStart).split('\n').length - 1;

            return { cursor, value, lineStart, beforeCursor, indentation, query, lineIndex };
        },
        updateAutocomplete(editor) {
            const context = this.getEditorContext(editor);
            const query = context.query.trim();

            if (!query || query.startsWith('//')) {
                this.autocompleteOpen = false;
                this.autocompleteMatches = [];
                return;
            }

            const normalizedQuery = query.toLowerCase();
            const exactCommand = this.commandSuggestions.some((suggestion) => suggestion.insert.toLowerCase() === normalizedQuery);
            if (exactCommand) {
                this.autocompleteOpen = false;
                this.autocompleteMatches = [];
                return;
            }

            const matches = this.commandSuggestions
                .filter((suggestion) => {
                    const insert = suggestion.insert.toLowerCase();
                    const label = suggestion.label.toLowerCase();
                    const hint = suggestion.hint.toLowerCase();
                    return insert.startsWith(normalizedQuery)
                        || label.includes(normalizedQuery)
                        || hint.includes(normalizedQuery);
                })
                .slice(0, 6);

            // Preserva o item destacado se ele continuar na lista apos refiltrar,
            // assim digitar mais uma letra nao reseta a selecao para o topo.
            const previousInsert = this.autocompleteMatches[this.autocompleteSelected]?.insert;
            const keptIndex = matches.findIndex((suggestion) => suggestion.insert === previousInsert);

            this.autocompleteMatches = matches;
            this.autocompleteSelected = keptIndex >= 0 ? keptIndex : 0;
            this.autocompleteOpen = matches.length > 0;
            this.positionAutocomplete(editor, context);
        },
        positionAutocomplete(editor, context = null) {
            const current = context ?? this.getEditorContext(editor);
            const visibleTop = 20 + (current.lineIndex * 32) - editor.scrollTop + 30;
            const maxTop = Math.max(52, editor.clientHeight - 210);
            this.autocompleteTop = Math.min(Math.max(52, visibleTop), maxTop);
            this.autocompleteLeft = Math.min(20 + (current.beforeCursor.length * 8.8), Math.max(20, editor.clientWidth - 340));
        },
        acceptAutocomplete(editor, suggestion = null) {
            const selected = suggestion ?? this.autocompleteMatches[this.autocompleteSelected];
            if (!selected) return;

            const context = this.getEditorContext(editor);
            const replaceStart = context.lineStart + context.indentation.length;
            const nextValue = context.value.slice(0, replaceStart)
                + selected.insert
                + context.value.slice(context.cursor);
            const nextCursor = replaceStart + selected.insert.length;

            editor.value = nextValue;
            editor.dispatchEvent(new Event('input', { bubbles: true }));
            this.autocompleteOpen = false;

            requestAnimationFrame(() => {
                editor.focus();
                editor.setSelectionRange(nextCursor, nextCursor);
            });
        }
    }"
    @fullscreenchange.window="if (focusMode && !document.fullscreenElement) focusMode = false"
    @keydown.window.escape="focusMode ? closeFocusMode() : (showInventory = false, showLore = false, showTutorial = false)"
>
    {{-- CSS Direto para Garantir a Identidade Visual Tática --}}
    <style>
        /* ===== FUNDO E ATMOSFERA ===== */
        body, html { background: #0f172a; }
        [x-cloak] { display: none !important; }

        /* ===== HUD TÁTICO ===== */
        .tactical-hud-container {
            background: linear-gradient(rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.85)), url('/interface/panel_tactical.png');
            background-size: cover;
            border: 3px solid #334155;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 
                inset 0 0 40px rgba(0, 0, 0, 0.5),
                0 15px 40px rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(4px);
        }

        .focus-mode-toggle {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 42;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border: 1px solid rgba(148, 163, 184, 0.48);
            border-radius: 6px;
            background: rgba(2, 6, 23, 0.82);
            color: #cbd5e1;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.38), inset 0 0 12px rgba(148, 163, 184, 0.06);
            backdrop-filter: blur(10px);
            transition: border-color 0.18s ease, color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }

        .focus-mode-toggle:hover {
            border-color: rgba(52, 211, 153, 0.88);
            color: #d1fae5;
            box-shadow: 0 0 18px rgba(16, 185, 129, 0.20), inset 0 0 16px rgba(16, 185, 129, 0.08);
            transform: translateY(-1px);
        }

        .focus-mode-toggle svg {
            width: 20px;
            height: 20px;
        }

        .focus-mode-exit {
            position: fixed;
            top: 14px;
            right: 14px;
            z-index: 58;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(148, 163, 184, 0.45);
            border-radius: 6px;
            background: rgba(2, 6, 23, 0.88);
            padding: 10px 12px;
            color: #cbd5e1;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(10px);
        }

        .focus-mode-exit:hover {
            border-color: rgba(248, 113, 113, 0.75);
            color: #fecaca;
        }

        .tactical-badge {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 2px solid #334155;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 1px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        .tactical-tooltip {
            visibility: hidden;
            position: absolute;
            top: calc(100% + 12px);
            left: 50%;
            transform: translateX(-50%);
            background:
                linear-gradient(135deg, rgba(15, 23, 42, 0.98), rgba(2, 6, 23, 0.98));
            border: 2px solid rgba(16, 185, 129, 0.72);
            color: #f1f5f9;
            padding: 12px 14px;
            border-radius: 6px;
            width: 330px;
            max-width: calc(100dvw - 32px);
            font-size: 13px;
            font-weight: 800;
            text-transform: none;
            letter-spacing: 0;
            line-height: 1.55;
            z-index: 80;
            box-shadow: 0 18px 42px rgba(0, 0, 0, 0.72), 0 0 22px rgba(16, 185, 129, 0.12);
            opacity: 0;
            transition: opacity 0.2s ease, visibility 0.2s ease;
            pointer-events: none;
        }

        .tactical-tooltip-title {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 7px;
            color: #34d399;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .tactical-badge:hover .tactical-tooltip {
            visibility: visible;
            opacity: 1;
        }

        /* O tooltip do "Objetivo" precisa aparecer acima do conteudo do jogo
           (ex.: terminal), mas fica preso no stacking context criado pelo
           backdrop-filter do .tactical-hud-container. Elevamos o painel APENAS
           enquanto o badge do objetivo (o unico com tooltip) esta sob o mouse
           -- nunca via :focus-within nem no header inteiro. Como as modais
           cobrem a tela toda (fixed inset-0), e impossivel ter o mouse no badge
           com uma modal aberta, entao elas continuam sempre acima do header. */
        .tactical-hud-container:has(.tactical-badge:hover .tactical-tooltip) {
            position: relative;
            z-index: 110;
        }

        .tactical-tooltip::before {
            content: "";
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 7px;
            border-style: solid;
            border-color: transparent transparent rgba(16, 185, 129, 0.72) transparent;
        }

        .health-bar-container {
            background: #0f172a;
            border: 2px solid #1e293b;
            border-radius: 4px;
            padding: 8px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.8);
        }

        .health-status-card {
            width: min(100%, 286px);
            flex: 0 1 286px;
        }

        .health-vital-row {
            display: grid;
            grid-template-columns: 46px minmax(0, 1fr) auto;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .health-vital-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.5);
            border-radius: 6px;
            box-shadow: inset 0 0 14px rgba(0, 0, 0, 0.45);
        }

        .health-vital-image {
            display: block;
            width: 36px;
            height: 36px;
            object-fit: contain;
            filter: drop-shadow(0 0 8px rgba(16, 185, 129, 0.35));
        }

        .health-vital-label {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .health-count {
            min-width: 2.5rem;
        }

        /* ===== ANIMACAO DE MOVIMENTO DO PERSONAGEM ===== */
        .player-animated {
            --player-walk-duration: 360ms;
            --player-frame-duration: 180ms;
            will-change: transform;
        }

        .player-board-token {
            position: absolute;
            left: 0;
            top: 0;
            width: var(--tile-size);
            height: var(--tile-size);
            z-index: 35;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            transform: translate3d(
                calc(var(--player-x) * var(--tile-size)),
                calc(var(--player-y) * var(--tile-size)),
                0
            );
            transition: transform var(--player-walk-duration) cubic-bezier(.22, .61, .36, 1);
        }

        .player-board-token.player-idle {
            transition-duration: 0ms;
        }

        @keyframes walk-bounce {
            0%, 100% { transform: translateY(0) rotate(0deg) scaleY(1); }
            25% { transform: translateY(-2px) rotate(-1.5deg) scaleY(0.99); }
            50% { transform: translateY(0) rotate(0deg) scaleY(1); }
            75% { transform: translateY(-2px) rotate(1.5deg) scaleY(0.99); }
        }

        .player-sprite-stack {
            position: relative;
            width: clamp(32px, calc(var(--tile-size) * 0.86), 56px);
            height: clamp(32px, calc(var(--tile-size) * 0.86), 56px);
            transform-origin: 50% 92%;
            will-change: transform;
        }

        .player-token {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .player-walking .player-sprite-stack {
            animation: walk-bounce var(--player-walk-duration) ease-in-out;
        }

        .player-walk-frames {
            position: absolute;
            inset: 0;
            animation: walk-window var(--player-walk-duration) steps(1, end) forwards;
        }

        .player-walk-frame-one {
            animation: walk-frame-one var(--player-frame-duration) steps(1, end) infinite;
        }

        .player-walk-frame-two {
            animation: walk-frame-two var(--player-frame-duration) steps(1, end) infinite;
        }

        @keyframes walk-window {
            0%, 99% { opacity: 1; }
            100% { opacity: 0; }
        }

        @keyframes walk-frame-one {
            0%, 49.99% { opacity: 1; }
            50%, 100% { opacity: 0; }
        }

        @keyframes walk-frame-two {
            0%, 49.99% { opacity: 0; }
            50%, 100% { opacity: 1; }
        }

        /* ===== GRID DE JOGO (MONITOR DE VIGILÂNCIA) ===== */
        .surveillance-monitor {
            background: #0f172a;
            border: 8px solid #1e293b;
            border-radius: 12px;
            padding: 12px;
            box-shadow: 
                inset 0 0 40px rgba(0, 0, 0, 1),
                0 20px 50px rgba(0, 0, 0, 0.8);
            position: relative;
        }

        .surveillance-monitor::before {
            content: "";
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(
                0deg,
                rgba(0, 255, 0, 0.03) 0px,
                rgba(0, 255, 0, 0.03) 1px,
                transparent 1px,
                transparent 2px
            );
            pointer-events: none;
            border-radius: 8px;
        }

        .game-board-column {
            min-width: 0;
        }

        .game-grid {
            --board-size: clamp(360px, min(58vw, 66vh), 560px);
            --tile-size: calc(var(--board-size) / var(--grid-size));
            --path-opacity: {{ ($gameState['level'] == 2) ? '0.95' : '0.06' }};
            --grid-opacity: {{ ($gameState['level'] == 2) ? '0.20' : '0.04' }};
            width: var(--board-size);
            height: var(--board-size);
            background: #080a0f;
            background-position: center;
            background-repeat: no-repeat;
            background-size: 100% 100%;
            border: 3px solid #0f172a;
            border-radius: 8px;
            box-shadow: inset 0 0 30px rgba(0, 0, 0, 1);
            overflow: hidden;
            transition: transform 0.1s ease-out;
        }

        .is-focus-mode .focus-host {
            position: fixed;
            inset: 0;
            z-index: 55;
            overflow: hidden;
            padding: 12px !important;
            background:
                radial-gradient(circle at 35% 35%, rgba(20, 184, 166, 0.10), transparent 34%),
                linear-gradient(135deg, #020617 0%, #0f172a 56%, #020617 100%);
        }

        .is-focus-mode .game-hud-section,
        .is-focus-mode .briefing-card,
        .is-focus-mode .board-telemetry,
        .is-focus-mode .shell-telemetry,
        .is-focus-mode .tactical-manual,
        .is-focus-mode .learning-feedback,
        .is-focus-mode .level-actions,
        .is-focus-mode .mobile-command-dock {
            display: none !important;
        }

        .is-focus-mode .game-shell {
            display: grid !important;
            grid-template-columns: minmax(0, 3fr) minmax(260px, 1fr);
            gap: 12px !important;
            align-items: stretch;
            justify-content: stretch;
            width: 100%;
            height: calc(100dvh - 24px);
            max-width: none !important;
            margin: 0 !important;
        }

        .is-focus-mode .game-board-column,
        .is-focus-mode .game-control-area {
            min-height: 0;
            width: 100%;
        }

        .is-focus-mode .game-board-column {
            align-items: center;
            justify-content: center;
        }

        .is-focus-mode .surveillance-monitor {
            width: fit-content;
            max-width: 100%;
            padding: 10px;
        }

        .is-focus-mode .game-grid {
            --board-size: min(calc(75vw - 42px), calc(100dvh - 60px));
        }

        .is-focus-mode .industrial-terminal-container {
            height: 100%;
            min-height: 0;
            padding: 14px;
            border-width: 2px;
            display: flex;
        }

        .is-focus-mode .industrial-terminal-header {
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .is-focus-mode .industrial-terminal-header h3 {
            font-size: 11px;
            letter-spacing: 0.08em;
        }

        .is-focus-mode .industrial-terminal-header p {
            display: none;
        }

        .is-focus-mode .industrial-terminal-screen {
            min-height: 0;
            flex: 1 1 auto;
        }

        .is-focus-mode .industrial-terminal-numbers {
            min-width: 38px;
            padding: 12px 8px;
            font-size: 10px;
        }

        .is-focus-mode .industrial-terminal-textarea {
            padding: 12px !important;
            font-size: 13px !important;
            line-height: 28px !important;
        }

        .is-focus-mode .game-action-row {
            gap: 8px;
            margin-top: 10px !important;
        }

        .is-focus-mode .game-action-row > button {
            min-height: 42px;
            padding: 9px 10px !important;
            font-size: 10px;
            letter-spacing: 0.02em;
        }

        .is-focus-mode .desktop-undo-button {
            display: flex !important;
        }

        .is-focus-mode .focus-command-strip {
            display: block;
            flex: 0 0 auto;
            margin-top: 10px;
            padding: 10px;
            border: 2px solid rgba(51, 65, 85, 0.9);
            border-radius: 8px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(2, 6, 23, 0.98));
            box-shadow: inset 0 0 18px rgba(0, 0, 0, 0.42), 0 8px 18px rgba(0, 0, 0, 0.38);
        }

        .is-focus-mode .focus-command-strip-title {
            margin-bottom: 8px;
            color: #94a3b8;
            font-size: 9px;
            font-weight: 900;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .is-focus-mode .focus-command-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .is-focus-mode .focus-command-button {
            display: flex;
            min-width: 0;
            min-height: 42px;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border: 2px solid #334155;
            border-radius: 6px;
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.96), rgba(15, 23, 42, 0.98));
            color: #cbd5e1;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06), 0 4px 10px rgba(0, 0, 0, 0.34);
            transition: border-color 0.15s ease, color 0.15s ease, transform 0.15s ease;
            touch-action: manipulation;
        }

        .is-focus-mode .focus-command-button:hover,
        .is-focus-mode .focus-command-button:focus-visible {
            border-color: #10b981;
            color: #6ee7b7;
        }

        .is-focus-mode .focus-command-button:active {
            transform: scale(0.98);
        }

        .is-focus-mode .focus-command-button svg {
            flex: 0 0 auto;
            width: 18px;
            height: 18px;
        }

        .is-focus-mode .focus-command-label {
            min-width: 0;
            overflow: hidden;
            font-size: 10px;
            font-weight: 900;
            line-height: 1.1;
            text-overflow: ellipsis;
            text-transform: uppercase;
            white-space: nowrap;
        }

        @media (max-width: 900px) {
            .is-focus-mode .focus-host {
                overflow-y: auto;
            }

            .is-focus-mode .game-shell {
                grid-template-columns: 1fr;
                grid-template-rows: auto auto;
                height: auto;
                min-height: calc(100dvh - 24px);
                justify-items: center;
                padding-top: 48px;
                box-sizing: border-box;
            }

            .is-focus-mode .game-board-column,
            .is-focus-mode .game-control-area {
                width: 100%;
                max-width: min(100%, 520px);
            }

            .is-focus-mode .game-board-column {
                align-items: center;
                overflow: visible;
            }

            .is-focus-mode .surveillance-monitor {
                display: block;
                width: min(calc(100vw - 32px), 520px);
                max-width: 100%;
                margin-inline: auto;
                padding: 6px;
                overflow: visible !important;
            }

            .is-focus-mode .game-grid {
                --board-size: min(calc(100vw - 52px), 44dvh, 380px);
                width: var(--board-size);
                min-width: var(--board-size);
                height: var(--board-size);
                min-height: var(--board-size);
                aspect-ratio: 1 / 1;
                flex: 0 0 auto;
                margin-inline: auto;
            }

            .surveillance-monitor {
                overflow: visible !important;
            }

            .is-focus-mode .focus-mode-toggle {
                display: none;
            }

            .focus-mode-toggle {
                top: -10px;
                right: -10px;
                width: 46px;
                height: 46px;
                z-index: 75;
                border-color: rgba(52, 211, 153, 0.75);
                background: rgba(2, 6, 23, 0.94);
                color: #d1fae5;
                box-shadow:
                    0 0 0 2px rgba(2, 6, 23, 0.84),
                    0 12px 26px rgba(0, 0, 0, 0.5),
                    0 0 18px rgba(16, 185, 129, 0.22);
                touch-action: manipulation;
            }

            .focus-mode-toggle svg {
                width: 22px;
                height: 22px;
            }

            .is-focus-mode .industrial-terminal-container {
                min-height: 340px;
            }

            .is-focus-mode .focus-command-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .is-focus-mode .focus-command-button {
                justify-content: center;
                padding: 8px;
            }

            .is-focus-mode .focus-command-label {
                display: none;
            }
        }

        @media (max-width: 420px) {
            .focus-mode-toggle {
                top: -8px;
                right: -8px;
                width: 44px;
                height: 44px;
            }
        }

        .screen-shake {
            animation: shake 0.3s cubic-bezier(.36,.07,.19,.97) both;
            transform: translate3d(0, 0, 0);
            backface-visibility: hidden;
            perspective: 1000px;
        }

        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }

        .zombie-die {
            animation: zombie-explode 0.6s forwards;
            filter: brightness(2) saturate(2);
            pointer-events: none;
            z-index: 50 !important;
        }

        @keyframes zombie-explode {
            0% { transform: scale(1) rotate(0deg); opacity: 1; filter: brightness(1) contrast(1.5); }
            30% { transform: scale(1.3) rotate(10deg); opacity: 0.9; filter: brightness(3) sepia(1) hue-rotate(-50deg); }
            100% { transform: scale(2); opacity: 0; filter: brightness(5); }
        }

        .zealot-sprite-stack {
            position: relative;
            width: 3.75rem;
            height: 3.75rem;
        }

        .zealot-sprite-stack .zombie-token {
            position: absolute;
            inset: 0;
            width: 100% !important;
            height: 100% !important;
            object-fit: contain;
        }

        .zealot-walk-frame {
            animation: zealot-walk-frame 0.36s steps(1, end) infinite;
        }

        @keyframes zealot-walk-frame {
            0%, 49.99% { opacity: 0; }
            50%, 100% { opacity: 1; }
        }

        .ganado-sprite-stack {
            position: relative;
            width: clamp(34px, calc(var(--tile-size) * 0.78), 54px);
            height: clamp(52px, calc(var(--tile-size) * 1.16), 76px);
            transform: translateY(-18%);
            transform-origin: 50% 94%;
        }

        .ganado-sprite-stack .zombie-token {
            position: absolute;
            inset: 0;
            width: 100% !important;
            height: 100% !important;
            object-fit: contain;
            object-position: center bottom;
            filter: drop-shadow(0 8px 8px rgba(0, 0, 0, 0.72)) contrast(1.08) saturate(1.04);
        }

        .ganado-sprite-stack.is-alerting {
            animation: ganado-lurch 0.42s ease-in-out;
        }

        @keyframes ganado-lurch {
            0%, 100% { transform: translateY(-18%) rotate(0deg); }
            35% { transform: translateY(-24%) rotate(-2deg); }
            65% { transform: translateY(-20%) rotate(2deg); }
        }

        .garrador-sprite-stack {
            position: relative;
            width: clamp(58px, calc(var(--tile-size) * 1.45), 104px);
            height: clamp(82px, calc(var(--tile-size) * 2.05), 146px);
            transform: translateY(-38%);
            transform-origin: 50% 96%;
            z-index: 24;
        }

        .garrador-sprite-stack.is-edge-right {
            transform: translate(-30%, -38%);
        }

        .zombie-name-badge.is-garrador {
            top: auto;
            bottom: -12px;
            left: auto;
            right: -10px;
            transform: none;
        }

        .zombie-name-badge.is-garrador.is-edge-right {
            right: 8px;
        }

        .garrador-sprite-stack .zombie-token {
            position: absolute;
            inset: 0;
            width: 100% !important;
            height: 100% !important;
            object-fit: contain;
            object-position: center bottom;
            filter: drop-shadow(0 10px 12px rgba(0, 0, 0, 0.8)) contrast(1.12) saturate(1.06);
        }

        .garrador-sprite-stack.is-alerting {
            animation: garrador-prowl 0.5s ease-in-out;
        }

        .garrador-sprite-stack.is-edge-right.is-alerting {
            animation: garrador-edge-prowl 0.5s ease-in-out;
        }

        @keyframes garrador-prowl {
            0%, 100% { transform: translateY(-38%) rotate(0deg); }
            35% { transform: translateY(-44%) rotate(-1.5deg); }
            70% { transform: translateY(-40%) rotate(1.5deg); }
        }

        @keyframes garrador-edge-prowl {
            0%, 100% { transform: translate(-30%, -38%) rotate(0deg); }
            35% { transform: translate(-30%, -44%) rotate(-1.5deg); }
            70% { transform: translate(-30%, -40%) rotate(1.5deg); }
        }

        .eliminated-text {
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            color: #ff4444;
            font-weight: 900;
            font-size: 14px;
            text-shadow: 0 0 8px #000, 0 0 4px #ff0000;
            white-space: nowrap;
            animation: float-up-fade 0.8s ease-out forwards;
            z-index: 100;
            pointer-events: none;
        }

        @keyframes float-up-fade {
            0% { transform: translate(-50%, 0); opacity: 0; }
            20% { transform: translate(-50%, -10px); opacity: 1; }
            100% { transform: translate(-50%, -40px); opacity: 0; }
        }

        .game-grid::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            background:
                radial-gradient(circle at 50% 50%, transparent 0 54%, rgba(0, 0, 0, .12) 82%, rgba(0, 0, 0, .35) 100%),
                linear-gradient(180deg, rgba(12, 18, 24, .04), rgba(2, 6, 10, .13));
        }

        .grid-tile {
            width: var(--tile-size);
            height: var(--tile-size);
            border: 1px solid rgba(215, 235, 240, var(--grid-opacity));
            background: transparent;
            position: relative;
            transition: background 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
            z-index: 2;
            overflow: visible;
        }

        .grid-tile::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 12;
            pointer-events: none;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.28);
        }

        .tile-floor {
            --path-color: 45, 212, 191;
            --path-glow: 45, 212, 191;
            border-color: rgba(var(--path-color), calc(var(--path-opacity) * .32));
            overflow: visible;
        }

        .path-street.tile-floor {
            --path-color: 94, 234, 212;
            --path-glow: 94, 234, 212;
        }

        .path-lab.tile-floor {
            --path-color: 45, 212, 191;
            --path-glow: 45, 212, 191;
            background-color: transparent !important;
            box-shadow: none !important;
        }

        /* ===== CAMINHO JOGÁVEL: LIMPO, VISÍVEL E SEM SOBREPOSIÇÃO =====
           Cada casa jogável usa apenas UM marcador central.
           Os conectores laterais/verticais foram removidos para evitar formas duplicadas. */
        .tile-floor .path-arm-left,
        .tile-floor .path-arm-right,
        .tile-floor .path-ribbon::before,
        .tile-floor .path-ribbon::after {
            display: none !important;
            content: none !important;
        }

        .tile-floor .path-ribbon {
            position: absolute;
            left: 18%;
            right: 18%;
            top: 18%;
            bottom: 18%;
            z-index: 4;
            border-radius: 12px;
            pointer-events: none;
            backdrop-filter: blur(0.4px);
            background:
                radial-gradient(circle at 50% 42%, rgba(255,255,255,0.18), transparent 62%),
                linear-gradient(180deg,
                    rgba(var(--path-color), calc(var(--path-opacity) + 0.08)),
                    rgba(var(--path-color), calc(var(--path-opacity) * 0.70))
                );
            border: 1px solid rgba(153, 246, 228, calc(var(--path-opacity) * 1.35));
            box-shadow:
                inset 0 0 12px rgba(255,255,255,0.07),
                0 0 14px rgba(var(--path-glow), calc(var(--path-opacity) * 0.85));
        }

        .path-street .path-ribbon {
            left: 19%;
            right: 19%;
            top: 19%;
            bottom: 19%;
            border-radius: 11px;
            background:
                radial-gradient(circle at 50% 42%, rgba(255,255,255,0.16), transparent 64%),
                linear-gradient(180deg,
                    rgba(var(--path-color), calc(var(--path-opacity) + 0.06)),
                    rgba(var(--path-color), calc(var(--path-opacity) * 0.62))
                );
        }

        .path-lab .path-ribbon {
            left: 20% !important;
            right: 20% !important;
            top: 20% !important;
            bottom: 20% !important;
            border-radius: 12px !important;
            background:
                radial-gradient(circle at 50% 42%, rgba(255,255,255,0.14), transparent 66%),
                linear-gradient(180deg,
                    rgba(var(--path-color), calc(var(--path-opacity) + 0.04)),
                    rgba(var(--path-color), calc(var(--path-opacity) * 0.58))
                ) !important;
            border: 1px solid rgba(153, 246, 228, calc(var(--path-opacity) * 1.15)) !important;
            box-shadow:
                inset 0 0 10px rgba(255,255,255,0.06),
                0 0 12px rgba(var(--path-glow), calc(var(--path-opacity) * 0.72)) !important;
        }

        .tile-floor.path-near-player .path-ribbon {
            background:
                radial-gradient(circle at 50% 42%, rgba(255,255,255,0.26), transparent 68%),
                linear-gradient(180deg,
                    rgba(var(--path-color), calc(var(--path-opacity) + 0.14)),
                    rgba(var(--path-color), calc(var(--path-opacity) * 0.84))
                ) !important;
            border-color: rgba(204, 251, 241, calc(var(--path-opacity) * 1.65)) !important;
            box-shadow:
                inset 0 0 14px rgba(255,255,255,0.09),
                0 0 20px rgba(var(--path-glow), calc(var(--path-opacity) + 0.10)) !important;
        }

        .tile-floor.path-start .path-ribbon {
            background:
                radial-gradient(circle at 50% 48%, rgba(255,255,255,0.25), transparent 70%),
                linear-gradient(180deg,
                    rgba(var(--path-color), calc(var(--path-opacity) + 0.12)),
                    rgba(var(--path-color), calc(var(--path-opacity) * 0.78))
                ) !important;
        }

        .tile-floor.path-goal .path-ribbon {
            border-radius: 999px !important;
            background:
                radial-gradient(circle at 50% 50%, rgba(236, 253, 245, 0.50), rgba(var(--path-color), calc(var(--path-opacity) + 0.08)) 56%, rgba(var(--path-color), calc(var(--path-opacity) * 0.62)) 78%) !important;
            box-shadow:
                inset 0 0 0 1px rgba(236,253,245, calc(var(--path-opacity) * .55)),
                0 0 26px rgba(45, 212, 191, calc(var(--path-opacity) + 0.08)) !important;
        }

        .tile-floor.path-guide .path-ribbon .path-arrow,
        .tile-floor.path-tutorial .path-ribbon .path-arrow {
            display: none !important;
        }

        .tile-obstacle {
            background: transparent;
            border-color: rgba(8, 13, 18, calc(var(--grid-opacity) * .55));
            box-shadow: none;
        }

        .tile-scenery {
            background: transparent;
            border-color: rgba(8, 13, 18, calc(var(--grid-opacity) * .55));
        }

        .visual-placeholder-warning {
            background: rgba(15, 23, 42, .72);
            border: 1px solid rgba(245, 158, 11, .38);
            color: #fbbf24;
        }

        .objective-marker {
            position: relative;
            width: 64%;
            height: 64%;
            border-radius: 999px;
            background:
                radial-gradient(circle at 50% 50%, rgba(236, 253, 245, .82) 0 8%, rgba(94, 234, 212, .34) 24%, rgba(20, 184, 166, .12) 54%, transparent 72%);
            border: 1px solid rgba(167, 243, 208, .34);
            box-shadow:
                0 0 22px rgba(45, 212, 191, .28),
                0 0 42px rgba(20, 184, 166, .14),
                inset 0 0 18px rgba(255, 255, 255, .08);
        }

        .objective-marker::before {
            content: "";
            position: absolute;
            left: 32%;
            right: 32%;
            top: 26%;
            bottom: 26%;
            border-radius: 999px;
            border: 1px solid rgba(236, 253, 245, .58);
            box-shadow:
                0 0 12px rgba(236, 253, 245, .30),
                inset 0 0 10px rgba(236, 253, 245, .16);
        }

        .objective-marker::after {
            content: "";
            position: absolute;
            left: 38%;
            right: 38%;
            top: 46%;
            height: 8%;
            border-radius: 999px;
            background: rgba(236, 253, 245, .72);
            box-shadow: 0 -7px 0 rgba(236, 253, 245, .32), 0 7px 0 rgba(236, 253, 245, .24);
        }

        .collectible-token {
            width: 68%;
            height: 68%;
            object-fit: contain;
            filter: drop-shadow(0 0 10px rgba(245, 158, 11, .65));
            animation: collectible-pulse 1.8s ease-in-out infinite;
        }

        @keyframes collectible-pulse {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-3px) scale(1.06); }
        }

        .inventory-overlay {
            background:
                radial-gradient(circle at 50% 30%, rgba(15, 23, 42, .72), rgba(0, 0, 0, .88) 70%),
                rgba(0, 0, 0, .72);
        }

        .inventory-panel {
            background:
                linear-gradient(rgba(10, 14, 20, .92), rgba(10, 14, 20, .92)),
                url('/interface/inventory_panel.svg');
            background-size: cover;
            border: 2px solid #475569;
            border-radius: 8px;
            box-shadow: inset 0 0 32px rgba(0, 0, 0, .78), 0 24px 70px rgba(0, 0, 0, .85);
        }

        .inventory-slot {
            aspect-ratio: 1 / 1;
            min-height: 76px;
            background:
                linear-gradient(145deg, rgba(15, 23, 42, .88), rgba(2, 6, 23, .95));
            border: 1px solid rgba(100, 116, 139, .72);
            border-radius: 4px;
            box-shadow: inset 0 0 14px rgba(0, 0, 0, .7);
            transition: border-color .18s ease, box-shadow .18s ease, opacity .18s ease, transform .18s ease;
        }

        .inventory-slot-filled {
            border-color: rgba(16, 185, 129, .72);
            box-shadow: inset 0 0 14px rgba(0, 0, 0, .7), 0 0 18px rgba(16, 185, 129, .12);
        }

        .inventory-slot-filled {
            cursor: grab;
        }

        .inventory-slot-filled:active {
            cursor: grabbing;
        }

        .inventory-slot.is-dragging {
            opacity: .46;
            transform: scale(.96);
            border-color: rgba(148, 163, 184, .5);
        }

        .inventory-slot.is-drop-target {
            border-color: rgba(96, 165, 250, .95);
            box-shadow:
                inset 0 0 18px rgba(59, 130, 246, .16),
                0 0 0 3px rgba(59, 130, 246, .18),
                0 0 22px rgba(59, 130, 246, .22);
        }

        .lore-toggle-button {
            background:
                linear-gradient(135deg, rgba(30, 41, 59, .92), rgba(2, 6, 23, .96)),
                url('/interface/panel_tactical.png');
            background-size: cover;
            border-color: rgba(245, 158, 11, .46);
            box-shadow: inset 0 0 18px rgba(0, 0, 0, .72), 0 8px 22px rgba(0, 0, 0, .42);
        }

        .lore-toggle-button:hover {
            border-color: rgba(245, 158, 11, .82);
            box-shadow: inset 0 0 18px rgba(0, 0, 0, .72), 0 0 18px rgba(245, 158, 11, .14);
        }

        .lore-overlay {
            background:
                radial-gradient(circle at 50% 24%, rgba(51, 65, 85, .44), rgba(0, 0, 0, .9) 72%),
                rgba(0, 0, 0, .74);
        }

        .lore-panel {
            max-height: min(760px, calc(100dvh - 32px));
            background:
                linear-gradient(135deg, rgba(15, 23, 42, .96), rgba(2, 6, 23, .98)),
                url('/interface/panel_tactical.png');
            background-size: cover;
            border: 2px solid rgba(245, 158, 11, .44);
            border-radius: 8px;
            box-shadow:
                inset 0 0 42px rgba(0, 0, 0, .82),
                0 26px 82px rgba(0, 0, 0, .9),
                0 0 28px rgba(245, 158, 11, .08);
        }

        .lore-tab {
            min-height: 42px;
            border: 1px solid rgba(71, 85, 105, .82);
            border-radius: 6px;
            background: rgba(15, 23, 42, .78);
            color: #94a3b8;
            transition: border-color .16s ease, color .16s ease, background .16s ease;
        }

        .lore-tab.is-active,
        .lore-tab:hover {
            border-color: rgba(245, 158, 11, .72);
            background: rgba(120, 53, 15, .24);
            color: #fde68a;
        }

        .lore-mode-tabs {
            display: inline-grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: min(100%, 360px);
            padding: 4px;
            background: rgba(2, 6, 23, .72);
            border: 1px solid rgba(71, 85, 105, .72);
            border-radius: 6px;
            gap: 4px;
        }

        .lore-mode-tab {
            min-height: 36px;
            border-radius: 4px;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
            transition: background .16s ease, color .16s ease, box-shadow .16s ease;
        }

        .lore-mode-tab.is-active,
        .lore-mode-tab:hover {
            background: rgba(245, 158, 11, .18);
            color: #fde68a;
            box-shadow: inset 0 0 0 1px rgba(245, 158, 11, .35);
        }

        .lore-content {
            max-height: min(430px, calc(100dvh - 310px));
            overflow-y: auto;
            background: rgba(2, 6, 23, .56);
            border: 1px solid rgba(71, 85, 105, .72);
            border-radius: 6px;
        }

        .lore-paragraph {
            color: #dbe4ee;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.78;
        }

        .lore-gallery {
            max-height: min(430px, calc(100dvh - 310px));
            overflow-y: auto;
        }

        .lore-image-card {
            min-width: 0;
            background:
                radial-gradient(circle at 50% 22%, rgba(245, 158, 11, .08), transparent 42%),
                linear-gradient(135deg, rgba(15, 23, 42, .88), rgba(2, 6, 23, .96));
            border: 1px solid rgba(71, 85, 105, .76);
            border-radius: 6px;
            overflow: hidden;
            box-shadow: inset 0 0 18px rgba(0, 0, 0, .56);
        }

        .lore-image-stage {
            aspect-ratio: 1 / 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background:
                linear-gradient(90deg, rgba(148, 163, 184, .045) 1px, transparent 1px),
                linear-gradient(0deg, rgba(148, 163, 184, .045) 1px, transparent 1px),
                rgba(0, 0, 0, .22);
            background-size: 18px 18px;
        }

        .lore-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 12px 18px rgba(0, 0, 0, .76));
        }

        .lore-image-caption {
            border-top: 1px solid rgba(71, 85, 105, .62);
            padding: 9px 10px;
            color: #cbd5e1;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .1em;
            text-align: center;
            text-transform: uppercase;
        }

        .sprite-grounded::after {
            content: "";
            position: absolute;
            left: 24%;
            right: 24%;
            bottom: 13%;
            height: 14%;
            border-radius: 50%;
            background: rgba(0, 0, 0, .46);
            filter: blur(5px);
            z-index: -1;
        }

        .tile-vision {
            background: rgba(239, 68, 68, 0.08);
            border: 2px solid rgba(239, 68, 68, 0.3);
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.2);
            z-index: 25;
            animation: vision-pulse 2s infinite ease-in-out;
        }
        @keyframes vision-pulse {
            0%, 100% { opacity: 0.4; transform: scale(0.95); }
            50% { opacity: 0.8; transform: scale(1.02); }
        }

        .grid-tile:hover {
            box-shadow: inset 0 0 12px rgba(34, 197, 94, 0.22), 0 0 8px rgba(34, 197, 94, 0.14);
            background: rgba(16, 185, 129, 0.035);
        }

        /* ===== TERMINAL DE COMUNICAÇÃO (LOGS) ===== */
        .radio-terminal {
            width: 100%;
            overflow: hidden;
            background:
                linear-gradient(135deg, rgba(15, 23, 42, 0.92), rgba(2, 6, 23, 0.96)),
                url('/interface/panel_tactical.png');
            background-size: cover;
            border: 2px solid #334155;
            border-radius: 8px;
            box-shadow:
                inset 0 0 30px rgba(0, 0, 0, 0.55),
                0 16px 34px rgba(0, 0, 0, 0.68);
        }

        .radio-terminal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            background: rgba(15, 23, 42, 0.72);
            border-bottom: 1px solid rgba(51, 65, 85, 0.95);
        }

        .radio-terminal-header > h3 {
            display: none;
        }

        .radio-terminal-title {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .radio-terminal-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            flex: 0 0 auto;
            color: #6ee7b7;
            background: rgba(16, 185, 129, 0.10);
            border: 1px solid rgba(16, 185, 129, 0.35);
            border-radius: 6px;
        }

        .radio-terminal-kicker {
            color: #64748b;
            font-size: 9px;
            font-weight: 900;
            letter-spacing: 0.18em;
            line-height: 1;
            text-transform: uppercase;
        }

        .radio-terminal-heading {
            margin-top: 3px;
            color: #e2e8f0;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.12em;
            line-height: 1;
            text-transform: uppercase;
        }

        .radio-terminal-status {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            flex: 0 0 auto;
            color: #6ee7b7;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .radio-terminal-status::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #10b981;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.75);
        }

        .radio-log-list {
            display: grid;
            gap: 8px;
            max-height: 150px;
            overflow-y: auto;
            padding: 10px;
        }

        .radio-log-entry {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 10px;
            align-items: start;
            color: #cbd5e1;
            padding: 9px 10px;
            background: rgba(15, 23, 42, 0.72);
            border: 1px solid rgba(71, 85, 105, 0.58);
            border-left: 3px solid rgba(16, 185, 129, 0.78);
            border-radius: 6px;
            font-size: 12px;
            line-height: 1.35;
        }

        .radio-log-index {
            color: #6ee7b7;
            font-family: 'Fira Code', monospace;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        .radio-log-text {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .radio-log-empty {
            color: #94a3b8;
            padding: 18px 12px;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
        }

        .shell-telemetry {
            display: none;
        }

        /* ===== TERMINAL INDUSTRIAL (EDITOR) ===== */
        .industrial-terminal-container {
            background: linear-gradient(rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.9)), url('/interface/panel_tactical.png');
            background-size: cover;
            border: 4px solid #334155;
            border-radius: 8px;
            box-shadow: 
                inset 0 0 60px rgba(0, 0, 0, 0.6),
                0 25px 70px rgba(0, 0, 0, 0.9);
            position: relative;
            padding: 24px;
            min-height: 550px;
            display: flex;
            flex-direction: column;
            backdrop-filter: blur(8px);
        }

        .industrial-terminal-header {
            padding-bottom: 15px;
            margin-bottom: 15px;
            border-bottom: 2px solid rgba(15, 23, 42, 0.5);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .industrial-terminal-screen {
            background-color: #1e1e1e !important;
            border: 4px solid #0f172a;
            border-radius: 8px;
            flex: 1;
            display: flex;
            overflow: hidden;
            box-shadow: inset 0 0 30px rgba(0, 0, 0, 1);
            position: relative;
        }

        .industrial-terminal-numbers {
            background: rgba(0, 0, 0, 0.3);
            border-right: 2px solid #0f172a;
            padding: 20px 15px;
            color: #475569;
            font-family: 'Fira Code', monospace;
            font-size: 13px;
            text-align: right;
            min-width: 60px;
            user-select: none;
        }

        .industrial-terminal-textarea {
            background: transparent !important;
            color: #e2e8f0 !important;
            font-family: 'Fira Code', monospace !important;
            font-size: 15px !important;
            line-height: 32px !important;
            padding: 20px !important;
            width: 100%;
            height: 100%;
            border: none !important;
            outline: none !important;
            resize: none !important;
            caret-color: #3a7d44;
        }

        .command-autocomplete {
            position: absolute;
            width: min(340px, calc(100% - 28px));
            z-index: 45;
            overflow: hidden;
            border: 1px solid rgba(16, 185, 129, 0.5);
            border-radius: 8px;
            background: linear-gradient(180deg, rgba(8, 15, 30, 0.98), rgba(2, 6, 23, 0.98));
            box-shadow: 0 22px 48px rgba(0, 0, 0, 0.6), 0 0 22px rgba(16, 185, 129, 0.14);
            backdrop-filter: blur(10px);
        }

        .command-autocomplete-scroll {
            max-height: 224px;
            overflow-y: auto;
        }

        .command-autocomplete-option {
            display: flex;
            align-items: center;
            gap: 11px;
            width: 100%;
            padding: 9px 12px;
            color: #cbd5e1;
            text-align: left;
            cursor: pointer;
            border-left: 2px solid transparent;
            border-bottom: 1px solid rgba(30, 41, 59, 0.6);
            transition: background 0.14s ease, color 0.14s ease, border-color 0.14s ease;
        }

        .command-autocomplete-option:last-child {
            border-bottom: 0;
        }

        .command-autocomplete-option.is-active {
            color: #ecfdf5;
            background: rgba(16, 185, 129, 0.16);
            border-left-color: #34d399;
        }

        .command-autocomplete-icon {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 6px;
            color: #6ee7b7;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.25);
            transition: background 0.14s ease, color 0.14s ease, border-color 0.14s ease;
        }

        .command-autocomplete-icon svg {
            width: 15px;
            height: 15px;
        }

        .command-autocomplete-option.is-active .command-autocomplete-icon {
            color: #022c22;
            background: #34d399;
            border-color: #34d399;
        }

        .command-autocomplete-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
            flex: 1 1 auto;
        }

        .command-autocomplete-label {
            color: #d1fae5;
            font-family: 'Fira Code', monospace;
            font-size: 12.5px;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .command-autocomplete-hint {
            color: #94a3b8;
            font-family: 'Inter', sans-serif;
            font-size: 10.5px;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .command-autocomplete-footer {
            display: flex;
            flex-wrap: wrap;
            gap: 4px 12px;
            padding: 7px 12px;
            background: rgba(2, 6, 23, 0.55);
            border-top: 1px solid rgba(16, 185, 129, 0.18);
            color: #64748b;
            font-family: 'Inter', sans-serif;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

         .industrial-btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white !important;
            font-weight: 900 !important;
            padding: 15px 40px !important;
            border: 2px solid #047857 !important;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
            transition: all 0.2s;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3), inset 0 1px 0 rgba(255,255,255,0.1);
            cursor: pointer;
        }

        .industrial-btn-primary:hover {
            background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.5), inset 0 1px 0 rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .mobile-command-dock {
            display: none;
        }

        .focus-command-strip {
            display: none;
        }

        .mobile-command-button {
            min-width: 0;
            aspect-ratio: 1 / 1;
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.96) 0%, rgba(15, 23, 42, 0.98) 100%);
            border: 2px solid #334155;
            border-radius: 6px;
            color: #cbd5e1;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06), 0 4px 10px rgba(0, 0, 0, 0.42);
            transition: transform 0.15s ease, border-color 0.15s ease, color 0.15s ease;
            touch-action: manipulation;
        }

        .mobile-command-button:active {
            border-color: #10b981;
            color: #6ee7b7;
            transform: scale(0.96);
        }

        .industrial-btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3), inset 0 2px 4px rgba(0,0,0,0.2);
        }

        /* Estado desabilitado: sem comandos no terminal nao da pra executar.
           Fica cinza/apagado e sem os efeitos de hover/clique. */
        .industrial-btn-primary:disabled,
        .industrial-btn-primary.is-disabled {
            background: linear-gradient(135deg, #475569 0%, #334155 100%) !important;
            border-color: #1e293b !important;
            color: #94a3b8 !important;
            text-shadow: none;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04) !important;
            cursor: not-allowed;
            opacity: 0.7;
            transform: none !important;
        }

        .industrial-btn-secondary {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            color: white !important;
            font-weight: 900 !important;
            padding: 15px 40px !important;
            border: 2px solid #334155 !important;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
            transition: all 0.2s;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255,255,255,0.1);
            cursor: pointer;
        }

        .industrial-btn-secondary:hover {
            background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .industrial-btn-secondary:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3), inset 0 2px 4px rgba(0,0,0,0.2);
        }

        .industrial-btn-undo {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            background:
                linear-gradient(135deg, rgba(180, 83, 9, 0.22) 0%, rgba(15, 23, 42, 0.96) 72%),
                repeating-linear-gradient(135deg, rgba(245, 158, 11, 0.18) 0 8px, transparent 8px 16px);
            color: #fcd34d !important;
            font-weight: 900 !important;
            padding: 15px 22px !important;
            border: 2px dashed rgba(245, 158, 11, 0.72) !important;
            border-radius: 6px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.55);
            box-shadow:
                0 4px 12px rgba(245, 158, 11, 0.16),
                inset 0 0 18px rgba(245, 158, 11, 0.08);
            cursor: pointer;
            transition: transform 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }

        .industrial-btn-undo::before {
            content: '';
            position: absolute;
            inset: 4px;
            border: 1px solid rgba(245, 158, 11, 0.18);
            border-radius: 4px;
            pointer-events: none;
            z-index: -1;
        }

        .industrial-btn-undo:hover {
            color: #fef3c7 !important;
            border-color: #f59e0b !important;
            box-shadow:
                0 6px 16px rgba(245, 158, 11, 0.28),
                inset 0 0 22px rgba(245, 158, 11, 0.14);
            transform: translateY(-2px);
        }

        .industrial-btn-undo:active {
            transform: translateY(0);
            box-shadow:
                0 2px 8px rgba(245, 158, 11, 0.18),
                inset 0 2px 4px rgba(0,0,0,0.24);
        }

        .rivet {
            width: 10px;
            height: 10px;
            background: #475569;
            border-radius: 50%;
            position: absolute;
            box-shadow: inset 1px 1px 2px rgba(255,255,255,0.2), 1px 1px 3px rgba(0,0,0,0.6);
        }

        .tactical-highlight {
            position: absolute;
            left: 0;
            right: 0;
            background: rgba(58, 125, 68, 0.2);
            border-left: 4px solid #3a7d44;
            pointer-events: none;
            z-index: 5;
        }

        /* ===== ALERTAS E FEEDBACK ===== */
        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%);
            border: 2px solid #10b981;
            border-radius: 6px;
            padding: 12px 16px;
            color: #a7f3d0;
            box-shadow: 0 0 12px rgba(16, 185, 129, 0.2), inset 0 0 8px rgba(16, 185, 129, 0.1);
        }

        .alert-error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
            border: 2px solid #ef4444;
            border-radius: 6px;
            padding: 12px 16px;
            color: #fca5a5;
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.2), inset 0 0 8px rgba(239, 68, 68, 0.1);
        }

        .alert-info {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%);
            border: 2px solid #3b82f6;
            border-radius: 6px;
            padding: 12px 16px;
            color: #93c5fd;
            box-shadow: 0 0 12px rgba(59, 130, 246, 0.2), inset 0 0 8px rgba(59, 130, 246, 0.1);
        }

        /* ===== MANUAL DE CAMPO (REFERÊNCIA) ===== */
        .learning-feedback {
            scroll-margin: 28px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(2, 6, 23, 0.98));
            border: 2px solid rgba(59, 130, 246, 0.45);
            border-radius: 8px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.35), inset 0 0 20px rgba(59, 130, 246, 0.06);
        }

        @keyframes context-hint-spotlight {
            0%, 100% {
                border-color: rgba(59, 130, 246, 0.45);
                box-shadow: 0 12px 24px rgba(0, 0, 0, 0.35), inset 0 0 20px rgba(59, 130, 246, 0.06);
            }
            18%, 62% {
                border-color: rgba(167, 139, 250, 0.95);
                box-shadow:
                    0 0 0 4px rgba(167, 139, 250, 0.18),
                    0 18px 42px rgba(0, 0, 0, 0.42),
                    0 0 34px rgba(167, 139, 250, 0.35),
                    inset 0 0 28px rgba(167, 139, 250, 0.10);
            }
        }

        .learning-feedback.is-context-hint-spotlight {
            animation: context-hint-spotlight 1.4s ease-in-out 2;
        }

        .learning-feedback-item {
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr);
            gap: 12px;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid rgba(148, 163, 184, 0.18);
            background: rgba(15, 23, 42, 0.72);
        }

        .learning-feedback-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 6px;
            border: 1px solid currentColor;
            background: rgba(255, 255, 255, 0.04);
        }

        .learning-feedback-tip { color: #93c5fd; }
        .learning-feedback-warning { color: #fbbf24; }
        .learning-feedback-info { color: #a7f3d0; }
        .learning-feedback-optimization { color: #38bdf8; }
        .learning-feedback-error { color: #f87171; }
        .learning-feedback-hint { color: #a78bfa; }
        .learning-feedback-success { color: #34d399; }

        .learning-feedback-item {
            position: relative;
            overflow: hidden;
        }

        .learning-feedback-item::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 3px;
            background: currentColor;
            opacity: 0.85;
        }

        .learning-feedback-suggestion {
            display: grid;
            gap: 6px;
            margin-top: 10px;
            padding: 10px;
            border-radius: 6px;
            background: rgba(2, 6, 23, 0.72);
            border: 1px solid rgba(148, 163, 184, 0.16);
        }

        .learning-code-block {
            display: block;
            white-space: pre-wrap;
            word-break: break-word;
            border-radius: 5px;
            border: 1px solid rgba(51, 65, 85, 0.9);
            background: rgba(15, 23, 42, 0.92);
            padding: 8px 10px;
            color: #a7f3d0;
            font-family: 'Fira Code', monospace;
            font-size: 11px;
            line-height: 1.5;
        }

        .learning-code-block.is-from {
            color: #fca5a5;
            border-color: rgba(248, 113, 113, 0.28);
        }

        .tutorial-overlay {
            background: rgba(2, 6, 23, 0.72);
            z-index: 120;
            isolation: isolate;
        }

        .tutorial-panel {
            position: relative;
            z-index: 2;
            background: linear-gradient(135deg, #0f172a 0%, #111827 54%, #020617 100%);
            border: 3px solid rgba(16, 185, 129, 0.45);
            border-radius: 8px;
            box-shadow: 0 26px 70px rgba(0, 0, 0, 0.72), inset 0 0 32px rgba(16, 185, 129, 0.06);
        }

        .tutorial-hero-card {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(16, 185, 129, 0.35);
            border-radius: 8px;
            background:
                linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(59, 130, 246, 0.08)),
                rgba(2, 6, 23, 0.78);
            box-shadow: inset 0 0 28px rgba(16, 185, 129, 0.08);
        }

        .tutorial-step-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 72px;
            height: 72px;
            border-radius: 8px;
            border: 2px solid rgba(16, 185, 129, 0.5);
            background: rgba(16, 185, 129, 0.12);
            color: #6ee7b7;
            box-shadow: 0 0 24px rgba(16, 185, 129, 0.12);
        }

        .tutorial-step-icon svg {
            width: 38px;
            height: 38px;
        }

        .tutorial-action-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 14px;
            border-radius: 6px;
            border: 1px solid rgba(16, 185, 129, 0.34);
            background: rgba(16, 185, 129, 0.10);
            padding: 8px 10px;
            color: #d1fae5;
            font-size: 12px;
            font-weight: 900;
        }

        .tutorial-loadout {
            border-radius: 8px;
            border: 1px solid rgba(51, 65, 85, 0.85);
            background: rgba(2, 6, 23, 0.52);
        }

        .tutorial-progress-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #475569;
            transition: width 0.2s ease, background-color 0.2s ease;
        }

        .tutorial-progress-dot.is-active {
            width: 28px;
            background: #10b981;
        }

        .tutorial-command-row {
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid rgba(51, 65, 85, 0.9);
            background: rgba(15, 23, 42, 0.78);
        }

        .tutorial-command-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            border-radius: 5px;
            border: 1px solid rgba(59, 130, 246, 0.38);
            color: #bfdbfe;
            background: rgba(59, 130, 246, 0.10);
        }

        .briefing-card {
            position: relative;
            overflow: hidden;
            border: 2px solid rgba(16, 185, 129, 0.28);
            background:
                linear-gradient(135deg, rgba(15, 23, 42, 0.92), rgba(2, 6, 23, 0.96)),
                radial-gradient(circle at 10% 0%, rgba(16, 185, 129, 0.16), transparent 34%);
        }

        .briefing-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 54px;
            height: 54px;
            border-radius: 8px;
            border: 2px solid rgba(16, 185, 129, 0.45);
            background: rgba(16, 185, 129, 0.12);
            color: #6ee7b7;
            box-shadow: inset 0 0 18px rgba(16, 185, 129, 0.07);
        }

        .briefing-icon-image {
            width: 42px;
            height: 42px;
            object-fit: contain;
            filter: drop-shadow(0 5px 10px rgba(0, 0, 0, 0.45));
        }

        .briefing-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 34px;
            border-radius: 6px;
            border: 1px solid rgba(51, 65, 85, 0.95);
            background: rgba(15, 23, 42, 0.74);
            padding: 7px 9px;
            color: #cbd5e1;
            font-size: 11px;
            font-weight: 900;
        }

        .briefing-command-chip {
            border-color: rgba(59, 130, 246, 0.34);
            color: #bfdbfe;
            font-family: 'Fira Code', monospace;
            font-weight: 700;
        }

        @keyframes tutorial-anchor-pulse {
            0%, 100% {
                box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.82), 0 0 26px rgba(16, 185, 129, 0.26);
            }
            50% {
                box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.36), 0 0 40px rgba(59, 130, 246, 0.26);
            }
        }

        .tutorial-anchor-highlight {
            position: relative;
            z-index: 60 !important;
            border-radius: 8px;
            animation: tutorial-anchor-pulse 1.8s ease-in-out infinite;
        }

        .tutorial-anchor-highlight::after {
            content: attr(data-tutorial-label);
            position: absolute;
            left: 50%;
            bottom: calc(100% + 10px);
            transform: translateX(-50%);
            z-index: 65;
            width: max-content;
            max-width: 210px;
            border: 1px solid rgba(16, 185, 129, 0.52);
            border-radius: 6px;
            background: rgba(2, 6, 23, 0.94);
            color: #d1fae5;
            padding: 7px 9px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.48);
            pointer-events: none;
        }

        .tutorial-anchor-token {
            z-index: 62 !important;
            border-radius: 999px;
        }

        .help-button {
            border: 2px solid rgba(59, 130, 246, 0.48);
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.95));
            color: #bfdbfe;
        }

        .help-button:hover {
            border-color: rgba(96, 165, 250, 0.9);
            color: #dbeafe;
        }

        .tactical-manual {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 3px solid #334155;
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.6);
        }

        .tactical-manual-header {
            background: linear-gradient(90deg, #0f172a 0%, #1e293b 100%);
            border-bottom: 2px solid #334155;
            padding: 12px 16px;
        }

        .tactical-command-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 2px solid #334155;
            border-radius: 6px;
            padding: 12px;
            transition: all 0.2s ease;
        }

        .tactical-command-card:hover {
            border-color: #3a7d44;
            box-shadow: 0 0 16px rgba(58, 125, 68, 0.4);
            transform: translateY(-2px);
        }

        .command-card-title {
            font-size: 16px;
            line-height: 1.2;
            letter-spacing: 0;
        }

        .command-card-snippet {
            margin-top: 2px;
            font-size: 13px;
            line-height: 1.3;
        }

        .command-panel-reference {
            margin-top: 18px;
            overflow: hidden;
        }

        /* ===== ANIMAÇÕES ===== */
        @keyframes pulse-green {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        .pulse-tactical { animation: pulse-green 2s ease-in-out infinite; }

        @keyframes scanlines {
            0% { transform: translateY(0); }
            100% { transform: translateY(10px); }
        }

        .scanline-effect {
            animation: scanlines 8s linear infinite;
        }

        /* ===== EFEITOS VISUAIS E ILUMINACAO ===== */
        .shadow-dramatic {
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.8)) drop-shadow(0 0 15px rgba(0, 0, 0, 0.6));
        }

        .glow-green {
            filter: drop-shadow(0 0 10px rgba(34, 197, 94, 0.6)) drop-shadow(0 0 20px rgba(34, 197, 94, 0.3));
        }

        .glow-red {
            filter: drop-shadow(0 0 10px rgba(239, 68, 68, 0.6)) drop-shadow(0 0 20px rgba(239, 68, 68, 0.3));
        }

        @keyframes flicker {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        .flicker-effect {
            animation: flicker 0.15s infinite;
        }

        .character-shadow {
            filter: drop-shadow(-2px 4px 8px rgba(0, 0, 0, 0.8));
        }

        @keyframes tile-highlight {
            0%, 100% { filter: brightness(1); }
            50% { filter: brightness(1.2); }
        }

        .tile-highlight {
            animation: tile-highlight 0.5s ease-in-out;
        }

        @media (min-width: 1200px) {
            .game-shell {
                display: grid !important;
                grid-template-columns: minmax(0, auto) clamp(420px, 32vw, 520px);
                column-gap: 28px;
                row-gap: 18px;
                max-width: 1680px;
                align-items: flex-start;
                justify-content: center;
            }

            .game-board-column {
                --desktop-board-size: clamp(600px, min(50vw, calc(100dvh - 220px)), 720px);
                grid-column: 1;
                grid-row: 1;
                flex: 0 0 auto;
                align-items: center;
                max-width: none;
            }

            .surveillance-monitor {
                width: fit-content;
            }

            .game-grid {
                --board-size: var(--desktop-board-size);
            }

            .game-control-area {
                grid-column: 2;
                grid-row: 1;
                display: block !important;
                width: clamp(420px, 32vw, 520px);
            }

            .industrial-terminal-container {
                min-height: 0;
                padding: 18px;
            }

            .industrial-terminal-screen {
                min-height: 300px;
            }

            .industrial-terminal-header {
                padding-bottom: 12px;
                margin-bottom: 12px;
            }

            .industrial-terminal-numbers {
                min-width: 48px;
                padding: 18px 10px;
            }

            .industrial-terminal-textarea {
                padding: 18px 16px !important;
            }

            .game-action-row {
                gap: 10px;
                margin-top: 14px;
            }

            .game-action-row > button {
                min-height: 46px;
                padding: 10px 14px !important;
                font-size: 11px;
                letter-spacing: 0.02em;
            }

            .tactical-manual {
                position: static;
            }

            .tactical-manual-header {
                padding: 10px 12px !important;
            }

            .tactical-manual-header .command-manual-title {
                font-size: 12px;
            }

            .command-reference-body {
                padding: 12px !important;
            }

            .command-reference-hint {
                display: none;
            }

            .mobile-command-list {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 8px !important;
            }

            .tactical-command-card {
                min-height: 46px;
                gap: 9px !important;
                padding: 8px 10px !important;
            }

            .command-card-icon {
                padding: 7px !important;
                border-radius: 5px;
            }

            .command-card-icon svg {
                width: 18px;
                height: 18px;
            }

            .command-card-title {
                font-size: 13px;
                line-height: 1.15;
            }

            .command-card-snippet {
                font-size: 10.5px;
                line-height: 1.25;
            }

            .level-actions {
                margin-top: 18px;
            }

            .board-telemetry {
                display: none;
            }

            .shell-telemetry {
                display: block;
                grid-column: 1 / -1;
                grid-row: 2;
            }

            .shell-telemetry .radio-log-list {
                grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
                max-height: 132px;
            }

            .shell-telemetry .radio-log-entry {
                min-height: 46px;
            }
        }

        @media (max-width: 767px) {
            .game-shell {
                gap: 14px;
            }

            .game-shell > .flex.flex-col {
                gap: 12px;
            }

            .tactical-hud-container {
                padding: 12px;
                border-width: 2px;
            }

            .tactical-badge {
                padding: 7px 10px;
                font-size: 10px;
                letter-spacing: 0.5px;
            }

            .tactical-tooltip {
                left: 0;
                right: auto;
                transform: none;
                width: min(320px, calc(100dvw - 48px));
                font-size: 12px;
            }

            .health-bar-container {
                width: 100%;
            }

            .lore-panel {
                max-height: calc(100dvh - 24px);
            }

            .lore-content {
                max-height: calc(100dvh - 330px);
            }

            .lore-paragraph {
                font-size: 13px;
                line-height: 1.7;
            }

            .lore-mode-tabs {
                width: 100%;
            }

            .lore-gallery {
                max-height: calc(100dvh - 330px);
            }

            .health-status-card {
                width: min(286px, calc(100dvw - 52px));
                max-width: calc(100dvw - 52px);
                flex-basis: auto;
            }

            .health-vital-row {
                grid-template-columns: 44px minmax(0, 1fr) auto;
            }

            .health-vital-icon {
                width: 44px;
                height: 44px;
            }

            .health-vital-image {
                width: 34px;
                height: 34px;
            }

            .surveillance-monitor {
                display: block;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                border-width: 4px;
                padding: 6px;
                border-radius: 10px;
                container-type: inline-size;
                box-shadow:
                    inset 0 0 26px rgba(0, 0, 0, 1),
                    0 12px 28px rgba(0, 0, 0, 0.65);
            }

            .game-grid {
                --board-size: min(360px, calc(100cqw - 16px), calc(100dvh - 360px));
                --tile-size: calc(var(--board-size) / var(--grid-size));
                width: var(--board-size);
                height: var(--board-size);
                max-width: calc(100cqw - 16px);
                margin-inline: auto;
                border-width: 2px;
                border-radius: 6px;
                box-shadow: inset 0 0 20px rgba(0, 0, 0, 1);
            }

            .mobile-command-dock {
                display: grid;
                grid-template-columns: repeat(7, minmax(0, 1fr));
                gap: 7px;
                width: min(360px, calc(100dvw - 24px));
                margin: 8px auto 0;
                padding: 8px;
                background: linear-gradient(135deg, rgba(15, 23, 42, 0.94), rgba(2, 6, 23, 0.96));
                border: 2px solid #334155;
                border-radius: 8px;
                box-shadow: 0 10px 24px rgba(0, 0, 0, 0.58), inset 0 0 20px rgba(0, 0, 0, 0.36);
            }

            .mobile-command-button {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .mobile-command-button svg {
                width: 22px;
                height: 22px;
            }

            .eliminated-text {
                font-size: clamp(9px, calc(var(--tile-size) * 0.22), 14px);
            }

            .player-token {
                width: clamp(28px, calc(var(--tile-size) * 0.86), 56px) !important;
                height: clamp(28px, calc(var(--tile-size) * 0.86), 56px) !important;
            }

            .zombie-token {
                width: clamp(26px, calc(var(--tile-size) * 0.76), 48px) !important;
                height: clamp(26px, calc(var(--tile-size) * 0.76), 48px) !important;
            }

            .grid-tile::after {
                box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.24);
            }

            .radio-terminal {
                border-width: 2px;
            }

            .radio-terminal-header {
                padding: 10px 12px;
            }

            .radio-terminal-status {
                display: none;
            }

            .radio-log-list {
                max-height: 126px;
                padding: 8px;
            }

            .radio-log-entry {
                font-size: 11px;
                line-height: 1.35;
                padding: 8px;
            }

            .industrial-terminal-container {
                height: auto;
                min-height: 430px;
                padding: 12px;
                border-width: 2px;
                box-shadow:
                    inset 0 0 34px rgba(0, 0, 0, 0.55),
                    0 14px 34px rgba(0, 0, 0, 0.75);
            }

            .industrial-terminal-header {
                align-items: flex-start;
                gap: 12px;
                padding-bottom: 12px;
                margin-bottom: 12px;
            }

            .industrial-terminal-screen {
                min-height: 260px;
                border-width: 2px;
            }

            .industrial-terminal-numbers {
                min-width: 44px;
                padding: 14px 8px;
                font-size: 11px;
            }

            .industrial-terminal-textarea {
                font-size: 14px !important;
                padding: 14px 12px !important;
            }

            .game-action-row {
                flex-direction: column;
                gap: 10px;
            }

            .game-action-row > button {
                width: 100%;
                flex: none !important;
                min-height: 54px;
                padding: 14px 16px !important;
            }

            .tactical-manual-header {
                padding: 12px 14px !important;
            }

            .tactical-manual {
                display: none;
            }

            .mobile-command-list {
                gap: 10px;
            }

            .tactical-command-card {
                min-height: 58px;
                padding: 12px !important;
                touch-action: manipulation;
            }

            .level-actions {
                align-items: stretch;
                flex-direction: column;
                gap: 12px;
            }

            .level-actions > * {
                width: 100%;
            }

            .tutorial-command-row {
                align-items: flex-start;
            }

            .tutorial-panel {
                max-height: calc(100dvh - 24px);
                overflow-y: auto;
            }

            .tutorial-step-icon {
                width: 58px;
                height: 58px;
            }

            .tutorial-step-icon svg {
                width: 30px;
                height: 30px;
            }

            .briefing-icon {
                width: 48px;
                height: 48px;
            }

            .briefing-chip {
                width: 100%;
                justify-content: flex-start;
            }

            .tutorial-anchor-highlight::after {
                display: none;
            }
        }

    </style>

    <button
        x-cloak
        x-show="focusMode"
        x-transition.opacity
        type="button"
        @click="closeFocusMode()"
        class="focus-mode-exit"
        title="Sair do modo foco"
        aria-label="Sair do modo foco"
    >
        {!! UIConfig::getIcon('x', 'w-4 h-4') !!}
        Sair do foco
    </button>

    {{-- ===================================================================
         OVERLAYS - Vitória e Derrota
    =================================================================== --}}
    <div
        x-cloak
        x-show="showTutorial && hasTutorial()"
        x-transition.opacity
        class="tutorial-overlay fixed inset-0 z-[120] flex items-center justify-center p-3 sm:p-5 backdrop-blur-md"
        role="dialog"
        aria-modal="true"
        aria-label="Como jogar esta fase"
    >
        <div
            x-show="showTutorial && hasTutorial()"
            x-transition.scale.origin.center
            @click.outside="closeTutorial(false)"
            class="tutorial-panel w-full max-w-3xl overflow-hidden"
        >
            <div class="flex items-start justify-between gap-4 border-b border-slate-700/80 px-5 py-4 sm:px-6">
                <div class="min-w-0">
                    <div class="text-[10px] font-black uppercase tracking-[0.22em] text-emerald-400">Como jogar esta fase</div>
                    <h2 class="mt-1 text-xl font-black text-slate-100 sm:text-2xl" x-text="tutorial.title"></h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-300" x-text="tutorial.summary"></p>
                </div>
                <button
                    type="button"
                    @click="closeTutorial(true)"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded border border-slate-700 bg-slate-950 text-slate-300 transition-colors hover:border-slate-500 hover:text-white"
                    title="Fechar tutorial"
                    aria-label="Fechar tutorial"
                >
                    {!! UIConfig::getIcon('x', 'w-5 h-5') !!}
                </button>
            </div>

            <div class="grid gap-5 px-5 py-5 sm:px-6 lg:grid-cols-[minmax(0,1fr)_286px]">
                <div class="min-w-0 space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap gap-2" aria-hidden="true">
                            <template x-for="(_, index) in tutorial.steps" :key="index">
                                <span class="tutorial-progress-dot" :class="{ 'is-active': index === tutorialStep }"></span>
                            </template>
                        </div>
                        <div class="rounded border border-slate-700 bg-slate-950/70 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest text-slate-300">
                            Passo <span x-text="tutorialStep + 1"></span>/<span x-text="tutorial.steps.length"></span>
                        </div>
                    </div>

                    <div class="tutorial-hero-card p-4 sm:p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <div class="tutorial-step-icon" x-html="tutorialIconSvg(tutorial.steps[tutorialStep]?.icon)"></div>
                            <div class="min-w-0">
                                <div class="text-[10px] font-black uppercase tracking-[0.22em] text-emerald-300" x-text="tutorialFocusLabel(tutorial.steps[tutorialStep]?.focus)"></div>
                                <h3 class="mt-1 text-2xl font-black tracking-tight text-slate-100" x-text="tutorial.steps[tutorialStep]?.title"></h3>
                                <p class="mt-2 text-base font-bold leading-relaxed text-slate-300" x-text="tutorial.steps[tutorialStep]?.body"></p>
                                <div class="tutorial-action-chip">
                                    {!! UIConfig::getIcon('target', 'w-4 h-4') !!}
                                    <span x-text="tutorial.steps[tutorialStep]?.action"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded border border-blue-500/25 bg-blue-500/10 p-4">
                            <div class="text-[10px] font-black uppercase tracking-widest text-blue-300">Objetivo</div>
                            <p class="mt-2 text-sm font-bold leading-relaxed text-slate-200" x-text="tutorial.objective"></p>
                        </div>
                        <div class="rounded border border-slate-700 bg-slate-950/70 p-4">
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Dicas rapidas</div>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <template x-for="tip in tutorial.quickTips" :key="tip">
                                    <span class="rounded border border-slate-700 bg-slate-900/80 px-2.5 py-1.5 text-[11px] font-bold text-slate-300" x-text="tip"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="tutorial-loadout p-4">
                    <div class="mb-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Comandos desta fase</div>
                    <div class="space-y-2">
                        <template x-for="command in tutorial.commands" :key="command.code">
                            <div class="tutorial-command-row">
                                <span class="tutorial-command-icon" x-html="tutorialIconSvg(command.icon)"></span>
                                <div class="min-w-0">
                                    <code class="block truncate font-mono text-xs font-bold text-emerald-300" x-text="command.code"></code>
                                    <span class="text-[11px] font-black uppercase tracking-wide text-slate-500" x-text="command.description"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </aside>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-700/80 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <button
                    type="button"
                    @click="closeTutorial(true)"
                    class="rounded border border-slate-700 px-4 py-3 text-sm font-black text-slate-300 transition-colors hover:border-slate-500 hover:text-white"
                >
                    Entendi, jogar
                </button>

                <div class="flex gap-3">
                    <button
                        type="button"
                        @click="previousTutorialStep()"
                        :disabled="tutorialStep === 0"
                        class="rounded border border-slate-700 px-4 py-3 text-sm font-black text-slate-300 transition-colors hover:border-slate-500 hover:text-white disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        Voltar
                    </button>
                    <button
                        type="button"
                        @click="nextTutorialStep()"
                        class="rounded bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-emerald-950/40 transition-colors hover:bg-emerald-500"
                        x-text="tutorialStep === tutorial.steps.length - 1 ? 'Concluir' : 'Proximo'"
                    ></button>
                </div>
            </div>
        </div>
    </div>

    @if ($gameState['win'] ?? false)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-md animate-fade-in-scale">
            <div class="bg-gradient-to-br from-slate-900 to-slate-950 rounded-lg p-10 text-center max-w-md w-full mx-4 shadow-2xl border-4 border-emerald-500/50 ring-4 ring-emerald-500/20">
                <div class="inline-flex items-center justify-center p-5 bg-emerald-500/20 text-emerald-400 rounded-lg mb-6 shadow-lg border-2 border-emerald-500/50">
                    {!! UIConfig::getIcon('target', 'w-16 h-16') !!}
                </div>
                <h2 class="text-3xl font-extrabold text-emerald-400 mb-2 tracking-wider">FASE CONCLUIDA</h2>
                <p class="text-slate-300 mb-8 text-lg">Sua sequencia levou Leon ao objetivo.</p>
                <div class="flex flex-col gap-3">
                    <button wire:click="nextLevel"
                            class="group w-full flex items-center justify-center gap-3 px-6 py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition-all shadow-lg shadow-emerald-500/30 active:scale-[0.98]">
                        {{ $this->getNextLevelActionLabel() }}
                        {!! UIConfig::getIcon('next', 'w-6 h-6 group-hover:translate-x-1 transition-transform') !!}
                    </button>
                    <button wire:click="resetLevel"
                            class="w-full flex items-center justify-center gap-3 px-6 py-4 bg-slate-700 hover:bg-slate-600 text-slate-100 font-bold rounded-lg transition-all active:scale-[0.98]">
                        {!! UIConfig::getIcon('reset', 'w-5 h-5') !!}
                        Repetir fase
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($gameState['lose'] ?? false)
        @php $loseModalDelayMs = (int) ($gameState['loseModalDelayMs'] ?? 0); @endphp

        @if ($loseModalDelayMs > 0)
            <div class="fixed inset-x-4 top-6 z-40 mx-auto max-w-md rounded-lg border-2 border-rose-500/70 bg-slate-950/95 px-5 py-4 text-center shadow-2xl shadow-rose-950/50">
                <div class="text-xs font-black uppercase tracking-[0.2em] text-rose-400">Garrador é imortal</div>
                <div class="mt-1 text-sm font-bold text-slate-100">{{ $gameState['message'] ?? 'Atacar o Garrador de perto foi fatal.' }}</div>
            </div>
        @endif

        <div
            x-data="{ showFailModal: {{ $loseModalDelayMs > 0 ? 'false' : 'true' }} }"
            x-init="@if ($loseModalDelayMs > 0) setTimeout(() => showFailModal = true, {{ $loseModalDelayMs }}) @endif"
            x-show="showFailModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-md animate-fade-in-scale"
        >
            <div class="bg-gradient-to-br from-slate-900 to-slate-950 rounded-lg p-10 text-center max-w-md w-full mx-4 shadow-2xl border-4 border-rose-500/50 ring-4 ring-rose-500/20">
                <div class="inline-flex items-center justify-center p-5 bg-rose-500/20 text-rose-400 rounded-lg mb-6 shadow-lg border-2 border-rose-500/50">
                    {!! UIConfig::getIcon('skull', 'w-16 h-16') !!}
                </div>
                <h2 class="text-3xl font-extrabold text-rose-400 mb-2 tracking-wider">TENTATIVA FALHOU</h2>
                <p class="text-slate-300 mb-8 text-lg">{{ $gameState['message'] ?? 'Leon foi derrotado. Ajuste seus comandos e tente novamente.' }}</p>
                <button wire:click="resetLevel"
                        class="w-full flex items-center justify-center gap-3 px-6 py-4 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg transition-all shadow-lg shadow-rose-500/30 active:scale-[0.98]">
                    {!! UIConfig::getIcon('reset', 'w-6 h-6') !!}
                    Tentar Novamente
                </button>
            </div>
        </div>
    @endif

    <div
        x-cloak
        x-show="showInventory"
        x-transition.opacity
        class="inventory-overlay fixed inset-0 z-[60] flex items-center justify-center p-4 backdrop-blur-md"
    >
        <div
            x-show="showInventory"
            x-transition.scale.origin.center
            @click.outside="showInventory = false"
            class="inventory-panel w-full max-w-2xl p-5 sm:p-7"
        >
            <div class="flex items-center justify-between gap-4 pb-4 mb-5 border-b border-slate-700/80">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="p-2.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/40 rounded">
                        {!! UIConfig::getIcon('bag', 'w-6 h-6') !!}
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-lg sm:text-xl font-black text-slate-100 uppercase tracking-[0.18em]">Inventario</h2>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Itens de Sobrevivencia</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="showInventory = false"
                    class="w-10 h-10 flex items-center justify-center bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white border border-slate-700 rounded transition-colors"
                    title="Fechar inventario"
                >
                    {!! UIConfig::getIcon('x', 'w-5 h-5') !!}
                </button>
            </div>

            <div class="mb-4 flex flex-col gap-2 rounded border border-slate-700/80 bg-slate-950/60 p-3 sm:flex-row sm:items-center sm:justify-between">
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                    {{ count($this->getInventoryItems()) }}/{{ $this->getInventorySlotCount() }} slots ocupados
                </span>
                <span class="text-[11px] font-bold text-slate-500">Arraste um item para trocar de slot.</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach ($this->getInventorySlots() as $slotIndex => $slot)
                    <div
                        wire:key="inventory-slot-{{ $slotIndex }}-{{ $slot['id'] ?? 'empty' }}"
                        class="inventory-slot {{ $slot ? 'inventory-slot-filled' : '' }} relative flex items-center justify-center p-2"
                        x-bind:class="{
                            'is-dragging': inventoryDraggingSlot === {{ $slotIndex }},
                            'is-drop-target': inventoryDraggingSlot !== null && inventoryDraggingSlot !== {{ $slotIndex }}
                        }"
                        draggable="{{ $slot ? 'true' : 'false' }}"
                        @dragstart="inventoryDraggingSlot = {{ $slotIndex }}; $event.dataTransfer.effectAllowed = 'move'"
                        @dragend="inventoryDraggingSlot = null"
                        @dragover.prevent="$event.dataTransfer.dropEffect = 'move'"
                        @drop.prevent="
                            if (inventoryDraggingSlot !== null) {
                                $wire.moveInventoryItem(inventoryDraggingSlot, {{ $slotIndex }});
                                inventoryDraggingSlot = null;
                            }
                        "
                    >
                        @if ($slot)
                            <img src="{{ asset($slot['sprite'] ?? 'mundo/tiles/transparent.svg') }}" class="max-w-full max-h-full object-contain drop-shadow-xl" alt="{{ $slot['name'] ?? 'Item' }}" />
                            @if ($slot['equipped'] ?? false)
                                <span class="absolute left-1.5 top-1.5 px-1.5 py-0.5 bg-emerald-500/20 border border-emerald-500/50 rounded text-[8px] font-black text-emerald-300 uppercase tracking-wider">EQP</span>
                            @endif
                            <span class="absolute left-1.5 right-1.5 bottom-1.5 truncate text-center text-[9px] font-black text-slate-300 uppercase tracking-wide">{{ $slot['name'] ?? 'Item' }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===================================================================
         DOSSIER / LORE DA FASE
    =================================================================== --}}
    @if (! empty($levelLoreEntries))
        <div
            x-cloak
            x-show="showLore"
            x-transition.opacity
            class="lore-overlay fixed inset-0 z-[60] flex items-center justify-center p-3 sm:p-5 backdrop-blur-md"
        >
            <div
                x-show="showLore"
                x-transition.scale.origin.center
                @click.outside="showLore = false"
                class="lore-panel w-full max-w-4xl overflow-hidden"
            >
                <div class="flex items-start justify-between gap-4 border-b border-amber-500/20 bg-black/20 p-4 sm:p-5">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="rounded border border-amber-500/40 bg-amber-500/10 p-2.5 text-amber-300">
                            {!! UIConfig::getIcon('book', 'w-6 h-6') !!}
                        </div>
                        <div class="min-w-0">
                            <div class="text-[10px] font-black uppercase tracking-[0.22em] text-amber-400/80">Arquivo do jogo</div>
                            <h2 class="truncate text-lg font-black uppercase tracking-[0.12em] text-slate-100 sm:text-xl">Personagens da fase {{ $gameState['level'] ?? 1 }}</h2>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="showLore = false"
                        class="flex h-10 w-10 flex-none items-center justify-center rounded border border-slate-700 bg-slate-950 text-slate-300 transition-colors hover:border-amber-500/60 hover:text-white"
                        title="Fechar historia"
                        aria-label="Fechar historia"
                    >
                        {!! UIConfig::getIcon('x', 'w-5 h-5') !!}
                    </button>
                </div>

                <div class="p-4 sm:p-5">
                    @if (count($levelLoreEntries) > 1)
                        <div class="mb-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach ($levelLoreEntries as $index => $entry)
                                <button
                                    type="button"
                                    @click="activeLoreTab = {{ $index }}; activeLorePanel = 'story'"
                                    class="lore-tab flex items-center justify-between gap-3 px-3 py-2 text-left"
                                    :class="{ 'is-active': activeLoreTab === {{ $index }} }"
                                >
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-black uppercase tracking-[0.12em]">{{ $entry['title'] }}</span>
                                        <span class="block truncate text-[10px] font-bold uppercase tracking-widest text-slate-500">{{ $entry['subtitle'] }}</span>
                                    </span>
                                    <span class="text-[10px] font-black tabular-nums text-amber-400/70">0{{ $index + 1 }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    @foreach ($levelLoreEntries as $index => $entry)
                        <article x-show="activeLoreTab === {{ $index }}" x-cloak>
                            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <h3 class="text-xl font-black uppercase tracking-[0.12em] text-amber-200">{{ $entry['title'] }}</h3>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">{{ $entry['subtitle'] }}</p>
                                </div>
                                <div class="lore-mode-tabs" role="tablist" aria-label="Arquivo de {{ $entry['title'] }}">
                                    <button
                                        type="button"
                                        class="lore-mode-tab"
                                        :class="{ 'is-active': activeLorePanel === 'story' }"
                                        @click="activeLorePanel = 'story'"
                                    >
                                        Historia
                                    </button>
                                    <button
                                        type="button"
                                        class="lore-mode-tab"
                                        :class="{ 'is-active': activeLorePanel === 'visuals' }"
                                        @click="activeLorePanel = 'visuals'"
                                    >
                                        Visual
                                    </button>
                                </div>
                            </div>

                            <div x-show="activeLorePanel === 'story'" x-cloak>
                                {{-- <div class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-600">Fonte: {{ $entry['source'] ?: 'Arquivo indisponivel' }}</div> --}}
                                <div class="lore-content scrollbar-thin p-4 sm:p-5">
                                    @php
                                        $loreParagraphs = array_values(array_filter(
                                            preg_split('/\n{2,}/', $entry['content']) ?: [],
                                            fn ($paragraph) => trim($paragraph) !== ''
                                        ));
                                    @endphp
                                    @forelse ($loreParagraphs as $paragraph)
                                        <p class="lore-paragraph mb-4 last:mb-0">{!! nl2br(e(trim($paragraph))) !!}</p>
                                    @empty
                                        <p class="lore-paragraph">Nenhum texto de historia foi encontrado para este personagem.</p>
                                    @endforelse
                                </div>
                            </div>

                            <div x-show="activeLorePanel === 'visuals'" x-cloak>
                                @if (! empty($entry['images']))
                                    <div class="lore-gallery scrollbar-thin grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                                        @foreach ($entry['images'] as $image)
                                            <figure class="lore-image-card">
                                                <div class="lore-image-stage">
                                                    <img
                                                        src="{{ asset($image['path']) }}"
                                                        alt="{{ $entry['title'] }} - {{ $image['label'] }}"
                                                        class="lore-image"
                                                        loading="lazy"
                                                    >
                                                </div>
                                                <figcaption class="lore-image-caption">{{ $image['label'] }}</figcaption>
                                            </figure>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="lore-content scrollbar-thin p-4 sm:p-5">
                                        <p class="lore-paragraph">Nenhum conceito visual foi encontrado para este personagem.</p>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ===================================================================
         HEADER / HUD TATICO
    =================================================================== --}}
    <div class="game-hud-section mb-6 md:mb-10 max-w-[1400px] mx-auto">
        <div class="tactical-hud-container">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 md:gap-6">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6 min-w-0">
                    <a href="/" class="text-3xl sm:text-4xl font-black text-slate-100 hover:text-emerald-400 transition-colors tracking-tight">
                        Code<span class="text-emerald-500">Survivor</span>
                    </a>
                    <div class="flex flex-wrap gap-2">
                        <div class="tactical-badge text-slate-300 border-slate-600">
                            {!! UIConfig::getIcon('book', 'w-4 h-4 inline mr-1') !!}
                            FASE {{ $gameState['level'] ?? 1 }}
                        </div>
                        <div class="tactical-badge text-emerald-400 border-emerald-600/50 bg-gradient-to-br from-emerald-950 to-emerald-900/50 cursor-help">
                            {!! UIConfig::getIcon('target', 'w-4 h-4 inline mr-1') !!}
                            {{ strtoupper($gameState['phaseType'] ?? 'objetivo') }}
                            
                            @if(!empty($gameState['objective']))
                                <div class="tactical-tooltip">
                                    <div class="tactical-tooltip-title">
                                        {!! UIConfig::getIcon('info', 'w-3 h-3') !!}
                                        Objetivo da fase
                                    </div>
                                    {{ $gameState['objective'] }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if (! empty($levelLoreEntries))
                    <button
                        type="button"
                        @click="activeLoreTab = 0; activeLorePanel = 'story'; showLore = true"
                        class="health-bar-container lore-toggle-button flex items-center gap-3 transition-colors"
                        title="Abrir historia da fase"
                    >
                        <div class="rounded border border-amber-500/40 bg-amber-500/10 p-2 text-amber-300">
                            {!! UIConfig::getIcon('book', 'w-5 h-5') !!}
                        </div>
                        <div class="text-left">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Arquivo</p>
                            <p class="text-sm font-black text-amber-300">Personagens</p>
                        </div>
                    </button>
                @endif

                <button
                    type="button"
                    @click="openTutorial()"
                    class="health-bar-container help-button flex items-center gap-3 transition-colors"
                    title="Abrir ajuda da fase"
                >
                    <div class="rounded border border-blue-500/40 bg-blue-500/10 p-2 text-blue-300">
                        {!! UIConfig::getIcon('info', 'w-5 h-5') !!}
                    </div>
                    <div class="text-left">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Ajuda</p>
                        <p class="text-sm font-black text-blue-200">Como jogar</p>
                    </div>
                </button>

                {{-- Status do Jogador --}}
                <button
                    type="button"
                    @click="showInventory = true"
                    class="health-bar-container flex items-center gap-3 hover:border-emerald-500/60 transition-colors"
                    title="Abrir inventario"
                >
                    <div class="p-2 bg-emerald-500/20 text-emerald-400 rounded border border-emerald-500/50">
                        {!! UIConfig::getIcon('bag', 'w-5 h-5') !!}
                    </div>
                    <div class="text-left">
                        <p class="text-[9px] text-slate-400 font-black uppercase tracking-widest">Inventario</p>
                        <p class="text-sm font-black text-emerald-400 tabular-nums">{{ count($this->getInventoryItems()) }}/{{ $this->getInventorySlotCount() }}</p>
                    </div>
                </button>

                <div class="health-bar-container health-status-card">
                    <div class="health-vital-row">
                        <div class="health-vital-icon">
                            <img
                                src="{{ asset($this->getPlayerHealthAsset()) }}"
                                alt="Integridade vital {{ $gameState['player']['health'] ?? 0 }}/{{ $gameState['player']['maxHealth'] ?? 3 }}"
                                class="health-vital-image"
                                loading="eager"
                            >
                        </div>
                        <p class="health-vital-label text-[9px] text-slate-400 font-black uppercase tracking-widest">Vida do Leon</p>
                        <span class="health-count text-sm font-black text-emerald-400 tabular-nums text-right">
                            {{ $gameState['player']['health'] ?? 0 }}/{{ $gameState['player']['maxHealth'] ?? 3 }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================================
         MAIN LAYOUT
    =================================================================== --}}
    <section class="briefing-card mb-5 max-w-[1400px] mx-auto rounded-lg p-4 shadow-lg shadow-black/30">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 gap-4">
                <div class="briefing-icon shrink-0">
                    <img
                        src="{{ asset($this->getCurrentLevelMapIconAsset()) }}"
                        alt="Icone da fase {{ $gameState['level'] ?? 1 }}"
                        class="briefing-icon-image"
                    >
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-black uppercase tracking-[0.22em] text-emerald-400">Briefing da fase</div>
                    <h2 class="mt-1 text-xl font-black leading-tight text-slate-100">{{ $levelTutorial['briefingTitle'] ?? 'Fase ativa' }}</h2>
                    <p class="mt-1 text-sm font-bold leading-relaxed text-slate-300">
                        {{ $levelTutorial['objective'] ?? ($gameState['objective'] ?? 'Planeje a rota e execute seus comandos.') }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                <span class="briefing-chip">
                    {!! UIConfig::getIcon('info', 'w-4 h-4') !!}
                    {{ $levelTutorial['mechanic'] ?? strtoupper($gameState['phaseType'] ?? 'objetivo') }}
                </span>
                <span class="briefing-chip">
                    {!! UIConfig::getIcon('target', 'w-4 h-4') !!}
                    {{ $levelTutorial['danger'] ?? 'Analise a rota' }}
                </span>
                <span class="briefing-chip">
                    {!! UIConfig::getIcon('terminal', 'w-4 h-4') !!}
                    Comandos no editor
                </span>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row lg:flex-col xl:flex-row">
                <button
                    type="button"
                    wire:click="askForContextHint"
                    class="help-button inline-flex shrink-0 items-center justify-center gap-2 rounded px-4 py-3 text-sm font-black transition-colors"
                >
                    {!! UIConfig::getIcon('compass', 'w-5 h-5') !!}
                    Estou preso
                </button>
                <button
                    type="button"
                    @click="openTutorial()"
                    class="help-button inline-flex shrink-0 items-center justify-center gap-2 rounded px-4 py-3 text-sm font-black transition-colors"
                >
                    {!! UIConfig::getIcon('book', 'w-5 h-5') !!}
                    Como jogar
                </button>
            </div>
        </div>
    </section>

    <div class="game-shell flex flex-col lg:flex-row gap-5 md:gap-8 lg:gap-10 max-w-[1400px] mx-auto">

        {{-- COLUNA ESQUERDA: MAPA E LOGS --}}
        <div class="game-board-column flex flex-col gap-5 md:gap-8 w-full lg:w-auto">

            {{-- GRID DO JOGO (MONITOR DE VIGILÂNCIA) --}}
            <div class="surveillance-monitor inline-block mx-auto lg:mx-0 border-4 border-slate-800 rounded-lg shadow-2xl overflow-hidden">
                <button
                    type="button"
                    @click="toggleFocusMode()"
                    class="focus-mode-toggle"
                    title="Expandir modo foco"
                    aria-label="Expandir modo foco"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 4H4v4.5M15.5 4H20v4.5M20 15.5V20h-4.5M4 15.5V20h4.5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 9 4.5 4.5M15 9l4.5-4.5M15 15l4.5 4.5M9 15l-4.5 4.5" />
                    </svg>
                </button>
                @php
                    $level = (int) ($gameState['level'] ?? 1);
                    $pathOpacity = match ($level) {
                        1 => '0.26',
                        2 => '0.30',
                        3 => '0.32',
                        4 => '0.36',
                        5 => '0.28',
                        default => '0.28',
                    };
                    $gridOpacity = match ($level) {
                        1 => '0.07',
                        2 => '0.08',
                        3 => '0.08',
                        4 => '0.09',
                        5 => '0.08',
                        default => '0.08',
                    };
                @endphp
                <div
                    class="game-grid relative {{ ($gameState['shake'] ?? false) ? 'screen-shake' : '' }}"
                    style="grid-template-columns: repeat({{ $gameState['gridSize'] ?? 8 }}, var(--tile-size)); display: grid; background-image: url('{{ asset($this->getBoardBackgroundAsset()) }}'); --grid-size: {{ $gameState['gridSize'] ?? 8 }}; --path-opacity: {{ $pathOpacity }}; --grid-opacity: {{ $gridOpacity }};"
                >
                    @php
                        $map     = $gameState['map'] ?? [];
                        $player  = $gameState['player'] ?? [];
                        $goal    = $gameState['goal'] ?? [];
                        $zombies = $gameState['zombies'] ?? [];
                        $scenery = $gameState['scenery'] ?? [];
                        $collectibles = $gameState['collectibles'] ?? [];
                        $atmosphere = $this->getAtmosphere();
                        $gridSize = $gameState['gridSize'] ?? 8;
                        $currentLine = $this->getCurrentCommandLine();
                        $visionCells = $this->getVisionCells();
                        $levelVisual = $this->getLevelVisual();
                    @endphp
                    @for ($y = 0; $y < $gridSize; $y++)
                        @for ($x = 0; $x < $gridSize; $x++)
                            @php
                                $tile     = $map[$y][$x] ?? 'grama';
                                $isGoal   = ($goal['x'] ?? -1) === $x && ($goal['y'] ?? -1) === $y;
                                $zombie   = collect($zombies)->first(fn($z) => $z['x'] === $x && $z['y'] === $y);
                                $collectible = collect($collectibles)->first(fn($c) => $c['x'] === $x && $c['y'] === $y);
                                $sceneryItems = collect($scenery)->filter(fn($s) => $s['x'] === $x && $s['y'] === $y);
                                $isVision = isset($visionCells["{$x}:{$y}"]);
                                $isNearPlayer = $tile === 'caminho'
                                    && abs(($player['x'] ?? -99) - $x) <= 1
                                    && abs(($player['y'] ?? -99) - $y) <= 1;
                            @endphp

                            @php
                                $tileData = $this->getTileData($tile, $x, $y);
                            @endphp
                            <div class="grid-tile {{ $this->getTileClasses($tile) }} path-{{ $levelVisual['pathStyle'] }} {{ $this->getPathConnectionClasses($x, $y) }} {{ $isNearPlayer ? 'path-near-player' : '' }} relative flex items-center justify-center">
                                @if ($tile === 'caminho')
                                    <div class="path-arm-left"></div>
                                    <div class="path-arm-right"></div>
                                    <div class="path-ribbon"></div>
                                @endif

                                {{-- Layer 1: Tile de Chao --}}
                                @if ($tileData['asset'] !== 'mundo/tiles/transparent.svg')
                                    <img src="{{ asset($tileData['asset']) }}"
                                         class="absolute inset-0 w-full h-full object-cover opacity-90"
                                         style="transform: rotate({{ $tileData['rotate'] }}deg);"
                                         alt="" />
                                @endif
                                
                                {{-- Layer 2: Overlay de Atmosfera --}}
                                {{-- <div class="absolute inset-0 pointer-events-none" style="background-color: {{ $atmosphere['overlay'] }}"></div> --}}
                                @if ($isVision)
                                    <div class="tile-vision absolute inset-0 z-20 pointer-events-none"></div>
                                @endif

                                {{-- Layer 3: Objetos de Cenario (Decorativos) --}}
                                @foreach ($sceneryItems as $item)
                                    @php $sceneryAsset = $this->getSceneryAsset($item['type']); @endphp
                                    @if ($sceneryAsset !== 'mundo/tiles/transparent.svg')
                                        <div class="absolute inset-0 flex items-center justify-center z-10 pointer-events-none shadow-dramatic">
                                            <img src="{{ asset($sceneryAsset) }}"
                                                 class="w-full h-full object-contain scale-95 opacity-90"
                                                 alt="{{ $item['type'] }}" />
                                        </div>
                                    @endif
                                @endforeach

                                @if ($collectible)
                                    @php
                                        $collectibleItem = \App\Game\Config\ItemConfig::get($collectible['itemId'] ?? $collectible['id'] ?? '');
                                    @endphp
                                    @if ($collectibleItem)
                                        <div class="absolute inset-0 flex items-center justify-center z-20 pointer-events-none">
                                            <img src="{{ asset($collectibleItem['sprite']) }}"
                                                 class="collectible-token"
                                                 alt="{{ $collectibleItem['name'] }}" />
                                        </div>
                                    @endif
                                @endif

                                @if ($isGoal)
                                    <div
                                        class="absolute inset-0 flex items-center justify-center z-20"
                                        x-bind:class="{ 'tutorial-anchor-highlight': tutorialFocusIs('objective') }"
                                        data-tutorial-label="Objetivo"
                                    >
                                        @if ($goal['asset'] ?? null)
                                            <img src="{{ asset($goal['asset']) }}" class="w-full h-full object-contain drop-shadow-2xl" alt="Objetivo" />
                                        @else
                                            <div class="objective-marker pulse-tactical" aria-label="Objetivo"></div>
                                        @endif
                                    </div>
                                @endif

                                @if ($zombie)
                                    @php
                                        $zombieEntity = \App\Game\Entities\Zombie::fromArray($zombie);
                                        $zealotWalkSprites = $zombieEntity->isZealot() ? $zombieEntity->getZealotWalkSprites() : [];
                                    @endphp
                                    <div class="absolute inset-0 flex items-center justify-center z-20 sprite-grounded {{ $zombieEntity->isDying ? 'zombie-die' : '' }}">
                                        <div class="relative w-full h-full flex items-center justify-center">
                                            @if ($zombieEntity->name !== 'Zumbi')
                                                <div class="zombie-name-badge {{ $zombieEntity->isGarrador() ? 'is-garrador' : '' }} {{ $zombieEntity->isGarrador() && $x >= $gridSize - 1 ? 'is-edge-right' : '' }} absolute {{ $zombieEntity->isGarrador() ? '-top-3' : ($zombieEntity->isGanado() ? '-top-4' : '-top-3') }} {{ $zombieEntity->isGarrador() ? '' : 'left-1/2 -translate-x-1/2' }} z-30 px-2 py-0.5 rounded bg-slate-950/80 border border-rose-500/50 text-[10px] font-black text-rose-200 uppercase tracking-wide shadow-lg">
                                                    {{ $zombieEntity->name }}
                                                </div>
                                            @endif

                                            @if ($zombieEntity->isZealot() && ! $zombieEntity->isDying)
                                                <div class="zealot-sprite-stack drop-shadow-xl filter brightness-105 contrast-105 {{ $zombieEntity->isInteracting ? 'animate-bounce' : '' }}">
                                                    <img src="{{ asset($zombieEntity->getSprite()) }}"
                                                         class="zombie-token" alt="Zumbi" />

                                                    @if (! $zombieEntity->hasBrokenShield())
                                                        <img src="{{ asset($zealotWalkSprites[1]) }}"
                                                             class="zombie-token zealot-walk-frame" alt="" aria-hidden="true" />
                                                    @endif
                                                </div>
                                            @elseif ($zombieEntity->isGanado() && ! $zombieEntity->isDying)
                                                <div class="ganado-sprite-stack {{ $zombieEntity->isInteracting ? 'is-alerting' : '' }}">
                                                    <img src="{{ asset($zombieEntity->getSprite()) }}"
                                                         class="zombie-token" alt="Ganado" />
                                                </div>
                                            @elseif ($zombieEntity->isGarrador() && ! $zombieEntity->isDying)
                                                <div class="garrador-sprite-stack {{ $x >= $gridSize - 1 ? 'is-edge-right' : '' }} {{ $zombieEntity->isInteracting ? 'is-alerting' : '' }}">
                                                    <img src="{{ asset($zombieEntity->getSprite()) }}"
                                                         class="zombie-token" alt="Garrador" />
                                                </div>
                                            @else
                                                <img src="{{ asset($zombieEntity->getSprite()) }}"
                                                     class="zombie-token w-12 h-12 drop-shadow-xl filter brightness-105 contrast-105 {{ $zombieEntity->isInteracting ? 'animate-bounce' : '' }}" alt="Zumbi" />
                                            @endif
                                            
                                            @if ($zombieEntity->isDying)
                                                <div class="eliminated-text">ELIMINADO!</div>
                                            @endif

                                            @if ($zombieEntity->isInteracting)
                                                <div class="absolute -top-10 left-1/2 -translate-x-1/2 z-30">
                                                    <div class="relative bg-red-600 text-white text-[10px] px-2 py-1 rounded-md font-black animate-bounce uppercase shadow-xl border-2 border-white whitespace-nowrap">
                                                        GRRRR!
                                                        <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-red-600 rotate-45 border-r-2 border-b-2 border-white"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endfor
                    @endfor
                    @php
                        $playerEntity = \App\Game\Entities\Player::fromArray($gameState['player'] ?? []);
                        $playerWalkSprites = $playerEntity->getWalkSprites();
                    @endphp
                    <div
                        wire:key="player-board-token"
                        class="player-board-token player-animated {{ $playerIsWalking ? 'player-walking' : 'player-idle' }} sprite-grounded"
                        x-bind:class="{ 'tutorial-anchor-highlight tutorial-anchor-token': tutorialFocusIs('board') }"
                        data-tutorial-label="Sobrevivente"
                        style="--player-x: {{ (int) ($player['x'] ?? 0) }}; --player-y: {{ (int) ($player['y'] ?? 0) }};"
                    >
                        <div class="player-sprite-stack drop-shadow-2xl filter brightness-110 contrast-105">
                            <img src="{{ asset($playerEntity->getIdleSprite()) }}"
                                 class="player-token" alt="Herói" />

                            @if ($playerIsWalking)
                                <span wire:key="player-walk-frames-{{ $playerAnimationTick }}" class="player-walk-frames" aria-hidden="true">
                                    <img src="{{ asset($playerWalkSprites[0]) }}"
                                         class="player-token player-walk-frame-one" alt="" />
                                    <img src="{{ asset($playerWalkSprites[1]) }}"
                                         class="player-token player-walk-frame-two" alt="" />
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- COMANDOS RAPIDOS MOBILE --}}
            <div class="mobile-command-dock" aria-label="Comandos rapidos">
                @foreach ($commandDefs as $cmd)
                    <button
                        type="button"
                        data-snippet="{{ $cmd['snippet'] }}"
                        @click="insertCommand($el.dataset.snippet)"
                        class="mobile-command-button"
                        title="{{ $cmd['label'] }}"
                        aria-label="{{ $cmd['label'] }}"
                    >
                        {!! UIConfig::getIcon($cmd['icon'], 'w-6 h-6') !!}
                    </button>
                @endforeach
                <button
                    type="button"
                    @click="undoLastCommand()"
                    class="mobile-command-button"
                    title="Desfazer ultimo comando"
                    aria-label="Desfazer ultimo comando"
                >
                    {!! UIConfig::getIcon('undo', 'w-6 h-6') !!}
                </button>
            </div>

            {{-- TERMINAL DE COMUNICAÇÃO (LOGS) --}}
            <div
                class="radio-terminal board-telemetry"
                x-bind:class="{ 'tutorial-anchor-highlight': tutorialFocusIs('feedback') }"
                data-tutorial-label="Log"
            >
                <div class="radio-terminal-header">
                    <div class="radio-terminal-title">
                        <div class="radio-terminal-icon">
                            {!! UIConfig::getIcon('info', 'w-5 h-5') !!}
                        </div>
                        <div class="min-w-0">
                            <div class="radio-terminal-kicker">Log da fase</div>
                            <h3 class="radio-terminal-heading">O que aconteceu</h3>
                        </div>
                    </div>
                    <div class="radio-terminal-status">Rodando</div>
                    <h3 class="text-xs font-black text-emerald-400 uppercase tracking-[0.2em]">Historico de comandos</h3>
                </div>
                @php $logEntries = array_reverse($gameState['log'] ?? []); @endphp
                <div class="radio-log-list scrollbar-thin">
                    @forelse ($logEntries as $entry)
                        <div class="radio-log-entry">
                            <span class="radio-log-index">LOG {{ sprintf('%02d', count($logEntries) - $loop->index) }}</span>
                            <span class="radio-log-text">{{ $entry }}</span>
                        </div>
                    @empty
                        <div class="radio-log-empty">Execute o codigo para ver o resultado aqui.</div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- COLUNA DIREITA: EDITOR E REFERÊNCIA --}}
        <div class="game-control-area flex flex-col gap-5 md:gap-8 flex-1 min-w-0 w-full">

            {{-- TERMINAL INDUSTRIAL (EDITOR DE CÓDIGO) --}}
            <div
                class="industrial-terminal-container order-2 lg:order-none"
                x-bind:class="{ 'tutorial-anchor-highlight': tutorialFocusIs('editor') }"
                data-tutorial-label="Editor"
            >
                
                {{-- Rebites Decorativos --}}
                <div class="rivet" style="top: 8px; left: 8px;"></div>
                <div class="rivet" style="top: 8px; right: 8px;"></div>
                <div class="rivet" style="bottom: 8px; left: 8px;"></div>
                <div class="rivet" style="bottom: 8px; right: 8px;"></div>

                {{-- Header do Painel --}}
                <div class="industrial-terminal-header">
                    <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                        <div class="p-2.5 bg-emerald-500/10 rounded border border-emerald-500/30 shadow-[0_0_15px_rgba(16,185,129,0.1)]">
                            {!! UIConfig::getIcon('terminal', 'w-6 h-6 text-emerald-400') !!}
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-100 uppercase tracking-[0.12em] sm:tracking-[0.2em]">Editor de codigo</h3>
                            <p class="text-[9px] text-emerald-500/70 font-bold uppercase tracking-wide sm:tracking-widest">Escreva comandos para mover Leon</p>
                        </div>
                    </div>
                    @if ($gameState['isRunning'] ?? false)
                        <div class="flex items-center gap-2 px-4 py-1.5 bg-emerald-500/10 rounded-full border border-emerald-500/40 backdrop-blur-sm">
                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.8)]"></div>
                            <span class="text-[10px] font-black text-emerald-400 uppercase tracking-widest">Codigo rodando</span>
                        </div>
                    @endif
                </div>

                {{-- Área de Código (Visor Tático) --}}
                <div class="industrial-terminal-screen" @dragover.prevent @drop.prevent="onDrop()">
                    
                    {{-- Números de Linha --}}
                    <div class="industrial-terminal-numbers">
                        @foreach (explode("\n", $commands ?: ' ') as $i => $line)
                            @php $lineNum = $i + 1; @endphp
                            <div class="leading-[32px] {{ $currentLine === $lineNum ? 'text-emerald-500 font-black' : '' }}">
                                {{ sprintf('%02d', $lineNum) }}
                            </div>
                        @endforeach
                    </div>

                    {{-- Textarea --}}
                    <div class="relative flex-1 group">
                        @if ($currentLine > 0)
                            <div class="tactical-highlight transition-all"
                                 style="top: {{ ($currentLine - 1) * 32 + 20 }}px; height: 32px;"></div>
                        @endif

                        <textarea
                            x-ref="commandEditor"
                            wire:model.live.debounce.300ms="commands"
                            class="industrial-terminal-textarea"
                            spellcheck="false" autocomplete="off" autocorrect="off" autocapitalize="off"
                            placeholder="// Escreva a rota de Leon...&#10;// Ex: hero.moveRight()"
                            aria-autocomplete="list"
                            aria-controls="command-autocomplete-list"
                            :aria-expanded="autocompleteOpen"
                            @input="updateAutocomplete($el); refreshCommandState()"
                            @focus="updateAutocomplete($el)"
                            @blur="setTimeout(() => autocompleteOpen = false, 150)"
                            @click="updateAutocomplete($el)"
                            @keyup="updateAutocomplete($el)"
                            @scroll="positionAutocomplete($el)"
                        ></textarea>

                        {{-- wire:ignore: o autocomplete e 100% client-side (Alpine). Sem isso, o
                             x-html dos icones entra em conflito com o morph do Livewire que roda a
                             cada tecla (wire:model.live) e acaba derrubando a classe is-focus-mode
                             da raiz, fazendo o jogo sair do modo foco ao digitar. --}}
                        <div
                            wire:ignore
                            x-cloak
                            x-show="autocompleteOpen"
                            x-ref="autocompleteList"
                            id="command-autocomplete-list"
                            role="listbox"
                            x-transition.opacity.duration.120ms
                            class="command-autocomplete"
                            :style="`top: ${autocompleteTop}px; left: ${autocompleteLeft}px;`"
                        >
                            <div class="command-autocomplete-scroll scrollbar-thin">
                                <template x-for="(suggestion, index) in autocompleteMatches" :key="suggestion.insert">
                                    <button
                                        type="button"
                                        role="option"
                                        class="command-autocomplete-option"
                                        :aria-selected="index === autocompleteSelected"
                                        :class="{ 'is-active': index === autocompleteSelected }"
                                        @mouseenter="autocompleteSelected = index"
                                        @mousedown.prevent="acceptAutocomplete($refs.commandEditor, suggestion)"
                                    >
                                        <span class="command-autocomplete-icon" x-html="tutorialIconSvg(suggestion.icon)"></span>
                                        <span class="command-autocomplete-text">
                                            <span class="command-autocomplete-label" x-text="suggestion.label"></span>
                                            <span class="command-autocomplete-hint" x-text="suggestion.hint"></span>
                                        </span>
                                    </button>
                                </template>
                            </div>
                            <div class="command-autocomplete-footer">
                                <span>Clique em um comando para inseri-lo no terminal</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Mensagem de Feedback --}}
                @if (! empty($gameState['message']))
                    @php $msgType = $gameState['messageType'] ?? 'info'; @endphp
                    <div class="mt-4 px-6 py-3 rounded-lg border text-sm font-bold flex items-center gap-3 animate-pulse
                        {{ $msgType === 'error'   ? 'alert-error' : '' }}
                        {{ $msgType === 'success' ? 'alert-success' : '' }}
                        {{ $msgType === 'info'    ? 'alert-info' : '' }}
                    ">
                        @if ($msgType === 'error')
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        @elseif ($msgType === 'success')
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        @endif
                        {{ $gameState['message'] }}
                    </div>
                @endif

                {{-- Botões de Ação Industriais --}}
                <div class="game-action-row flex gap-4 mt-5">
                    <button
                        type="button"
                        @click="runEditorCommands()"
                        wire:loading.attr="disabled"
                        x-bind:disabled="!hasCommands"
                        class="industrial-btn-primary flex-[2] flex items-center justify-center gap-3"
                        x-bind:class="{ 'tutorial-anchor-highlight': tutorialFocusIs('run'), 'is-disabled': !hasCommands }"
                        x-bind:title="hasCommands ? '' : 'Use ao menos um comando no terminal para executar'"
                        data-tutorial-label="Executar"
                    >
                        <div wire:loading.remove wire:target="runCommands" class="flex items-center gap-3">
                            {!! UIConfig::getIcon('play', 'w-6 h-6') !!}
                            EXECUTAR CODIGO
                        </div>
                        <div wire:loading wire:target="runCommands" class="flex items-center gap-3">
                            <div class="w-5 h-5 border-4 border-white/30 border-t-white rounded-full animate-spin"></div>
                            RODANDO...
                        </div>
                    </button>

                    <button
                        type="button"
                        @click="undoLastCommand()"
                        class="industrial-btn-undo desktop-undo-button hidden md:flex flex-none items-center justify-center gap-2"
                        title="Desfazer ultimo comando"
                        aria-label="Desfazer ultimo comando"
                    >
                        {!! UIConfig::getIcon('undo', 'w-5 h-5') !!}
                        <span>DESFAZER</span>
                    </button>

                    <button
                        wire:click="resetLevel"
                        class="industrial-btn-secondary flex-1 flex items-center justify-center gap-2"
                        title="Reiniciar fase"
                    >
                        {!! UIConfig::getIcon('reset', 'w-5 h-5') !!}
                        REINICIAR
                    </button>

                    @if ($gameState['win'] ?? false)
                        <button
                            wire:click="nextLevel"
                            class="industrial-btn-primary flex-1 flex items-center justify-center gap-2"
                            style="background-color: #0f172a !important; box-shadow: 0 5px 0 #000, 0 8px 15px rgba(0,0,0,0.4) !important;"
                        >
                            {!! UIConfig::getIcon('next', 'w-5 h-5') !!}
                            {{ $this->getNextLevelActionShortLabel() }}
                        </button>
                    @endif
                </div>

                <div class="focus-command-strip" aria-label="Comandos rapidos do modo foco">
                    <div class="focus-command-strip-title">Comandos rapidos</div>
                    <div class="focus-command-grid">
                        @foreach ($commandDefs as $cmd)
                            <button
                                type="button"
                                data-snippet="{{ $cmd['snippet'] }}"
                                @click="insertCommand($el.dataset.snippet)"
                                class="focus-command-button"
                                title="{{ $cmd['label'] }}"
                                aria-label="{{ $cmd['label'] }}"
                            >
                                {!! UIConfig::getIcon($cmd['icon'], 'w-5 h-5') !!}
                                <span class="focus-command-label">{{ $cmd['label'] }}</span>
                            </button>
                        @endforeach

                        <button
                            type="button"
                            @click="undoLastCommand()"
                            class="focus-command-button"
                            title="Desfazer ultimo comando"
                            aria-label="Desfazer ultimo comando"
                        >
                            {!! UIConfig::getIcon('undo', 'w-5 h-5') !!}
                            <span class="focus-command-label">Desfazer</span>
                        </button>
                    </div>
                </div>

                <div
                    class="tactical-manual command-panel-reference order-1 lg:order-none"
                    x-bind:class="{ 'tutorial-anchor-highlight': tutorialFocusIs('commands') }"
                    data-tutorial-label="Comandos"
                >

                <button
                    wire:click="toggleReference"
                    class="tactical-manual-header w-full flex items-center justify-between px-6 py-4 hover:bg-slate-800/50 transition-colors group"
                >
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-slate-800/50 text-slate-400 rounded group-hover:bg-emerald-500/20 group-hover:text-emerald-400 transition-colors">
                            {!! UIConfig::getIcon('book', 'w-5 h-5') !!}
                        </div>
                        <span class="command-manual-title font-black text-slate-200 text-sm tracking-tight">COMANDOS</span>
                    </div>
                    <div class="text-slate-500 group-hover:text-emerald-400 transition-colors">
                        {!! UIConfig::getIcon($showReference ? 'x' : 'next', 'w-5 h-5 ' . ($showReference ? '' : 'rotate-90')) !!}
                    </div>
                </button>

                @if ($showReference)
                    <div class="command-reference-body p-4 sm:p-6 bg-black/30 animate-slide-down border-t border-slate-700">
                        <p class="command-reference-hint text-xs text-slate-400 mb-6 font-medium leading-relaxed">
                            Toque em um comando para inserir no editor. No desktop, tambem e possivel arrastar.
                        </p>

                        <div class="mobile-command-list grid grid-cols-1 sm:grid-cols-2 gap-4">

                            @foreach ($commandDefs as $cmd)
                                <button
                                    data-snippet="{{ $cmd['snippet'] }}"
                                    @click="insertCommand($el.dataset.snippet)"
                                    draggable="true"
                                    @dragstart="onDragStart($el.dataset.snippet)"
                                    class="tactical-command-card flex items-center gap-4 p-4 text-left group active:scale-[0.98]"
                                >
                                    <div class="command-card-icon p-3 bg-slate-800/50 text-slate-400 rounded group-hover:bg-emerald-500/30 group-hover:text-emerald-400 transition-colors shadow-sm">
                                        {!! UIConfig::getIcon($cmd['icon'], 'w-6 h-6') !!}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="command-card-title font-black text-slate-200">{{ $cmd['label'] }}</div>
                                        <div class="command-card-snippet text-slate-500 font-mono truncate">{{ $cmd['snippet'] }}</div>
                                    </div>
                                </button>
                            @endforeach

                        </div>

                    </div>
                @endif

                </div>

                @if (! empty($codeFeedback))
                    <div
                        x-ref="learningFeedback"
                        data-context-hint-panel
                        class="learning-feedback mt-4 p-4"
                        x-bind:class="{
                            'tutorial-anchor-highlight': tutorialFocusIs('feedback'),
                            'is-context-hint-spotlight': hintSpotlight
                        }"
                        data-tutorial-label="Feedback"
                    >
                        <div class="mb-3 flex items-center gap-2">
                            <div class="learning-feedback-icon learning-feedback-tip">
                                {!! UIConfig::getIcon('light', 'w-5 h-5') !!}
                            </div>
                            <div>
                                <div class="text-[10px] font-black uppercase tracking-widest text-blue-300">Tutor de comandos</div>
                                <div class="text-sm font-black text-slate-100">Dicas para melhorar sua solucao</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            @foreach ($codeFeedback as $feedback)
                                @php
                                    $feedbackType = $feedback['type'] ?? 'tip';
                                    $feedbackClass = $feedbackType === 'tip' ? 'info' : $feedbackType;
                                    $feedbackIcon = match ($feedbackClass) {
                                        'optimization' => 'light',
                                        'hint' => 'light',
                                        'success' => 'check',
                                        'warning', 'error' => 'info',
                                        default => 'light',
                                    };
                                    $suggestion = $feedback['suggestion'] ?? [];
                                @endphp
                                <div class="learning-feedback-item learning-feedback-{{ $feedbackClass }}">
                                    <div class="learning-feedback-icon learning-feedback-{{ $feedbackClass }}">
                                        {!! UIConfig::getIcon($feedbackIcon, 'w-4 h-4') !!}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded border border-current/30 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider">
                                                {{ $feedback['category'] ?? 'Dica' }}
                                            </span>
                                            <h4 class="text-sm font-black text-slate-100">{{ $feedback['title'] ?? 'Dica' }}</h4>
                                            @if (! empty($feedback['line']))
                                                <span class="rounded border border-slate-600 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-slate-400">
                                                    Linha {{ $feedback['line'] }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="mt-1 text-xs font-bold leading-relaxed text-slate-300">{{ $feedback['message'] ?? '' }}</p>
                                        @if (! empty($suggestion))
                                            <div class="learning-feedback-suggestion">
                                                <div class="text-[10px] font-black uppercase tracking-widest text-slate-500">{{ $suggestion['label'] ?? 'Sugestao' }}</div>
                                                @if (! empty($suggestion['from']))
                                                    <code class="learning-code-block is-from">{{ $suggestion['from'] }}</code>
                                                @endif
                                                @if (! empty($suggestion['to']))
                                                    <code class="learning-code-block">{{ $suggestion['to'] }}</code>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

                {{-- SELETOR DE FASES E BOTAO VOLTAR --}}
            <div class="level-actions order-3 lg:order-none flex items-center gap-4">
                <div class="flex items-center justify-between sm:justify-start gap-4 bg-gradient-to-r from-slate-900 to-slate-950 p-4 rounded-lg border border-slate-700 shadow-lg">
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest px-2">Fases</span>
                    <div class="flex gap-2">
                        @foreach ($this->getLevelNumbers() as $lvl)
                            <button
                                wire:click="loadLevel({{ $lvl }})"
                                class="w-10 h-10 flex items-center justify-center text-sm font-black rounded-lg border-2 transition-all
                                    {{ ($gameState['level'] ?? 1) === $lvl
                                        ? 'bg-emerald-600 border-emerald-500 text-white shadow-lg shadow-emerald-500/50 scale-110'
                                        : 'bg-slate-800 border-slate-700 text-slate-400 hover:border-emerald-500/50 hover:text-emerald-400' }}"
                            >
                                {{ $lvl }}
                            </button>
                        @endforeach
                    </div>
                </div>
                
                <button
                    wire:click="backToMap"
                    class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-700 hover:to-slate-800 text-slate-300 hover:text-white font-black text-sm rounded-lg border border-slate-700 hover:border-slate-600 transition-all shadow-lg"
                    title="Voltar para o mapa de fases"
                >
                    {!! UIConfig::getIcon('arrow-l', 'w-5 h-5') !!}
                    <span class="hidden sm:inline">MAPA</span>
                </button>
            </div>

        </div>

        <div
            class="radio-terminal shell-telemetry"
            x-bind:class="{ 'tutorial-anchor-highlight': tutorialFocusIs('feedback') }"
            data-tutorial-label="Log"
        >
            <div class="radio-terminal-header">
                <div class="radio-terminal-title">
                    <div class="radio-terminal-icon">
                        {!! UIConfig::getIcon('info', 'w-5 h-5') !!}
                    </div>
                    <div class="min-w-0">
                        <div class="radio-terminal-kicker">Log da fase</div>
                        <h3 class="radio-terminal-heading">O que aconteceu</h3>
                    </div>
                </div>
                <div class="radio-terminal-status">Rodando</div>
            </div>
            @php $shellLogEntries = array_reverse($gameState['log'] ?? []); @endphp
            <div class="radio-log-list scrollbar-thin">
                @forelse ($shellLogEntries as $entry)
                    <div class="radio-log-entry">
                        <span class="radio-log-index">LOG {{ sprintf('%02d', count($shellLogEntries) - $loop->index) }}</span>
                        <span class="radio-log-text">{{ $entry }}</span>
                    </div>
                @empty
                    <div class="radio-log-empty">Execute o codigo para ver o resultado aqui.</div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Game Loop --}}
    @if ($gameState['isRunning'] ?? false)
        <div wire:poll.400ms="step"></div>
    @endif

</div>
