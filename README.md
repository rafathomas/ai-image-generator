# Foto IA

Editor de fotos com Laravel 13, Vue 3, MySQL e Gemini. Envie uma imagem, descreva a edição e opcionalmente envie uma foto de referência para composições.

## Configuração

1. Copie `.env.example` para `.env`, configure o MySQL e defina `GEMINI_API_KEY` (crie uma chave no Google AI Studio; há cota gratuita, sujeita às regras e limites do Google).
2. Execute `php artisan migrate`, `php artisan storage:link`, `npm install` e `npm run build`.
3. Para processar edições, execute `php artisan queue:work`. Em outro terminal, `php artisan serve`.

O modelo é ajustável com `GEMINI_IMAGE_MODEL`. Para testes, a aplicação usa SQLite em memória e não chama a API externa.
# ai-image-generator
