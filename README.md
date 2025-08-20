# SymfonyAI-POC

POC of a simulated order system that can be queried using an LLM agent.<br>
It is using the Symfony AI bundle.

**The data retrieval is secured using metadata**: A user can only access his own orders using the LLM.<br>
This cannot be _hacked_ using some special prompts because the restriction is applied on the vector database directly.


## Stack

- PHP 8.4
- Symfony 7.3
- Symfony AI Bundle
- ChromaDB (as a vector database)
- Gemini 2 Flash
- Docker
- FrankenPHP

## Usage

Create a free API key for Google Gemini and put it in the `.env` file.

Start docker:<br>
`docker compose up`

Generate the data (migrations, fixtures, embeddings):<br>
`make init`

Go to https://localhost.

## Tools

PHP-CS-FIXER:<br>
`make php-cs-fixer`

PHPSTAN / PHPAT (level 10):<br>
`make phpstan`

## Preview

https://github.com/user-attachments/assets/39f97333-f30b-4b67-ac1b-67665d3db3ed

