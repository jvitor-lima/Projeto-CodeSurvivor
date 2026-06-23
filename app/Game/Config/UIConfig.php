<?php

namespace App\Game\Config;

/**
 * Configurações de UI/UX do CodeSurvivor.
 * Centraliza a paleta de cores, ícones e padrões visuais.
 */
class UIConfig
{
    // Paleta de Cores (Tailwind Classes)
    public const COLOR_PRIMARY   = 'emerald'; // Ações principais, Sucesso, Vida
    public const COLOR_SECONDARY = 'slate';   // Interface, Texto, Fundo
    public const COLOR_ACCENT    = 'blue';    // Informação, Dicas, Referência
    public const COLOR_DANGER    = 'rose';    // Erros, Zumbis, Morte
    public const COLOR_WARNING   = 'amber';   // Alertas, Atenção

    /**
     * Retorna o SVG do ícone solicitado (Heroicons Outline).
     */
    public static function getIcon(string $name, string $class = 'w-5 h-5'): string
    {
        $icons = [
            'play'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg>',
            'reset'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>',
            'next'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>',
            'target'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>',
            'skull'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>',
            'book'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18c-2.305 0-4.408.867-6 2.292m0-14.25v14.25" /></svg>',
            // 'light'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-3m0 0a3 3 0 1 1 0-6 3 3 0 0 1 0 6Zm0 0v1.5m0 1.5v.75m-3.75-1.5h7.5" /></svg>',
           'light' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'">
    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503-11.995 4.875-1.625A.75.75 0 0 1 21.375 4.34v13.802a.75.75 0 0 1-.513.711l-5.875 1.96a.75.75 0 0 1-.474 0l-5.626-1.875a.75.75 0 0 0-.474 0l-4.875 1.625A.75.75 0 0 1 2.625 19.84V6.037a.75.75 0 0 1 .513-.711l5.875-1.96a.75.75 0 0 1 .474 0l5.626 1.875a.75.75 0 0 0 .474 0Z" />
</svg>',
            'compass' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" /><path stroke-linecap="round" stroke-linejoin="round" d="m15.25 8.75-1.55 4.95-4.95 1.55 1.55-4.95 4.95-1.55Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 12h.01" /></svg>',
            'swords'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.048 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /></svg>',
            
            'attack' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M7.15 17.75 15.9 9c.96-.96 2.22-1.53 3.58-1.62l.74-.05-.05.74c-.09 1.36-.66 2.62-1.62 3.58l-8.75 8.75a1.75 1.75 0 0 1-2.47 0l-.28-.28a1.75 1.75 0 0 1 0-2.47Z" /><path stroke-linecap="round" stroke-linejoin="round" d="m12.7 9.9 1.25-2.4" /><path stroke-linecap="round" stroke-linejoin="round" d="m14.5 11.7 2.45-1.05" /><path stroke-linecap="round" stroke-linejoin="round" d="m10.85 11.7-1.75-1.75" /><path stroke-linecap="round" stroke-linejoin="round" d="m8.15 16.75 2.1 2.1" /><path stroke-linecap="round" stroke-linejoin="round" d="m6.95 17.95 2.1 2.1" /><path stroke-linecap="round" stroke-linejoin="round" d="M5.5 21.25 7 19.75" /></svg>',

            'undo'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25 4.5 9.75 9 5.25" /><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 9.75h9a5.25 5.25 0 0 1 0 10.5H12" /></svg>',
            'arrow-r' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>',
            'arrow-l' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>',
            'arrow-u' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18" /></svg>',
            'arrow-d' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" /></svg>',
            'loop'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>',
            'info'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>',
            'check'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>',
            'x'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>',
            'terminal' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 5.25h15A2.25 2.25 0 0 1 21.75 7.5v9A2.25 2.25 0 0 1 19.5 18.75h-15A2.25 2.25 0 0 1 2.25 16.5v-9A2.25 2.25 0 0 1 4.5 5.25Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5" /><path stroke-linecap="round" stroke-linejoin="round" d="m6.75 12 2.25 2.25-2.25 2.25" /><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 16.5h3" /><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 11.25 18.75 13.125 15.75 15v-3.75Z" /></svg>',
            'bag'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>',
        ];

        return $icons[$name] ?? '';
    }
}
