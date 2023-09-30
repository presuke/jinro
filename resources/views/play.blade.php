<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>The Cash Flow</title>
  @vite(['resources/css/app.css', 'resources/js/play/app.js'])
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