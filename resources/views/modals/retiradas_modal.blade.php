
<!-- Modal -->
<div class = "modal fade" id = "retiradasModal" aria-labelledby = "retiradasModalLabel" aria-hidden = "true">
    <div class = "modal-dialog modal-dialog-centered" role = "document">
        <div class = "modal-content">
            <div class = "modal-header">
                <h6 class = "modal-title header-color" id = "retiradasModalLabel"></h6>
                <button type = "button" class = "close" data-dismiss = "modal" aria-label = "Close">
                    <span aria-hidden = "true">&times;</span>
                </button>
            </div>
            <form action = "" method = "POST">
                <div class = "modal-body">
                    <div class = "container">
                        @csrf
                        <input id = "id" name = "id" type = "hidden" />
                        <div class = "row">
                            <div class = "col-12">
                                <label for = "descr" class = "custom-label-form">Descrição: *</label>
                                <input id = "descr" name = "descr" class = "form-control" autocomplete = "off" type = "text" onkeyup = "contar_char(this, 32)" />
                                <span class = "custom-label-form tam-max"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class = "d-flex">
                    <button type = "button" class = "btn btn-target mx-auto mb-4 px-5" onclick = "validar()">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type = "text/javascript" language = "JavaScript">
    function retirar(id) {
        if (gradeGlobal) {
            
        }
        $("#retiradasModal").modal();
    }
</script>