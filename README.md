# CodeSurvivor

Jogo web educacional onde o jogador controla um personagem por comandos de código para resolver fases em grid. Desenvolvido com Laravel, Livewire e Tailwind CSS.

## Requisitos

- Docker com o plugin Compose

## Como rodar

```bash
git clone https://github.com/jvitor-lima/Projeto-CodeSurvivor.git codeSurvivor
cd codeSurvivor
docker compose up -d --build
```

Acesse em `http://localhost`.

## Comandos úteis

```bash
docker compose logs -f          # logs em tempo real
docker compose ps               # status dos containers
docker compose down             # parar e remover
docker compose up -d --build    # atualizar após git pull
```

## Rotas

| Rota | Descrição |
|------|-----------|
| `/` | Tela inicial |
| `/map` | Mapa de fases |
| `/game?level=N` | Fase N |
