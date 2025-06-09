using Microsoft.AspNetCore.Mvc;
using TodoList.Data;
using TodoList.Models;
using System.Collections.Generic;
using System.Linq;
using System; // Adicione este using para DateTime, se ainda não tiver

namespace TarefasApi.Controllers
{
    [ApiController]
    [Route("api/[controller]")] // A rota base será /api/Tarefas
    public class TarefasController : ControllerBase
    {
        // Método GET: Para listar tarefas
        [HttpGet("{tipoUsuario}/{nomeUsuario}")]
        public ActionResult<List<TodoTask>> Get(string tipoUsuario, string nomeUsuario)
        {
            BancoDados.CarregarDados();

            if (tipoUsuario.ToLower() == "admin")
                return Ok(BancoDados.Tarefas);
            else
                return Ok(BancoDados.Tarefas.Where(t => t.Usuario == nomeUsuario).ToList());
        }

        // NOVO MÉTODO POST: Para criar uma nova tarefa
        // A rota será /api/Tarefas (pois o [Route] já define a base)
        [HttpPost]
        public IActionResult Post([FromBody] TodoTask novaTarefa)
        {
            // O BancoDados.CarregarDados() precisa ser chamado antes de manipular a lista
            BancoDados.CarregarDados();

            // Atribuir um novo ID único para a tarefa
            // Isso é importante para evitar colisões e ter um ID válido
            novaTarefa.Id = BancoDados.Tarefas.Any() ? BancoDados.Tarefas.Max(t => t.Id) + 1 : 1;

            // Definir a data de criação (se não vier do front-end ou se quiser garantir que seja a do servidor)
            // Se o PHP já envia 'CriadoEm', pode remover essa linha. Se não, é uma boa garantia.
            novaTarefa.CriadoEm = DateTime.Now; 
            
            // Definir ConcluidoEm como null para tarefas novas
            novaTarefa.ConcluidoEm = null;

            // Adicionar a nova tarefa à lista e salvar no arquivo JSON
            BancoDados.Tarefas.Add(novaTarefa);
            BancoDados.SalvarDados();

            // Retornar um status 201 Created com a URL da nova tarefa e a tarefa criada
            // O "nameof(Get)" aponta para o método Get acima (com {tipoUsuario}/{nomeUsuario}), 
            // então precisamos de um ID e dos parâmetros de usuário para a rota, 
            // ou podemos simplificar para Ok() se não precisarmos da URL exata da nova tarefa.
            // Para simplificar, vou mudar para Ok(novaTarefa). Se o PHP só precisa do status 200, Ok() é suficiente.
            // Se precisar de 201 Created, ajuste a rota para o método Get apropriado.
            // Por enquanto, vamos usar Ok().
            return Ok(novaTarefa); 
        }


        // Método DELETE: Para apagar uma tarefa
        [HttpDelete("{id}")]
        public IActionResult Delete(int id, [FromQuery] string tipoUsuario, [FromQuery] string nomeUsuario)
        {
            BancoDados.CarregarDados();

            var tarefa = BancoDados.Tarefas.FirstOrDefault(t => t.Id == id);
            if (tarefa == null)
                return NotFound("Tarefa não encontrada.");

            if (tipoUsuario == "admin" || tarefa.Usuario.ToLower() == nomeUsuario.ToLower()) // Comparação case-insensitive
            {
                BancoDados.Tarefas.Remove(tarefa);
                BancoDados.SalvarDados();
                return Ok("Tarefa apagada com sucesso.");
            }

            return Forbid("Você não tem permissão para apagar esta tarefa.");
        }

        // Método PUT: Para atualizar o status de uma tarefa
        [HttpPut("{id}/status")]
        public IActionResult AtualizarStatus(int id, [FromQuery] string tipoUsuario, [FromQuery] string nomeUsuario)
        {
            BancoDados.CarregarDados();

            var tarefa = BancoDados.Tarefas.FirstOrDefault(t => t.Id == id);
            if (tarefa == null)
                return NotFound("Tarefa não encontrada.");

            if (tipoUsuario == "admin" || tarefa.Usuario.ToLower() == nomeUsuario.ToLower()) // Comparação case-insensitive
            {
                tarefa.Status = !tarefa.Status;
                // Se a tarefa foi concluída, define ConcluidoEm. Se for desfeita, limpa.
                tarefa.ConcluidoEm = tarefa.Status ? DateTime.Now : (DateTime?)null; 
                
                BancoDados.SalvarDados();
                return Ok("Status atualizado com sucesso.");
            }

            return Forbid("Você não tem permissão para atualizar esta tarefa.");
        }
    }
}