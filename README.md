# GamerOrganizer

App de organização para desenvolvedores de jogos.

## Estrutura

```
gamerorganizer/
├── backend/   # Laravel 11 (API + Auth via Sanctum)
└── frontend/  # Vue 3 + Vite + Tailwind CSS
```

## Iniciar o projeto

### Backend (Laravel)
```bash
cd backend
php artisan serve
# Rodando em http://localhost:8000
```

### Frontend (Vue 3)
```bash
cd frontend
npm run dev
# Rodando em http://localhost:5173
```

## Funcionalidades

### Tasks (Kanban)
- Criar múltiplos boards
- Colunas customizáveis com cores
- Cards com: título, descrição, categoria, prioridade, data limite, labels, anexos
- Drag & drop entre colunas
- Modal detalhado ao clicar no card

### Marketing
- Cadastro de influenciadores, YouTubers, streamers
- Filtros por: tipo, país, idioma, nicho, status
- Campos: contato, métricas (inscritos, views, engajamento), jogos cobertos, notas
- Estatísticas gerais de alcance

### Meus Jogos
- Cadastro de jogos com links de todas as plataformas
- Integração automática com Steam API (busca dados, screenshots, preço, reviews)
- Atualização manual dos dados da Steam
- Modal detalhado com screenshots e métricas

## Stack

**Backend:** PHP 8.3, Laravel 11, SQLite, Laravel Sanctum

**Frontend:** Vue 3 (Composition API), TypeScript, Vite, Tailwind CSS 3, Pinia, Vue Router 4, Axios
