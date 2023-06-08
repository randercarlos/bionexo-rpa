<h4 align="center">
  🚀 Bionexo - RPA para automatização de tarefas - Teste técnico
</h4>

<p align="center">
 <img src="https://img.shields.io/static/v1?label=PRs&message=welcome&color=7159c1&labelColor=000000" alt="PRs welcome!" />

  <img alt="License" src="https://img.shields.io/static/v1?label=license&message=MIT&color=7159c1&labelColor=000000">
</p>

<p align="center">
  <a href="#rocket-tecnologias">Tecnologias</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
  <a href="#-projeto">Projeto</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
  <a href="#-funcionalidades">Funcionalidades</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
  <a href="#-requisitos">Requisitos</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
  <a href="#-instalação">Instalação</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
</p>

<br>

## :rocket: Tecnologias

Esse projeto foi desenvolvido com as seguintes tecnologias:

- [PHP 8.2](https://php.net)
- [Laravel 10](https://laravel.com)
- [MySQL 5.7](https://mysql.com)
- [Docker](https://docker.com)


## 💻 Projeto

Esse projeto é uma RPA para automatizações de tarefas desenvolvida como teste técnico para o processo seletivo de Desenvolvedor PHP Sênior na Bionexo.


## 💻 Funcionalidades

O sistema possui a automatização de 5 tarefas:

- Acessar a página https://testpages.herokuapp.com/styled/tag/table.html e capturar todas as informações exibidas na tabela e armazenar em um banco de dados ( Ex.: myqsl)
- Preencher o formulário através do link https://testpages.herokuapp.com/styled/basic-html-form-test.html e retornar se preenchimento foi ok ou não. ( pode inventar as informações a serem preenchidas)
- Baixar o arquivo através do link https://testpages.herokuapp.com/styled/download/download.html pelo botão “Direct Link Download” e salvar no seu disco local e renomear o arquivo para “Teste TKS”
- Realizar o upload do arquivo baixado no item 3 através do link https://testpages.herokuapp.com/styled/file-upload-test.html.
- Leitura de PDF, extração dos dados lidos e gravação num arquivo excel. 

## 📄 Requisitos

* PHP 8.2+, Laravel 10+, MySQL 5.7+ e Docker


## ⚙️ Instalação e execução

**Windows, OS X & Linux:**

Baixe o arquivo zip e o descompacte ou baixe o projeto para sua máquina através do git clone [https://github.com/randercarlos/bionexo-rpa.git](https://github.com/randercarlos/bionexo-rpa.git)


- Entre no prompt de comando e vá até a pasta do projeto:

```sh
cd ir-ate-a-pasta-do-projeto
```

- Crie o arquivo .env a partir do arquivo .env.example. As variáveis de ambiente relacionadas ao banco já estão configuradas.

```sh
copy .env.example .env
```

- Assumindo que tenha o docker instalado na máquina, para subir os containeres, execute o comando:

```sh
docker-compose up -d
```

- Após isso, execute o comando abaixo para instalar as dependências do laravel.

```sh
docker-compose exec bionexo-rpa-app composer install
```
- Aguarde até que todas as dependências do laravel estejam instaladas. Após isso, rode o comando abaixo para instalar as migrações:

```sh
docker-compose exec bionexo-rpa-app php artisan migrate
``` 

- Após rodar o comando acima, basta acessar o endereço [http://localhost:8000](http://localhost:8000) para o RPA executar as tarefas.

## 📝 Documentação

- Os arquivos descritos na tarefa para serem lidos ou gerados encontram-se dentro da pasta *storage/app*. O arquivo excel gerado possui o nome de *Leitura_PDF.xlsx*
- As credenciais de acesso ao banco de dados são: 

```sh
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3307
DB_DATABASE=bionexo-rpa
DB_USERNAME=bionexo
DB_PASSWORD=rpa
```

Desenvolvido por Rander Carlos :wave: [Linkedin!](https://www.linkedin.com/in/rander-carlos-308a63a8//)
