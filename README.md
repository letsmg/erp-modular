# ERP Zenite - Laravel 11 & Vue 3 (Inertia.js)

Sistema de gestão empresarial (ERP) focado em alta performance, segurança e SEO.

## 🚀 Tecnologias Utilizadas
- **Backend:** Laravel 11 (PHP 8.2+)
- **Frontend:** Vue 3 com Vite e Tailwind CSS
- **Banco de Dados:** PostgreSQL
- **Comunicação:** Inertia.js (Padrão SPA sem complexidade de API externa)
- **Segurança:** Hashing de senhas via **Argon2id** (Configurado para 64MB/2 Threads)

### 🏛️ Estrutura Arquitetural (Inertia vs API Pura)
- Optamos pelo uso de **Controllers Híbridos** (Inertia) em vez de uma pasta `/Api` separada.
- **Motivo:** O Inertia.js elimina a necessidade de rotas de API externas para o funcionamento do Frontend, utilizando o estado compartilhado e garantindo maior segurança via CSRF nativo do Laravel.
- **Vantagem:** Desenvolvimento 2x mais rápido para sistemas de gestão (ERP).

### 🔌 Escalabilidade para Terceiros
- **Status Atual:** Interface via Inertia.js (Consumo interno).
- **Estratégia Futura:** Caso haja necessidade de API pública, a lógica de negócio será extraída para `Services`, permitindo que `Controllers/Api` exponham dados em JSON puro via Laravel Sanctum, mantendo o reaproveitamento de código.

### 📡 Comunicação Front-End / Back-End
- **Inertia.js como Middleware:** Explicado no projeto como a ferramenta que substitui a necessidade de Axios/API Rest para consumo interno, permitindo que o Laravel injete dados diretamente nas props do Vue 3.
- **Reatividade Progressiva:** Implementada busca em tempo real (Debounce Search) nos módulos de fornecedores utilizando `router.get` do Inertia.


## 🛠️ Configurações Implementadas

### 1. Banco de Dados & Performance
- Migrations criadas para `users`, `suppliers` e `products`.
- Estrutura de **Cache** preparada no servidor para reduzir consultas ao banco (PostgreSQL).
- Relacionamento entre Produtos e Fornecedores estabelecido.

### 2. Segurança (Auth)
- Sistema de login manual (sem pacotes prontos como Breeze) para total controle.
- Validação de senha: Mínimo 6 caracteres e confirmação obrigatória.
- Interface de login com recurso de visibilidade de senha (toggle eye icon).

### 3. SEO & Vitrine
- Tabela de produtos inclui campos de `slug`, `seo_title` e `seo_keywords`.
- Geração automática de URL amigável (slug) ao cadastrar novos produtos.

## 📦 Como rodar o projeto
1. Configure o `.env` com suas credenciais do PostgreSQL.
2. Ative as extensões `pdo_pgsql` e `pgsql` no seu `php.ini`.
3. Rode as migrações e seeders:
   ```bash
   php artisan migrate:fresh --seed


