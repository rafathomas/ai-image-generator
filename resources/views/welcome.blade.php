<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Foto IA — Estúdio de edição</title>
    @unless(app()->environment('testing'))
      @vite(['resources/js/app.js'])
    @endunless
  </head>
  <body><main id="app"></main></body>
</html>
