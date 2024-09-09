<div class="modal fade" id="modalImportar"  role="dialog" tabindex="-1" data-backdrop="static" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importar Jugadores</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <div class="col-sm-12">
                        <label for="file_excel">Archivo (.xlsx)</label>
                        <input type="file" name="file_excel" id="file_excel" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                        <span data-valmsg-for="file_excel"></span>
                    </div>
                </div>
                <div class="form row mt-3">
                    <div class="col-sm-12">
                        <a href="{{ asset('upload/file/Importar Jugadores.xlsx') }}" class="btn btn-success btn-xs w-100" download><i class="fa fa-file-excel"></i> Descargar Plantilla</a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                <button type="button" id="btnImportarExcel" class="btn btn-primary pull-right">Importar</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('auth/pages/'.strtolower($ViewName).'/ajax/partialViewImportExcel.min.js?v='.\Carbon\Carbon::now()->toDateTimeString()) }}"></script>


