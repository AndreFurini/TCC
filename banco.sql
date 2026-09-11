-- ============================================================
-- BANCO: fluxo_ops  —  CoordenaTask (TCC)
-- ============================================================
-- ATENÇÃO: este arquivo é GERADO a partir das migrations Laravel
-- (database/migrations/). A fonte da verdade do schema são as
-- migrations — rode `php artisan migrate` sempre que possível.
-- Este .sql serve apenas para bootstrap manual (ex.: console do
-- Railway) e deve ser regerado quando as migrations mudarem.
--
-- Regras de integridade relevantes:
--  * users.role / ordens_servico.status / ordens_servico.urgencia => ENUM no banco
--  * users.ativo / setores.ativo => flag de inativação (0 = inativo)
--  * ordens_servico.setor_id e ordens_servico.criado_por => NOT NULL,
--    FK sem ON DELETE (RESTRICT): não é possível excluir setor/usuário
--    que tenha OS vinculada — apenas inativar.
-- ============================================================

CREATE DATABASE IF NOT EXISTS fluxo_ops CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fluxo_ops;

-- ==============================
-- TABELA: empresas
-- ==============================
CREATE TABLE IF NOT EXISTS empresas (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(255) NOT NULL,
    cnpj            VARCHAR(18)  NULL,
    codigo_empresa  VARCHAR(6)   NOT NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    UNIQUE KEY empresas_codigo_empresa_unique (codigo_empresa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- TABELA: setores
-- (FK de responsavel_id -> users é adicionada via ALTER, após users)
-- ==============================
CREATE TABLE IF NOT EXISTS setores (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id      BIGINT UNSIGNED NOT NULL,
    nome            VARCHAR(255) NOT NULL,
    responsavel_id  BIGINT UNSIGNED NULL,
    ativo           TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    CONSTRAINT setores_empresa_id_foreign
        FOREIGN KEY (empresa_id) REFERENCES empresas (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- TABELA: users
-- ==============================
CREATE TABLE IF NOT EXISTS users (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id        BIGINT UNSIGNED NOT NULL,
    setor_id          BIGINT UNSIGNED NULL,
    criado_por        BIGINT UNSIGNED NULL, -- quem cadastrou (admin/coordenador); NULL = cadastro da empresa
    name              VARCHAR(255) NOT NULL,
    username          VARCHAR(50)  NOT NULL,
    email             VARCHAR(255) NOT NULL,
    role              ENUM('admin','coordenador','executor','colaborador') NOT NULL DEFAULT 'colaborador',
    ativo             TINYINT(1) NOT NULL DEFAULT 1,
    email_verified_at TIMESTAMP NULL,
    password          VARCHAR(255) NOT NULL,
    remember_token    VARCHAR(100) NULL,
    created_at        TIMESTAMP NULL,
    updated_at        TIMESTAMP NULL,
    UNIQUE KEY users_username_unique (username),
    UNIQUE KEY users_email_unique (email),
    CONSTRAINT users_empresa_id_foreign
        FOREIGN KEY (empresa_id) REFERENCES empresas (id) ON DELETE CASCADE,
    CONSTRAINT users_setor_id_foreign
        FOREIGN KEY (setor_id) REFERENCES setores (id) ON DELETE SET NULL,
    CONSTRAINT users_criado_por_foreign
        FOREIGN KEY (criado_por) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agora que users existe, adiciona a FK de responsavel_id em setores
ALTER TABLE setores
    ADD CONSTRAINT setores_responsavel_id_foreign
    FOREIGN KEY (responsavel_id) REFERENCES users (id) ON DELETE SET NULL;

-- ==============================
-- TABELA: ordens_servico
-- ==============================
CREATE TABLE IF NOT EXISTS ordens_servico (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id      BIGINT UNSIGNED NOT NULL,
    setor_id        BIGINT UNSIGNED NOT NULL,
    executor_id     BIGINT UNSIGNED NULL,
    criado_por      BIGINT UNSIGNED NOT NULL,
    atualizado_por  BIGINT UNSIGNED NULL,
    titulo          VARCHAR(255) NOT NULL,
    descricao       TEXT NOT NULL,
    status          ENUM('ABERTA','EM_ANDAMENTO','FINALIZADA','CANCELADA') NOT NULL DEFAULT 'ABERTA',
    urgencia        ENUM('BAIXA','MEDIA','ALTA','URGENTE') NOT NULL DEFAULT 'BAIXA',
    data_entrega    DATE NULL,
    -- última vez que o CRIADOR editou o título/descrição (não muda com devolutiva/executor)
    alterada_pelo_criador_em TIMESTAMP NULL,
    devolutiva      TEXT NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    CONSTRAINT ordens_servico_empresa_id_foreign
        FOREIGN KEY (empresa_id) REFERENCES empresas (id) ON DELETE CASCADE,
    CONSTRAINT ordens_servico_setor_id_foreign
        FOREIGN KEY (setor_id) REFERENCES setores (id),
    CONSTRAINT ordens_servico_executor_id_foreign
        FOREIGN KEY (executor_id) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT ordens_servico_criado_por_foreign
        FOREIGN KEY (criado_por) REFERENCES users (id),
    CONSTRAINT ordens_servico_atualizado_por_foreign
        FOREIGN KEY (atualizado_por) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- TABELA: sessions (necessária para SESSION_DRIVER=database)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
