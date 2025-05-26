CREATE DATABASE IF NOT EXISTS pizzaria;
USE pizzaria;

CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    telefone VARCHAR(20),
    quadra VARCHAR(50),
    casa VARCHAR(20),
    cep VARCHAR(20)
);

CREATE TABLE pizzas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    ingredientes TEXT,
    preco DECIMAL(10,2)
);

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    pizza_id INT,
    pagamento VARCHAR(50),
    observacoes TEXT,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (pizza_id) REFERENCES pizzas(id)
);


CREATE TABLE funcionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL, 
    nome VARCHAR(100)
);

INSERT INTO pizzas (nome, ingredientes, preco) VALUES
('Pizza Italiana', 'Molho de tomate artesanal, mussarela e manjericão fresco', 35.00),
('Pizza Havaiana', 'Molho de tomate, mussarela, presunto e pedaços de abacaxi', 34.00),
('Pizza Grega', 'Queijo, tomate, cebola roxa, azeitonas pretas, orégano grego', 33.00),
('Bacon Crispy Thins', 'Massa fininha, bacon crocante, queijo derretido', 32.00),
('Havaiana Especial', 'Mais queijo, presunto, abacaxi caramelizado, borda recheada', 49.90),
('Ultimate Overload', 'Muito queijo, pepperoni, bacon, calabresa, cebola, azeitonas', 45.00),
('Pizza de Bacon', 'Massa tradicional, bacon, queijo e toque especial', 36.00),
('Presunto & Abacaxi', 'Massa tradicional, bacon, queijo e toque especial', 36.00);
!--INSERT INTO funcionarios (usuario, senha, nome) VALUES ('admin', '$2y$10$yrOIEvqBimSGR.RoKbiEq.NblcjR/ekzkZhOnkfSvqrF8139AP6N.', 'Administrador');