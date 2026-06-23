@php use App\Game\Config\UIConfig; @endphp
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CodeSurvivor | Start</title>
        <link rel="icon" type="image/png" href="{{ asset('icons/codeSurvivorGreenIcon.png') }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@500;600;700&family=Inter:wght@600;700;800;900&display=swap" rel="stylesheet">

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <style>
            :root {
                color-scheme: dark;
                --bg: #020304;
                --green: #37ff86;
                --green-dark: #075f31;
                --green-soft: rgba(55, 255, 134, 0.22);
                --amber: #ffd166;
                --line: rgba(255, 255, 255, 0.18);
                --panel: rgba(5, 7, 11, 0.78);
                --text: #f8fafc;
                --muted: #a9b3c3;
            }

            * {
                box-sizing: border-box;
            }

            html,
            body {
                min-width: 0;
                min-height: 100%;
                margin: 0;
                overflow-x: hidden;
                background: var(--bg);
            }

            body {
                min-height: 100dvh;
                font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
                color: var(--text);
            }

            a {
                color: inherit;
            }

            .code-font {
                font-family: 'Fira Code', ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            }

            .screen-bg {
                position: fixed;
                inset: 0;
                z-index: 0;
                overflow: hidden;
                background: #05070a;
            }

            .screen-bg::before {
                content: "";
                position: absolute;
                inset: -2%;
                background:
                    url('/mapas/new_campaign_map.png') center / cover no-repeat;
                filter: brightness(0.68) contrast(1.18) saturate(0.9);
                transform: scale(1.03);
            }

            .screen-bg::after {
                content: "";
                position: absolute;
                inset: 0;
                background:
                    linear-gradient(90deg, rgba(2, 3, 4, 0.82), rgba(2, 3, 4, 0.3) 45%, rgba(2, 3, 4, 0.84)),
                    linear-gradient(180deg, rgba(2, 3, 4, 0.58), rgba(2, 3, 4, 0.08) 42%, rgba(2, 3, 4, 0.86)),
                    repeating-linear-gradient(0deg, rgba(255, 255, 255, 0.03) 0 1px, transparent 1px 5px);
                pointer-events: none;
            }

            .fog {
                position: fixed;
                inset: -10%;
                z-index: 1;
                pointer-events: none;
                background:
                    url('/efeitos/smoke_effect.png') 8% 72% / 360px auto no-repeat,
                    url('/efeitos/smoke_effect.png') 84% 20% / 430px auto no-repeat,
                    url('/efeitos/smoke_effect.png') 52% 102% / 520px auto no-repeat;
                opacity: 0.28;
                filter: blur(1px) grayscale(0.5);
                animation: fog-drift 14s ease-in-out infinite alternate;
            }

            @keyframes fog-drift {
                from {
                    transform: translate3d(-14px, 8px, 0) scale(1);
                }
                to {
                    transform: translate3d(16px, -10px, 0) scale(1.03);
                }
            }

            .start-screen {
                position: relative;
                z-index: 2;
                width: min(100%, 1320px);
                min-height: 100dvh;
                margin: 0 auto;
                padding: clamp(14px, 2vw, 28px);
                display: grid;
                grid-template-rows: auto minmax(0, 1fr) auto;
                gap: 18px;
            }

            .top-line {
                display: flex;
                min-width: 0;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
            }

            .seal {
                display: flex;
                min-width: 0;
                align-items: center;
                gap: 10px;
                text-decoration: none;
            }

            .seal img {
                width: 42px;
                height: 42px;
                border: 1px solid rgba(55, 255, 134, 0.54);
                border-radius: 6px;
                object-fit: cover;
                box-shadow: 0 0 22px rgba(55, 255, 134, 0.18);
            }

            .seal strong {
                display: block;
                color: #fff;
                font-size: 1rem;
                font-weight: 900;
                line-height: 1;
            }

            .seal strong span {
                color: var(--green);
            }

            .seal small {
                display: block;
                margin-top: 5px;
                color: var(--amber);
                font-size: 0.62rem;
                font-weight: 800;
                letter-spacing: 0.14em;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .top-badges {
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-end;
                gap: 8px;
            }

            .badge {
                border: 1px solid rgba(255, 255, 255, 0.16);
                border-radius: 4px;
                padding: 8px 10px;
                color: #dbeafe;
                background: rgba(5, 7, 11, 0.58);
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
                font-size: 0.64rem;
                font-weight: 900;
                letter-spacing: 0.13em;
                text-transform: uppercase;
                backdrop-filter: blur(8px);
                white-space: nowrap;
            }

            .badge.danger {
                border-color: rgba(255, 209, 102, 0.5);
                color: #fde68a;
            }

            .center-stage {
                display: grid;
                place-items: center;
                text-align: center;
            }

            .title-block {
                width: min(100%, 820px);
                margin-top: clamp(0px, 2vh, 18px);
            }

            .logo {
                width: min(100%, 600px);
                margin: 0 auto;
                filter:
                    drop-shadow(0 28px 42px rgba(0, 0, 0, 0.82))
                    drop-shadow(0 0 30px rgba(55, 255, 134, 0.2));
            }

            .logo img {
                display: block;
                width: 100%;
                height: auto;
            }

            .subtitle {
                width: min(100%, 680px);
                margin: 14px auto 0;
                border-top: 1px solid rgba(55, 255, 134, 0.34);
                border-bottom: 1px solid rgba(55, 255, 134, 0.22);
                padding: 11px 14px;
                color: #d1fae5;
                background: linear-gradient(90deg, transparent, rgba(5, 7, 11, 0.66), transparent);
                font-size: clamp(0.82rem, 1.7vw, 1.08rem);
                font-weight: 900;
                letter-spacing: 0.14em;
                text-transform: uppercase;
                text-shadow: 0 0 18px rgba(55, 255, 134, 0.32), 0 10px 24px rgba(0, 0, 0, 0.9);
            }

            .main-menu {
                width: min(100%, 480px);
                margin: clamp(14px, 2vh, 20px) auto 0;
                display: grid;
                gap: 9px;
            }

            .menu-item {
                min-height: 54px;
                display: grid;
                grid-template-columns: 2rem minmax(0, 1fr) 2rem;
                align-items: center;
                gap: 10px;
                border: 1px solid rgba(255, 255, 255, 0.17);
                border-radius: 4px;
                padding: 12px 14px;
                color: #f8fafc;
                background:
                    linear-gradient(90deg, rgba(5, 7, 11, 0.86), rgba(18, 25, 35, 0.56)),
                    url('/interface/panel_tactical.png') center / cover;
                box-shadow:
                    0 12px 34px rgba(0, 0, 0, 0.32),
                    inset 0 1px 0 rgba(255, 255, 255, 0.08);
                text-decoration: none;
                transition: transform 0.14s ease, border-color 0.14s ease, filter 0.14s ease;
            }

            .menu-item strong {
                min-width: 0;
                font-size: 0.96rem;
                font-weight: 900;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                overflow-wrap: anywhere;
            }

            .menu-item svg {
                width: 1.15rem;
                height: 1.15rem;
                justify-self: center;
            }

            .menu-item.primary {
                border-color: rgba(174, 255, 210, 0.72);
                color: #021408;
                background:
                    linear-gradient(180deg, rgba(255, 255, 255, 0.28), transparent 45%),
                    linear-gradient(90deg, #1be776, #37ff86 46%, #10a854);
                box-shadow:
                    0 6px 0 var(--green-dark),
                    0 22px 42px rgba(55, 255, 134, 0.2),
                    inset 0 1px 0 rgba(255, 255, 255, 0.16);
                text-shadow: 0 1px 0 rgba(255, 255, 255, 0.28);
            }

            .menu-item:hover {
                transform: translateY(-2px);
                border-color: rgba(55, 255, 134, 0.56);
                filter: brightness(1.08);
            }

            .menu-item:active {
                transform: translateY(3px);
            }

            .press-start {
                margin: 16px 0 0;
                color: var(--green);
                font-size: 0.72rem;
                font-weight: 900;
                letter-spacing: 0.18em;
                text-transform: uppercase;
                text-shadow: 0 0 16px rgba(55, 255, 134, 0.56);
                animation: blink 1.28s steps(2, end) infinite;
            }

            @keyframes blink {
                50% {
                    opacity: 0.28;
                }
            }

            .chapter-rail {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 10px;
            }

            .chapter {
                min-width: 0;
                display: grid;
                grid-template-columns: 74px minmax(0, 1fr);
                gap: 10px;
                align-items: center;
                overflow: hidden;
                border: 1px solid rgba(255, 255, 255, 0.16);
                border-radius: 6px;
                padding: 8px;
                background: rgba(5, 7, 11, 0.7);
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
                backdrop-filter: blur(10px);
            }

            .chapter img {
                width: 74px;
                height: 54px;
                border-radius: 4px;
                object-fit: cover;
                filter: contrast(1.08) saturate(0.9);
            }

            .chapter span {
                display: block;
                color: var(--amber);
                font-size: 0.6rem;
                font-weight: 900;
                letter-spacing: 0.13em;
                text-transform: uppercase;
            }

            .chapter strong {
                display: block;
                margin-top: 4px;
                color: #fff;
                font-size: 0.82rem;
                font-weight: 900;
                line-height: 1.18;
            }

            .chapter.active {
                border-color: rgba(55, 255, 134, 0.58);
                box-shadow:
                    inset 4px 0 0 rgba(55, 255, 134, 0.9),
                    inset 0 1px 0 rgba(255, 255, 255, 0.05);
            }

            @media (max-width: 900px) {
                .start-screen {
                    min-height: auto;
                }

                .chapter-rail {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 640px) {
                .start-screen {
                    padding: 12px;
                    gap: 14px;
                }

                .top-line {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .top-badges {
                    width: 100%;
                    justify-content: flex-start;
                }

                .badge {
                    flex: 1 1 135px;
                    text-align: center;
                }

                .seal small {
                    max-width: 210px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                .title-block {
                    margin-top: 16px;
                }

                .logo {
                    width: min(100%, 390px);
                }

                .subtitle {
                    font-size: 0.74rem;
                    line-height: 1.5;
                    letter-spacing: 0.09em;
                }

                .main-menu {
                    width: 100%;
                }

                .menu-item {
                    grid-template-columns: 1.6rem minmax(0, 1fr) 1.6rem;
                    min-height: 52px;
                }

                .menu-item strong {
                    font-size: 0.86rem;
                    letter-spacing: 0.1em;
                }

                .chapter-rail {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <div class="screen-bg" aria-hidden="true"></div>
        <div class="fog" aria-hidden="true"></div>

        <main class="start-screen">
            <header class="top-line">
                <a href="/" class="seal" aria-label="CodeSurvivor inicio">
                    <img src="{{ asset('icons/codeSurvivorGreenIcon.png') }}" alt="">
                    <span>
                        <strong>Code<span>Survivor</span></strong>
                        <small class="code-font">programe para sobreviver</small>
                    </span>
                </a>

                <div class="top-badges" aria-label="Status">
                    <span class="badge danger">Aprenda jogando</span>
                    <span class="badge">Rotas com codigo</span>
                    <span class="badge">Sobrevivencia 2D</span>
                </div>
            </header>

            <section class="center-stage" aria-label="Menu inicial">
                <div class="title-block">
                    <div class="logo" aria-hidden="true">
                        <img src="{{ asset('icons/codeSurvivorGreen.png') }}" alt="">
                    </div>

                    <p class="subtitle code-font">programe a rota, execute o plano, sobreviva ao caos</p>

                    <nav class="main-menu" aria-label="Menu principal">
                        <a href="/game?level=1" class="menu-item primary">
                            {!! UIConfig::getIcon('play') !!}
                            <strong>Iniciar jogo</strong>
                            {!! UIConfig::getIcon('arrow-r') !!}
                        </a>
                        <a href="/map" class="menu-item">
                            {!! UIConfig::getIcon('compass') !!}
                            <strong>Selecionar fase</strong>
                            {!! UIConfig::getIcon('arrow-r') !!}
                        </a>
                    </nav>

                    <p class="press-start code-font">pressione iniciar jogo</p>
                </div>
            </section>

            <section id="chapters" class="chapter-rail" aria-label="Fases disponiveis">
                <article class="chapter active">
                    <img src="{{ asset('mundo/backgrounds/level_1_hospital_abandonado.png') }}" alt="">
                    <div>
                        <span>Fase 01</span>
                        <strong>Primeira Rota</strong>
                    </div>
                </article>
                <article class="chapter">
                    <img src="{{ asset('mundo/backgrounds/level_2_rua_contaminada.png') }}" alt="">
                    <div>
                        <span>Fase 02</span>
                        <strong>Coleta de Suprimentos</strong>
                    </div>
                </article>
                <article class="chapter">
                    <img src="{{ asset('mundo/previews/setor-radioativo-preview.png') }}" alt="">
                    <div>
                        <span>Fase 03</span>
                        <strong>Primeiro Infectado</strong>
                    </div>
                </article>
                <article class="chapter">
                    <img src="{{ asset('mundo/backgrounds/level_4_corredor_interceptacao.png') }}" alt="">
                    <div>
                        <span>Fase 04</span>
                        <strong>Fuga do Garrador</strong>
                    </div>
                </article>
            </section>
        </main>
    </body>
</html>
