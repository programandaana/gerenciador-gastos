<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FinAI - @yield('title')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body>

<div>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{route('view.home')}}">FinAI</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->routeIs('view.home') ? 'active' : '' }}"
                            aria-current="page"
                            href="{{ route('view.home') }}"
                        >Home</a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->routeIs('view.receipt.*') ? 'active' : '' }}"
                            href="{{ route('view.receipt.list') }}"
                        >Notas Fiscais</a>
                    </li>

                </ul>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        @yield('content')
    </div>

</div>
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <!-- Toasts will be appended here -->
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
@stack('scripts')
<script src="{{ asset('js/script.js') }}"></script>
</html>
