<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\LogController;
use App\Models\Atribuicoes;

class AtribuicoesController extends Controller {
    public function verMaximo(Request $request) {
        $query = $request->tipo == "referencia" ? "
            SELECT IFNULL(SUM(qtd), 0) AS saldo
                    
            FROM (
                SELECT
                    CASE
                        WHEN (es = 'E') THEN qtd
                        ELSE qtd * -1
                    END AS qtd,
                    id_produto

                FROM estoque
            ) AS estq

            WHERE id_produto = ".$request->id."
        " : "
            SELECT IFNULL(SUM(qtd), 0) AS saldo
                        
            FROM (
                SELECT
                    CASE
                        WHEN (es = 'E') THEN qtd
                        ELSE qtd * -1
                    END AS qtd,
                    referencia

                FROM estoque

                JOIN produtos
                    ON produtos.id = estoque.id_produto
            ) AS estq

            WHERE referencia IN (
                SELECT referencia
                FROM produtos
                WHERE id = ".$request->id."
            )
        ";
        return DB::select(DB::raw($query))[0]->saldo;
    }

    public function salvar(Request $request) {
        if (!sizeof(
            DB::table("produtos")
                ->where($request->produto_ou_referencia_chave, $request->produto_ou_referencia_valor)
                ->where("lixeira", 0)
                ->get()
        )) return 404;
        if (sizeof(
            DB::table("atribuicoes")
                ->where("produto_ou_referencia_valor", $request->produto_ou_referencia_valor)
                ->where("produto_ou_referencia_chave", $request->produto_ou_referencia_chave)
                ->where("lixeira", 0)
                ->get()
        )) return 403;
        $linha = new Atribuicoes;
        $linha->pessoa_ou_setor_chave = $request->pessoa_ou_setor_chave;
        $linha->pessoa_ou_setor_valor = $request->pessoa_ou_setor_valor;
        $linha->produto_ou_referencia_chave = $request->produto_ou_referencia_chave;
        $linha->produto_ou_referencia_valor = $request->produto_ou_referencia_valor;
        $linha->qtd = $request->qtd;
        $linha->save();
        $log = new LogController;
        $log->inserir("C", "atribuicoes", $linha->id);
        return 201;
    }

    public function excluir(Request $request) {
        $linha = Atribuicoes::find($request->id);
        $linha->lixeira = 1;
        $linha->save();
        $log = new LogController;
        $log->inserir("D", "atribuicoes", $linha->id);
    }

    public function mostrar(Request $request) {
        return json_encode(
            DB::table("atribuicoes")
                ->select(
                    "id",
                    "produto_ou_referencia_valor",
                    "qtd"
                )
                ->where("pessoa_ou_setor_valor", $request->id)
                ->where("produto_ou_referencia_chave", $request->tipo)
                ->where("lixeira", 0)
                ->get()
        );
    }
}