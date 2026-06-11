-- ==============================
-- BANCO: fluxo_ops
-- ==============================
CREATE DATABASE IF NOT EXISTS fluxo_ops CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fluxo_ops;

-- ==============================
-- TABELA: empresas
-- ==============================
CREATE TABLE IF NOT EXISTS empresas (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(255) NOT NULL,
    cnpj            VARCHAR(20)  NULL,
    codigo_empresa  VARCHAR(10)  NOT NULL UNIQUE,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL
);

-- ==============================
-- TABELA: setores
-- ==============================
CREATE TABLE IF NOT EXISTS setores (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id      BIGINT UNSIGNED NOT NULL,
    nome            VARCHAR(255) NOT NULL,
    responsavel_id  BIGINT UNSIGNED NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
);

-- ==============================
-- TABELA: users
-- ==============================
CREATE TABLE IF NOT EXISTS users (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id        BIGINT UNSIGNED NOT NULL,
    name              VARCHAR(255) NOT NULL,
    username          VARCHAR(50)  NOT NULL UNIQUE,
    email             VARCHAR(255) NOT NULL UNIQUE,
    password          VARCHAR(255) NOT NULL,
    role              ENUM('admin','coordenador','executor','colaborador') NOT NULL DEFAULT 'colaborador',
    setor_id          BIGINT UNSIGNED NULL,
    remember_token    VARCHAR(100) NULL,
    email_verified_at TIMESTAMP NULL,
    created_at        TIMESTAMP NULL,
    updated_at        TIMESTAMP NULL,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (setor_id)   REFERENCES setores(id)  ON DELETE SET NULL
);

-- Agora que users existe, adiciona a FK de responsavel_id em setores
ALTER TABLE setores
    ADD CONSTRAINT fk_setor_responsavel
    FOREIGN KEY (responsavel_id) REFERENCES users(id) ON DELETE SET NULL;

-- ==============================
-- TABELA: ordens_servico
-- ==============================
CREATE TABLE IF NOT EXISTS ordens_servico (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id      BIGINT UNSIGNED NOT NULL,
    titulo          VARCHAR(255) NOT NULL,
    descricao       TEXT NOT NULL,
    status          ENUM('ABERTA','EM_ANDAMENTO','FINALIZADA','CANCELADA') NOT NULL DEFAULT 'ABERTA',
    urgencia        ENUM('BAIXA','MEDIA','ALTA','URGENTE') NOT NULL DEFAULT 'BAIXA',
    setor_id        BIGINT UNSIGNED NOT NULL,
    executor_id     BIGINT UNSIGNED NULL,
    criado_por      BIGINT UNSIGNED NOT NULL,
    atualizado_por  BIGINT UNSIGNED NULL,
    devolutiva      TEXT NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    FOREIGN KEY (empresa_id)     REFERENCES empresas(id)      ON DELETE CASCADE,
    FOREIGN KEY (setor_id)       REFERENCES setores(id)       ON DELETE RESTRICT,
    FOREIGN KEY (executor_id)    REFERENCES users(id)         ON DELETE SET NULL,
    FOREIGN KEY (criado_por)     REFERENCES users(id)         ON DELETE RESTRICT,
    FOREIGN KEY (atualizado_por) REFERENCES users(id)         ON DELETE SET NULL
);

-- ==============================
-- TABELA: sessions (necessária para SESSION_DRIVER=file no Railway)
-- ==============================
CREATE TABLE IF NOT EXISTS sessions (
    id            VARCHAR(255) NOT NULL PRIMARY KEY,
    user_id       BIGINT UNSIGNED NULL,
    ip_address    VARCHAR(45) NULL,
    user_agent    TEXT NULL,
    payload       LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX sessions_user_id_index (user_id),
    INDEX sessions_last_activity_index (last_activity)
);
