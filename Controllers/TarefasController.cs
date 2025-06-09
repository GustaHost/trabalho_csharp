using Microsoft.AspNetCore.Mvc;
using TodoList.Data;
using TodoList.Models;
using System.Collections.Generic;
using System.Linq;

namespace TarefasApi.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class TarefasController : ControllerBase
    {
        [HttpGet("{tipoUsuario}/{nomeUsuario}")]
        public ActionResult<List<TodoTask>> Get(string tipoUsuario, string nomeUsuario)
        {
            BancoDados.CarregarDados();

            if (tipoUsuario.ToLower() == "admin")
                return Ok(BancoDados.Tarefas);
            else
                return Ok(BancoDados.Tarefas.Where(t => t.Usuario == nomeUsuario).ToList());
        }


        [HttpDelete("{id}")]
        public IActionResult Delete(int id, [FromQuery] string tipoUsuario, [FromQuery] string nomeUsuario)
        {
            BancoDados.CarregarDados();

            var tarefa = BancoDados.Tarefas.FirstOrDefault(t => t.Id == id);
            if (tarefa == null)
                return NotFound("Tarefa não encontrada.");

            if (tipoUsuario == "admin" || tarefa.Usuario == nomeUsuario)
            {
                BancoDados.Tarefas.Remove(tarefa);
                BancoDados.SalvarDados();
                return Ok("Tarefa apagada com sucesso.");
            }

            return Forbid("Você não tem permissão para apagar esta tarefa.");
        }

        [HttpPut("{id}/status")]
        public IActionResult AtualizarStatus(int id, [FromQuery] string tipoUsuario, [FromQuery] string nomeUsuario)
        {
            BancoDados.CarregarDados();

            var tarefa = BancoDados.Tarefas.FirstOrDefault(t => t.Id == id);
            if (tarefa == null)
                return NotFound("Tarefa não encontrada.");

            if (tipoUsuario == "admin" || tarefa.Usuario == nomeUsuario)
            {
                tarefa.Status = !tarefa.Status;
                BancoDados.SalvarDados();
                return Ok("Status atualizado com sucesso.");
            }

            return Forbid("Você não tem permissão para atualizar esta tarefa.");
        }


    }
}

