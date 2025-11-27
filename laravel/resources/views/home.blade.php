@extends('template')

@section('title', 'Painel de Controle')

@section('content')
    <br>
    <div class="row mb-4">
        <div class="col-md-6">
            <form method="GET" action="{{ route('view.home') }}" class="row g-3 align-items-end">
                <div class="col-auto">
                    <label for="data_inicio" class="form-label">Data Início:</label>
                    <input type="date" name="data_inicio" id="data_inicio" class="form-control"
                           value="{{ $dataInicio }}" required>
                </div>
                <div class="col-auto">
                    <label for="data_fim" class="form-label">Data Fim:</label>
                    <input type="date" name="data_fim" id="data_fim" class="form-control"
                           value="{{ $dataFim }}" required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Aplicar Filtro
                    </button>
                </div>
            </form>
        </div>
    </div>


    <div class="row">
        <div class="col-sm-6 mb-3 mb-sm-0">
            <div class="card shadow-sm p-3 mb-5 bg-body-tertiary rounded" >
                <div class="card-body">
                    <h5 class="card-title">Gastos do Período</h5>
                    <div>
                        @include('relatorios.categoria_pizza')
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="card shadow-sm p-3 mb-5 bg-body-tertiary rounded">
                <div class="card-body">
                    <h5 class="card-title">Últimos itens comprados</h5>
                    <div>
                        @include('relatorios.itens_recentes')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')

@endpush
