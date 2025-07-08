
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://themosvagas.com.br/wp-content/uploads/2021/10/unnamed.png" width="400" alt="Laravel Logo"></a></p>


## Sobre o Meu Projeto

Construí a API solicitada com os seguintes endpoints:

- POST /api/login
- POST /api/wallet/deposit
- POST /api/wallet/transfer
- POST /api/wallet/reverse

## 📄 Documentação

[Visualizar documentação em PDF](docs/documentacao.pdf)

## ✨ Funcionalidades

- 🔐 **Autenticação via token (Sanctum)**
- 💰 **Depósito** de valores em carteira
- 🔄 **Transferência** entre carteiras de usuários distintos
- ⏪ **Reversão** de transações (uma única vez por operação)
- 📜 **Validações rigorosas** com Form Requests
- 🛡️ **Controle de acesso** com Policies por operação
- 🧾 **Logs detalhados** de todas as ações críticas
- ⚖️ Proteção contra abuso via **Rate Limiting** (60 requisições/minuto)
- ✅ Testes automatizados com PHPUnit
- 📚 Documentação clara para uso via Postman

## 🔐 Segurança

- Acesso autenticado com tokens (Bearer Token)
- Policies garantindo que só o dono da carteira possa movimentá-la
- Logs para falhas, transações e reversões
- Validações aplicadas no nível do service e das requisições
- Proteção contra overposting e abuso de endpoints

