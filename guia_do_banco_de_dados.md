# 🐾 Pet Vida 2026 — Guia do Banco de Dados (MySQL)

Este guia orienta passo a passo como configurar, importar e acessar o banco de dados do projeto **Pet Vida 2026** utilizando o **MySQL** e a interface gráfica **MySQL Workbench**.

---

## 📋 Pré-requisitos

Antes de iniciar, certifique-se de instalar as seguintes ferramentas em seu computador:

1. **Git**: [Baixar Git](https://git-scm.com/)
2. **MySQL Server** (versão 8.0 ou superior): [Baixar MySQL Server](https://dev.mysql.com/downloads/mysql/)
3. **MySQL Workbench**: [Baixar MySQL Workbench](https://dev.mysql.com/downloads/workbench/)

---

## 🚀 Passo a Passo de Instalação e Configuração

### 1. Clonar o Repositório

Abra o seu terminal (Prompt de Comando, PowerShell ou Terminal do VS Code) e execute:

```bash
git clone https://github.com/GabrielFFreita/Pet-Vida-2026.git
cd Pet-Vida-2026
```

---

### 2. Conectar ao MySQL via MySQL Workbench

1. Abra o **MySQL Workbench**.
2. Na tela inicial, clique na conexão local criada durante a instalação (geralmente chamada **`Local instance MySQL Router`** ou **`Local Instance 3306`**).
3. Insira a senha do usuário `root` definida durante a instalação do MySQL.

---

### 3. Criar a Base de Dados

1. No MySQL Workbench, abra uma nova aba de consulta SQL (clique no ícone de raio com a folha ou pressione `Ctrl + T`).
2. Digite o comando abaixo para criar o banco de dados e clique no ícone de **raio (Execute)**:

```sql
CREATE DATABASE IF NOT EXISTS pet_vida_db;
USE pet_vida_db;
```

---

### 4. Importar a Estrutura do Banco (Script SQL)

#### **Opção A: Abrir o arquivo SQL direto no Workbench**
1. No menu superior, acesse **File > Open SQL Script...** (ou pressione `Ctrl + O`).
2. Navegue até a pasta do projeto clonado (`Pet-Vida-2026`) e selecione o arquivo do banco de dados (exemplo: `schema.sql`, `banco.sql` ou similar).
3. Selecione o banco de dados no menu esquerdo (`SCHEMAS`) ou adicione a linha `USE pet_vida_db;` no início do script.
4. Clique no ícone de **raio** para executar todo o script e criar as tabelas.

#### **Opção B: Usar a ferramenta Data Import**
1. No menu esquerdo, acesse a aba **Management** e clique em **Data Import/Restore**.
2. Marque a opção **Import from Self-Contained File**.
3. Selecione o arquivo `.sql` da pasta do projeto.
4. Em **Default Target Schema**, selecione `pet_vida_db`.
5. Clique na aba **Start Import** no canto inferior direito.

---

### 5. Configurar o Arquivo de Conexão (`.env`)

Na raiz do projeto `Pet-Vida-2026`, crie ou atualize o arquivo `.env` com as informações de acesso local do seu MySQL Workbench:

```env
# Configurações do Banco de Dados MySQL
DB_HOST=localhost
DB_PORT=3306
DB_NAME=pet_vida_db
DB_USER=root
DB_PASSWORD=sua_senha_do_mysql
```

> **Atenção:** Substitua `sua_senha_do_mysql` pela senha criada no seu MySQL Workbench.

---

## 🔍 Parâmetros de Conexão no MySQL Workbench

Se você precisar criar uma nova conexão manual no MySQL Workbench, utilize os seguintes dados:

| Parâmetro | Valor Padrão |
| :--- | :--- |
| **Hostname** | `127.0.0.1` ou `localhost` |
| **Port** | `3306` |
| **Username** | `root` (ou outro usuário configurado) |
| **Password** | *(sua senha do MySQL)* |
| **Default Schema** | `pet_vida_db` |

---

## 🛠️ Solução de Problemas Comuns

* **Erro "Access denied for user 'root'@'localhost'"**:
  * Verifique se a senha informada no `.env` ou no login do Workbench é exatamente a senha que você definiu ao instalar o MySQL.

* **Erro "Can't connect to MySQL server on 'localhost' (10061)"**:
  * O serviço do MySQL pode estar parado. No Windows, pressione `Win + R`, digite `services.msc`, procure por **MySQL80** (ou similar) e clique em **Iniciar**.

* **Tabelas não aparecem após a importação**:
  * No painel esquerdo do MySQL Workbench, clique com o botão direito na área de `SCHEMAS` e selecione **Refresh All**.

---

✉️ *Em caso de dúvidas ou erros durante o processo, abra uma **Issue** no repositório do projeto.*