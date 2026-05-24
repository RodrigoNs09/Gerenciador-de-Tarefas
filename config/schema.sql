CREATE DATABASE IF NOT EXISTS gerenciador_tarefas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE gerenciador_tarefas;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome VARCHAR(80) NOT NULL,
    cor VARCHAR(7) DEFAULT '#6366f1',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tarefas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    categoria_id INT,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT,
    prioridade ENUM('baixa', 'media', 'alta') DEFAULT 'media',
    status ENUM('pendente', 'em_andamento', 'concluida') DEFAULT 'pendente',
    prazo DATE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
);

-- Usuário de teste: senha = 123456
INSERT INTO usuarios (nome, email, senha) VALUES
('Dev Teste', 'dev@teste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
