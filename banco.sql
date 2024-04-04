CREATE DATABASE kxsafe;
USE kxsafe;

CREATE TABLE valores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  seq INT,
	descr VARCHAR(32),
	alias VARCHAR(16),
	lixeira TINYINT DEFAULT 0,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE setores (
	id INT AUTO_INCREMENT PRIMARY KEY,
	descr VARCHAR(32),
	cria_usuario TINYINT DEFAULT 0,
	lixeira TINYINT DEFAULT 0,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE empresas (
	id INT AUTO_INCREMENT PRIMARY KEY,
	razao_social VARCHAR(128),
	nome_fantasia VARCHAR(64),
	cnpj VARCHAR(32),
	lixeira TINYINT DEFAULT 0,
	id_matriz INT,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE pessoas (
	id INT AUTO_INCREMENT PRIMARY KEY,
	nome VARCHAR(64),
	cpf VARCHAR(16),
	lixeira TINYINT DEFAULT 0,
	id_setor INT,
	id_empresa INT,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	funcao VARCHAR(64),
	admissao DATE,
	senha INT,
	foto VARCHAR(512)
);

CREATE TABLE produtos (
	id INT AUTO_INCREMENT PRIMARY KEY,
	descr VARCHAR(256),
	preco NUMERIC(8,2),
	validade INT,
	lixeira TINYINT DEFAULT 0,
	ca VARCHAR(16),
	foto VARCHAR(512),
	cod_externo VARCHAR(8),
	id_categoria INT,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE estoque (
	id INT AUTO_INCREMENT PRIMARY KEY,
	es CHAR,
	descr VARCHAR(16),
	qtd NUMERIC(10,5),
	id_maquina INT,
	id_produto INT,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE gestor_estoque (
	id INT AUTO_INCREMENT PRIMARY KEY,
	descr VARCHAR(16),
	minimo NUMERIC(10,5),
	maximo NUMERIC(10,5),
	id_maquina INT,
	id_produto INT,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE comodatos (
	id INT AUTO_INCREMENT PRIMARY KEY,
	inicio DATE,
	fim DATE,
	fim_orig DATE,
	id_maquina INT,
	id_empresa INT,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE log (
	id INT AUTO_INCREMENT PRIMARY KEY,
	id_pessoa INT,
	acao CHAR,
	tabela VARCHAR(16),
	fk INT,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);