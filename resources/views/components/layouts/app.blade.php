<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Form Builder</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

    @livewireStyles
</head>
<body class="bg-gray-100">

<div class="container mx-auto py-8">

    {{ $slot }}

</div>

@livewireScripts

</body>
</html>