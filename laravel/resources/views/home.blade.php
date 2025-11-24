@extends('template')

@section('title', 'Painel de Controle')

@section('content')
    <br>

    <div class="row">
        <div class="col-sm-6 mb-3 mb-sm-0">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Gastos do mês</h5>
                    <div>
                        @include('relatorios.categoria_pizza')
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="card">
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

