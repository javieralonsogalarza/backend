<link rel="stylesheet" href="{{ asset('auth/plugins/file-input/css/fileinput.min.css') }}">
<style type="text/css">
    .select2-container--default .select2-selection--multiple .select2-selection__choice{ background-color: #007bff !important;border-color: #006fe6 !important; }
    @media (max-width: 500px) {  #modalTorneo .modal-content{ height: 500px; overflow-y: scroll }  }
</style>
<div class="modal fade" id="modal{{$ViewName}}"  role="dialog" tabindex="-1" data-backdrop="static" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ ($Model != null ? "Modificar" : "Registrar")." ".$ViewName }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('auth.'.strtolower($ViewName).'.store') }}" id="frm{{$ViewName}}" enctype="multipart/form-data" method="POST"
                  data-ajax="true" class="form" data-ajax-loading="#loading" data-ajax-success="OnSuccess{{$ViewName}}"
                  data-ajax-failure="OnFailure{{$ViewName}}">
                @csrf
                <input type="hidden" id="id" name="id" value="{{ $Model != null ? $Model->id : 0 }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <label for="nombre">Nombre: <span class="text-danger">(*)</span></label>
                                    <input type="text" name="nombre" id="nombre" class="form-control" value="{{ $Model != null ? $Model->nombre : "" }}" required autocomplete="off" >
                                    <span data-valmsg-for="nombre"></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label for="fecha_inicio">Fecha Inicio: <span class="text-danger">(*)</span></label>
                                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ $Model != null ? $Model->fecha_inicio : "" }}" required autocomplete="off" >
                                    <span data-valmsg-for="fecha_inicio"></span>
                                </div>
                                <div class="col-sm-6">
                                    <label for="fecha_final">Fecha Final: <span class="text-danger">(*)</span></label>
                                    <input type="date" name="fecha_final" id="fecha_final" class="form-control" value="{{ $Model != null ? $Model->fecha_final : "" }}" required autocomplete="off" >
                                    <span data-valmsg-for="fecha_final"></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <label for="formato_id">Formato: <span class="text-danger">(*)</span></label>
                                    <div class="input-group mr-3">
                                        <select name="formato_id" id="formato_id" class="form-control" required>
                                            <option value="">Seleccione</option>
                                            @foreach($Formatos as $q)
                                                <option value="{{$q->id}}" data-info="{{ $q->nombre }}" {{ $Model != null && $Model->formato_id == $q->id ? "selected" : "" }}>{{ $q->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-prepend">
                                            <button id="btnAgregarFormato" type="button" class="btn btn-primary"><i class="fa fa-plus"></i></button>
                                        </div>
                                    </div>
                                    <span data-valmsg-for="formato_id"></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <label for="categoria_id">Categoría</label>
                                    <div class="input-group mr-3">
                                        <select name="categoria_id" id="categoria_id" class="form-control">
                                            @foreach($Categorias as $q)
                                                <option value="{{$q->id}}" data-info="{{ $q->nombre }}" data-multiple="{{ $q->dupla }}">{{ $q->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-prepend">
                                            <button id="btnRegistrarCategoria" type="button" class="btn btn-default">Nueva Categoría</button>
                                            <button id="btnAgregarCategoria" type="button" class="btn btn-primary"><i class="fa fa-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 mt-2">
                                    <span data-valmsg-for="categorias"></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <label for="zonas_id">Zonas: <span class="text-danger">(*)</span></label>
                                    <div class="input-group">
                                        <div style="width:calc(100% - 42px) !important;">
                                            <select name="zonas_id[]" id="zonas_id" class="form-control" style="width: 100% !important;"
                                                    data-initial="{{ ($Model != null && $Model->zonas != null && count($Model->zonas) > 0 ) ?  implode(",", $Model->zonas->pluck('zona_id')->toArray())  : "" }}" multiple="multiple">
                                                <option value="">Seleccione</option>
                                                @foreach($Zonas as $q)
                                                    <option value="{{$q->id}}" data-info="{{ $q->nombre }}">{{ $q->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="input-group-prepend">
                                            <button id="btnAgregarZona" type="button" class="btn btn-primary"><i class="fa fa-plus"></i></button>
                                        </div>
                                    </div>
                                    <span data-valmsg-for="zonas_id"></span>
                                </div>
                            </div>
                            <div class="form-group ro mt-3">
                                <div class="col-sm-6">
                                    <div class="icheck-primary d-inline">
                                        <input type="checkbox" name="rankeado" id="rankeado" value="1" {{ ($Model == null || $Model->rankeado) ? "checked" : "" }}>
                                        <label for="rankeado">¿Rankeado?</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <label for="imagen" class="form-label col-form-label">Imagen de Fondo:</label>
                                    <div class="file-loading">
                                        <input id="imagen" name="imagen" data-preview="{{ $Model != null ? $Model->imagen : ""}}"  type="file">
                                    </div>
                                    <div id="kartik-file-errors"></div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <strong class="text-sm text-uppercase">Categorías Seleccionadas</strong>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <table id="table{{$ViewName}}" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Categoría</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @if($Model != null && count($Model->torneoCategorias) > 0)
                                            @foreach($Model->torneoCategorias as $q)
                                                <tr>
                                                    <td><input type="hidden" name="categorias[]" data-multiple="{{ $q->multiple }}" data-info="{{ ($q->categoriaSimple != null ? $q->categoriaSimple->nombre : "-") }}"
                                                    value="{{ $q->multiple ? ($q->categoria_simple_id."-".$q->categoria_dupla_id) : $q->categoria_simple_id }}">{{ $q->categoriaSimple->nombre }}</td>
                                                    <td width="50" class="text-center"><button type="button" class="btn btn-danger btn-delete btn-xs"><i class="fa fa-trash"></i></button></td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary pull-right">{{ ($Model != null ? "Modificar" : "Registrar") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="{{ asset('auth/plugins/file-input/js/fileinput.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('auth/plugins/file-input/js/locales/es.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    $(function(){
        const $image = $("#imagen");
        $image.fileinput({
            showPreview: false,
            showUpload: false,
            language: "es",
            initialPreviewAsData: true,
            initialPreviewFileType: 'image',
            elErrorContainer: '#kartik-file-errors', allowedFileExtensions: ["jpg", "png", "gif"],
            initialPreview:  $image.attr('data-preview') !== "" ? [$image.attr('data-preview')] : [],
            initialPreviewConfig: [{caption: "Imagen de Fondo"}]
        });
        setTimeout(function (){ $("form").find("input[type=text]").first().focus().select(); }, 500);
        const $btnAgregarCategoria = $("#btnAgregarCategoria"), $btnRegistrarCategoria = $("#btnRegistrarCategoria");
        const $table = $("#table{{$ViewName}}"), $id = parseInt($("#id").val());
        const $valor_set = $("#valor_set"); const $categoria = $("#categoria_id"); const $formato = $("#formato_id");

        $valor_set.inputmask("numeric", { min: 0, digits: 0, removeMaskOnSubmit: true, groupSeparator: ",", groupSize: 3 });

        let $arrayTotal = [];
        if($id !== 0)
        {
            $table.find("tbody tr").each(function (i, v){
                const $tr = $(v);
                const $keys = $tr.find("td:first-child > input[type=hidden]").val();
                const $names = $tr.find("td:first-child").text();
                const $multiple = parseInt($tr.find("td:first-child > input[type=hidden]").attr("data-multiple"));
                $arrayTotal.push({key: $keys, value: $names, multiple: $multiple});
            });
        }

        $btnRegistrarCategoria.on("click", function (){
            invocarModal(`/auth/categoria/partialView/0`, function ($modal) {
                if ($modal.attr("data-reload") === "true"){
                    actionAjax(`/auth/categoria/list-json`, null, "GET", function (data) {
                        if(data.data){
                            $categoria.html("");
                            $.each(data.data, function (i, e) {
                                const max = Math.max.apply(Math, data.data.map(function (o) { return o.id; }));
                                $categoria.append(`<option value="${e.id}" data-info="${e.nombre}" data-multiple="${e.dupla}" ${max === e.id ? "selected" : ""}>${e.nombre}</option>`);
                            });
                        }
                    });
                }
            });
        });

        const $btnAgregarFormato = $("#btnAgregarFormato");
        $btnAgregarFormato.on("click", function (){
            invocarModal(`/auth/formato/partialView/0`, function ($modal) {
                if ($modal.attr("data-reload") === "true"){
                    actionAjax(`/auth/formato/list-json`, null, "GET", function (data) {
                        if(data.data){
                            $formato.html("");
                            $formato.append(`<option value="">Seleccione</option>`);
                            $.each(data.data, function (i, e) {
                                const max = Math.max.apply(Math, data.data.map(function (o) { return o.id; }));
                                $formato.append(`<option value="${e.id}" data-info="${e.nombre}" ${max === e.id ? "selected" : ""}>${e.nombre}</option>`);
                            });
                        }
                    });
                }
            });
        });

        $btnAgregarCategoria.on("click", function ()
        {
            const $this = $categoria;

            if($this.find("option:selected").val() !== "" && !isNaN($this.find("option:selected").val()))
            {
                let $multiple = parseInt($this.find("option:selected").attr("data-multiple"));
                let $keys = `${ $multiple ? ($this.find("option:selected").val()+'-'+$this.find("option:selected").val()) : $this.find("option:selected").val()}`;
                let $names = `${$this.find("option:selected").attr("data-info")}`;
                if($arrayTotal.length === 0){
                    $table.append(`<tr><td><input type="hidden" name="categorias[]" value="${$keys}">${$names}</td><td width="50" class="text-center"><button type="button" class="btn btn-danger btn-delete btn-xs"><i class="fa fa-trash"></i></button></td></tr>`);
                    $arrayTotal.push({key: $keys, value: $names, multiple: $multiple });
                }else{
                    if(!$arrayTotal.some(x => x.key === $keys)){
                        $table.append(`<tr><td><input type="hidden" name="categorias[]" value="${$keys}">${$names}</td><td width="50" class="text-center"><button type="button" class="btn btn-danger btn-delete btn-xs"><i class="fa fa-trash"></i></button></td></tr>`);
                        $arrayTotal.push({key: $keys, value: $names, multiple: $multiple});
                    }else Toast.fire({icon: 'error', title: `La Categoría ${$names} ya ha sido agregada anteriormente.`});
                }
            }else{ Toast.fire({icon: 'error', title: 'Por favor, seleccione una dupla categorica vàlida.'});}


            /*const $checkeds = $("input[name=categoria_multiple_id]:checked");
            if([1,2].includes($checkeds.length)) {

                let $keys = null; let $names = null;

                if ($checkeds.length === 1){ $keys = `${$($checkeds[0]).val()}-${$($checkeds[0]).val()}`;$names = `${$($checkeds[0]).attr("data-info")}`;
                }else if($checkeds.length === 2){
                    let $array = [];
                    $.each($checkeds, function (i,v){$array.push({ key: parseInt($(v).val()), value: $(v).attr("data-info") });});
                    $keys  =  $array.map(u => u.key).join('-'); $names =  $array.map(u => u.value).join(' + ');
                }

                if($arrayTotal.length === 0){
                    $table.append(`<tr><td><input type="hidden" name="categorias[]" value="${$keys}">${$names}</td><td width="50" class="text-center"><button type="button" class="btn btn-danger btn-delete btn-xs"><i class="fa fa-trash"></i></button></td></tr>`);
                    $arrayTotal.push({key: $keys, value: $names});
                    $.each($checkeds, function (i,v){$(v).prop("checked", false);});
                }else{
                    if(!$arrayTotal.some(x => x.key === $keys))
                    {
                        $table.append(`<tr><td><input type="hidden" name="categorias[]" value="${$keys}">${$names}</td><td width="50" class="text-center"><button type="button" class="btn btn-danger btn-delete btn-xs"><i class="fa fa-trash"></i></button></td></tr>`);
                        $arrayTotal.push({key: $keys, value: $names});
                        $.each($checkeds, function (i,v){$(v).prop("checked", false);});
                    }else Toast.fire({icon: 'error', title: 'Esta dupla categorica, ya ha sido agregada anteriormente.'});
                }
            }else Toast.fire({icon: 'error', title: 'Por favor, seleccione una dupla categorica vàlida.'});*/

            /*if($checkeds.length === 2)
            {
                let $array = [];
                $.each($checkeds, function (i,v){
                    const $item = $(v);
                    $array.push({ key: parseInt($item.val()), value: $item.attr("data-info") });
                });

                if($array.length > 0)
                {
                    const $keys  =  $array.map(u => u.key).join('-');
                    const $names =  $array.map(u => u.value).join(' + ');
                    if($arrayTotal.length === 0){
                        $table.append(`<tr><td><input type="hidden" name="categorias[]" value="${$keys}">${$names}</td><td width="50" class="text-center"><button type="button" class="btn btn-danger btn-delete btn-xs"><i class="fa fa-trash"></i></button></td></tr>`);
                        $arrayTotal.push({key: $keys, value: $names});
                        $.each($checkeds, function (i,v){$(v).prop("checked", false);});
                    }else{
                        if(!$arrayTotal.some(x => x.key === $keys))
                        {
                            $table.append(`<tr><td><input type="hidden" name="categorias[]" value="${$keys}">${$names}</td><td width="50" class="text-center"><button type="button" class="btn btn-danger btn-delete btn-xs"><i class="fa fa-trash"></i></button></td></tr>`);
                            $arrayTotal.push({key: $keys, value: $names});
                            $.each($checkeds, function (i,v){$(v).prop("checked", false);});
                        }else Toast.fire({icon: 'error', title: 'Esta dupla categorica, ya ha sido agregada anteriormente.'});
                    }
                }
            }else Toast.fire({icon: 'error', title: 'Por favor, seleccione una dupla categorica vàlida.'});*/
        });

        const $zonas_id = $("#zonas_id");
        $zonas_id.select2({
            ajax: {
                url: "/auth/zona/list-json", dataType: "json", type: "GET", delay: 250,
                data: function(params) {
                    return {
                        select2: true,
                        nombre: params.term
                    };
                },
                processResults: function(data) {
                    return { results: data.data };
                },
                cache: true
            }
        });

        if($zonas_id.attr("data-initial").length !== 0) {
            $zonas_id.val($zonas_id.attr("data-initial").split(",")).trigger("change");
        }

        const $btnAgregarZona = $("#btnAgregarZona");
        $btnAgregarZona.on("click", function(){
            invocarModal(`/auth/zona/partialView/0`);
        });

        $table.on("click", ".btn-delete", function (){
            const $tr = $(this).closest("tr");
            const $key = $tr.find("input[type=hidden]").val();
            $arrayTotal = $arrayTotal.filter(x => { return x.key !== $key});
            $tr.remove();
        });

        OnSuccess{{$ViewName}} = (data) => onSuccessForm(data, $("form#frm{{$ViewName}}"), $("#modal{{$ViewName}}"));
        OnFailure{{$ViewName}} = () => onFailureForm();
    })
</script>

