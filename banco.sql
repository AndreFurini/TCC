use projeto_ecommerce;

DROP TABLE users;

USE fluxo_ops;

-- ==============================
-- TABELA: users (NÃO MEXER)
-- ==============================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- ==============================
-- TABELA: setoresUSE fluxo_ops;

DROP TABLE users;

-- ==============================
-- TABELA: users (NÃO MEXER)
-- ==============================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- ==============================
-- TABELA: setores
-- ==============================
CREATE TABLE setores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- ==============================
-- TABELA: ordens_servico
-- ==============================
CREATE TABLE ordens_servico (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT NOT NULL,

    status ENUM('ABERTA', 'EM_ANDAMENTO', 'FINALIZADA')
        NOT NULL DEFAULT 'ABERTA',

    setor_id BIGINT UNSIGNED NOT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (setor_id) REFERENCES setores(id)
);
-- ==============================
CREATE TABLE setores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- ==============================
-- TABELA: ordens_servico
-- ==============================
CREATE TABLE ordens_servico (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT NOT NULL,

    status ENUM('ABERTA', 'EM_ANDAMENTO', 'FINALIZADA')
        NOT NULL DEFAULT 'ABERTA',

    setor_id BIGINT UNSIGNED NOT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (setor_id) REFERENCES setores(id)
);