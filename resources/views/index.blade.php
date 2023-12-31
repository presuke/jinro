<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>Jinro</title>
  @vite(['resources/css/app.css', 'resources/js/index/app.js'])
  <script src="//unpkg.com/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>
  <link href="//fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
  <link href="//cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
  <link href="//cdn.jsdelivr.net/npm/vuetify@3.x/dist/vuetify.min.css" rel="stylesheet">
  <style>
    body {
      overflow-y: hidden;
    }

    ::-webkit-scrollbar {
      width: 10px;
    }

    ::-webkit-scrollbar-track {
      background: #fff;
      border: none;
      border-radius: 10px;
      box-shadow: inset 0 0 2px #777;
    }

    ::-webkit-scrollbar-thumb {
      background: #ccc;
      border-radius: 10px;
      box-shadow: none;
    }

    #main {
      margin: 0px;
      background-color: #400;
      text-align: center;
    }

    #app {
      max-width: 800px;
      margin: 0 auto;
    }
  </style>
</head>

<body>
  <div id="main">
    <div id="app"></div>
  </div>
</body>

</html>