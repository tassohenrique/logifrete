-- =====================================================
-- LogiFrete - Gerenciador Logístico
-- Script de criação do banco de dados (MySQL / MariaDB - XAMPP)
-- Execute no phpMyAdmin: aba "SQL" > colar este conteúdo > Executar
-- =====================================================

CREATE DATABASE IF NOT EXISTS logifrete
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

USE logifrete;

-- Produtos que podem ser transportados
CREATE TABLE IF NOT EXISTS produtos (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(100)   NOT NULL,
    descricao   VARCHAR(255)   NULL,
    peso_kg     DECIMAL(10,2)  NOT NULL DEFAULT 0,
    valor       DECIMAL(10,2)  NOT NULL DEFAULT 0,
    criado_em   TIMESTAMP      DEFAULT CURRENT_TIMESTAMP
);

-- Endereços (usados como origem ou destino dos fretes)
CREATE TABLE IF NOT EXISTS enderecos (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    descricao    VARCHAR(100)  NOT NULL,          -- ex.: "Centro de Distribuição Cascavel"
    cep          CHAR(8)       NOT NULL,
    logradouro   VARCHAR(150)  NOT NULL,
    numero       VARCHAR(10)   NOT NULL,
    complemento  VARCHAR(100)  NULL,
    bairro       VARCHAR(100)  NOT NULL,
    cidade       VARCHAR(100)  NOT NULL,
    uf           CHAR(2)       NOT NULL,
    criado_em    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

-- Transportadores (motoristas autônomos ou transportadoras)
CREATE TABLE IF NOT EXISTS transportadores (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(100)  NOT NULL,
    cpf_cnpj    VARCHAR(14)   NOT NULL,           -- somente números
    telefone    VARCHAR(11)   NULL,               -- somente números
    email       VARCHAR(100)  NULL,
    veiculo     VARCHAR(50)   NOT NULL,
    placa       VARCHAR(8)    NOT NULL,
    criado_em   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

-- Fretes: ligam um transportador a uma origem e um destino
CREATE TABLE IF NOT EXISTS fretes (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    transportador_id  INT            NOT NULL,
    origem_id         INT            NOT NULL,
    destino_id        INT            NOT NULL,
    data_frete        DATE           NOT NULL,
    valor_frete       DECIMAL(10,2)  NOT NULL DEFAULT 0,
    status            VARCHAR(20)    NOT NULL DEFAULT 'Pendente',
    observacao        VARCHAR(255)   NULL,
    criado_em         TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transportador_id) REFERENCES transportadores(id),
    FOREIGN KEY (origem_id)        REFERENCES enderecos(id),
    FOREIGN KEY (destino_id)       REFERENCES enderecos(id)
);

-- Itens do frete: um frete leva VÁRIOS produtos (relação 1 para muitos)
CREATE TABLE IF NOT EXISTS frete_produtos (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    frete_id    INT  NOT NULL,
    produto_id  INT  NOT NULL,
    quantidade  INT  NOT NULL DEFAULT 1,
    FOREIGN KEY (frete_id)   REFERENCES fretes(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- =====================================================
-- Dados de exemplo (opcional) para demonstração
-- =====================================================
INSERT INTO produtos (nome, descricao, peso_kg, valor) VALUES
('Caixa de Soja 25kg', 'Saca de soja em grão', 25.00, 180.00),
('Notebook Dell Inspiron', 'Notebook 15 polegadas', 2.30, 3899.90),
('Geladeira Frost Free', 'Geladeira 400 litros', 70.00, 3499.00),
('Pneu Aro 15', 'Pneu para carro de passeio', 9.50, 420.00);

INSERT INTO enderecos (descricao, cep, logradouro, numero, complemento, bairro, cidade, uf) VALUES
('Centro de Distribuição Cascavel', '85810010', 'Rua Paraná', '1500', 'Galpão 2', 'Centro', 'Cascavel', 'PR'),
('Loja Curitiba', '80010000', 'Rua XV de Novembro', '300', NULL, 'Centro', 'Curitiba', 'PR'),
('Depósito Foz do Iguaçu', '85851000', 'Avenida Brasil', '850', NULL, 'Centro', 'Foz do Iguaçu', 'PR');

INSERT INTO transportadores (nome, cpf_cnpj, telefone, email, veiculo, placa) VALUES
('Transportes Oeste Ltda', '12345678000190', '45999990000', 'contato@transoeste.com.br', 'Caminhão Truck', 'ABC1D23'),
('João da Silva', '12345678909', '45988887777', 'joao.silva@email.com', 'Van Sprinter', 'XYZ9K87');
