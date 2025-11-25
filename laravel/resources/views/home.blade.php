@extends('template')

@section('title', 'Painel de Controle')

@section('content')
    <div class="row">
        <div class="col-sm-6 mb-3 mb-sm-0">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Gastos do mês</h5>
                    <div>
                        @include('relatorios.categoria_pizza')
                    <a href="#" class="btn btn-primary">Ver detalhes</a>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Relatórios</h5>
                    <p class="card-text">Visualize relatórios detalhados sobre seus gastos.</p>
                    <a href="#" class="btn btn-primary">Ver relatórios</a>
                </div>
            </div>
        </div>
    </div>
@endsection

