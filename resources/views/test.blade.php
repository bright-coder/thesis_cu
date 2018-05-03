<html>
    <meta charset="UTF-8">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <body>
            
    <p>กฤษฎา</p>
        <button class="btn btn-primary">Hello Btn</button>
            <div id="app">
                {{-- <example></example> --}}
            </div>
        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>