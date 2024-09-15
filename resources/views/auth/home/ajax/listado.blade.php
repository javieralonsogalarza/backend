@foreach($lists as $q)
    <div class="card" data-id="{{ $q->id }}" data-info="{{ $q->nombre }}">
        <div class="header-card">
            <div class="title-header"><h5>{{ $q->nombre }}</h5></div>
        </div>
        <div class="body-card">
            <div class="row row-center-vertical">
                <div class="col-md-5">
                    <img src="{{ ($q->imagen_path != null && $q->imagen_path != "") ? ('/img/'.$q->imagen_path) : "/upload/image/default.png" }}"
                         style="width: 100%" class="img-responsive" alt="{{ $q->nombre }}">
                </div>
                <div class="col-md-7">
                    <ul>
                        <li><i class="fa fa-phone" title="Teléfono"></i> <span>{{ $q->telefono != null ? $q->telefono : "-" }}</span></li>
                        <li><i class="fa fa-envelope" title="E-mail"></i> <span>{{ $q->email != null ? $q->email : "-" }}</span></li>
                        <li><i class="fa fa-calendar" title="Fecha Creación"></i> <span>{{ $q->created_at }}</span></li>
                    </ul>
                    @if(!$q->deleted_at)
                        <div class="list-actions text-right">
                            <button type="button" class="btn btn-primary btn-sm btn-update" title="Editar"><i class="fa fa-edit"></i></button>
                            <button type="button" class="btn btn-danger btn-sm btn-delete" title="Eliminar"><i class="fa fa-trash"></i></button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach
