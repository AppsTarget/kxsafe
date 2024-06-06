CREATE DATABASE kxsafe;
USE kxsafe;

CREATE TABLE valores (
	id INT AUTO_INCREMENT PRIMARY KEY,
	seq INT,
	descr VARCHAR(32),
	alias VARCHAR(16), -- categorias, maquinas
	lixeira TINYINT DEFAULT 0,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-------------------------------------------------------------------------------------------------------------------------------------------------------------
-------------------------------------------------------------------------- PESSOAS --------------------------------------------------------------------------
-------------------------------------------------------------------------------------------------------------------------------------------------------------

CREATE TABLE setores (
	id INT AUTO_INCREMENT PRIMARY KEY,
	descr VARCHAR(32),
	cria_usuario TINYINT DEFAULT 0,
	padrao TINYINT DEFAULT 0,
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

ALTER TABLE empresas ADD FOREIGN KEY (id_matriz) REFERENCES empresas(id);

CREATE TABLE empresas_setores (
	id INT AUTO_INCREMENT PRIMARY KEY,
	id_empresa INT,
	id_setor INT,
	lixeira TINYINT DEFAULT 0,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	FOREIGN KEY (id_empresa) REFERENCES empresas(id),
	FOREIGN KEY (id_setor) REFERENCES setores(id)
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
	foto VARCHAR(512),
	supervisor TINYINT DEFAULT 0,
	FOREIGN KEY (id_setor) REFERENCES setores(id),
	FOREIGN KEY (id_empresa) REFERENCES empresas(id)
);

CREATE TABLE users (
	id INT AUTO_INCREMENT PRIMARY KEY,
	email VARCHAR(64),
	password VARCHAR(512),
	lixeira TINYINT DEFAULT 0,
	id_pessoa INT,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	FOREIGN KEY (id_pessoa) REFERENCES pessoas(id)
);

------------------------------------------------------------------------------------------------------------------------------------------------------------
------------------------------------------------------------------------- PRODUTOS -------------------------------------------------------------------------
------------------------------------------------------------------------------------------------------------------------------------------------------------

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
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	referencia VARCHAR(64),
	tamanho VARCHAR(32),
	detalhes TEXT,
	FOREIGN KEY (id_categoria) REFERENCES valores(id)
);

CREATE TABLE estoque (
	id INT AUTO_INCREMENT PRIMARY KEY,
	es CHAR,
	descr VARCHAR(16),
	qtd NUMERIC(10,5),
	id_maquina INT,
	id_produto INT,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	FOREIGN KEY (id_maquina) REFERENCES valores(id),
	FOREIGN KEY (id_produto) REFERENCES produtos(id)
);

------------------------------------------------------------------------------------------------------------------------------------------------------------
------------------------------------------------------------------------- RESTANTE -------------------------------------------------------------------------
------------------------------------------------------------------------------------------------------------------------------------------------------------

CREATE TABLE gestor_estoque (
	id INT AUTO_INCREMENT PRIMARY KEY,
	descr VARCHAR(16),
	minimo NUMERIC(10,5),
	maximo NUMERIC(10,5),
	id_maquina INT,
	id_produto INT,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	preco NUMERIC(8,2),
	FOREIGN KEY (id_maquina) REFERENCES valores(id),
	FOREIGN KEY (id_produto) REFERENCES produtos(id)
);

CREATE TABLE comodatos (
	id INT AUTO_INCREMENT PRIMARY KEY,
	inicio DATE,
	fim DATE,
	fim_orig DATE,
	id_maquina INT,
	id_empresa INT,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	FOREIGN KEY (id_maquina) REFERENCES valores(id),
	FOREIGN KEY (id_empresa) REFERENCES empresas(id)
);

CREATE TABLE atribuicoes (
	id INT AUTO_INCREMENT PRIMARY KEY,
	pessoa_ou_setor_chave VARCHAR(16),
	pessoa_ou_setor_valor INT,
	produto_ou_referencia_chave VARCHAR(16),
	produto_ou_referencia_valor VARCHAR(256),
	qtd NUMERIC(10,5),
	validade INT,
	lixeira TINYINT DEFAULT 0,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	FOREIGN KEY (pessoa_ou_setor_valor) REFERENCES pessoas(id),
	FOREIGN KEY (pessoa_ou_setor_valor) REFERENCES setores(id),
	FOREIGN KEY (produto_ou_referencia_valor) REFERENCES produtos(cod_externo),
	FOREIGN KEY (produto_ou_referencia_valor) REFERENCES produtos(referencia)
);

CREATE TABLE retiradas (
	id INT AUTO_INCREMENT PRIMARY KEY,
	qtd NUMERIC(10,5),
	id_atribuicao INT,
	id_comodato INT,
	id_pessoa INT,
	id_supervisor INT,
	id_produto INT,
	observacao TEXT,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	FOREIGN KEY (id_atribuicao) REFERENCES atribuicoes(id),
	FOREIGN KEY (id_comodato) REFERENCES comodatos(id),
	FOREIGN KEY (id_pessoa) REFERENCES pessoas(id),
	FOREIGN KEY (id_supervisor) REFERENCES pessoas(id),
	FOREIGN KEY (id_produto) REFERENCES produtos(id)
);

CREATE TABLE log (
	id INT AUTO_INCREMENT PRIMARY KEY,
	id_pessoa INT,
	nome VARCHAR(32),
	acao CHAR,
	tabela VARCHAR(16),
	fk INT,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	FOREIGN KEY (id_pessoa) REFERENCES pessoas(id),
	FOREIGN KEY (fk) REFERENCES valores(id),
	FOREIGN KEY (fk) REFERENCES pessoas(id),
	FOREIGN KEY (fk) REFERENCES users(id),
	FOREIGN KEY (fk) REFERENCES empresas(id),
	FOREIGN KEY (fk) REFERENCES produtos(id),
	FOREIGN KEY (fk) REFERENCES estoque(id),
	FOREIGN KEY (fk) REFERENCES comodatos(id)
);