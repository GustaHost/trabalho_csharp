using System;

namespace TodoList.Controllers
{
    public class UserAccessController
    {
        public string SelecionarTipoUsuario()
        {
            Console.WriteLine("Selecione o tipo de acesso:");
            Console.WriteLine("1 - Usuário");
            Console.WriteLine("2 - Administrador");
            Console.Write("Opção: ");

            var opcao = Console.ReadLine();

            if (opcao == "1")
                return "usuario";
            else if (opcao == "2")
                return "admin";
            else
                return null;
        }
    }
}
