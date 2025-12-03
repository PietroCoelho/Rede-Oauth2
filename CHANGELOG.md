# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

## [1.0.0] - 2025-01-XX

### Adicionado
- Implementação de autenticação OAuth 2.0
- Cliente OAuth com gerenciamento automático de tokens
- Cliente HTTP autenticado com renovação automática de tokens
- Classes principais do SDK (Transaction, eRede, Store, etc.)
- Suporte a todas as funcionalidades do SDK original:
  - Autorização de transações
  - Captura de transações
  - Cancelamento de transações
  - Consulta de transações
  - Suporte a 3DS2
  - Suporte a IATA
  - Suporte a MCC dinâmico
  - Parcelamento
- Testes unitários e de integração
- Configuração Docker para desenvolvimento
- CI/CD com GitHub Actions
- Documentação completa
- Exemplos de uso

### Mudanças
- Substituição de Basic Auth por OAuth 2.0
- Refatoração seguindo princípios de Clean Code e SOLID

