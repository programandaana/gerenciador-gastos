@extends('template')

@section('title', 'Enviar Arquivo')

@section('content')
    <div class="container-fluid">
        <br>
        <h2 style="font-size: 150%">Enviar Nota Fiscal</h2>
        <p>Selecione um arquivo para enviar.</p>

        @if ($errors->any())
            <div style="color: #856404; background-color: #fff3cd; border: 1px solid #ffeeba; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <h4>⚠️ Falha na Validação:</h4>
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div style="color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                ✅ Sucesso: {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                ❌ Erro: {{ session('error') }}
            </div>
        @endif
        <form
            action="{{ route('receipt.upload') }}"
            method="POST"
            enctype="multipart/form-data"
        >
            @csrf
            <div class="form-group">
                <label for="arquivo" class="form-label">Selecione o arquivo:</label>
                <input
                    type="file"
                    name="nota_imagem"
                    id="arquivo"
                    class="form-control-file"
                    required
                >
            </div>
            <button type="submit" class="btn-primary btn">Enviar</button>
        </form>
        <hr>

        <a href="{{ route('view.receipt.list') }}" class="btn-secondary btn">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
@endsection
