<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Carteira Digital</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons (necessário para ícones bi bi-person etc.) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 Theme Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    <style>
        /* ===================== TOASTS ===================== */
        #toast-container {
            position: fixed;
            top: 50px;
            right: 20px;
            z-index: 999999;
        }

        .toast-msg {
            min-width: 250px;
            padding: 12px 18px;
            border-radius: 6px;
            margin-top: 10px;
            color: #fff;
            opacity: 0;
            transform: translateX(30px);
            transition: all .3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .toast-success {
            background: #28a745;
        }

        .toast-danger {
            background: #dc3545;
        }

        .toast-msg.show {
            opacity: 1;
            transform: translateX(0);
        }

        /* ===================== SELECT2 Ajustes ===================== */

        .select2-container .select2-selection--single {
            height: 48px !important;
            padding: 8px 12px !important;
            font-size: 16px;
            border: 1px solid #ced4da !important;
            border-radius: 8px !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-selection__rendered {
            line-height: 32px !important;
            padding-left: 4px !important;
        }

        .select2-selection__arrow {
            top: 10px !important;
            right: 12px !important;
        }

        .select2-container .select2-dropdown {
            margin-top: 8px !important;
            border-radius: 0 0 8px 8px !important;
            border-color: #ced4da !important;
        }

        /* Aumenta a distância entre o select e o dropdown */
.select2-container .select2-dropdown {
    margin-top: 8px !important;  /* ajuste aqui o espaço */
    border: 1px solid #ced4da !important;
    
}

/* Opcional: deixar a animação mais suave */
.select2-container .select2-dropdown {
    transition: margin 0.2s ease;
}

    </style>

    <style>
.timeline-item {
    position: relative;
}

.timeline-marker {
    width: 12px;
    height: 12px;
    background: #0d6efd;
    border-radius: 50%;
    margin-top: 6px;
    flex-shrink: 0;
    position: relative;
}

.timeline-item::before {
    content: "";
    position: absolute;
    left: 5px;
    top: 20px;
    width: 2px;
    height: calc(100% - 20px);
    background: #d8d8d8;
    z-index: 0;
}

.timeline-item:last-child::before {
    display: none;
}
</style>

</head>

<body class="bg-light">

    <div id="toast-container"></div>

    <!-- ===================== NAVBAR ===================== -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm fixed-top">
        <div class="container">

            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
                <i class="bi bi-wallet2 me-2"></i>
                <span class="fw-bold">MyWallet</span>
            </a>

            <!-- Mobile toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menu -->
            <div class="collapse navbar-collapse" id="navbarContent">

                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('dashboard') ? 'active fw-bold' : '' }}"
                            href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('transactions') ? 'active fw-bold' : '' }}"
                            href="{{ route('transactions.index') }}">
                            <i class="bi bi-list-ul me-1"></i> Extract
                        </a>
                    </li>

                    <!-- Dropdown User -->
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center"
                            href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>
                            {{ auth()->user()->name }}
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-gear me-2"></i> Settings
                                </a>
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>

                    </li>

                </ul>

            </div>
        </div>
    </nav>

    <!-- Espaço para compensar navbar fixa -->
    <div style="height: 70px;"></div>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="container mt-4">

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>

    <!-- JS ESSENCIAL -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: "bootstrap-5",
                width: '100%',
                placeholder: "Selecione um usuário"
            });
        });
    </script>

</body>

</html>
