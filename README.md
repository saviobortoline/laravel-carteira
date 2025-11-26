# Financial Portfolio – Sistema de Carteira Digital

Este projeto é um sistema completo de **carteira digital**, permitindo:

- Depósitos
- Transferências entre usuários
- Histórico completo de movimentações
- Reversão de transações
- Painel financeiro em tempo real (AJAX)
- Filtros avançados de extrato
- Uso de Select2 para busca de usuários
- Interface totalmente responsiva (Bootstrap 5)

---

## Tecnologias

- **PHP 8+**
- **Laravel 12**
- **MySQL / MariaDB**
- **Bootstrap 5**
- **jQuery + Select2**
- **Blade Templates**
- **Breeze (Autenticação)**

---

## Estrutura do banco de dados

### Tabelas principais

#### 🔹 users
- `id`
- `name`
- `email`
- `password`

#### 🔹 wallets
- `id`
- `user_id` (FK)
- `balance`

#### 🔹 transactions
- `id`
- `from_user_id`
- `to_user_id`
- `amount`
- `type` (`deposit`, `transfer`)
- `status` (`completed`, `reversed`)
- `created_at`

---

## ⚙️ Instalação

### 1. Clone o projeto
```bash
git clone https://github.com/saviobortoline/laravel-carteira.git

### 2. Acesse a pasta do projeto
cd  C:\local

### 3. Instale o composer
composer install

### 4. rodo o projeto
php artisan serve
