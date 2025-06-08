using System;
using TodoList.Data;
using TodoList.Models;

namespace TodoList.Controllers
{
    public class PostTarefaController
    {
        public void CriarTarefa(string titulo, string nomeUsuario)
        {
            var novaTarefa = new TodoTask
            {
                Id = BancoDados.Tarefas.Count > 0 ? BancoDados.Tarefas[^1].Id + 1 : 1,
                Titulo = titulo,
                Status = false,
                CriadoEm = DateTime.Now,
                ConcluidoEm = null,
                Usuario = nomeUsuario
            };

            BancoDados.Tarefas.Add(novaTarefa);
            BancoDados.SalvarDados();
        }
    }
}
