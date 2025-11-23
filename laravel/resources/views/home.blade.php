@extends('template')

@section('title', 'Painel de Controle')

@section('content')
    <h1>Página Inicial</h1>
    <p>Selecione uma das opções abaixo.</p>
    <a href="/relatorio" class="btn">Exibir Relatório</a>
    <a href="{{ route('view.receipt.upload') }}" class="btn btn-secondary">Enviar Arquivo</a>
@endsection
