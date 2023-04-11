<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>MQS</title>

   <link href="bootstrap-4.0.0-dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
   <script src="bootstrap-4.0.0-dist/js/bootstrap.min.js" crossorigin="anonymous"></script>

    <!-- Styles -->
    <style>
        body{
           background-color: rebeccapurple;
           background-image: url('NatureMoonlightWallpapers.jpeg');
           background-size: cover;
           background-repeat: no-repeat;
         }
    </style>
</head>
<body class="antialiased">
    <div
        class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center py-4 sm:pt-0">
        @if (Route::has('login'))
            <div class="hidden fixed top-0 right-0 px-6 py-4 sm:block">
                @auth
                <a href="{{ url('/home') }}" class="text-sm text-gray-700 dark:text-gray-500 underline" style=" margin-left: 20px">MANAGE</a>
                @else
                    <a href="{{ route('login') }}" 
                        class="text-sm text-gray-700 dark:text-gray-500 underline" style=" margin-left: 20px">LOG IN</a>
                    @if (Route::has('register'))
                    <a href="{{ route('register') }}"
                        class="text-sm text-gray-700 dark:text-gray-500 underline"style=" margin-left: 20px">REGISTER</a>
                    @endif
                    <a href="{{ url('http://localhost:3113/') }}" 
                        class="text-sm text-gray-700 dark:text-gray-500 underline" style=" margin-left: 20px">MACSEDO QUICK SERVICE</a>
                @endauth
            </div>
        @endif

    </div>
</body>

</html>