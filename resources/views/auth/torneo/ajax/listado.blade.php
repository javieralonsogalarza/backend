@foreach($lists as $q)
    <div class="card" data-id="{{ $q->id }}" data-info="{{ $q->nombre }}"
    style="{{ $landing ? ('background-image: url('.$q->imagen.')') : '' }}  ">
        <div class="header-card pt-2">
            <div class="title-header"><h5 style="color: #000 !important;">{{ $q->nombre }}</h5></div>
            <div class="etiqueta {{ $q->estado_texto == "Próximamente" ? "bg-gray" : ($q->estado_texto == "En transcurso" ? "bg-warning" : ($q->estado_texto == "Cancelado" ? "bg-dander" : ($q->estado_texto == "Finalizado" ? "bg-success" : ""))) }}"
                 style="padding: 0.2rem 0.5rem;width: auto; position: absolute;top: 0;right: 0;font-size: 13px; color: #f1f1f1 !important;">
                {{ $q->estado_texto }}
            </div>
        </div>
        <div class="body-card">
            <div class="row row-center-vertical">
                <div class="col-md-12">
                    <ul>
                        <li><i class="fa fa-calendar" title="Fecha Inicio"></i> Fecha Inicio: <span>{{ $q->fecha_inicio_texto }}</span></li>
                        <li><i class="fa fa-calendar" title="Fecha Final"></i> Fecha Final: <span>{{ $q->fecha_final_texto }}</span></li>
                        <li><i class="fa fa-file" title="Formato"></i> Formato: <span>{{ $q->formato != null ? $q->formato->nombre : "-" }}</span></li>
                    </ul>
                    @if(!$q->deleted_at)
                        <div class="list-actions text-left">
                            <button type="button" class="btn btn-primary btn-sm btn-detail" style="{{ $landing ? 'padding: 0px 5px;' : '' }}" title="ver detalle"><i class="fa fa-eye"></i></button>
                            @if(!$landing)
                                <button type="button" class="btn btn-primary btn-sm btn-update" title="Editar"><i class="fa fa-edit"></i></button>
                                <button type="button" class="btn btn-danger btn-sm btn-delete" title="Eliminar"><i class="fa fa-trash"></i></button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach
