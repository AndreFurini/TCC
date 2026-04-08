use projeto_ecommerce;

DROP TABLE users;

-- ==============================
-- TABELA: users
-- ==============================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('ADM', 'CLI') NOT NULL DEFAULT 'CLI',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);


-- ==============================
-- TABELA: categorias
-- ==============================
CREATE TABLE categorias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);


-- ==============================
-- TABELA: produtos
-- ==============================
CREATE TABLE produtos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT NULL,
    preco DECIMAL(10,2) NOT NULL,
    estoque INT NOT NULL,
    categoria_id BIGINT UNSIGNED NOT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    CONSTRAINT fk_produtos_categoria
        FOREIGN KEY (categoria_id) REFERENCES categorias(id)
        ON UPDATE NO ACTION ON DELETE NO ACTION
);


-- ==============================
-- TABELA: pedidos
-- ==============================
CREATE TABLE pedidos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    status ENUM('ABERTO', 'PAGO', 'ENVIADO', 'CONCLUIDO', 'CANCELADO') NOT NULL,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    CONSTRAINT fk_pedidos_users
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE NO ACTION ON DELETE NO ACTION
);


-- ==============================
-- TABELA: itens_pedido
-- ==============================
CREATE TABLE itens_pedido (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id BIGINT UNSIGNED NOT NULL,
    produto_id BIGINT UNSIGNED NOT NULL,
    quantidade INT NOT NULL,
    preco DECIMAL(10,2) NOT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    CONSTRAINT fk_itens_pedido_pedido
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
        ON UPDATE NO ACTION ON DELETE NO ACTION,

    CONSTRAINT fk_itens_pedido_produto
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
        ON UPDATE NO ACTION ON DELETE NO ACTION
);


-- ==============================
-- TABELA: enderecos
-- ==============================
CREATE TABLE enderecos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    endereco VARCHAR(255) NOT NULL,
    cidade VARCHAR(255) NOT NULL,
    estado VARCHAR(255) NOT NULL,
    cep VARCHAR(20) NOT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    CONSTRAINT fk_enderecos_usuario
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE CASCADE
);
