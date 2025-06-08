using System;

namespace TodoList.Controllers
{
    public class MenuController
    {
        public void MostrarMenu(string tipoUsuario)
        {
            Console.Clear();
            Console.WriteLine($"Você está logado como: {tipoUsuario}\n");

            if (tipoUsuario == "admin")
            {
                Console.WriteLine("1 - Listar todas as tarefas");
                Console.WriteLine("2 - Criar tarefa");
                Console.WriteLine("3 - Apagar tarefa");
                Console.WriteLine("4 - Atualizar status da tarefa");
                Console.WriteLine("0 - Sair");
            }
            else
            {
                Console.WriteLine("1 - Listar suas tarefas");
                Console.WriteLine("2 - Criar tarefa");
                Console.WriteLine("3 - Apagar tarefa");
                Console.WriteLine("4 - Atualizar status da tarefa");
                Console.WriteLine("0 - Sair");
            }

            Console.Write("\nEscolha uma opção: ");
        }
    }
}