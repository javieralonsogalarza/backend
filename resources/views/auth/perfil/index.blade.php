@extends('auth.layout.app')

@section('main')
    <div class="box" id="contentView"></div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(function(){
            const $contentView = $("#contentView");
            invocarVista(`/auth/perfil/partialView`, function (data){ $contentView.html("").append(data);});
        });
    </script>
@endsection
