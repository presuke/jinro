<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>リダイレクト</title>
  @vite(['resources/css/app.css', 'resources/js/room/app.js'])
  <script src="//unpkg.com/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>
  <link href="//fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
  <link href="//cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
  <link href="//cdn.jsdelivr.net/npm/vuetify@3.x/dist/vuetify.min.css" rel="stylesheet">
  <script>
    window.location.href = "./room/";
  </script>
</head>

<body>
  <div style="text-align:center;">
    redirect page.
  </div>
</body>

</html>