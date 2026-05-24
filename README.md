# TarefasFlow — Gerenciador de Tarefas

Sistema web de gerenciamento de tarefas com autenticação, categorias e filtros, desenvolvido com PHP puro, MySQL e JavaScript vanilla.

## Funcionalidades

- Autenticação segura com `password_hash` e sessões PHP
- Cadastro e login de usuários
- CRUD completo de tarefas
- Prioridades: Alta, Média, Baixa
- Status: Pendente, Em andamento, Concluída
- Categorias personalizadas com cor
- Filtros por status, prioridade e categoria
- Dashboard com estatísticas em tempo real
- Interface responsiva

## Tecnologias

| Camada     | Tecnologia                     |
|------------|-------------------------------|
| Backend    | PHP 8+                        |
| Banco      | MySQL 8 / MariaDB             |
| Frontend   | HTML5, CSS3, JavaScript       |
| Ambiente   | XAMPP / WAMP / Docker         |

## Como rodar localmente

### Pré-requisitos

- PHP 8.0+
- MySQL 8.0+
- XAMPP, WAMP ou servidor equivalente

### Passo a passo

1. Clone o repositório:
   ```bash
   git clone https://github.com/seu-usuario/gerenciador-tarefas.git
   cd gerenciador-tarefas
   ```

2. Importe o banco de dados:
   ```bash
   mysql -u root -p < config/schema.sql
   ```
   Ou abra o arquivo `config/schema.sql` no phpMyAdmin e execute.

3. Configure a conexão em `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', 'sua_senha');
   define('DB_NAME', 'gerenciador_tarefas');
   ```

4. Coloque o projeto na pasta `htdocs` (XAMPP) ou `www` (WAMP).

5. Acesse `http://localhost/gerenciador-tarefas`

### Usuário de teste

| Campo | Valor          |
|-------|----------------|
| Email | dev@teste.com  |
| Senha | 123456         |

## Estrutura do projeto

```
gerenciador-tarefas/
├── config/
│   ├── database.php      # Conexão com banco
│   ├── auth.php          # Funções de sessão e autenticação
│   └── schema.sql        # Estrutura do banco
├── pages/
│   ├── tarefa_form.php   # Criar e editar tarefas
│   └── tarefa_delete.php # Excluir tarefa
├── assets/
│   ├── css/style.css     # Estilos
│   └── js/main.js        # JavaScript
├── index.php             # Dashboard principal
├── login.php             # Tela de login
├── register.php          # Cadastro de usuário
└── logout.php            # Encerrar sessão
```

## Capturas de tela

> *(Adicione prints aqui após rodar o projeto localmente)*

## Próximas melhorias

- [ ] Drag-and-drop estilo Kanban
- [ ] Notificações de prazo
- [ ] Exportar tarefas em PDF
- [ ] API REST para consumo mobile

## Autor

**Rodrigo Nascimento da Silva**
- GitHub: [@seu-usuario](https://github.com/seu-usuario)
- LinkedIn: [seu-linkedin](https://linkedin.com/in/seu-linkedin)
