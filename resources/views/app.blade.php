<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Gym System') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
    <script>
           (function () {
               try {
                   const stored = localStorage.getItem('theme');
                   const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                   const theme = stored || (prefersDark ? 'dark' : 'light');
                   if (theme === 'dark') {
                       document.documentElement.classList.add('dark');
                   }
               } catch (e) {}
           })();
       </script>
</head>
<body class="font-sans antialiased bg-slate-950 text-slate-100">
    @inertia
</body>
</html>
